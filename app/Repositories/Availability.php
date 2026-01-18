<?php

namespace Wappointment\Repositories;

use Wappointment\Managers\Central;
use Wappointment\Services\Staff;
use Wappointment\Managers\Service as ManageService;
use Wappointment\Services\Settings;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Helpers\Site;

class Availability extends AbstractRepository
{
    public $cache_key = 'availability';

    public function query()
    {
        $staff_timezone = \Wappointment\Services\VersionDB::atLeast(\Wappointment\Services\VersionDB::CAN_CREATE_SERVICES) 
         ? (\Wappointment\Services\Calendars::all()->first()->options['timezone'] ?? 'UTC')
         : Settings::getStaff('timezone', 'UTC');

        return apply_filters('wappointment_front_availability', [
            'staffs' => Staff::get(),
            'week_starts_on' => Settings::get('week_starts_on'),
            'frontend_weekstart' => Settings::get('frontend_weekstart'),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'min_bookable' => Settings::get('hours_before_booking_allowed'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'now' => (new Carbon())->setTimezone($staff_timezone)->format('Y-m-d\\TH:i:00'),
            'buffer_time' => Settings::get('buffer_time'),
            'services' => ManageService::all(),
            'site_lang' => Site::lang(),
            'custom_fields' => Central::get('CustomFields')::get(),
            'availability_fluid' => Settings::get('availability_fluid'),
            'more_st' => Settings::get('more_st'),
            'starting_each' => Settings::get('starting_each'),
        ]);
    }
}
