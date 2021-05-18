<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Services\Staff;
use Wappointment\WP\WidgetAPI;
use Wappointment\Services\Status;
use Wappointment\Managers\Service as ManageService;
use Wappointment\Managers\Central;
use Wappointment\Models\Service as ModelService;
use Wappointment\Repositories\Availability;

class ViewsData
{
    public function load($key)
    {
        $values = [];
        if (method_exists($this, $key)) {
            $values = $this->$key();
        }

        return apply_filters('wappointment_viewdata_' . $key, $values);
    }


    private function getCalendarsStaff()
    {
        $calendars = Central::get('CalendarModel')::orderBy('sorting')->fetch();
        $staffs = [];
        foreach ($calendars->toArray() as $calendar) {
            $staffs[] = (new \Wappointment\WP\Staff($calendar))->fullData();
        }
        return $staffs;
    }


    private function regav()
    {
        if (VersionDB::canServices()) {
            $calendars = $this->getCalendarsStaff();
            $data = [
                'calendar' => $calendars[0],
                'timezones_list' => DateTime::tz(),
                'calendars' => $calendars,
                'staffs' => Staff::getWP(),
                'staffDefault' => Settings::staffDefaults(),
            ];
        } else {
            $gravatar_img = get_avatar_url(Settings::get('activeStaffId'), ['size' => 40]);

            $data = [
                'regav' => Settings::getStaff('regav'),
                'avb' => Settings::getStaff('availaible_booking_days'),
                'staffs' => Staff::getWP(),
                'activeStaffId' => (int)Settings::get('activeStaffId'),
                'activeStaffAvatar' => Settings::getStaff('avatarId') ? wp_get_attachment_image_src(Settings::getStaff('avatarId'))[0] : $gravatar_img,
                'activeStaffGravatar' => $gravatar_img,
                'activeStaffName' => Staff::getNameLegacy(),
                'activeStaffAvatarId' => Settings::getStaff('avatarId'),
                'timezone' => Settings::getStaff('timezone'),
                'timezones_list' => DateTime::tz(),
                'savedTimezone' => Settings::hasStaff('timezone')
            ];
        }
        return apply_filters('wappointment_back_regav', $data);
    }

    private function staffCustomField()
    {
        return [
            'custom_fields' => WPHelpers::getOption('staff_custom_fields', [])
        ];
    }

    private function serverinfo()
    {
        return [
            'server' => \Wappointment\Services\Server::data()
        ];
    }

    private function calsync()
    {
        return [
            'calurl' => Settings::getStaff('calurl')
        ];
    }

    private function service()
    {
        return [
            'service' => VersionDB::canServices() ? $this->getConvertedDataServiceNewToLegacy() : Service::get()
        ];
    }

    protected function getConvertedDataServiceNewToLegacy()
    {
        $service = ModelService::first();
        if (empty($service)) {
            return [];
        }
        $types = [];
        $address = '';
        $video = '';
        $countries = [];
        if (!empty($service->locations)) {
            foreach ($service->locations as $location) {
                $types[] = $location->options['type'];
                if ($location->options['type'] == 'physical') {
                    $address = $location->options['address'];
                }
                if ($location->options['type'] == 'phone') {
                    $countries = $location->options['countries'];
                }
                if ($location->options['type'] == 'zoom') {
                    $video = $location->options['video'];
                }
            }
        }


        $data = [
            'id' => $service->id,
            'name' => $service->name,
            'duration' => $service->options['durations'][0]['duration'],
            'type' => $types,
            'address' => $address,
            'options' => [
                'countries' => $countries,
                'video' => $video,
            ]
        ];
        if (!empty($service->options['countries'])) {
            $data['options']['countries'] = $service->options['countries'];
            $data['options']['phone_required'] = true;
        }
        return $data;
    }

    private function wizardwidget()
    {

        return [
            'booking_page_id' => (int) Settings::get('booking_page'),
            'widget' => (new WidgetSettings)->get(),
        ];
    }

    private function widget()
    {
        $data = [
            'front_availability' => $this->front_availability(),
            'widget' => (new WidgetSettings)->get(),
            'widgetDefault' => (new WidgetSettings)->defaultSettings(),
            'config' => [
                'service' => Service::get(),
                'approval_mode' => Settings::get('approval_mode'),
            ],
            'bgcolor' => WPHelpers::getThemeBgColor(),
            'more' => get_theme_mods(),
            'widgetFields' => (new \Wappointment\Services\WidgetSettings)->adminFieldsInfo(),
            'booking_page_id' => (int) Settings::get('booking_page'),
            'booking_page_url' => get_permalink((int) Settings::get('booking_page')),
        ];
        if (VersionDB::canServices()) {
            $data['config']['services'] = ManageService::all();
            $data['config']['locations'] = \Wappointment\Models\Location::get();
        }
        return apply_filters('wappointment_back_widget_editor', $data);
    }

    private function widgetcancel()
    {
        return [
            'widget' => (new WidgetSettings)->get(),
            'widgetDefault' => (new WidgetSettings)->defaultSettings(),
            'staff' => Staff::getWP(), //(new \Wappointment\WP\Staff())->toArray()
        ];
    }

