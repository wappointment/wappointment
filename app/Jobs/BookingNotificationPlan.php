<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

/**
 * Which notifications a booking transition produces, following legacy master
 * (`System\Listeners`): nothing when `mail_status` is off; a customer email only when a
 * published email reminder (`wappo_reminders.type = 1`) exists for the matching event;
 * an organizer email when its `notify_*` toggle is on.
 */
final class BookingNotificationPlan
{
    public const BOOKED_PENDING = 'booked_pending';
    public const CONFIRMED = 'confirmed';
    public const RESCHEDULED = 'rescheduled';
    public const CANCELED = 'canceled';

    /**
     * Legacy `Reminder::APPOINTMENT_*` event codes for the customer notifications.
     * `customer_reminder` (event 1) is scheduled per reminder by
     * `QueueBookingNotificationsAction`, not by a transition.
     */
    public const CUSTOMER_EVENTS = [
        'customer_reminder' => 1,
        'customer_confirmed' => 2,
        'customer_rescheduled' => 3,
        'customer_canceled' => 4,
        'customer_pending' => 5,
    ];

    public const ADMIN_TOGGLES = [
        'admin_new' => 'notify_new_appointments',
        'admin_pending' => 'notify_pending_appointments',
        'admin_rescheduled' => 'notify_rescheduled_appointments',
        'admin_canceled' => 'notify_canceled_appointments',
    ];

    private const TRANSITIONS = [
        self::BOOKED_PENDING => ['customer_pending', 'admin_pending'],
        self::CONFIRMED => ['customer_confirmed', 'admin_new'],
        self::RESCHEDULED => ['customer_rescheduled', 'admin_rescheduled'],
        self::CANCELED => ['customer_canceled', 'admin_canceled'],
    ];

    /**
     * @param array<string, mixed> $settings
     * @param list<int> $publishedEmailEvents
     * @return list<string>
     */
    public static function for(string $transition, array $settings, array $publishedEmailEvents): array
    {
        if (!isset(self::TRANSITIONS[$transition])) {
            throw new \InvalidArgumentException('Unknown booking transition "' . $transition . '".');
        }
        if (empty($settings['mail_status'])) {
            return [];
        }
        [$customer, $admin] = self::TRANSITIONS[$transition];
        $planned = [];
        if (in_array(self::CUSTOMER_EVENTS[$customer], array_map('intval', $publishedEmailEvents), true)) {
            $planned[] = $customer;
        }
        if (!empty($settings[self::ADMIN_TOGGLES[$admin]])) {
            $planned[] = $admin;
        }
        return $planned;
    }

    public static function isCustomer(string $notification): bool
    {
        return isset(self::CUSTOMER_EVENTS[$notification]);
    }

    public static function exists(string $notification): bool
    {
        return isset(self::CUSTOMER_EVENTS[$notification]) || isset(self::ADMIN_TOGGLES[$notification]);
    }
}
