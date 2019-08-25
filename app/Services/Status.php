<?php

namespace Wappointment\Services;

use Wappointment\Models\Status as MStatus;
use Wappointment\ClassConnect\Carbon;

class Status
{
    private static $diff = 0;
    private static $debug = 0;
    public static function delete($id)
    {
        $statusObject = Mstatus::find($id);
        if ($statusObject && !empty($statusObject->source)) {
            $status = $statusObject->update(['muted' => 1]);
        } else {
            $status = Mstatus::destroy($id);
        }
        if ($status) {
            (new \Wappointment\Services\Availability())->regenerate();
            return $statusObject;
        }
    }

    public static function free($start, $end, $timezone)
    {
        return self::create($start, $end, $timezone, MStatus::TYPE_FREE);
    }

    public static function busy($start, $end, $timezone)
    {
        return self::create($start, $end, $timezone, MStatus::TYPE_BUSY);
    }

    protected static function create($start, $end, $timezone, $type)
    {
        $result = MStatus::create(
            [
                'start_at' => DateTime::converTotUtc($start, $timezone),
                'end_at' => DateTime::converTotUtc($end, $timezone),
                'type' => (int) $type,
                //'source' => '',
                //'eventkey' => ''
            ]
        );
        if ($result) {
            (new \Wappointment\Services\Availability())->regenerate();
        }

        return $result;
    }

    public static function expand($recurringBusy, $until = false)
    {
        $punctualEvents = [];
        //echo 'until ' . Carbon::createFromTimestamp($until)->toDayDateTimeString() . "\n";
        foreach ($recurringBusy as $recurring) {
            $punctualEvents = array_merge($punctualEvents, self::generateRecurring($recurring, $until));
        }
        //dd('endpunctual');
        return $punctualEvents;
    }

    private static function generateRecurring($statusRecurrent, $until)
    {
        $newEvents = [];
        $from = time();
        $i = 0;
        self::$diff = $statusRecurrent->end_at->timestamp - $statusRecurrent->start_at->timestamp;
        $next = self::getNext($statusRecurrent, $from, $until);
        while ($next) {
            $newEvents[] = $next;
            $from = $next->end_at->timestamp;
            $i++;
            if ($i > 300) {
                throw new \WappointmentException('Error Infinite loop', 1);
            }
            $next = self::getNext($next, $from, $until);
        }

        return $newEvents;
    }

    private static function getNext($statusRecurrent, $from, $until)
    {
        if ($from < $until) {

            //$start_at = $statusRecurrent->start_at->timestamp < time() ? Carbon::now() : $statusRecurrent->start_at;
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
            }
        }

        return false;
    }

    private static function getNextDaily($statusRecurrent, $start_at, $from, $until)
    {
        $interval = self::getInterval($statusRecurrent);

        $daysAdded = $start_at->timestamp > time() ? 0 : Carbon::now()->diffInDays($start_at);
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
        $days_accepted = $statusRecurrent->options['byday'];


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

        $origintimedate = Carbon::parse($statusRecurrent->options['origin_start'], $statusRecurrent->options['origin_tz']);
        $copyOriginTZ = $start_at->setTime($origintimedate->hour, $origintimedate->minute)->copy();

        //if ($statusRecurrent->options['title'] == 'Test wonder') echo 'Origin ' . ' full date ' . $copyOriginTZ->toDayDateTimeString() . "\n";
        $start_at = $copyOriginTZ->tz('UTC')->copy();

        /*       if ($statusRecurrent->options['title'] == 'Test wonder') {
            echo $statusRecurrent->options['title'] . "\n";
            echo 'Origin UTC ' . ' full date ' . $copyOriginTZ->toDayDateTimeString() . "\n";
            echo 'Day of week ' . $start_at->dayOfWeek . ' full date ' . $start_at->toDayDateTimeString() . "\n";
            echo '---------------------------------------------'  . "\n";
        } */


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
            $dayofthemonth = self::getByDay($statusRecurrent);
        }

        $diffInSeconds = $start_at->timestamp > time() ? 0 : Carbon::now()->diffInSeconds($start_at);

        if ($diffInSeconds > 0) {
            $start_at->addSeconds($diffInSeconds);
            $start_at = self::setDayOfTheMonth($start_at, $dayofthemonth, $statusRecurrent);
        }


        while ($start_at->timestamp < $from) {
            $start_at->tz($statusRecurrent->options['origin_tz'])->addMonths($interval)->startOfMonth();

            $start_at = self::setDayOfTheMonth($start_at, $dayofthemonth, $statusRecurrent);
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
                        dd('gone baby gone');
                    }
                }
            }
        } else {

            $carbonNewStart->day = $dayofthemonth;
        }

        $origintimedate = Carbon::parse($statusRecurrent->options['origin_start'], $statusRecurrent->options['origin_tz']);
        $carbonNewStart->hour = $origintimedate->hour;
        $carbonNewStart->minute = $origintimedate->minute;

        $carbon  = $carbonNewStart->tz('UTC')->copy();

        return $carbon;
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
        $origintimedate = Carbon::parse($statusRecurrent->options['origin_start'], $statusRecurrent->options['origin_tz']);
        $copyOriginTZ = $start_at->tz($statusRecurrent->options['origin_tz'])->setTime($origintimedate->hour, $origintimedate->minute)->copy();
        $newCopy->start_at = $copyOriginTZ->tz('UTC')->copy();

        //old
        //$newCopy->start_at = $start_at->setTime($statusRecurrent->start_at->hour, $statusRecurrent->start_at->minute);


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

    private static function getByDay($statusRecurrent)
    {
        return empty($statusRecurrent->options['byday']) ? false : $statusRecurrent->options['byday'];
    }
}