    private function calendar()
    {
        $staff_timezone = Settings::getStaff('timezone');
        $services = ManageService::all();

        $data = [
            'week_starts_on' => Settings::get('week_starts_on'),
            'wizard_step' => WPHelpers::getOption('wizard_step'),
            'timezones_list' => DateTime::tz(),
            'service' => Service::get(),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'preferredCountries' => Service::getObject()->getCountries(),
            'buffer_time' => Settings::get('buffer_time'),
            'widget' => (new WidgetSettings)->get(),
            'booking_page_id' => (int) Settings::get('booking_page'),
            'booking_page_url' => get_permalink((int) Settings::get('booking_page')),
            'showWelcome' => Settings::get('show_welcome'),
            'subscribe_email' => Settings::get('email_notifications'),
            'welcome_site' => get_site_url(),
            'preferences' => (new Preferences)->preferences,
            //'is_dotcom_connected' => Settings::getStaff('dotcom'),
            'services' => $services,
            'durations' => ManageService::extractDurations($services),
            'cal_duration' => (new Preferences)->get('cal_duration'),
        ];

        if (VersionDB::canServices()) {
            $data['staff'] = Calendars::all();
            if (!isset($data['staff'][0])) {
                throw new \WappointmentException("There is no active calendar change that in Wappointment > Settings > Calendars", 1);
            }
            $data['timezone'] = $data['staff'][0]->options['timezone'];
            $data['locations'] = \Wappointment\Models\Location::get();
            $data['custom_fields'] = Central::get('CustomFields')::get();
            $data['now'] = (new Carbon())->setTimezone($data['timezone'])->format('Y-m-d\TH:i:00');
            $data['regav'] = $data['staff'][0]->options['regav'];
            $data['availability'] = $data['staff'][0]->availability;
        } else {
            $data['staff'] = (new \Wappointment\WP\StaffLegacy())->toArray();
            $data['regav'] = Settings::getStaff('regav');
            $data['availability'] = WPHelpers::getStaffOption('availability');
            $data['timezone'] = $staff_timezone;
            $data['now'] = (new Carbon())->setTimezone($staff_timezone)->format('Y-m-d\TH:i:00');
            $data['legacy'] = true;
        }

        return  apply_filters('wappointment_back_calendar', $data);
    }

    private function settingsadvanced()
    {
        if (!VersionDB::canServices()) {
            $timezone = Settings::getStaff('timezone');
        } else {
            $staff = Calendars::all();
            if (!isset($staff[0])) {
                throw new \WappointmentException("There is no active calendar change that in Wappointment > Settings > Calendars", 1);
            }
            $timezone = $staff[0]->options['timezone'];
        }

        return [
            'debug' => \WappointmentLv::isTest(),
            'buffer_time' => Settings::get('buffer_time'),
            'front_page_id' => (int) Settings::get('front_page'),
            'front_page' => get_permalink((int) Settings::get('front_page')),
            'front_page_type' => get_post_type((int) Settings::get('front_page')),

            // advanced
            'approval_mode' => Settings::get('approval_mode'),
            'today_formatted' => DateTime::i18nDateTime(time(), $timezone),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'allow_cancellation' => Settings::get('allow_cancellation'),
            'allow_rescheduling' => Settings::get('allow_rescheduling'),
            'week_starts_on' => Settings::get('week_starts_on'),
            'hours_before_booking_allowed' => Settings::get('hours_before_booking_allowed'),
            'hours_before_cancellation_allowed' => Settings::get('hours_before_cancellation_allowed'),
            'hours_before_rescheduling_allowed' => Settings::get('hours_before_rescheduling_allowed'),
            'timezone' => $timezone,
            'config' => [
                'approval_mode' => Settings::get('approval_mode'),
            ],
            //notifications
            'weekly_summary' => Settings::get('weekly_summary'),
            'weekly_summary_day' => Settings::get('weekly_summary_day'),
            'weekly_summary_time' => Settings::get('weekly_summary_time'),
            'daily_summary' => Settings::get('daily_summary'),
            'daily_summary_time' => Settings::get('daily_summary_time'),
            'notify_new_appointments' => Settings::get('notify_new_appointments'),
            'notify_canceled_appointments' => Settings::get('notify_canceled_appointments'),
            'notify_rescheduled_appointments' => Settings::get('notify_rescheduled_appointments'),
            'email_notifications' => Settings::get('email_notifications'),
            'mail_status' => (bool) Settings::get('mail_status'),
            'allow_staff_cf' => Settings::get('allow_staff_cf'),
            'cache' => Settings::get('cache'),

        ];
    }

    private function wizardinit()
    {
        return [
            'admin_email' => wp_get_current_user()->user_email,
            'admin_name' => wp_get_current_user()->display_name,
        ];
    }

    private function wizardlast()
    {
        return [
            'areas' => WidgetAPI::getSidebars(),
            'widgets' => WidgetAPI::getWidgets(),
        ];
    }



    private function settingsmailer()
    {
        return [
            'mail_config' => Settings::get('mail_config'),
            'wp_mail_overidden' => Status::hasSmtpPlugin(),
            'recipient' => wp_get_current_user()->user_email,
        ];
    }

    private function TESTprocessAvail($avails)
    {
        foreach ($avails as &$avail) {
            $avail[0] = Carbon::createFromTimestamp($avail[0])->setTimezone($this->timezone)->format('Y-m-d\TH:i:00 T');
            $avail[1] = Carbon::createFromTimestamp($avail[1])->setTimezone($this->timezone)->format('Y-m-d\TH:i:00 T');
        }
        return $avails;
    }



    private function front_availability()
    {
        return apply_filters('wappointment_front_availability', (new Availability)->get());
    }
}
