<?php
declare(strict_types=1);

namespace Wappointment\Actions\Jobs;

use Wappointment\Jobs\BookingNotificationPlan;
use Wappointment\Jobs\JobRegistry;
use Wappointment\Jobs\QueuedJob;
use Wappointment\Repositories\JobQueueRepository;
use Wappointment\Repositories\NotificationRepository;
use Wappointment\System\Settings;

/**
 * Record the notifications for one booking transition as durable jobs. Must be called
 * inside the booking transaction so the jobs commit or roll back with the change.
 * The payload holds IDs and times only; contact details are read at delivery.
 */
class QueueBookingNotificationsAction
{
    public function __construct(
        private JobQueueRepository $queue,
        private NotificationRepository $notifications,
        private Settings $settings
    ) {}

    /**
     * @param array $appointment the stored appointment row after the transition
     * @param array|null $previous the row before a reschedule
     * @return bool whether a customer notification was queued
     */
    public function handle(string $transition, array $appointment, ?array $previous = null): bool
    {
        $settings = $this->settings->all();
        $events = empty($settings['mail_status']) ? [] : $this->notifications->publishedEmailReminderEvents();
        $planned = BookingNotificationPlan::for($transition, $settings, $events);
        if ($planned === []) {
            return false;
        }
        [$start, $end] = $this->times($appointment);
        [$previousStart, $previousEnd] = $previous !== null ? $this->times($previous) : [null, null];
        $now = time();
        foreach ($planned as $notification) {
            $this->queue->enqueue(
                JobRegistry::queueFor(JobRegistry::BOOKING_NOTIFICATION),
                QueuedJob::encode(JobRegistry::BOOKING_NOTIFICATION, [
                    'notification' => $notification,
                    'appointment_id' => (int) $appointment['id'],
                    'start' => $start,
                    'end' => $end,
                    'previous_start' => $previousStart,
                    'previous_end' => $previousEnd,
                ]),
                (int) $appointment['id'],
                0,
                $now
            );
        }
        return (bool) array_filter($planned, [BookingNotificationPlan::class, 'isCustomer']);
    }

    /** @return array{int, int} start and end excluding the reserved buffer */
    private function times(array $appointment): array
    {
        $options = is_array($appointment['options'] ?? null)
            ? $appointment['options']
            : (json_decode((string) ($appointment['options'] ?? ''), true) ?: []);
        $buffer = (int) ($options['buffer_time'] ?? 0);
        $utc = new \DateTimeZone('UTC');
        $start = (new \DateTimeImmutable($appointment['start_at'], $utc))->getTimestamp();
        $end = (new \DateTimeImmutable($appointment['end_at'], $utc))->getTimestamp() - $buffer * 60;
        return [$start, $end];
    }
}
