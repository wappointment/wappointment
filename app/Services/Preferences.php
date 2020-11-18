<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;

class Preferences
{
    public $preferences = [
        'cal_duration' => '60',
        'cal_minH' => '7',
        'cal_maxH' => '19',
        'cal_avail_col' => '#f2f2f2',
        'cal_appoint_col' => '#4b6c97',
    ];

    public function __construct()
    {
        $pref = WPHelpers::getStaffOption('preferences', WPHelpers::userId());
        if (!empty($pref)) {
            $this->preferences = $pref;
        }
    }

    public function saveMany($prefs_array)
    {
        foreach ($prefs_array as $name => $value) {
            if (empty($this->preferences[$name]) || $this->preferences[$name] !== $value) {
                $this->preferences[$name] = $value;
            }
        }
        $this->updateRecord();
    }

    public function save($name, $value)
    {
        if (empty($this->preferences[$name]) || $this->preferences[$name] !== $value) {
            $this->preferences[$name] = $value;
            $this->updateRecord();
        }
    }

    public function get($name)
    {
        return empty($this->preferences[$name]) ? false : $this->preferences[$name];
    }

    private function updateRecord()
    {
        WPHelpers::setStaffOption('preferences', $this->preferences, WPHelpers::userId());
    }
}
