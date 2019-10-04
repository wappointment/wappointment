<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Models\WPUser;
use Wappointment\Models\WPUserMeta;

class ViewsData
{
    public function load($key)
    {
        if (method_exists($this, $key)) {
            return $this->$key();
        }
        return apply_filters('wappointment_viewdata_' . $key, []);
    }

    private function regav()
    {
        $gravatar_img = get_avatar_url(Settings::get('activeStaffId'), ['size' => 40]);
        return [
            'regav' => Settings::getStaff('regav'),
            'staffs' => WPUser::whereIn('ID', WPUserMeta::getUserIdWithRoles())->get(),
            'activeStaffId' => Settings::get('activeStaffId'),
            'activeStaffAvatar' => Settings::getStaff('avatarId') ? wp_get_attachment_image_src(Settings::getStaff('avatarId'))[0] : $gravatar_img,
            'activeStaffGravatar' => $gravatar_img,
            'activeStaffAvatarId' => Settings::getStaff('avatarId'),
            'timezone' => Settings::getStaff('timezone'),
            'timezones_list' => DateTime::tz(),
            'savedTimezone' => Settings::hasStaff('timezone')
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
            'service' => Settings::get('service')
        ];
    }

    private function widget()
    {
        return [
            'widget' => (new WidgetSettings)->get(),
            'widgetDefault' => (new WidgetSettings)->default(),
            'config' => [
                'service' => Settings::get('service'),
                'approval_mode' => Settings::get('approval_mode'),
            ],
            'bgcolor' => WPHelpers::getThemeBgColor(),
            'more' => get_theme_mods(),
            'widgetFields' => (new \Wappointment\Services\WidgetSettings)->adminFieldsInfo()
        ];
    }

    private function widgetcancel()
    {
        return [
            'widget' => (new WidgetSettings)->get(),
            'widgetDefault' => (new WidgetSettings)->default(),
            'staff' => (new \Wappointment\WP\Staff())->toArray()
        ];
    }

    private function calendar()
    {
        $staff_timezone = Settings::getStaff('timezone');

        return [
            'regav' => Settings::getStaff('regav'),
            'availability' => WPHelpers::getStaffOption('availability'),
            'timezone' => $staff_timezone,
            'week_starts_on' => Settings::get('week_starts_on'),
            'wizard_step' => WPHelpers::getOption('wizard_step'),
            'timezones_list' => DateTime::tz(),
            'now' => (new Carbon())->setTimezone($staff_timezone)->format('Y-m-d\TH:i:00'),
            'service' => Settings::get('service'),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'preferredCountries' => Service::get()->getCountries(),
        ];
    }

    private function settingsgeneral()
    {
        $service = Settings::get('service');
        $widget = (new WidgetSettings)->get();
        return [
            // general
            'approval_mode' => Settings::get('approval_mode'),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'allow_cancellation' => Settings::get('allow_cancellation'),
            'allow_rescheduling' => Settings::get('allow_rescheduling'),
            'hours_before_booking_allowed' => Settings::get('hours_before_booking_allowed'),
            'is_availability_set' => empty(WPHelpers::getStaffOption('availability')) ? false : true,
            'is_service_set' => empty($service['type']) && empty($service['name']) ? false : true,
            'is_widget_set' => empty($widget['form']) ? false : true,
            'hours_before_cancellation_allowed' => Settings::get('hours_before_cancellation_allowed'),
            'hours_before_rescheduling_allowed' => Settings::get('hours_before_rescheduling_allowed'),
            'timezone' => Settings::getStaff('timezone'),
            'widget' => (new WidgetSettings)->get(),
            'config' => [
                'service' => Settings::get('service'),
                'approval_mode' => Settings::get('approval_mode'),
            ],
            'bgcolor' => WPHelpers::getThemeBgColor(),
        ];
    }

    private function settingsnotifications()
    {
        return [
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
            'timezone' => Settings::getStaff('timezone'),
            'time_format' => Settings::get('time_format'),
        ];
    }

    private function settingssync()
    {
        return [
            'is_calendar_sync_set' => empty(Settings::getStaff('calurl')) ? false : true,
            'timezone' => Settings::getStaff('timezone'),
            'week_starts_on' => Settings::get('week_starts_on'),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'wappointment_allowed' => Settings::get('wappointment_allowed'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'last_checked' => WPHelpers::getStaffOption('last-calendar-checked'),
            'last_parsed' => WPHelpers::getStaffOption('last-calendar-parsed'),
            'last_process' => WPHelpers::getStaffOption('last-calendar-process')
        ];
    }
    private function wizardinit()
    {
        return [
            'admin_email' => wp_get_current_user()->user_email,
            'admin_name' => wp_get_current_user()->display_name,
        ];
    }

    private function settingsmailer()
    {
        return [
            'mail_config' => Settings::get('mail_config'),
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
        $staff_availability = [];
        $staffs = [];
        foreach (Staff::getIds() as $staff_id) {
            $staff = new \Wappointment\WP\Staff($staff_id);
            $staff_availability[$staff_id] = $staff->getAvailability();
            $staffs[] = $staff->toArray();
        }

        return [
            'staffs' => $staffs,
            'availability' => $staff_availability,
            'timezones_list' => DateTime::tz(),
            'week_starts_on' => Settings::get('week_starts_on'),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'now' => (new Carbon())->format('Y-m-d\TH:i:00'),
            'services' => Service::all()
        ];
    }
}
