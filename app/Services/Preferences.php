<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;

class Preferences
{
    public $preferences = [];

    public function __construct()
    {
        $this->preferences = WPHelpers::getStaffOption('preferences', WPHelpers::userId());
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
