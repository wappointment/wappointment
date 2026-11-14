<?php
declare(strict_types=1);

namespace Wappointment\Http;

class ApiSchemas
{
    public static function object(array $properties, ?array $required = null, bool|array $additional = false): array
    {
        return ['type' => 'object', 'properties' => $properties, 'required' => $required ?? array_keys($properties), 'additionalProperties' => $additional];
    }

    public static function list(array $items): array
    {
        return ['type' => 'array', 'items' => $items];
    }

    public static function nullable(array $schema): array
    {
        return ['anyOf' => [$schema, ['type' => 'null']]];
    }

    public static function id(): array
    {
        return ['anyOf' => [['type' => 'integer', 'minimum' => 1], ['type' => 'string', 'pattern' => '^[1-9][0-9]*$']]];
    }

    public static function selection(): array
    {
        return [
            'service_id' => ['type' => 'integer', 'minimum' => 1],
            'calendar_id' => ['type' => 'integer', 'minimum' => 1],
            'location_id' => ['type' => 'integer', 'minimum' => 1],
            'duration' => ['type' => 'integer', 'minimum' => 5, 'maximum' => 1440],
            'date' => ['type' => 'string', 'format' => 'date'],
        ];
    }

    public static function editKey(): array
    {
        return ['type' => 'string', 'pattern' => '^[a-f0-9]{32}$'];
    }

    public static function booking(): array
    {
        return self::object([
            'id' => ['type' => 'integer', 'minimum' => 1],
            'status' => ['type' => 'integer'],
            'start' => ['type' => 'integer', 'minimum' => 1],
            'end' => ['type' => 'integer', 'minimum' => 1],
            'edit_key' => self::editKey(),
        ]);
    }

    public static function client(): array
    {
        return self::object([
            'id' => self::id(), 'name' => ['type' => 'string'], 'email' => ['type' => 'string'],
            'options' => self::nullable(['type' => 'string']),
            'deleted_at' => self::nullable(['type' => 'string']),
            'created_at' => self::nullable(['type' => 'string']),
            'updated_at' => self::nullable(['type' => 'string']),
        ]);
    }

    public static function pagination(array $item): array
    {
        return self::object([
            'data' => self::list($item),
            'total' => ['type' => 'integer', 'minimum' => 0],
            'page' => ['type' => 'integer', 'minimum' => 1],
            'per_page' => ['type' => 'integer', 'minimum' => 1],
            'total_pages' => ['type' => 'number', 'minimum' => 0],
        ]);
    }

    public static function paginationRequest(bool $search = false): array
    {
        $properties = [
            'page_num' => ['type' => 'integer', 'minimum' => 1],
            'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
        ];
        if ($search) {
            $properties['search'] = ['type' => 'string', 'maxLength' => 255];
        }
        return self::object($properties, []);
    }

    public static function settings(): array
    {
        $properties = [];
        foreach (explode(' ', 'wappointment_allowed allow_cancellation allow_rescheduling weekly_summary daily_summary notify_new_appointments notify_pending_appointments notify_canceled_appointments notify_rescheduled_appointments mail_status show_welcome force_ugly_permalinks allow_staff_cf services_sold max_active_per_staff autofill onsite_enabled cache alt_port forceemail allow_refreshavb calendar_handles_free calendar_ignores_free invoice wp_remote frontend_weekstart availability_fluid more_st big_prices') as $key) {
            $properties[$key] = ['type' => 'boolean'];
        }
        foreach (explode(' ', 'buffer_time scheduler_mode front_page approval_mode week_starts_on hours_before_booking_allowed hours_before_cancellation_allowed hours_before_rescheduling_allowed weekly_summary_day weekly_summary_time daily_summary_time booking_page max_active_bookings video_link_shows refreshavb_at clean_pending_every starting_each tax') as $key) {
            $properties[$key] = ['type' => 'number', 'minimum' => 0];
        }
        $properties['approval_mode'] = ['type' => 'integer', 'enum' => [0, 1]];
        $properties['starting_each'] = ['type' => 'integer', 'minimum' => 1, 'maximum' => 1440];
        foreach (explode(' ', 'date_format time_format date_time_union email_footer email_link_color email_notifications reschedule_link cancellation_link save_appointment_text_link new_booking_link currency invoice_seller invoice_num jitsi_url collate charset') as $key) {
            $properties[$key] = ['type' => 'string'];
        }
        foreach (['email_logo', 'activeStaffId', 'clean_last_check'] as $key) {
            $properties[$key] = ['anyOf' => [['type' => 'boolean'], ['type' => 'integer'], ['type' => 'string']]];
        }
        foreach (['calendar_roles', 'payments_order', 'invoice_client'] as $key) {
            $properties[$key] = self::list(['type' => 'string']);
        }
        $properties['servicesDefault'] = ['anyOf' => [['type' => 'boolean'], self::list(['type' => 'integer'])]];
        $properties['service'] = self::object([
            'name' => ['type' => 'string'], 'duration' => ['type' => 'integer', 'minimum' => 5],
            'type' => ['anyOf' => [['type' => 'string'], self::list(['type' => 'string'])]],
            'address' => ['type' => 'string'], 'options' => self::object([], [], true),
        ], []);
        $mail = [];
        foreach (explode(' ', 'method from_address from_name mgdomain mgkey host port username password') as $key) {
            $mail[$key] = ['type' => 'string'];
        }
        $mail['wpmail_html'] = $mail['attachments_off'] = ['type' => 'boolean'];
        $properties['mail_config'] = self::object($mail, []);
        $schedule = array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            self::list(['type' => 'array', 'minItems' => 2, 'maxItems' => 2, 'items' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 1440]]));
        $schedule['precise'] = ['type' => 'boolean'];
        $properties['regavDefault'] = self::object($schedule, []);
        return $properties;
    }
}
