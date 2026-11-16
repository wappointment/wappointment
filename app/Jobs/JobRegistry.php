<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

/**
 * The closed list of job types v3 executes. Payloads name a type, never a class,
 * so stored data cannot choose which code runs.
 */
final class JobRegistry
{
    public const BOOKING_NOTIFICATION = 'booking_notification';

    /** Queue names written by v3. Legacy queues (client, admin, api, availability, daily, ...) are left untouched. */
    public const NOTIFICATIONS_QUEUE = 'v3_notifications';

    private const TYPES = [
        self::BOOKING_NOTIFICATION => [
            'queue' => self::NOTIFICATIONS_QUEUE,
            'handler' => BookingNotificationJob::class,
        ],
    ];

    /** @return list<string> */
    public static function queues(): array
    {
        return array_values(array_unique(array_column(self::TYPES, 'queue')));
    }

    public static function queueFor(string $type): string
    {
        return self::definition($type)['queue'];
    }

    /** @return class-string<JobHandler> */
    public static function handlerFor(string $type): string
    {
        return self::definition($type)['handler'];
    }

    public static function has(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    private static function definition(string $type): array
    {
        if (!isset(self::TYPES[$type])) {
            throw new UnsupportedJobException('Unregistered job type "' . $type . '".');
        }
        return self::TYPES[$type];
    }
}
