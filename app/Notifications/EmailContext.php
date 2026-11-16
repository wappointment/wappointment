<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

/**
 * Everything a booking email shows, read at delivery time. Times are Unix seconds and
 * `end` excludes the reserved buffer. Links are already-built absolute URLs, or null
 * when the site has no published booking page to point them at.
 */
final class EmailContext
{
    /**
     * @param array{add_to_calendar: ?string, reschedule: ?string, cancel: ?string, view: ?string, new: ?string, new_staff: ?string} $links
     * @param array{date_format: string, time_format: string, date_time_union: string} $formats
     */
    public function __construct(
        public readonly string $clientName,
        public readonly string $clientEmail,
        public readonly string $clientPhone,
        public readonly string $serviceName,
        public readonly string $serviceAddress,
        public readonly string $locationName,
        public readonly int $appointmentType,
        public readonly string $staffName,
        public readonly string $staffFirstName,
        public readonly int $start,
        public readonly int $end,
        public readonly ?int $previousStart,
        public readonly ?int $previousEnd,
        public readonly string $timezone,
        public readonly array $links,
        public readonly array $formats,
    ) {}

    public function tags(): EmailTags
    {
        return new EmailTags([
            'client:name' => $this->clientName,
            'client:email' => $this->clientEmail,
            'client:phone' => $this->clientPhone,
            'service:name' => $this->serviceName,
            'service:address' => $this->serviceAddress,
            /* translators: %s - minutes */
            'appointment:duration' => sprintf(__('%s min', 'wappointment'), (string) intdiv(max(0, $this->end - $this->start), 60)),
            'appointment:location' => $this->locationName,
            'appointment:starts' => $this->startsDayAndTime(),
            'staff:name' => $this->staffName,
            // Order tables belong to the order migration (F40); free bookings have none.
            'order:summary' => '',
            'appointment:linkAddEventToCalendar' => $this->links['add_to_calendar'] ?? '',
            'appointment:linkRescheduleEvent' => $this->links['reschedule'] ?? '',
            'appointment:linkCancelEvent' => $this->links['cancel'] ?? '',
            'appointment:linkNew' => $this->links['new'] ?? '',
            'appointment:linkNewStaff' => $this->links['new_staff'] ?? '',
            'appointment:linkView' => $this->links['view'] ?? '',
        ]);
    }

    /** Legacy `getStartsDayAndTime`: date format, union and time format, then the zone. */
    public function startsDayAndTime(): string
    {
        $format = $this->formats['date_format'] . $this->formats['date_time_union'] . $this->formats['time_format'];
        return self::format($this->start, $format, $this->zone()) . ' (' . $this->zone()->getName() . ')';
    }

    public function zone(): \DateTimeZone
    {
        try {
            return new \DateTimeZone($this->timezone !== '' ? $this->timezone : 'UTC');
        } catch (\Exception) {
            return new \DateTimeZone('UTC');
        }
    }

    public static function format(int $timestamp, string $format, \DateTimeZone $zone): string
    {
        if (function_exists('wp_date')) {
            $formatted = wp_date($format, $timestamp, $zone);
            if (is_string($formatted)) {
                return $formatted;
            }
        }
        return (new \DateTimeImmutable('@' . $timestamp))->setTimezone($zone)->format($format);
    }
}
