<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\ClassConnect\Carbon;

class Settings
{
    public static $key_option = 'settings';
    public static $settings = false;
    public static $staffSettings = [];
    public static $msg = '';
    public static $valid = true;

    protected static function updateLocalSettings($values)
    {
        static::$settings = $values;
    }

    protected static function updateLocalStaffSettings($staff_id, $values)
    {
        static::$staffSettings[$staff_id] = $values;
    }

    protected static function getValues($reset = false)
    {
        if (static::$settings === false) {
            static::$settings = WPHelpers::getOption(static::$key_option);
        }
        return static::$settings;
    }

    protected static function getStaffValues($staff_id = false, $reset = false)
    {
        if ($staff_id === false) {
            $staff_id = Settings::get('activeStaffId');
        }
        if (!isset(static::$staffSettings[$staff_id])) {
            static::$staffSettings[$staff_id] = WPHelpers::getStaffOption(static::$key_option, $staff_id, self::staffDefaults());
        }
        return static::$staffSettings[$staff_id];
    }

    public static function allDefaults()
    {
        return [
            'service' => [
                'name' => '',
                'duration' => 60,
                'type' => '',
                'address' => '',
                'options' => [
                    'countries' => []
                ]
            ],
            'wappointment_allowed' => false,
            'scheduler_mode' => 0,
            'front_page' => 0,
            'approval_mode' => 1, // 1 stands for automatic
            'week_starts_on' => 1, // 1 stands for monday
            'date_format' => WPHelpers::getWPOption('date_format'),
            'time_format' => WPHelpers::getWPOption('time_format'),
            'date_time_union' => ' - ',
            'allow_cancellation' => true,
            'allow_rescheduling' => true,
            'hours_before_booking_allowed' => 3,
            'hours_before_cancellation_allowed' => 24,
            'hours_before_rescheduling_allowed' => 24,
            'activeStaffId' => false,
            'weekly_summary' => false,
            'weekly_summary_day' => 1,
            'weekly_summary_time' => 10,
            'daily_summary' => false,
            'daily_summary_time' => 10,
            'notify_new_appointments' => false,
            'notify_canceled_appointments' => false,
            'notify_rescheduled_appointments' => false,
            'email_notifications' => '',
            'mail_status' => false,
            'mail_config' => [
                'provider' => '',
                'from_address' => self::getFromEmail(),
                'from_name' => self::getFromName(),
                'mgdomain' => '',
                'mgkey' => '',
                'host' => '',
                'port' => '',
                'username' => '',
                'password' => '',
            ],
            'reschedule_link' => 'Reschedule',
            'cancellation_link' => 'Cancel',
            'save_appointment_text_link' => 'Save to calendar',
            'new_booking_link' => 'Book a new appointment',
        ];
    }

    public static function getFromName()
    {
        return ucfirst(WPHelpers::currentUserName()) . ' from ' . \WappointmentLv::blogname();
    }

    public static function getFromEmail()
    {
        return 'contact@' . self::getHost();
    }

    public static function getHost()
    {
        $parsed_url = parse_url(WPHelpers::getWPOption('home'));
        return !empty($parsed_url['host']) ? $parsed_url['host'] : 'example.com';
    }

    public static function staffDefaults()
    {
        $timezone = WPHelpers::getWPOption('timezone_string');
        //if (empty($timezone)) $timezone = 'UTC';
        return [
            'regav' => [
                'monday' => [[8, 12], [14, 19]],
                'tuesday' => [[8, 12], [14, 19]],
                'wednesday' => [[8, 12], [14, 19]],
                'thursday' => [[8, 12], [14, 19]],
                'friday' => [[8, 12], [14, 19]],
                'saturday' => [],
                'sunday' => []
            ],
            'calurl' => '',
            'timezone' => $timezone,
            'avatarId' => false,
            'viewed_updates' => false,
            'email_logo' => false
        ];
    }

    public static function
    default($key)
    {
        $default_settings = static::allDefaults();

        return isset($default_settings[$key]) ? $default_settings[$key] : false;
    }

