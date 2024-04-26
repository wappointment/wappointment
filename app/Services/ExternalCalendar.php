<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\RakitValidator;
use Wappointment\Helpers\Translations;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Models\Calendar;

class ExternalCalendar
{
    private $isLegacy = true;
    private $staff = null;

    public function __construct($staff_id = false)
    {
        $this->isLegacy = !VersionDB::atLeast(VersionDB::CAN_CREATE_SERVICES);
        if (!$this->isLegacy) {
            $this->staff = Calendar::findOrFail($staff_id);
        }
    }

    public function disconnect($calendar_key)
    {

        $calurls = $this->getCalUrls();
        if (empty($calendar_key) || empty($calurls[$calendar_key])) {
            throw new \WappointmentException("Cannot find calendar", 1);
        }
        $this->deleteConnectedStatus($calendar_key);

        //unset calendars
        unset($calurls[$calendar_key]);
        $this->saveUrls($calurls);
        $this->updateCalendarLogs($calendar_key);
        $this->refreshCalendars(true);
        return ['message' => __('Calendar has been disconnected', 'wappointment')];
    }

    protected function updateCalendarLogs($calendar_key)
    {
        if ($this->isLegacy) {
            $calendar_logs = WPHelpers::getStaffOption('calendar_logs');
            if (isset($calendar_logs[$calendar_key])) {
                unset($calendar_logs[$calendar_key]);
                WPHelpers::setStaffOption('calendar_logs', $calendar_logs);
            }
        } else {
            $options = $this->staff->options;
            $calendar_logs = !empty($options['calendar_logs']) ? $options['calendar_logs'] : [];
            if (isset($calendar_logs[$calendar_key])) {
                unset($calendar_logs[$calendar_key]);
                $options['calendar_logs'] = $calendar_logs;
                return $this->staff->update([
                    'options' => $options
                ]);
            }
        }
    }

    protected function deleteConnectedStatus($calendar_key)
    {
        if ($this->isLegacy) {
            //remove events
            \Wappointment\Models\Status::where('source', $calendar_key)->delete();
        } else {
            \Wappointment\Models\Status::where('source', $calendar_key)->where('staff_id', $this->staff->id)->delete();
        }
    }

    public function refreshCalendars($force = false)
    {

        (new Availability($this->staff))->syncAndRegen($force);

        return [
            'message' => Translations::get('element_refreshed'),
        ];
    }

    public function save($calurl)
    {

        if (!$this->calurlValid($calurl)) {
            throw new \WappointmentException(__('Cannot save calendar verify it respects the Ical ics format', 'wappointment'));
        }

        $this->isKnownDomainOrThrow($calurl);

        $calurls = $this->getCalUrls();

        if (isset($calurls[md5($calurl)])) {
            throw new \WappointmentException(__('Calendar already connected', 'wappointment'));
        }
        if (count($calurls) > 2) {
            throw new \WappointmentException('Cannot connect more than 3 calendars');
        }

        $calurls[md5($calurl)] = $calurl;

        $statusSaved = $this->saveUrls($calurls);
        $this->refreshCalendars(true);
        return ['message' => Translations::get('element_saved'), 'status' => $statusSaved];
    }

    protected function saveUrls($calurls)
    {
        return $this->isLegacy ? $this->saveUrlLegacy($calurls) : $this->saveUrlsNew($calurls);
    }

    protected function getCalUrls()
    {
        return $this->isLegacy ? $this->getCurrentUrlsLegacy() : $this->getCurrentUrls();
    }

    protected function getCurrentUrls()
    {
        return $this->staff->getCalendarUrls();
    }

    protected function getCurrentUrlsLegacy()
    {
        $calurls = WPHelpers::getStaffOption('cal_urls');
        if (empty($calurls)) {
            $calurls = [];
        }
        return $calurls;
    }

    protected function saveUrlsNew($calurls)
    {
        $options = $this->staff->options;
        $options['cal_urls'] = $calurls;
        return $this->staff->update([
            'options' => $options
        ]);
    }


    protected function saveUrlLegacy($calurls)
    {
        return WPHelpers::setStaffOption('cal_urls', $calurls);
    }


    protected function calurlValid($value)
    {
        // 1 is url

        $validator = new RakitValidator;

        $validation = $validator->validate(['calurl' => $value], [
            'calurl' => 'required|url:http,https'
        ]);

        if ($validation->fails()) {
            throw new \WappointmentException('The URL is not valid');
        }

        // 2 is reachable
        $result = false;
        try {
            if ($this->isLegacy) {
                $result = (new \Wappointment\Services\Calendar($value, Settings::get('activeStaffId'), true))->refetch();
            } else {
                $result = (new \Wappointment\Services\Calendar($value, $this->staff, false))->refetch();
            }
        } catch (\Exception $e) {
            throw new \WappointmentException($e->getMessage());
        }

        return $result;
    }

    protected function isKnownDomainOrThrow($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        foreach($this->allowedDomains() as $testDomain){
            if($this->endsWith($host, $testDomain)){
                return true;
            }
        }
        throw new \WappointmentException(
                sprintf(
                    'Unknown calendar external domain %s (allowed: %s)', 
                    $host, 
                    implode(',',$this->allowedDomains()
                )
            )
        );
    }

    protected function allowedDomains()
    {
        return [
            'google.com',
            'apple.com',
            'icloud.com',
            'microsoft.com',
            'live.com',
            'outlook.com',
            'calendly.com',
        ];
    }

    protected function endsWith($haystack, $needle) {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

}
