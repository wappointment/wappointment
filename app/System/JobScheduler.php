<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Database\BookingSchema;
use Wappointment\UseCases\Jobs\ProcessJobQueueUseCase;

/**
 * Native WP-cron trigger for the v3 job queue. Legacy cron hooks
 * (`wappointment_process_every_min`, ...) are not registered by v3, so the two
 * generations never consume the queue together.
 */
final class JobScheduler
{
    public const HOOK = 'wappointment_v3_process_queue';
    public const SCHEDULE = 'wappointment_v3_every_minute';

    public static function register(): void
    {
        add_filter('cron_schedules', [self::class, 'addSchedule']);
        add_action(self::HOOK, [self::class, 'run']);
        add_action('init', [self::class, 'ensureScheduled'], 20);
    }

    public static function addSchedule(array $schedules): array
    {
        $schedules[self::SCHEDULE] = ['interval' => 60, 'display' => 'Wappointment queue (every minute)'];
        return $schedules;
    }

    public static function ensureScheduled(): void
    {
        if (wp_next_scheduled(self::HOOK) === false
            && wp_schedule_event(time() + 60, self::SCHEDULE, self::HOOK, [], true) !== true) {
            error_log('Wappointment: the job queue could not be scheduled in WP-cron.');
        }
    }

    public static function unschedule(): void
    {
        wp_clear_scheduled_hook(self::HOOK);
    }

    public static function run(): void
    {
        if (get_option('wappointment_booking_schema') !== BookingSchema::VERSION) {
            return;
        }
        try {
            Container::getInstance()->make(ProcessJobQueueUseCase::class)->execute(time());
        } catch (\Throwable $error) {
            error_log('Wappointment: job queue pass stopped: ' . $error->getMessage());
        }
    }
}
