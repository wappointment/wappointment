<?php

namespace Wappointment\Repositories;

use Wappointment\Managers\Central;
use Wappointment\Services\CurrentUser;
use Wappointment\WP\Staff;

class CalendarsBack extends AbstractRepository
{
    use MustRefreshAvailability;

    public $cache_key = 'calendars_back';

    public function query()
    {
        $calendarsQry = Central::get('CalendarModel')::orderBy('sorting')->with(['services']);
        /*if (!CurrentUser::isAdmin()) {
            $calendarsQry->where('id', CurrentUser::calendarId());
        }*/

        $calendars = $calendarsQry->fetch();
        $staffs = [];
        foreach ($calendars->toArray() as $calendar) {
            $staffs[] = (new Staff($calendar))->fullData();
        }
        $this->refreshAvailability();
        return $staffs;
    }
}
