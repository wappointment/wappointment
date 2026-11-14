<?php
declare(strict_types=1);

namespace Wappointment\Database;

class BookingSchema
{
    public static function install(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $tables = [
            'clients' => "id int unsigned NOT NULL AUTO_INCREMENT,
                email varchar(191) NOT NULL, name varchar(255) NOT NULL, options longtext NULL,
                deleted_at datetime NULL, created_at datetime NULL, updated_at datetime NULL,
                PRIMARY KEY  (id), UNIQUE KEY email (email)",
            'services' => "id int unsigned NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL, sorting tinyint unsigned NOT NULL DEFAULT 0,
                options longtext NULL, deleted_at datetime NULL, created_at datetime NULL, updated_at datetime NULL,
                PRIMARY KEY  (id)",
            'locations' => "id int unsigned NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL, type tinyint unsigned NOT NULL DEFAULT 1,
                status tinyint NOT NULL DEFAULT 0, options longtext NULL,
                deleted_at datetime NULL, created_at datetime NULL, updated_at datetime NULL,
                PRIMARY KEY  (id)",
            'calendars' => "id int unsigned NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL, account_key varchar(255) NULL, wp_uid bigint unsigned NOT NULL DEFAULT 0,
                status tinyint unsigned NOT NULL DEFAULT 1, sorting tinyint unsigned NOT NULL DEFAULT 0,
                options longtext NULL, availability mediumtext NULL, deleted_at datetime NULL,
                created_at datetime NULL, updated_at datetime NULL,
                PRIMARY KEY  (id)",
            'calendar_service' => "calendar_id int unsigned NOT NULL, service_id int unsigned NOT NULL,
                PRIMARY KEY  (calendar_id,service_id)",
            'service_location' => "service_id int unsigned NOT NULL, location_id int unsigned NOT NULL,
                PRIMARY KEY  (service_id,location_id)",
            'appointments' => "id int unsigned NOT NULL AUTO_INCREMENT,
                start_at datetime NOT NULL, end_at datetime NOT NULL,
                type tinyint unsigned NOT NULL DEFAULT 0, status tinyint NOT NULL DEFAULT 0,
                edit_key varchar(32) NULL, options longtext NULL, staff_id int unsigned NULL,
                service_id int unsigned NULL, location_id int unsigned NULL, client_id int unsigned NULL,
                recurrent tinyint NOT NULL DEFAULT 0, parent int unsigned NULL,
                created_at datetime NULL, updated_at datetime NULL,
                PRIMARY KEY  (id), KEY staff_time (staff_id,start_at,end_at), KEY edit_key (edit_key)",
            'statuses' => "id int unsigned NOT NULL AUTO_INCREMENT,
                type tinyint unsigned NOT NULL DEFAULT 0, start_at datetime NOT NULL, end_at datetime NOT NULL,
                recur tinyint unsigned NOT NULL DEFAULT 0, muted tinyint unsigned NOT NULL DEFAULT 0,
                source varchar(32) NULL, eventkey varchar(32) NULL, staff_id int unsigned NULL, options longtext NULL,
                PRIMARY KEY  (id), KEY staff_time (staff_id,start_at,end_at)",
        ];
        foreach ($tables as $suffix => $columns) {
            $table = $wpdb->prefix . 'wappo_' . $suffix;
            // Existing v2 tables are deliberately not rewritten by the v3 bootstrap.
            if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) === $table) {
                continue;
            }
            dbDelta("CREATE TABLE {$table} ({$columns}) ENGINE=InnoDB {$wpdb->get_charset_collate()};");
            if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) !== $table) {
                throw new \RuntimeException('Could not create Wappointment booking table: ' . $suffix);
            }
        }
        update_option('wappointment_booking_schema', '3.0.0', false);
    }
}
