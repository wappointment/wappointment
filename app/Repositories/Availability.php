<?php

namespace Wappointment\Repositories;

use Wappointment\Managers\Central;
use Wappointment\Services\Staff;
use Wappointment\Managers\Service as ManageService;
use Wappointment\Services\Settings;
use Wappointment\ClassConnect\Carbon;

class Availability extends AbstractRepository
{
    public $cache_key = 'availability';

    public function query()
    {
        return apply_filters('wappointment_front_availability', [
            'staffs' => Staff::get(),
            'week_starts_on' => Settings::get('week_starts_on'),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'min_bookable' => Settings::get('hours_before_booking_allowed'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'now' => (new Carbon())->format('Y-m-d\TH:i:00'),
            'buffer_time' => Settings::get('buffer_time'),
            'services' => ManageService::all(),
            'site_lang' => substr(get_locale(), 0, 2),
            'custom_fields' => Central::get('CustomFields')::get(),
        ]);
    }
}
