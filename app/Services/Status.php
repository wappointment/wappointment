<?php

namespace Wappointment\Services;

use Wappointment\Models\Status as MStatus;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Helpers\Translations;
// @codingStandardsIgnoreFile
class Status
{
    private static $diff = 0;

    public static function delete($id)
    {
        $statusObject = Mstatus::find($id);
        if ($statusObject && !empty($statusObject->source)) {
            $status = $statusObject->update(['muted' => 1]);
        } else {
            do_action('wappointment_admin_status_deleted', $id);
            $status = Mstatus::destroy($id);
        }
        if ($status) {
            (new \Wappointment\Services\Availability($statusObject->staff_id))->regenerate();
            return $statusObject;
        }
    }

    private static function getAllowedStaffId($staff_id = null)
    {
        return $staff_id = CurrentUser::isAdmin() ? $staff_id : CurrentUser::calendarId();
    }

    public static function free($start, $end, $timezone, $request, $staff_id = null)
    {
        if (CurrentUser::canCreateFreeBlock()) {
            return self::create($start, $end, $timezone, MStatus::TYPE_FREE, $request, static::getAllowedStaffId($staff_id));
        }
        throw new \WappointmentException(Translations::get('error_saving'), 1);
    }

    public static function busy($start, $end, $timezone, $staff_id = null)
    {
        if (CurrentUser::canCreateBusyBlock()) {
            return self::create($start, $end, $timezone, MStatus::TYPE_BUSY, null, static::getAllowedStaffId($staff_id));
        }
        throw new \WappointmentException(Translations::get('error_saving'), 1);
    }

    protected static function create($start, $end, $timezone, $type, $request = null, $staff_id = null)
    {
        $arrayCreate = [
            'start_at' => static::tryConvertToUTCFormatted($start, $timezone),
            'end_at' => static::tryConvertToUTCFormatted($end, $timezone),
            'type' => (int) $type,
            'staff_id' => $staff_id
        ];

        $resultObject = MStatus::create($arrayCreate);

        if ($resultObject) {
            (new \Wappointment\Services\Availability($staff_id))->regenerate();
            do_action('wappointment_admin_status_created', $resultObject, $request);
        }

        return $resultObject;
    }

    public static function tryConvertToUTCFormatted($timevalue, $timezone)
    {
        if (is_numeric($timevalue)) { //unixtimestamp
            return Carbon::createFromTimestamp($timevalue)->format(WAPPOINTMENT_DB_FORMAT . ':00');
        } else {
            return DateTime::converTotUtc($timevalue, $timezone);
        }
    }

    public static function expand($recurringBusy, $until = false)
    {
        $punctualEvents = [];

        if ($until === false) {
            $until =  (new \Wappointment\Services\Availability())->getMaxTs();
        }

        foreach ($recurringBusy as $recurring) {
            $recurring_until = !empty($recurring->options['until']) ? $recurring->options['until'] : false;
            $until_end_recurring = $recurring_until !== false && $recurring_until < $until ? $recurring_until : $until;
            $punctualEvents = array_merge($punctualEvents, self::generateRecurring($recurring, $until_end_recurring));
        }
        return $punctualEvents;
    }

    private static function generateRecurring($statusRecurrent, $until)
    {

        $newEvents = [];
        $from = time();
        $i = 0;

        self::$diff = $statusRecurrent->end_at->timestamp - $statusRecurrent->start_at->timestamp;
        if (self::$diff <= 0) {
            return $newEvents;
        }
        $next = self::getNext($statusRecurrent, $from, $until, true);

        while ($next) {
            if (static::canAdd($next)) {
                $newEvents[] = $next;
            }

            $from = $next->end_at->timestamp;
            $i++;
            if ($i > 300) {
                throw new \WappointmentException('Error Infinite loop', 1);
            }
            $next = self::getNext($next, $from, $until + 1);
            // +1 is for when we generate recurrent in the weekly view, making sure that the last day is not forgotten when daily recurring

            if (empty($next) || $next->start_at->timestamp < $from) {
                break; //if increment doesn't occur we just give up
            }
        }

        return $newEvents;
    }

