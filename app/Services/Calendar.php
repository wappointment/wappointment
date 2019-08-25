<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;

class Calendar
{
    public $url = '';
    public $staff_id = false;
    public $response = null;

    public function __construct($calendar_url, $staff_id)
    {
        $this->url = $calendar_url;
        $this->staff_id = $staff_id;
    }

    public function fetch()
    {
        WPHelpers::setStaffOption('last-calendar-process-duration', '', $this->staff_id);
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
            throw new \WappointmentException('Cannot recognise a calendar file');
        }
        $original_content = $body->getContents();
        $body_string = $this->cleanContent($original_content);
        $results = false;
        $calendarProcess = [];
        WPHelpers::setStaffOption('last-calendar-checked', time(), $this->staff_id);
        if ($this->hasChanged($body_string)) {
            $parser = new CalendarParser($this->url, $original_content, $this->staff_id);
            $calendarProcess['parser'] = $parser->handle();
            WPHelpers::setStaffOption('last-calendar-id', md5($body_string), $this->staff_id);
            WPHelpers::setStaffOption('last-calendar-parsed', time(), $this->staff_id);
        }
        $calendarProcess['duration'] = round(microtime(true) - $start, 2);
        WPHelpers::setStaffOption('last-calendar-process', $calendarProcess, $this->staff_id);
        return $results;
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
        return md5($content) != WPHelpers::getStaffOption('last-calendar-id', $this->staff_id);
    }
}
