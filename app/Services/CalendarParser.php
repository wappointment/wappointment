<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\Carbon;
use Wappointment\Models\Status;

class CalendarParser
{
    protected $url;
    protected $type;
    protected $source;
    protected $content;
    protected $statusEvents;
    protected $timezone = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $content)
    {
        $this->url = $url;
        $this->source = md5($this->url);
        $this->content = $content;
    }

    public function handle()
    {
        $start = microtime(true);
        if (empty($this->content)) {
            throw new \Exception('Calendar is not accessible (' . $this->url . ')', 1);
        }

        $this->statusEvents = \WappointmentLv::collect([]);

        $vcalendar = VcardReader::read($this->content, VcardReader::OPTION_IGNORE_INVALID_LINES);

        // skip calendar that has no events
        if (!isset($vcalendar) || !is_object($vcalendar->VEVENT) || count($vcalendar->VEVENT) <= 0) {
            return false;
        }

        $this->setTimezone($vcalendar);


        $uids = \WappointmentLv::collect([]);
        foreach ($vcalendar->VEVENT as $vevent) {
            if ($start > ($start + 60)) {
                throw new \WappointmentException(" 60s Timeout reached parsing calendar", 1);
            }
            $until = null;
            $recur = STATUS::RECUR_NOT;
            $carbon_end = $this->vcalDateToCarbon((string) $vevent->DTEND, $vevent);

            /*             echo 'Original: ' . (string)$vevent->DTEND . ' ' .
            $carbon_end->toRfc822String() . ' tz' . $this->timezone . "\n";
                        echo 'Converted: ' . $carbon_end->copy()->tz('UTC')->toDateTimeString()
                        . ' tz' . $this->timezone . "\n"; */
            if ($vevent->RRULE) { // recurrent events
                $until = $this->getUntil($vevent);
                // skip recurring events that don't recurre anymore
                if (!empty($until) && $until->lt(Carbon::yesterday())) {
                    continue;
                }
                $recur = $this->getRecur($vevent);
            } else { //punctual events
                // skip events that are passed since yesterday
                if ($carbon_end->lt(Carbon::yesterday())) {
                    continue;
                }
            }

            $start_at_record = $this->vcalDateToDbFormat((string) $vevent->DTSTART);
            $end_at_record = $this->getFormatedDate($carbon_end);

            /* when we have a recurring event that occurs the whole day and is of
            the type recurs every first monday
                we need to record it from midnight til midnight otherwise we have
                issues recurring it properly
            $remainder = ($carbon_end->timestamp -
            $this->vcalDateToCarbon((string)$vevent->DTSTART)->timestamp) % 86400;
             if ($recur > STATUS::RECUR_NOT && $remainder === 0) {
                $start_at_record = $this->vcalDateToDbFormat((string)$vevent->DTSTART, 'Y-m-d 00:00');
                $end_at_record = $this->getFormatedDate($carbon_end, 'Y-m-d 00:00');
            } */

            $dataInsert = [
                'start_at' => $start_at_record,
                'end_at' => $end_at_record,
                'recur' => $recur,
                'source' => $this->source,
                'type' => STATUS::TYPE_BUSY,
                'eventkey' => md5($this->source . (string) $vevent->UID . (string) $vevent->CREATED),
                'options' => $this->getOptions($vevent, $until, $recur),
                'staff_id' => Settings::get('activeStaffId')
            ];

            $this->statusEvents->push($dataInsert);
            $uids->push((string) $vevent->UID);
        }

        return [
            'detected' => count($this->statusEvents),
            'deleted' => $this->deleteRemovedEvents($uids),
            'inserted' => Status::upsert($this->statusEvents->toArray()),
            'duration' => round(microtime(true) - $start, 2)
        ];
    }

    private function deleteRemovedEvents($uids)
    {
        $deleted = 0;
        if (count($uids->unique()) === count($uids)) {
            $deleted = Status::where('source', $this->source)
                ->whereNotIn('eventkey', $this->statusEvents->pluck('eventkey'))
                ->delete();
        }
        return $deleted;
    }

    private function getUntil($vevent)
    {
        return empty($vevent->RRULE->getParts()['UNTIL']) ?
            null : $this->vcalDateToCarbon($vevent->RRULE->getParts()['UNTIL'], $vevent);
    }

    private function getRecur($vevent)
    {
        return empty($vevent->RRULE->getParts()['FREQ']) ?
            STATUS::RECUR_NOT : $this->getFrequency($vevent->RRULE->getParts()['FREQ']);
    }

    private function vcalDateToDbFormat(String $vcalDateTimeString, $format = WAPPOINTMENT_DB_FORMAT)
    {
        return $this->getFormatedDate($this->vcalDateToCarbon($vcalDateTimeString), $format);
    }

    protected function findTimezone($timezone_string)
    {
        return empty($timezone_string) ? '' : DateTime::isKnownTimezone($timezone_string);
    }

    private function vcalDateToCarbon($vcalDateTimeString, $vevent = null)
    {
        $timezone = '';
        if (empty($this->timezone)) {
            if ($vevent !== false) {
                $this->timezone = $timezone = $this->findTimezone($vevent->DTSTART['TZID']->getValue());
            }
        } else {
            $timezone = $this->timezone;
        }

        if (empty($timezone) && !empty($vevent->DTSTART)) {
            $timezone = $this->findTimezone($vevent->DTSTART['TZID']->getValue());
        }

        return Carbon::parse($vcalDateTimeString, $timezone);
    }

    private function getFormatedDate($carbonTime, $format = WAPPOINTMENT_DB_FORMAT)
    {
        return $carbonTime->tz('UTC')->format($format);
    }

    private function setTimezone($vcalObject)
    {

        $timezonekey = 'X-WR-TIMEZONE';
        $this->timezone = $this->findTimezone((string) $vcalObject->$timezonekey);
    }

    private function getFrequency($frequency)
    {
        switch (strtoupper($frequency)) {
            case 'DAILY':
                return STATUS::RECUR_DAILY;
            case 'WEEKLY':
                return STATUS::RECUR_WEEKLY;
            case 'MONTHLY':
                return STATUS::RECUR_MONTHLY;
            case 'YEARLY':
                return STATUS::RECUR_YEARLY;
            default:
                return STATUS::RECUR_NOT;
        }
    }

    private function getOptions($vevent, $until, $recur)
    {
        $options = [];
        if (!empty((string) $vevent->SUMMARY)) {
            $options['title'] = (string) $vevent->SUMMARY;
        }
        if ($recur > STATUS::RECUR_NOT) {
            if (!empty($until)) {
                $options['until'] = $until;
            }
            if (!empty($vevent->RRULE)) {
                if (!empty($vevent->RRULE->getParts()['BYDAY'])) {
                    $options['byday'] = $this->convertDays($vevent->RRULE->getParts()['BYDAY']);
                }

                if (!empty($vevent->RRULE->getParts()['BYMONTHDAY'])) {
                    $options['bymonthday'] = (int) $vevent->RRULE->getParts()['BYMONTHDAY'];
                }

                if (!empty($vevent->RRULE->getParts()['INTERVAL'])) {
                    $options['interval'] = (int) $vevent->RRULE->getParts()['INTERVAL'];
                }

                $options['origin_tz'] = $this->timezone;
                $options['origin_start'] = $this->vcalDateToCarbon((string) $vevent->DTSTART, $vevent)
                    ->format(WAPPOINTMENT_DB_FORMAT);
            }

            /*$remainder = ($carbon_end->timestamp -
            $this->vcalDateToCarbon((string)$vevent->DTSTART)->timestamp) % 86400;
             if ($recur > STATUS::RECUR_NOT && $remainder === 0) {
                $start_at_record = $this->vcalDateToDbFormat((string)$vevent->DTSTART, 'Y-m-d 00:00');
                $end_at_record = $this->getFormatedDate($carbon_end, 'Y-m-d 00:00');
            }
            $options['fullday']
            */
        }

        return empty($options) ? '' : json_encode($options);
    }

    private function convertDays($days)
    {
        if (!is_array($days)) {
            $days = [$days];
        }
        $newArray = [];
        foreach ($days as $day) {
            $dayconverted = $this->getDayNumber($day);
            if (is_array($dayconverted) && count($days) == 1) {
                return $dayconverted;
            }
            $newArray[] = $dayconverted;
        }
        return $newArray;
    }

    private function getDayNumber($dayPrefix)
    {
        switch ($dayPrefix) {
            case 'MO':
                return 1;
            case 'TU':
                return 2;
            case 'WE':
                return 3;
            case 'TH':
                return 4;
            case 'FR':
                return 5;
            case 'SA':
                return 6;
            case 'SU':
                return 0;
            default:
                return $this->convertDayPrefix($dayPrefix);
        }
    }

    private function convertDayPrefix($dayPrefix)
    {
        if (strlen($dayPrefix) === 3) {
            return [
                'each' => (int) substr($dayPrefix, 0, 1),
                'day' => $this->getDayNumber(substr($dayPrefix, -2, 2))
            ];
        } elseif (strlen($dayPrefix) === 4) {
            return [
                'each' => 'last',
                'day' => $this->getDayNumber(substr($dayPrefix, -2, 2))
            ];
        }
    }
}
