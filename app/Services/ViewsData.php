<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Services\Staff;
use Wappointment\WP\WidgetAPI;

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

    private function regav()
    {
        $gravatar_img = get_avatar_url(Settings::get('activeStaffId'), ['size' => 40]);
        $staff_name = Settings::getStaff('display_name');
        return apply_filters('wappointment_back_regav', [
            'regav' => Settings::getStaff('regav'),
            'availaible_booking_days' => Settings::getStaff('availaible_booking_days'),
            'staffs' => Staff::getWP(),
            'activeStaffId' => (int)Settings::get('activeStaffId'),
            'activeStaffAvatar' => Settings::getStaff('avatarId') ?
                wp_get_attachment_image_src(Settings::getStaff('avatarId'))[0] : $gravatar_img,
            'activeStaffGravatar' => $gravatar_img,
            'activeStaffName' => empty($staff_name) ? (new \Wappointment\WP\Staff(Settings::get('activeStaffId')))->name : $staff_name,
            'activeStaffAvatarId' => Settings::getStaff('avatarId'),
            'timezone' => Settings::getStaff('timezone'),
            'timezones_list' => DateTime::tz(),
            'savedTimezone' => Settings::hasStaff('timezone')
        ]);
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
            'service' => Service::get()
        ];
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
        return apply_filters('wappointment_back_widget_editor', [
            'front_availability' => $this->front_availability(),
            'widget' => (new WidgetSettings)->get(),
            'widgetDefault' => (new WidgetSettings)->defaultSettings(),
            'config' => [
                'service' => Service::get(),
                'approval_mode' => Settings::get('approval_mode'),
            ],
            'bgcolor' => WPHelpers::getThemeBgColor(),
            'more' => get_theme_mods(),
            'widgetFields' => (new \Wappointment\Services\WidgetSettings)->adminFieldsInfo()
        ]);
    }

    private function widgetcancel()
    {
        return [
            'widget' => (new WidgetSettings)->get(),
            'widgetDefault' => (new WidgetSettings)->defaultSettings(),
            'staff' => (new \Wappointment\WP\Staff())->toArray()
        ];
    }

    private function calendar()
    {
        $staff_timezone = Settings::getStaff('timezone');
        return  apply_filters('wappointment_back_calendar', [
            'regav' => Settings::getStaff('regav'),
            'availability' => WPHelpers::getStaffOption('availability'),
            'timezone' => $staff_timezone,
            'week_starts_on' => Settings::get('week_starts_on'),
            'wizard_step' => WPHelpers::getOption('wizard_step'),
            'timezones_list' => DateTime::tz(),
            'now' => (new Carbon())->setTimezone($staff_timezone)->format('Y-m-d\TH:i:00'),
            'service' => Service::get(),
            'durations' => [Service::get()['duration']],
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'preferredCountries' => Service::getObject()->getCountries(),
            'buffer_time' => Settings::get('buffer_time'),
            'widget' => (new \Wappointment\Services\WidgetSettings)->get(),
            'booking_page_id' => (int) Settings::get('booking_page'),
            'booking_page_url' => get_permalink((int) Settings::get('booking_page')),
            'showWelcome' => Settings::get('show_welcome'),
            'subscribe_email' => Settings::get('email_notifications'),
            'welcome_site' => get_site_url(),
            'cal_duration' => Service::get()['duration'],
            'preferences' => (new Preferences)->preferences
        ]);
    }

    private function settingsgeneral()
    {
        $service = Service::get();
        $widget = (new WidgetSettings)->get();
        return [
            // general
            'approval_mode' => Settings::get('approval_mode'),
            'today_formatted' => DateTime::i18nDateTime(time(), Settings::getStaff('timezone')),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'allow_cancellation' => Settings::get('allow_cancellation'),
            'allow_rescheduling' => Settings::get('allow_rescheduling'),
            'week_starts_on' => Settings::get('week_starts_on'),
            'hours_before_booking_allowed' => Settings::get('hours_before_booking_allowed'),
            'is_availability_set' => empty(WPHelpers::getStaffOption('availability')) ? false : true,
            'is_dotcom_connected' => false,
            'is_service_set' => empty($service['type']) && empty($service['name']) ? false : true,
            'is_widget_set' => empty($widget['form']) ? false : true,
            'hours_before_cancellation_allowed' => Settings::get('hours_before_cancellation_allowed'),
            'hours_before_rescheduling_allowed' => Settings::get('hours_before_rescheduling_allowed'),
            'timezone' => Settings::getStaff('timezone'),
            'widget' => (new WidgetSettings)->get(),
            'booking_page_id' => (int) Settings::get('booking_page'),
            'booking_page_url' => get_permalink((int) Settings::get('booking_page')),
            'config' => [
                'service' => Service::get(),
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
        $calurl = WPHelpers::getStaffOption('cal_urls');
        return [
            'is_calendar_sync_set' => empty($calurl) ? false : true,
            'timezone' => Settings::getStaff('timezone'),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'calendar_logs' => WPHelpers::getStaffOption('calendar_logs'),
            'calendar_url' => $calurl,
            'oldcal' => empty(Settings::getStaff('calurl')) ? false : true
        ];
    }

    private function settingsadvanced()
    {
        return [
            'buffer_time' => Settings::get('buffer_time'),
            'front_page_id' => (int) Settings::get('front_page'),
            'front_page' => get_permalink((int) Settings::get('front_page')),
            'front_page_type' => get_post_type((int) Settings::get('front_page'))
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
    private function all_versions_changes()
    {
        return ['versions' => \Wappointment\System\Status::allUpdates()];
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

        return apply_filters('wappointment_front_availability', [
            'staffs' => $staffs,
            'availability' => $staff_availability,
            'week_starts_on' => Settings::get('week_starts_on'),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'min_bookable' => Settings::get('hours_before_booking_allowed'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'now' => (new Carbon())->format('Y-m-d\TH:i:00'),
            'buffer_time' => Settings::get('buffer_time'),
            'services' => Service::all(),
            'site_lang' => substr(get_locale(), 0, 2)
        ]);
    }
}
