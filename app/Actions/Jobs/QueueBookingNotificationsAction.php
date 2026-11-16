<?php
declare(strict_types=1);

namespace Wappointment\Actions\Jobs;

use Wappointment\Jobs\BookingNotificationPlan;
use Wappointment\Jobs\JobRegistry;
use Wappointment\Jobs\QueuedJob;
use Wappointment\Jobs\ReminderSchedule;
use Wappointment\Repositories\JobQueueRepository;
use Wappointment\Repositories\NotificationRepository;
use Wappointment\System\Settings;

/**
 * Record the notifications for one booking transition as durable jobs. Must be called
 * inside the booking transaction so the jobs commit or roll back with the change.
 * The payload holds IDs and times only; contact details are read at delivery.
 *
 * Timed reminders follow legacy master (`AppointmentReminderListener`, `IsReminder`,
 * `Queue::cancelAppointmentJob`): one job per published "before the appointment"
 * reminder when the appointment is confirmed, none for test appointments or times
 * already past, and pending reminders removed when the appointment is canceled or
 * rescheduled. Legacy did not schedule them again after a reschedule, so rescheduled
 * clients got no reminder; v3 schedules them for the new time.
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
     * @return bool whether a customer notification was queued for the transition
     */
    public function handle(string $transition, array $appointment, ?array $previous = null): bool
    {
        $settings = $this->settings->all();
        $events = empty($settings['mail_status']) ? [] : $this->notifications->publishedEmailReminderEvents();
        $planned = BookingNotificationPlan::for($transition, $settings, $events);
        $appointmentId = (int) $appointment['id'];
        [$start, $end] = $this->times($appointment);
        [$previousStart, $previousEnd] = $previous !== null ? $this->times($previous) : [null, null];
        $now = time();
        foreach ($planned as $notification) {
            $this->enqueue($appointmentId, 0, $now, [
                'notification' => $notification,
                'appointment_id' => $appointmentId,
                'start' => $start,
                'end' => $end,
                'previous_start' => $previousStart,
                'previous_end' => $previousEnd,
            ]);
        }

        if ($transition !== BookingNotificationPlan::BOOKED_PENDING) {
            // Confirming again or moving the slot replaces earlier reminders; canceling drops them.
            $this->removePendingReminders($appointmentId);
        }
        $confirmed = (int) ($appointment['status'] ?? 0) === 1;
        if (in_array($transition, [BookingNotificationPlan::CONFIRMED, BookingNotificationPlan::RESCHEDULED], true)
            && $confirmed && !empty($settings['mail_status'])
            && !$this->notifications->isTestClient((int) ($appointment['client_id'] ?? 0))) {
            foreach ($this->notifications->publishedTimedReminders() as $reminder) {
                $sendAt = ReminderSchedule::sendAt($reminder['options'], $start, $now);
                if ($sendAt === null) {
                    continue;
                }
                $this->enqueue($appointmentId, $sendAt, $now, [
                    'notification' => 'customer_reminder',
                    'reminder_id' => $reminder['id'],
                    'appointment_id' => $appointmentId,
                    'start' => $start,
                    'end' => $end,
                ]);
            }
        }

        return (bool) array_filter($planned, [BookingNotificationPlan::class, 'isCustomer']);
    }

    private function enqueue(int $appointmentId, int $availableAt, int $now, array $params): void
    {
        $this->queue->enqueue(
            JobRegistry::queueFor(JobRegistry::BOOKING_NOTIFICATION),
            QueuedJob::encode(JobRegistry::BOOKING_NOTIFICATION, $params),
            $appointmentId,
            $availableAt,
            $now
        );
    }

    /** Delete unclaimed timed reminders for the appointment; other notifications are kept. */
    private function removePendingReminders(int $appointmentId): void
    {
        $queue = JobRegistry::queueFor(JobRegistry::BOOKING_NOTIFICATION);
        foreach ($this->queue->unclaimedForAppointment($queue, $appointmentId) as $row) {
            $payload = json_decode((string) ($row['payload'] ?? ''), true);
            if (is_array($payload) && ($payload['job'] ?? null) === JobRegistry::BOOKING_NOTIFICATION
                && ($payload['params']['notification'] ?? null) === 'customer_reminder') {
                $this->queue->deleteUnclaimed((int) $row['id']);
            }
        }
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