    public static function defaultStaff($key)
    {
        $default_settings = static::staffDefaults();

        return isset($default_settings[$key]) ? $default_settings[$key] : false;
    }

    public static function get($setting_key, $default = null)
    {
        $values = static::getValues();

        if (isset($values[$setting_key])) {
            return $values[$setting_key];
        }
        return ($default !== null) ? $default : static::
            default($setting_key);
    }

    public static function save($setting_key, $value)
    {
        if (!static::$valid || static::valid($setting_key, $value)) {
            $values = static::getValues();

            $values[$setting_key] = $value;

            //before save
            $method = $setting_key . 'BeforeSave';
            if (method_exists(__CLASS__, $method)) {
                static::$method($value);
            }

            static::updateLocalSettings($values);

            $method = $setting_key . 'Saved';
            if (method_exists(__CLASS__, $method)) {
                static::$method($setting_key, $value);
            }

            WPHelpers::setOption(static::$key_option, $values, true);
            return ['message' => 'Setting saved'];
        }
    }

    public static function delete()
    {
        static::$settings = false;
        return WPHelpers::deleteOption(static::$key_option);
    }

    public static function getStaff($setting_key, $staff_id = false)
    {
        $settingsStaff = static::getStaffValues($staff_id);
        //dd($settingsStaff);
        $value = static::defaultStaff($setting_key);
        if (isset($settingsStaff[$setting_key])) {
            $value = $settingsStaff[$setting_key];
            //dd('ccurent val', $value);
        }

        $preparedValue = static::prepare($setting_key, $value);
        if ($preparedValue === false) {
            $preparedValue = $value;
        }
        return $preparedValue;
    }

    protected static function prepare($setting_key, $value)
    {
        $method = $setting_key . 'Prepare';
        return method_exists(get_called_class(), $method) ? static::$method($value) : false;
    }

    public static function hasStaff($setting_key, $staff_id = false)
    {
        return (static::getStaff($setting_key, $staff_id)) ? true : false;
    }

    public static function saveStaff($setting_key, $value, $staff_id = false)
    {
        if ($staff_id === false) {
            $staff_id = Settings::get('activeStaffId');
        }

        if (static::valid($setting_key, $value, $staff_id)) {
            $values = static::getStaffValues($staff_id);
            $values[$setting_key] = $value;

            WPHelpers::setStaffOption(static::$key_option, $values, $staff_id);
            //dd('saveStaff', $value);

            static::updateLocalStaffSettings($staff_id, $values);

            //dd($values[$setting_key]);
            $methodAfterSaved = $setting_key . 'Saved';
            if (method_exists(__CLASS__, $methodAfterSaved)) {
                static::$methodAfterSaved($staff_id);
            }

            return ['message' => empty(static::$msg) ? 'Setting saved' : static::$msg];
        }
    }

    public static function deleteStaff($staff_id = false)
    {
        if ($staff_id === false) {
            $staff_id = Settings::get('activeStaffId');
        }
        static::$staffSettings = [];
        return WPHelpers::deleteStaffOption(static::$key_option, $staff_id);
    }

    protected static function valid($setting_key, $value, $staff_id = false)
    {
        $method = $setting_key . 'Valid';
        if (method_exists(__CLASS__, $method)) {
            return static::$method($value, $staff_id);
        }

        if (!isset(static::allDefaults()[$setting_key]) && !isset(static::staffDefaults()[$setting_key])) {
            return false;
        }
        return true;
    }

    protected static function weekly_summary_dayValid($value)
    {
        if (self::between($value, 0, 6)) {
            return true;
        }
        throw new \WappointmentException('Week day is not valid');
    }

    protected static function weekly_summary_timeValid($value)
    {
        if (self::between($value, 0, 23)) {
            return true;
        }
        throw new \WappointmentException('Time is not valid');
    }

    protected static function weekly_summarySaved($key, $value)
    {
        if ($value) {
            Queue::queueWeeklyJob();
        } else {
            Queue::cancelWeeklyJob();
        }
    }

    protected static function weekly_summary_timeSaved($key, $value)
    {
        self::weekly_summarySaved($key, true);
    }

    protected static function weekly_summary_daySaved($key, $value)
    {
        self::weekly_summarySaved($key, true);
    }

