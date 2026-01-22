<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

use Wappointment\Database\WpDbConnector;
use Wappointment\Models\Setting;

/**
 * Setting Repository - Handles settings using WordPress wp_options table
 */
class SettingRepository extends BaseRepository
{
    private string $optionPrefix = 'wappointment_';

    public function __construct(
        WpDbConnector $db,
        Setting $model
    ) {
        parent::__construct($db, $model);
    }

    /**
     * Get a setting by name
     */
    public function get(string $name, mixed $default = null): mixed
    {
        $optionName = $this->optionPrefix . $name;
        $value = get_option($optionName, $default);
        
        // WordPress get_option automatically unserializes arrays
        return $value;
    }

    /**
     * Get multiple settings by names
     */
    public function getMultiple(array $names): array
    {
        $settings = [];
        
        foreach ($names as $name) {
            $settings[$name] = $this->get($name);
        }
    }

    /**
     * Get all settings grouped by tab/category
     */
    public function getAll(): array
    {
        global $wpdb;
        
        // Get all wappointment settings from wp_options
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                $this->optionPrefix . '%'
            ),
            ARRAY_A
        );
        
        $settings = [];
        foreach ($results as $result) {
            // Remove prefix from option name
            $name = str_replace($this->optionPrefix, '', $result['option_name']);
            $value = maybe_unserialize($result['option_value']);
            $settings[$name] = $value;
        }
        
        return $settings;
    }

    /**
     * Set a setting by name
     */
    public function set(string $name, mixed $value): bool
    {
        $optionName = $this->optionPrefix . $name;
        
        // WordPress update_option handles insert/update automatically
        // and serializes arrays/objects automatically
        return update_option($optionName, $value);
    }

    /**
     * Set multiple settings at once
     */
    public function setMultiple(array $settings): bool
    {
        $success = true;
        
        foreach ($settings as $name => $value) {
            if (!$this->set($name, $value)) {
                $success = false;
            }
        }
        
        return $success;
    }

    /**
     * Delete a setting by name
     */
    public function deleteSetting(string $name): bool
    {
        $optionName = $this->optionPrefix . $name;
        return delete_option($optionName);
    }
}
