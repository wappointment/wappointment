<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\Carbon;
use Wappointment\Models\Status;
use Wappointment\System\Status as SystemStatus;
use Wappointment\ClassConnect\VtzUtil;

class CalendarParser
{
    protected $url;
    protected $type;
    protected $source;
    protected $content;
    protected $statusEvents;
    protected $timezone = false;
    protected $staff_id = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $content, $staff_id = 0)
    {
        $this->url = $url;
        $this->source = md5($this->url);
        $this->content = $content;
        $this->staff_id = $staff_id;
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
            $carbon_start = $this->vcalDateToCarbon((string) $vevent->DTSTART, $vevent);
            $carbon_end = $this->vcalDateToCarbon((string) $vevent->DTEND, $vevent);


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

            //$start_at_record = $this->vcalDateToDbFormat((string) $vevent->DTSTART);
            $start_at_record = $this->getFormatedDate($carbon_start);
            $end_at_record = $this->getFormatedDate($carbon_end);


            $dataInsert = [
                'start_at' => $start_at_record,
                'end_at' => $end_at_record,
                'recur' => $recur,
                'source' => $this->source,
                'type' => STATUS::TYPE_BUSY,
                'eventkey' => md5($this->source . (string) $vevent->UID . (string) $vevent->CREATED),
                'options' => $this->getOptions($vevent, $until, $recur),
                'staff_id' => $this->staff_id
            ];

            $this->statusEvents->push($dataInsert);
            $uids->push((string) $vevent->UID);
        }

        return [
            'detected' => count($this->statusEvents),
            'deleted' => $this->deleteRemovedEvents($uids),
            'inserted' => $this->insertIgnoreOrUpsert($this->statusEvents->toArray()),
            'duration' => round(microtime(true) - $start, 2)
        ];
    }

    private function insertIgnoreOrUpsert($array)
    {
        return version_compare(SystemStatus::dbVersion(), '2.0.3') >= 0 ? Status::upsert($array) : Status::insertIgnore($array);
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

    private function vcalDateToCarbon($vcalDateTimeString, $vevent = null)
    {
        $timezoneTemp = '';

        if (empty($this->timezone)) {
            if (!empty($vevent) && !empty($vevent->DTSTART['TZID'])) {
                $timezoneTemp = $this->findTimezone($vevent->DTSTART['TZID']->getValue());
                $this->timezone = $timezoneTemp; //we assign the first value found to the global tz
            }
        } else {
            $timezoneTemp = $this->timezone;
        }


        if (!empty($vevent) && !empty($vevent->DTSTART) && !empty($vevent->DTSTART['TZID']) && $timezoneTemp != $vevent->DTSTART['TZID']->getValue()) {
            $timezoneTemp = $this->findTimezone($vevent->DTSTART['TZID']->getValue());
        }

        return Carbon::parse($vcalDateTimeString, $timezoneTemp);
    }

    private function getFormatedDate($carbonTime, $format = WAPPOINTMENT_DB_FORMAT)
    {
        return $carbonTime->tz('UTC')->format($format);
    }

    private function setTimezone($vcalObject)
    {
        $this->timezone = $this->tryGetTz($vcalObject);
        if (empty($this->timezone)) {
            $timezonekey = 'X-WR-TIMEZONE';
            $this->timezone = $this->findTimezone((string) $vcalObject->$timezonekey);
        }
    }

    protected function tryGetTz($vobject)
    {
        if (!empty($vobject->VTIMEZONE)) {
            $tzobject = $vobject->VTIMEZONE->getTimeZone();
            if (!empty($tzobject->getName())) {
                return $tzobject->getName();
            }
        }
        return '';
    }

    protected function findTimezone($timezone_string)
    {
        if (!empty($timezone_string)) {
            $tzobject = VtzUtil::getTimeZone((string) $timezone_string);
            if (!empty($tzobject) && !empty($tzobject->getName())) {
                return $tzobject->getName();
            }
        }

        return '';
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
