<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

use Wappointment\Jobs\SummarySchedule;

/**
 * Daily and weekly organizer summaries, with the content of legacy master
 * `Messages\AdminDailySummaryEmail` / `AdminWeeklySummaryEmail`. The translatable strings
 * are the legacy ones, unchanged, so existing translations still apply.
 *
 * Not included yet: the `.ics` attachment of the period's appointments and its "Ps" line
 * (F37), as for the other organizer emails.
 */
final class SummaryEmail
{
    /**
     * @param array{
     *   kind: string, dates: list<string>, timezone: \DateTimeZone, staff_first_name: string,
     *   appointments: list<array{start: int, end: int, buffer: int, client_name: string, client_email: string, location_name: string}>,
     *   free_slots: int, coverage: ?int, duration: int, calendar_url: ?string,
     *   date_format: string, time_format: string, link_color: string
     * } $summary
     */
    public static function compose(array $summary): RenderedEmail
    {
        $weekly = $summary['kind'] === SummarySchedule::WEEKLY;
        $zone = $summary['timezone'];
        $first = $summary['dates'][0];
        $last = $summary['dates'][count($summary['dates']) - 1];
        $firstDay = \DateTimeImmutable::createFromFormat('!Y-m-d', $first, $zone);
        $dateString = EmailContext::format($firstDay->getTimestamp(), $summary['date_format'], $zone);

        $subject = $weekly
            /* translators: %1$s - start of the week's date %2$s end of the week's date. */
            ? sprintf(__('Weekly summary  %1$s - %2$s', 'wappointment'), $first, $last)
            /* translators: %s - today's date. */
            : sprintf(__('Daily summary for %s', 'wappointment'), $dateString);

        $lines = [
            $summary['staff_first_name'] !== ''
                /* translators: %s - client's first name. */
                ? sprintf(__('Hi %s,', 'wappointment'), $summary['staff_first_name'])
                : __('Hi,', 'wappointment'),
            $weekly
                /* translators: %1$s - start of the week's date %2$s end of the week's date. */
                ? sprintf(__('Here is a summary of your appointments for this week: %1$s - %2$s', 'wappointment'), $first, $last)
                /* translators: %s - today's date. */
                : sprintf(__('Here is a summary of your appointments for %s', 'wappointment'), $dateString),
        ];
        if ($summary['coverage'] !== null) {
            /* translators: %s - number of appointments. */
            $lines[] = sprintf(__('New Appointments: %s', 'wappointment'), (string) count($summary['appointments']));
            /* translators: %1$s - numbers of slots, %2$s slots duration. */
            $lines[] = sprintf(__('Available slots: %1$s (duration %2$s min)', 'wappointment'), (string) $summary['free_slots'], (string) $summary['duration']);
            /* translators: %s - percentage. */
            $lines[] = sprintf(__('Coverage: %s', 'wappointment'), $summary['coverage'] . '%');
        } else {
            $lines[] = $weekly
                ? __('No availabilities for this week', 'wappointment')
                /* translators: %s - todays date. */
                : sprintf(__('No availabilities for %s', 'wappointment'), $dateString);
        }
        $html = self::lines($lines);

        $calendarUrl = $summary['calendar_url'] !== null ? Html::url($summary['calendar_url']) : null;
        if ($summary['free_slots'] === 0 && $calendarUrl !== null) {
            $html .= '<p style="margin:24px 0"><a href="' . $calendarUrl . '" style="display:inline-block;padding:10px 20px;'
                . 'border-radius:4px;background:' . Html::attr(self::color($summary['link_color'])) . ';color:#ffffff;text-decoration:none">'
                . Html::text(__('Open new slots', 'wappointment')) . '</a></p>';
        }

        $html .= $weekly ? self::week($summary) : self::day($summary, $dateString);
        $html .= self::lines([$weekly ? __('Have a great week!', 'wappointment') : __('Have a great day!', 'wappointment')]);

        return new RenderedEmail(
            $subject,
            EmailLayout::wrap($html, $subject, ['link_color' => $summary['link_color']]),
            HtmlToText::convert($html)
        );
    }

    /** Legacy daily list: only shown when there are appointments, under the date. */
    private static function day(array $summary, string $dateString): string
    {
        if ($summary['appointments'] === []) {
            return '';
        }
        $rows = ['<strong>' . Html::text($dateString) . '</strong>'];
        foreach ($summary['appointments'] as $appointment) {
            $rows[] = self::appointment($appointment, $summary);
        }
        return self::box($rows);
    }

    /** Legacy weekly list: every day of the period, with a note on days without appointments. */
    private static function week(array $summary): string
    {
        $zone = $summary['timezone'];
        $byDay = [];
        foreach ($summary['appointments'] as $appointment) {
            $byDay[(new \DateTimeImmutable('@' . $appointment['start']))->setTimezone($zone)->format('Y-m-d')][] = $appointment;
        }
        $rows = [];
        foreach ($summary['dates'] as $date) {
            $day = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, $zone);
            $rows[] = '<strong>' . Html::text(EmailContext::format($day->getTimestamp(), 'D Y-m-d', $zone)) . '</strong>';
            if (isset($byDay[$date])) {
                foreach ($byDay[$date] as $appointment) {
                    $rows[] = self::appointment($appointment, $summary);
                }
            } else {
                $rows[] = '<small>' . Html::text(__('No appointments for that day', 'wappointment')) . '</small>';
            }
            $rows[] = ' ';
        }
        return self::box($rows);
    }

    /** Legacy `getAppointmentFormatted`: time, client / duration without buffer - location, then email. */
    private static function appointment(array $appointment, array $summary): string
    {
        $minutes = intdiv(max(0, $appointment['end'] - $appointment['buffer'] - $appointment['start']), 60);
        return '<hr/>'
            . Html::text(EmailContext::format($appointment['start'], $summary['time_format'], $summary['timezone']))
            . ' ' . Html::text($appointment['client_name']) . ' / '
            /* translators: %s - minutes */
            . Html::text(sprintf(__('%s min', 'wappointment'), (string) $minutes))
            . ' - ' . Html::text($appointment['location_name'])
            . '<br>' . Html::text($appointment['client_email']);
    }

    /** @param list<string> $lines plain text */
    private static function lines(array $lines): string
    {
        return implode('', array_map(static fn(string $line): string => '<p>' . Html::text($line) . '</p>', $lines));
    }

    /** @param list<string> $lines already-escaped HTML */
    private static function box(array $lines): string
    {
        return '<div style="margin:16px 0;padding:16px;border:1px solid #e5e5e5;border-radius:6px;background:#fafafa">'
            . implode('<br/>', $lines) . '</div>';
    }

    private static function color(string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $color) === 1 ? $color : '#6664cb';
    }
}