    public static function canAdd($next)
    {
        if (isset($next->options['exdate'])) {
            foreach ($next->options['exdate'] as $exdate) {
                if (Carbon::createFromTimestamp($exdate)->timestamp === $next->start_at->timestamp) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function getPreviousFrom($from, $recur)
    {
        $day_in_sec = (3600 * 24);
        switch ($recur) {
            case MStatus::RECUR_DAILY:
                return $from - $day_in_sec;
            case MStatus::RECUR_WEEKLY:
                return $from - ($day_in_sec * 7);
            case MStatus::RECUR_MONTHLY:
            case MStatus::RECUR_YEARLY:
            default:
                return $from;
        }
    }

    private static function getNext($statusRecurrent, $from, $until, $first_recurring = false)
    {

        if ($first_recurring) {
            $from = self::getPreviousFrom($from, $statusRecurrent->recur);
        }

        if ($from < $until) {
            $start_at =  $statusRecurrent->start_at;
            switch ($statusRecurrent->recur) {
                case MStatus::RECUR_DAILY:
                    return self::getNextDaily($statusRecurrent, $start_at, $from, $until);
                case MStatus::RECUR_WEEKLY:
                    return self::getNextWeekly($statusRecurrent, $start_at, $from, $until);
                case MStatus::RECUR_MONTHLY:
                    return self::getNextMonthly($statusRecurrent, $start_at, $from, $until);
                case MStatus::RECUR_YEARLY:
                    return self::getNextYearly($statusRecurrent, $start_at, $from, $until);
                default:
                    return false;
            }
        }

        return false;
    }

    private static function getNextDaily($statusRecurrent, $start_at, $from, $until)
    {
        $interval = self::getInterval($statusRecurrent);

        $daysAdded = $start_at->timestamp > $from ? 0 : Carbon::createFromTimestamp($from)->diffInDays($start_at);
        $start_at->addDays($daysAdded);

        while ($start_at->timestamp < $from) {
            $start_at->addDays($interval);
        }
        if ($start_at->timestamp > $until) {
            return false;
        }

        return self::punctualStatusEvent($statusRecurrent, $start_at);
    }

    private static function getNextWeekly($statusRecurrent, $start_at, $from, $until)
    {
        $interval = self::getInterval($statusRecurrent);

        $days_accepted = static::getByDay($statusRecurrent);

        $daysAdded = $start_at->timestamp > time() ? 0 : Carbon::now()->diffInDays($start_at);

        $start_at->tz($statusRecurrent->options['origin_tz'])->addDays($daysAdded);

        $weekWhenEnter = $start_at->weekNumberInMonth;

        $i = 0;
        //while these conditions are not met we go to the next record to find a match
        while ($i < 50 && ($start_at->timestamp < $from || !in_array($start_at->dayOfWeek, $days_accepted))) {
            $start_at_copy = $start_at->copy();

            $start_at->addDay();

            if ($weekWhenEnter != $start_at->weekNumberInMonth && $interval > 1) {
                if ($start_at->weekNumberInMonth != 1 && $start_at->weekNumberInMonth != 0) {
                    $start_at = $start_at_copy->addWeeks($interval)->startOfWeek()->copy();
                }

                $weekWhenEnter = $start_at->weekNumberInMonth;
            }
            $i++;
        }

        $origintimedate = Carbon::parse(
            $statusRecurrent->options['origin_start'],
            $statusRecurrent->options['origin_tz']
        );
        $copyOriginTZ = $start_at->setTime($origintimedate->hour, $origintimedate->minute)->copy();

        $start_at = $copyOriginTZ->tz('UTC')->copy();

        if ($start_at->timestamp > $until) {
            return false;
        }


        return self::punctualStatusEvent($statusRecurrent, $start_at);
    }

    private static function getNextMonthly($statusRecurrent, $start_at, $from, $until)
    {
        $interval = self::getInterval($statusRecurrent);
        $dayofthemonth = self::getMonthDay($statusRecurrent);

        if (!$dayofthemonth) {
            $dayofthemonth = self::getByDayMonthly($statusRecurrent);
        }

        if (!$dayofthemonth) {
            $dayofthemonth = $start_at->day;
        }


        $diffInSeconds = $start_at->timestamp > time() ? 0 : Carbon::now()->diffInSeconds($start_at);

        if ($diffInSeconds > 0) {
            $start_at->addSeconds($diffInSeconds);
            if ($dayofthemonth !== false) {
                $start_at = self::setDayOfTheMonth($start_at, $dayofthemonth, $statusRecurrent);
            }
        }

        $i = 0;
        while ($start_at->timestamp < $from) {
            $start_at->tz($statusRecurrent->options['origin_tz'])
                ->addMonths($interval)
                ->startOfMonth();

            if ($dayofthemonth !== false) {
                $start_at = self::setDayOfTheMonth($start_at, $dayofthemonth, $statusRecurrent);
            }

            if ($i > 30) { // fail safe
                throw new \WappointmentException("Error handling recurrent event", 1);
            }
            $i++;
        }
        if ($start_at->timestamp > $until) {
            return false;
        }

        return self::punctualStatusEvent($statusRecurrent, $start_at);
    }

    /**
     * This function manage the generation of punctual events from recurrent ones based on rules such as
     * monday and thursday every 3 weeks
     * 1st and  last monday of every months
     * etc
     *
     * @param [type] $carbon
     * @param [type] $dayofthemonth
     * @param [type] $statusRecurrent
     * @return void
     */
    private static function setDayOfTheMonth($carbon, $dayofthemonth, $statusRecurrent)
    {
        //1 - get original time
        $hour = $carbon->hour;
        $minute = $carbon->minute;

        $carbonNewStart = Carbon::createFromTimestamp($carbon->timestamp, $statusRecurrent->options['origin_tz']);
        //2 - change day (which will go to a midnight value)
        if (is_array($dayofthemonth)) {
            $i = 0;
            if ($dayofthemonth['each'] == 'last') {
                $carbonNewStart->lastOfMonth($dayofthemonth['day']);
            } else {
                $carbonNewStart->next($dayofthemonth['day']);

                //$carbon->next($dayofthemonth['day']);
                while ($carbonNewStart->weekOfMonth != $dayofthemonth['each']) {
                    $carbonNewStart->next($dayofthemonth['day']);

                    $i++;
                    if ($i > 20) {
                        throw new \WappointmentException("Error handling recurrent event2", 1);
                    }
                }
            }
        } else {
            $carbonNewStart->day = $dayofthemonth;
        }

        $carbonNewStart->hour = $hour;
        $carbonNewStart->minute = $minute;

        return $carbonNewStart->tz('UTC')->copy();
    }

    private static function getNextYearly($statusRecurrent, $start_at, $from, $until)
    {
        $interval = self::getInterval($statusRecurrent);

        $yearsAdded = $start_at->timestamp > time() ? 0 : Carbon::now()->diffInYears($start_at);
        $start_at->addYears($yearsAdded);

        while ($start_at->timestamp < $from) {
            $start_at->addYears($interval);
        }
        if ($start_at->timestamp > $until) {
            return false;
        }

        return self::punctualStatusEvent($statusRecurrent, $start_at);
    }

    private static function punctualStatusEvent($statusRecurrent, $start_at)
    {
        //1 - original time diff between start and end in second

        $newCopy = $statusRecurrent->replicate();


        //new
        $origintimedate = Carbon::parse(
            $statusRecurrent->options['origin_start'],
            $statusRecurrent->options['origin_tz']
        );
        $copyOriginTZ = $start_at->tz(
            $statusRecurrent->options['origin_tz']
        )->setTime($origintimedate->hour, $origintimedate->minute)->copy();
        $newCopy->start_at = $copyOriginTZ->tz('UTC')->copy();

        $newCopy->end_at = Carbon::createFromTimestamp($start_at->timestamp + self::$diff);
        $newCopy->id = $statusRecurrent->id;
        return $newCopy;
    }

    private static function getInterval($statusRecurrent)
    {
        return empty($statusRecurrent->options['interval']) ? 1 : $statusRecurrent->options['interval'];
    }

    private static function getMonthDay($statusRecurrent)
    {
        return empty($statusRecurrent->options['bymonthday']) ? false : $statusRecurrent->options['bymonthday'];
    }

    protected static function getByDay($statusRecurrent)
    {
        return empty($statusRecurrent->options['byday']) ? [] : $statusRecurrent->options['byday'];
    }
    private static function getByDayMonthly($statusRecurrent)
    {
        return static::fixArrayBydays(static::getByDay($statusRecurrent));
    }

    /**
     *  e don't accept array of bydays for monthly recurrence [1,4,5] this only applies to weekly recurrence
     *  so if it occurs we return the first value only
     * @param [type] $byday
     * @return void
     */
    protected static function fixArrayBydays($byday)
    {
        return is_array($byday) && isset($byday[0]) ? $byday[0] : (empty($byday) ? false : $byday);
    }
}
