<?php
declare(strict_types=1);

namespace Wappointment\Helpers;

use Wappointment\System\Settings;

/**
 * WPSettings Helper - Static helper methods for Settings
 */
class WPSettings
{
    private static ?Settings $instance = null;
    
    /**
     * Get the Settings instance
     */
    private static function instance(): Settings
    {
        if (self::$instance === null) {
            self::$instance = new Settings();
        }
        return self::$instance;
    }
    
    /**
     * Get a setting value
     */
    public static function get(string $key, $default = null)
    {
        return self::instance()->get($key, $default);
    }
    
    /**
     * Set a setting value
     */
    public static function set(string $key, $value): bool
    {
        return self::instance()->set($key, $value);
    }
    
    /**
     * Delete a setting
     */
    public static function delete(string $key): bool
    {
        return self::instance()->delete($key);
    }
    
    /**
     * Get all settings
     */
    public static function all(): array
    {
        return self::instance()->all();
    }
    
    /**
     * Reset all settings to defaults
     */
    public static function reset(): bool
    {
        return self::instance()->reset();
    }
    
    /**
     * Clear cache
     */
    public static function clearCache(): void
    {
        self::instance()->clearCache();
    }
}
