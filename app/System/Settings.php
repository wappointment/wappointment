<?php
declare(strict_types=1);

namespace Wappointment\System;

/**
 * Settings - Manages application settings via WordPress options API
 * All settings are stored in a single JSON value under 'wappointment_settings'
 */
class Settings
{
    private const OPTION_KEY = 'wappointment_settings';
    
    private ?array $cache = null;
    
    /**
     * Get a setting value
     */
    public function get(string $key, $default = null)
    {
        $settings = $this->all();
        return $settings[$key] ?? $default;
    }
    
    /**
     * Set a setting value
     */
    public function set(string $key, $value): bool
    {
        $settings = $this->all();
        $settings[$key] = $value;
        $this->cache = $settings;
        update_option(self::OPTION_KEY, $settings, false);
        return true;
    }
    
    /**
     * Set multiple settings at once
     */
    public function setMultiple(array $data): bool
    {
        $settings = $this->all();
        foreach ($data as $key => $value) {
            $settings[$key] = $value;
        }
        $this->cache = $settings;
        update_option(self::OPTION_KEY, $settings, false);
        return true;
    }
    
    /**
     * Delete a setting
     */
    public function delete(string $key): bool
    {
        $settings = $this->all();
        if (isset($settings[$key])) {
            unset($settings[$key]);
            $this->cache = $settings;
            return update_option(self::OPTION_KEY, $settings, false);
        }
        return false;
    }
    
    /**
     * Get all settings
     */
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }
        
        $settings = get_option(self::OPTION_KEY, []);
        
        // Merge with defaults for any missing keys
        if (empty($settings)) {
            $settings = $this->getDefaults();
            update_option(self::OPTION_KEY, $settings, false);
        } else {
            $settings = array_merge($this->getDefaults(), $settings);
        }
        
        $this->cache = $settings;
        return $settings;
    }
    
    /**
     * Reset all settings to defaults
     */
    public function reset(): bool
    {
        $this->cache = null;
        return update_option(self::OPTION_KEY, $this->getDefaults(), false);
    }
    
    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        $this->cache = null;
    }
    
    /**
     * Get default sender name
     */
    private function getFromName(): string
    {
        $current_user = wp_get_current_user();
        if ($current_user && !empty($current_user->display_name)) {
            return $current_user->display_name;
        }
        return get_option('blogname', 'WordPress');
    }
    
    /**
     * Get default email address
     */
    private function getFromEmail(): string
    {
        return 'contact@' . $this->getHost();
    }
    
    /**
     * Get site host
     */
    private function getHost(): string
    {
        $parsed_url = wp_parse_url(get_option('home'));
        if (!empty($parsed_url['host'])) {
            $host = $parsed_url['host'];
            return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
        }
        return 'example.com';
    }
    
    /**
     * Get all default settings
     */
    private function getDefaults(): array
    {
        return [
            'service' => ['name' => '', 'duration' => 60, 'type' => '', 'address' => '', 'options' => ['countries' => []]],
            'email_logo' => false,
            'wappointment_allowed' => false,
            'buffer_time' => 0,
            'scheduler_mode' => 0,
            'front_page' => 0,
            'approval_mode' => 1, // 1 stands for automatic
            'week_starts_on' => 1, // 1 stands for monday
            'date_format' => get_option('date_format'),
            'time_format' => get_option('time_format'),
            'date_time_union' => ' - ',
            'allow_cancellation' => true,
            'allow_rescheduling' => true,
            'email_footer' => '',
            'email_link_color' => '#6664cb',
            'hours_before_booking_allowed' => 3,
            'hours_before_cancellation_allowed' => 24,
            'hours_before_rescheduling_allowed' => 24,
            'activeStaffId' => false,
            'weekly_summary' => false,
            'weekly_summary_day' => 1,
            'weekly_summary_time' => 10,
            'daily_summary' => false,
            'daily_summary_time' => 10,
            'notify_new_appointments' => true,
            'notify_pending_appointments' => true,
            'notify_canceled_appointments' => true,
            'notify_rescheduled_appointments' => true,
            'email_notifications' => '',
            'mail_status' => true,
            'mail_config' => [
                'method' => 'wpmail',
                'from_address' => $this->getFromEmail(),
                'from_name' => $this->getFromName(),
                'mgdomain' => '',
                'mgkey' => '',
                'host' => '',
                'port' => '',
                'username' => '',
                'password' => '',
                'wpmail_html' => false,
                'attachments_off' => false
            ],
            'reschedule_link' => 'Reschedule',
            'cancellation_link' => 'Cancel',
            'save_appointment_text_link' => 'Save to calendar',
            'new_booking_link' => 'Book a new appointment',
            'booking_page' => 0,
            'show_welcome' => false,
            'force_ugly_permalinks' => false,
            'allow_staff_cf' => false,
            'currency' => 'USD',
            'services_sold' => false,
            'calendar_roles' => ['administrator', 'author', 'editor', 'contributor', 'wappointment_staff'],
            'max_active_bookings' => 0,
            'max_active_per_staff' => false,
            'autofill' => true,
            'onsite_enabled' => true,
            'cache' => false,
            'tax' => 0,
            'payments_order' => [],
            'alt_port' => false,
            'video_link_shows' => 0,
            'forceemail' => false,
            'allow_refreshavb' => false,
            'refreshavb_at' => 23,
            'clean_pending_every' => 25,
            'clean_last_check' => false,
            'regavDefault' => [
                'monday' => [[480, 720], [840, 1140]],
                'tuesday' => [[480, 720], [840, 1140]],
                'wednesday' => [[480, 720], [840, 1140]],
                'thursday' => [[480, 720], [840, 1140]],
                'friday' => [[480, 720], [840, 1140]],
                'saturday' => [],
                'sunday' => [],
                'precise' => true
            ],
            'servicesDefault' => true,
            'calendar_handles_free' => false,
            'calendar_ignores_free' => false,
            'invoice' => false,
            'invoice_seller' => '',
            'invoice_num' => 'Order nº',
            'invoice_client' => ['name'],
            'wp_remote' => false,
            'jitsi_url' => '',
            'frontend_weekstart' => false,
            'availability_fluid' => false,
            'more_st' => false,
            'starting_each' => 30,
            'collate' => '',
            'charset' => '',
            'big_prices' => false,
        ];
    }
}
