<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;

class Calendar
{
    public $url = '';
    public $staff_id = false;
    public $response = null;
    public $calendar_id = null;
    public $calendar_logs = [];

    public function __construct($calendar_url, $staff_id)
    {
        $this->url = $calendar_url;
        $this->calendar_id = md5($this->url);
        $this->staff_id = $staff_id;
        $this->calendar_logs = WPHelpers::getStaffOption('calendar_logs');
    }
    /* old
        last-calendar-id
        last-calendar-parsed
        last-calendar-process
        last-calendar-checked
    WPHelpers::deleteStaffOption('last-calendar-id', $staff_id);
    WPHelpers::deleteStaffOption('last-calendar-checked', $staff_id);
    WPHelpers::deleteStaffOption('last-calendar-parsed', $staff_id);
    WPHelpers::deleteStaffOption('last-calendar-process', $staff_id);
    */

    public function refetch()
    {
        //make sure we can process it fully again
        $this->log('last-hash', false);
        return $this->fetch();
    }
    public function fetch()
    {

        $start = microtime(true);

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $this->url, [
            'curl' => [
                CURLOPT_URL => $this->url,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
            ]
        ]);

        // Only headers are downloaded here.

        if ($response->getStatusCode() != 200) {
            throw new \WappointmentException('Cannot connect to the calendar');
        }

        $body = $response->getBody();

        if (strpos($response->getHeaderLine('content-type'), 'text/calendar') === false) {
            throw new \WappointmentException('Cannot recognise a calendar file ');
        }
        $original_content = $body->getContents();
        $body_string = $this->cleanContent($original_content);
        $result = false;

        $this->log('last-checked', time());

        if ($this->hasChanged($body_string)) {
            $parser = new CalendarParser($this->url, $original_content, $this->staff_id);

            $this->log('last-parser', $parser->handle());
            $this->log('last-hash', md5($body_string), false);
            $this->log('last-parsed', time(), false);
            $result = true;
        }
        $this->log('last-duration', round(microtime(true) - $start, 2));

        return $result;
    }


    private function getCalendarLogs()
    {
        if (empty($this->calendar_logs[$this->calendar_id])) {
            return [
                'last-checked' => false,
                'last-hash' => false,
                'last-parsed' => false,
                'last-duration' => false,
                'last-parser' => false,
            ];
        }
        return $this->calendar_logs[$this->calendar_id];
    }

    private function log($property, $value, $save = true)
    {
        if (empty($this->calendar_logs[$this->calendar_id])) {
            $this->calendar_logs[$this->calendar_id] = $this->getCalendarLogs();
        }

        $this->calendar_logs[$this->calendar_id][$property] = $value;

        if ($save) {
            WPHelpers::setStaffOption('calendar_logs', $this->calendar_logs, $this->staff_id);
        }
    }

    private function cleanContent($content)
    {
        preg_match('/^PRODID:.*\\n/m', $content, $matches);
        if (count($matches) > 0) {
            if (strpos(strtolower($matches[0]), 'google') !== false) {
                return $this->googleClean($content);
            }
        }
        return $content;
    }

    // used to then store a md5 version of the ics making sure it is cached
    private function googleClean($in)
    {
        $in = preg_replace('/^DTSTAMP:.*\\n/m', '', $in);
        $in = preg_replace('/^SUMMARY:.*\\n/m', '', $in);
        $in = preg_replace('/^ACTION:.*\\n/m', '', $in);
        $in = preg_replace('/^ATTENDEE:.*\\n/m', '', $in);
        return preg_replace('/^TRIGGER:.*\\n/m', '', $in);
    }

    private function hasChanged($content)
    {
        return md5($content) != $this->calendar_logs[$this->calendar_id]['last-hash'];
    }
}
