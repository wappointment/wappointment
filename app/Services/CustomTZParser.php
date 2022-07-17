<?php

namespace Wappointment\Services;

use stdClass;
use Wappointment\ClassConnect\Carbon;

trait CustomTZParser
{
    protected $customTZ = null;

    public function isCustomTZ()
    {
        return !is_null($this->customTZ);
    }

    /**
     * Fixes issue with exchange and non standard timezone id
     * https://answers.microsoft.com/en-us/outlook_com/forum/all/outlookcom-ics-time-zone/299f0580-a53e-4971-8f52-dbb92c730ebb
     *
     */
    private function isCustomTimezone($vcalObject)
    {
        $found_customtimezone = false;
        if (!empty($vcalObject->VTIMEZONE) && method_exists($vcalObject->VTIMEZONE, 'children')) {
            foreach ($vcalObject->VTIMEZONE->children() as $childrenTZ) {
                if ($childrenTZ->name === 'TZID' && $childrenTZ->getValue() === 'Customized Time Zone') {
                    $found_customtimezone = true;
                    $this->initCustomTZ($childrenTZ);
                }
                if (in_array($childrenTZ->name, ['STANDARD', 'DAYLIGHT']) && $found_customtimezone) {
                    $this->setCustomTZProps($childrenTZ);
                }
            }
        }


        return !is_null($this->customTZ);
    }

    protected function initCustomTz($childrenTZ)
    {
        $this->customTZ = new stdClass;
        $this->customTZ->name = $childrenTZ->getValue();
    }

    protected function setCustomTZProps($childrenTZ)
    {
        foreach ($childrenTZ->children() as $subproperty) {
            if (in_array($subproperty->name, ['TZOFFSETFROM', 'TZOFFSETTO', 'RRULE'])) {
                $standardOrDST = $childrenTZ->name;
                if (!isset($this->customTZ->$standardOrDST)) {
                    $this->customTZ->$standardOrDST = new stdClass;
                }
                $prop = $subproperty->name;
                $this->customTZ->$standardOrDST->$prop = $subproperty->getValue();
            }
        }
    }

    public function getTimezoneNameFromDateString($vcalDateTimeString)
    {
        $dst = $this->isCustomTZDST($vcalDateTimeString);
        $offSetToApply = $dst ? $this->customTZ->DAYLIGHT->TZOFFSETTO :
            $this->customTZ->STANDARD->TZOFFSETTO;
        return  $this->tnameFromOFfset($offSetToApply, $dst);
    }

    public function parseCustomTZ($vcalDateTimeString, $vevent = null)
    {

        if (
            !empty($vevent) && !empty($vevent->DTSTART) && !empty($vevent->DTSTART['TZID']) &&
            !empty((string)$vevent->DTSTART['TZID']) && (string)$vevent->DTSTART['TZID'] !== $this->customTZ->name
        ) {
            return $this->standardParsing($vcalDateTimeString, $vevent);
        }

        return Carbon::parse($vcalDateTimeString, $this->getTimezoneNameFromDateString($vcalDateTimeString));
    }

    public function tnameFromOFfset($offset, $dst)
    {
        $sign = substr($offset, 0, 1);
        $hours = substr($offset, 1, 2);
        $minutes = substr($offset, 3, 2);
        $seconds = ($hours * 3600) + ($minutes * 60);
        return timezone_name_from_abbr('', intval($sign . (string)$seconds), $dst);
    }

    public function getOffsetCustomTZ($vcalDateTimeString)
    {
        return $this->isCustomTZDST($vcalDateTimeString) ?
            $this->customTZ->DAYLIGHT->TZOFFSETTO :
            $this->customTZ->STANDARD->TZOFFSETTO;
    }

    public function getDataFromRule($rule)
    {
        foreach (explode(';', $rule) as $part) {
            $keyval = explode('=', $part);
            if ($keyval[0] == 'BYDAY') {
                $data['day'] = $this->convertDayCodeToString($keyval[1]);
            }
            if ($keyval[0] == 'BYMONTH') {
                $data['month'] = $this->convertMonthCodeToString($keyval[1]);
            }
        }

        return $data;
    }

    public function convertDayCodeToString($daycode)
    {
        switch ($daycode) {
            case '-1SU':
                return 'last sunday';
        }
    }

    public function convertMonthCodeToString($monthcode)
    {

        return strlen($monthcode) === 1 ? '0' . $monthcode : $monthcode;
        switch ($monthcode) {
            case '3':
                return 'march';
            case '10':
                return 'october';
        }
    }

    public function getMonthAndDayStart($year, $rule)
    {
        $data = $this->getDataFromRule($rule);
        $carbon = Carbon::createFromFormat('Y-m', $year . '-' . $data['month']);
        return $carbon->modify($data['day'] . ' of this month');
    }

    public function isCustomTZDST($vcalDateTimeString)
    {
        $carbon = Carbon::parse($vcalDateTimeString);

        if (empty($this->customTZ->DAYLIGHT->RRULE) || $this->customTZ->STANDARD->RRULE) {
            return false;
        }
        $dstLowerLimit = $this->getMonthAndDayStart($carbon->year, $this->customTZ->DAYLIGHT->RRULE);
        $dstHigherLimit = $this->getMonthAndDayStart($carbon->year, $this->customTZ->STANDARD->RRULE);

        return $carbon->gt($dstLowerLimit) && $carbon->lt($dstHigherLimit);
    }
}