    protected static function daily_summary_timeValid($value)
    {
        if (self::between($value, 0, 23)) {
            return true;
        }
        throw new \WappointmentException('Time is not valid');
    }

    protected static function daily_summarySaved($key, $value)
    {
        if ($value) {
            Queue::queueDailyJob();
        } else {
            Queue::cancelDailyJob();
        }
    }

    protected static function daily_summary_timeSaved($key, $value)
    {
        self::daily_summarySaved($key, true);
    }

    protected static function approval_modeValid($value)
    {
        if (self::between($value, 1, 2)) {
            return true;
        }
        throw new \WappointmentException('Approval mode is not valid');
    }

    protected static function week_starts_onValid($value)
    {
        if (self::between($value, 0, 6)) {
            return true;
        }
        throw new \WappointmentException('Week starts on is not valid');
    }

    protected static function date_time_unionValid($value)
    {
        if (empty($value)) {
            throw new \WappointmentException('Union field can\'t be empty');
        }
        return true;
    }

    protected static function email_notificationsValid($value)
    {
        return self::emailField($value);
    }

    protected static function hours_before_booking_allowedValid($value)
    {
        if ($value < 1) {
            throw new \WappointmentException('You need to give at least one hour before the appointment');
        }
        return  self::hourField($value);
    }

    protected static function hours_before_cancellation_allowedValid($value)
    {
        return self::hourField($value);
    }

    protected static function hours_before_rescheduling_allowedValid($value)
    {
        return self::hourField($value);
    }

    protected static function between($value, $minLimit, $maxLimit)
    {
        return (is_numeric($value) && $value >= $minLimit && $value <= $maxLimit) ? true : false;
    }

    protected static function greaterEqualTo($value, $minLimit = 0)
    {
        return (is_numeric($value) && $value >= $minLimit) ? true : false;
    }

    protected static function hourField($value)
    {
        if (self::greaterEqualTo($value)) {
            return true;
        }
        throw new \WappointmentException('Hour field is not valid');
    }

    protected static function emailField($value)
    {
        $validator = new \Rakit\Validation\Validator;

        $validation = $validator->validate(['email' => $value], [
            'email' => 'required|email'
        ]);

        if ($validation->fails()) {
            throw new \WappointmentException('The Email is not valid');
        }
        return true;
    }

    // remove slots that are in the future but are not bookable
    protected static function availabilityPrepare($availabilities)
    {
        $booking_allowed_from = Carbon::now()->timestamp + (Settings::get('hours_before_booking_allowed') * 60 * 60);

        foreach ($availabilities as &$avail) {
            if ($booking_allowed_from > $avail[0]) {
                if ($booking_allowed_from < $avail[1]) {
                    $avail[0] = $booking_allowed_from;
                } else {
                    $avail = null;
                }
            }
        }
        //we clear the null records and reset the array index
        return array_values(array_filter($availabilities));
    }

    protected static function activeStaffIdValid($value)
    {
        return ($value > 0 && WPHelpers::getUserBy('id', $value) !== false) ? true : false;
    }

    protected static function activeStaffIdBeforeSave($newStaffId)
    {
        //transfer all staff id settings to the right full owner
        //dd('current ' . Settings::get('activeStaffId'), 'old ' . $newStaffId);
        WPHelpers::transferStaffOptions(Settings::get('activeStaffId'), $newStaffId);
    }

    protected static function calurlValid($value, $staff_id)
    {
        // 1 is url
        $validator = new \Rakit\Validation\Validator;

        $validation = $validator->validate(['calurl' => $value], [
            'calurl' => 'required|url:http,https'
        ]);

        if ($validation->fails()) {
            throw new \WappointmentException('The URL is not valid');
        }
        // 2 is reachable
        $result = false;
        try {
            //make sure we can process it fully again
            WPHelpers::deleteStaffOption('last-calendar-id', $staff_id);
            $result = (new \Wappointment\Services\Calendar($value, $staff_id))->fetch();
        } catch (\Exception $e) {
            throw new \WappointmentException($e->getMessage());
        }

        return true;
    }
}
