<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

/**
 * Plain-text booking notifications. These are the basic v3 messages; rendering the
 * stored `wappo_reminders` templates is the separate F32 slice.
 */
final class BookingNotificationMessage
{
    /**
     * @param array{client_name: string, client_email: string, client_phone?: string, service_name: string,
     *              location_name: string, start: int, end: int, previous_start?: ?int,
     *              previous_end?: ?int, timezone: string} $context
     * @return array{subject: string, body: string}
     */
    public static function compose(string $notification, array $context, ?string $manageUrl): array
    {
        $when = self::range($context['start'], $context['end'], $context['timezone']);
        $previous = !empty($context['previous_start']) && !empty($context['previous_end'])
            ? self::range((int) $context['previous_start'], (int) $context['previous_end'], $context['timezone'])
            : null;
        [$subject, $intro] = match ($notification) {
            'customer_confirmed' => [__('Your appointment is confirmed', 'wappointment'), __('Your appointment is confirmed.', 'wappointment')],
            'customer_pending' => [__('Your appointment request was received', 'wappointment'), __('Your appointment is awaiting approval. You will receive another email once it is confirmed.', 'wappointment')],
            'customer_rescheduled' => [__('Your appointment was rescheduled', 'wappointment'), __('Your appointment has been rescheduled.', 'wappointment')],
            'customer_canceled' => [__('Your appointment was canceled', 'wappointment'), __('Your appointment has been canceled.', 'wappointment')],
            'admin_new' => [sprintf(__('New appointment: %s', 'wappointment'), $context['client_name']), __('A new appointment was booked.', 'wappointment')],
            'admin_pending' => [sprintf(__('Appointment awaiting approval: %s', 'wappointment'), $context['client_name']), __('A new appointment is awaiting your approval.', 'wappointment')],
            'admin_rescheduled' => [sprintf(__('Appointment rescheduled: %s', 'wappointment'), $context['client_name']), __('An appointment was rescheduled.', 'wappointment')],
            'admin_canceled' => [sprintf(__('Appointment canceled: %s', 'wappointment'), $context['client_name']), __('An appointment was canceled.', 'wappointment')],
            default => throw new PermanentJobFailure('Unknown booking notification "' . $notification . '".'),
        };

        $lines = [$intro, ''];
        if ($context['service_name'] !== '') {
            $lines[] = sprintf(__('Service: %s', 'wappointment'), $context['service_name']);
        }
        $lines[] = sprintf(__('When: %s', 'wappointment'), $when);
        if ($previous !== null) {
            $lines[] = sprintf(__('Previously: %s', 'wappointment'), $previous);
        }
        if ($context['location_name'] !== '') {
            $lines[] = sprintf(__('Location: %s', 'wappointment'), $context['location_name']);
        }
        if (!BookingNotificationPlan::isCustomer($notification)) {
            $client = $context['client_name'] . ' <' . $context['client_email'] . '>';
            $lines[] = sprintf(__('Client: %s', 'wappointment'), $client);
            if (($context['client_phone'] ?? '') !== '') {
                $lines[] = sprintf(__('Phone: %s', 'wappointment'), $context['client_phone']);
            }
        }
        if ($manageUrl !== null) {
            $lines[] = '';
            $lines[] = __('Manage your appointment:', 'wappointment') . ' ' . $manageUrl;
        }

        return ['subject' => $subject, 'body' => implode("\n", $lines) . "\n"];
    }

    private static function range(int $start, int $end, string $timezone): string
    {
        $zone = self::zone($timezone);
        $from = (new \DateTimeImmutable('@' . $start))->setTimezone($zone);
        $to = (new \DateTimeImmutable('@' . $end))->setTimezone($zone);
        $date = function_exists('wp_date')
            ? wp_date('l j F Y, H:i', $start, $zone)
            : $from->format('l j F Y, H:i');
        $sameDay = $from->format('Y-m-d') === $to->format('Y-m-d');
        $until = function_exists('wp_date')
            ? wp_date($sameDay ? 'H:i' : 'l j F Y, H:i', $end, $zone)
            : $to->format($sameDay ? 'H:i' : 'l j F Y, H:i');
        return $date . ' - ' . $until . ' (' . $zone->getName() . ')';
    }

    private static function zone(string $timezone): \DateTimeZone
    {
        try {
            return new \DateTimeZone($timezone !== '' ? $timezone : 'UTC');
        } catch (\Exception) {
            return new \DateTimeZone('UTC');
        }
    }
}
