<?php

namespace Wappointment\WP;

use Wappointment\Services\Settings;

class Helpers
{
    public static $option_prefix = WAPPOINTMENT_SLUG;
    private static $request;

    public static function getThemeBgColor()
    {
        $color = get_background_color();
        return !empty($color) ? '#' . $color : '#fcfcfc';
    }

    public static function getOption($option_name, $default = false)
    {
        return self::getWPOption(self::$option_prefix . '_' . strtolower($option_name), $default);
    }

    public static function setOption($option_name, $value, $autoload = false)
    {
        return update_option(self::$option_prefix . '_' . strtolower($option_name), $value, $autoload);
    }

    public static function deleteOption($option_name)
    {
        return delete_option(self::$option_prefix . '_' . strtolower($option_name));
    }

    public static function currentUser()
    {
        if (\WappointmentLv::function_exists('wp_get_current_user')) {
            return wp_get_current_user();
        }
        return false;
    }

    public static function getUserBy($field, $value)
    {
        if (\WappointmentLv::function_exists('get_user_by')) {
            return get_user_by($field, $value);
        }
        return false;
    }

    public static function currentUserEmail()
    {
        $currentUser = self::currentUser();
        if ($currentUser) {
            return $currentUser->user_email;
        }
        return '';
    }

    public static function currentUserName()
    {
        $currentUser = self::currentUser();
        if ($currentUser) {
            return $currentUser->display_name;
        }
        return false;
    }

    public static function userId()
    {
        return get_current_user_id();
    }

    public static function getStaffOption($option_name, $staff_id = false, $default = false)
    {
        if ($staff_id === false) {
            $staff_id = Settings::get('activeStaffId');
        }

        $setting = get_user_option(self::$option_prefix . '_' . strtolower($option_name), $staff_id);

        return (empty($setting)) ? $default : $setting;
    }

    public static function setStaffOption($option_name, $value, $staff_id = false, $global = false)
    {
        if ($staff_id === false) {
            $staff_id = Settings::get('activeStaffId');
        }
        // dd($option_name, $value);
        return update_user_option($staff_id, self::$option_prefix . '_' . strtolower($option_name), $value, $global);
    }
    public static function transferStaffOptions($old_staff_id, $new_staff_id)
    {
        //dd($old_staff_id, $new_staff_id);
        $options_to_transfer = ['settings', 'since_last_refresh', 'availability'];
        foreach ($options_to_transfer as $option_name) {
            $originalUserSetting = self::getStaffOption($option_name, $old_staff_id);
            self::setStaffOption($option_name, $originalUserSetting, $new_staff_id);
            self::deleteStaffOption($option_name, $old_staff_id);
        }
    }

    public static function deleteStaffOption($option_name, $staff_id = false)
    {
        if ($staff_id === false) {
            $staff_id = Settings::get('activeStaffId');
        }
        return delete_user_option($staff_id, self::$option_prefix . '_' . strtolower($option_name));
    }

    public static function adminUrl($link = false)
    {
        return $link === false ? admin_url() : admin_url('admin.php?page=' . $link);
    }

    public static function link($label, $link, $backend = false)
    {
        if ($backend) {
            $link = self::adminUrl($link);
        }

        return '<a href="' . esc_url($link) . '">' . esc_html($label) . '</a>';
    }

    public static function getWPOption($option_name, $default = false)
    {
        if (\WappointmentLv::function_exists('get_option')) {
            return get_option($option_name, $default);
        }
        return false;
    }

    public static function requestCapture()
    {
        self::$request = \Wappointment\ClassConnect\Request::capture();
    }

    public static function requestGet($params = [])
    {
        return self::$request->merge($params);
    }

    public static function isPluginPage()
    {
        return \WappointmentLv::starts_with(self::$request->get('page'), self::$option_prefix);
    }

    public static function restError($error_string, $status = 500, $errors = [])
    {
        return new \WP_Error(
            WAPPOINTMENT_SLUG . '_error_rest',
            $error_string,
            ['status' => $status, 'errors' => $errors]
        );
    }

    public static function enqueueFrontScripts()
    {
        self::enqueue('front');
        do_action('wappointment_enqueue_front');
    }
    public static function enqueue($script, $requires = [])
    {
        $scriptname = WAPPOINTMENT_SLUG . '_' . $script;
        wp_register_script($scriptname, \Wappointment\System\Helpers::assetUrl($script . '.js'), $requires, null, true);
        wp_enqueue_script($scriptname);
        return $scriptname;
    }
}
