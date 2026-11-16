<?php
declare(strict_types=1);

namespace Wappointment\Actions\Notifications;

use Wappointment\Booking\Availability;
use Wappointment\Booking\BookingException;
use Wappointment\Jobs\PermanentJobFailure;
use Wappointment\Jobs\SummaryFigures;
use Wappointment\Jobs\SummarySchedule;
use Wappointment\Notifications\OrganizerRecipients;
use Wappointment\Notifications\SummaryEmail;
use Wappointment\Repositories\BookingRepository;
use Wappointment\Repositories\SummaryRepository;
use Wappointment\System\Mailer;
use Wappointment\System\Settings;

/**
 * Sends one daily or weekly organizer summary for the period stored in the job. Runs
 * only inside the job worker.
 *
 * Legacy master sent summaries whatever `mail_status` was (that switch only gated the
 * booking listeners), to every `email_notifications` address plus the calendar owner.
 * v3 keeps both. A summary is refused, visibly in failed jobs, when it was turned off,
 * when its period was already sent, or when the period has already begun (a delayed
 * worker would otherwise describe a day that is under way).
 */
class DeliverAdminSummaryAction
{
    public function __construct(
        private SummaryRepository $summaries,
        private BookingRepository $booking,
        private Availability $availability,
        private Settings $settings,
        private Mailer $mailer
    ) {}

    public function handle(array $params, int $now): void
    {
        $kind = $params['kind'] ?? null;
        if (!in_array($kind, SummarySchedule::KINDS, true)) {
            throw new PermanentJobFailure('Unknown summary kind.');
        }
        if (!is_int($params['calendar_id'] ?? null) || $params['calendar_id'] <= 0 || !SummarySchedule::validDate($params['period_start'] ?? null)) {
            throw new PermanentJobFailure('Summary parameters are invalid.');
        }
        $settings = $this->settings->all();
        if (!SummarySchedule::enabled($kind, $settings)) {
            throw new PermanentJobFailure('The ' . $kind . ' summary was turned off before it was sent.');
        }
        $calendar = $this->summaries->calendars($params['calendar_id'])[0] ?? null;
        if ($calendar === null) {
            throw new PermanentJobFailure('Calendar ' . $params['calendar_id'] . ' no longer receives summaries.');
        }
        $periodStart = $params['period_start'];
        $lastSent = $this->summaries->lastSent()[SummaryRepository::key($kind, $calendar['id'])] ?? null;
        if ($lastSent !== null && $lastSent >= $periodStart) {
            throw new PermanentJobFailure('The ' . $kind . ' summary for ' . $periodStart . ' was already sent.');
        }
        $zone = $this->zone($calendar['timezone']);
        $period = SummarySchedule::period($kind, $periodStart, $zone);
        if ($now >= $period['from']) {
            throw new PermanentJobFailure('The ' . $kind . ' summary for ' . $periodStart . ' was not sent before the period began.');
        }

        $service = $settings['service'] ?? [];
        $duration = $calendar['duration'] ?? (is_array($service) && is_numeric($service['duration'] ?? null) ? (int) $service['duration'] : 60);
        $duration = max(5, $duration);
        $appointments = $this->summaries->appointments($calendar['id'], $period['from'], $period['to']);
        $free = $this->freeWindows($calendar, $settings, $period, $now);
        $booked = array_map(static fn(array $row): array => [$row['start'], $row['end']], $appointments);
        $owner = $calendar['wp_uid'] > 0 ? get_userdata($calendar['wp_uid']) : false;

        $email = SummaryEmail::compose([
            'kind' => $kind,
            'dates' => $period['dates'],
            'timezone' => $zone,
            'staff_first_name' => $owner ? (string) $owner->first_name : '',
            'appointments' => $appointments,
            'free_slots' => SummaryFigures::freeSlots($free, $duration * 60),
            'coverage' => SummaryFigures::coverage($booked, $free, $duration * 60),
            'duration' => $duration,
            'calendar_url' => admin_url('admin.php?page=wappointment-appointments'),
            'date_format' => (string) (($settings['date_format'] ?? '') ?: get_option('date_format', 'F j, Y')),
            'time_format' => (string) (($settings['time_format'] ?? '') ?: get_option('time_format', 'g:i a')),
            'link_color' => (string) ($settings['email_link_color'] ?? '#6664cb'),
        ]);
        $this->mailer->send(
            OrganizerRecipients::resolve($settings['email_notifications'] ?? '', $owner ? (string) $owner->user_email : null),
            $email
        );
        // The email is out: a failure to record it must not turn into a retry and a duplicate.
        if (!$this->summaries->markSent($kind, $calendar['id'], $periodStart)) {
            error_log('Wappointment: the ' . $kind . ' summary for calendar ' . $calendar['id'] . ' was sent but could not be recorded.');
        }
    }

    /**
     * Free time per local day from the v3 availability engine, limited to the days the
     * calendar accepts bookings for (legacy precomputed availability stopped at its horizon).
     *
     * @return list<array{int, int}>
     */
    private function freeWindows(array $calendar, array $settings, array $period, int $now): array
    {
        $engineCalendar = ['options' => $calendar['options']];
        try {
            $latest = $this->availability->latestBookableDate($engineCalendar, $settings, $now);
            [$busy, $extra] = $this->booking->intervals($calendar['id'], $period['from'], $period['to']);
            $free = [];
            foreach ($period['dates'] as $date) {
                if ($date > $latest) {
                    break;
                }
                array_push($free, ...$this->availability->freeWindows($date, $engineCalendar, $busy, $extra));
            }
            return $free;
        } catch (BookingException $error) {
            // Same rule as booking: availability that v3 cannot interpret (for example
            // Services conditions) is not guessed at.
            throw new PermanentJobFailure('Calendar ' . $calendar['id'] . ' availability cannot be summarized: ' . $error->getMessage(), 0, $error);
        }
    }

    private function zone(string $timezone): \DateTimeZone
    {
        try {
            return new \DateTimeZone($timezone !== '' ? $timezone : 'UTC');
        } catch (\Exception) {
            throw new PermanentJobFailure('The calendar timezone "' . $timezone . '" is invalid.');
        }
    }
}
