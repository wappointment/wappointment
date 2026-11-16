<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

use Wappointment\Jobs\PermanentJobFailure;

/**
 * Organizer emails. Legacy master did not use stored templates for these; it built them
 * in `Messages\Admin*AppointmentEmail` with `AdminGeneratesDefault`. v3 keeps that
 * content: greeting with the staff first name, a detail box (date, time without buffer,
 * staff, service, location, client name/email/phone), the former slot for reschedules,
 * and a link to the admin screen for pending approvals. Times are in the calendar's
 * timezone. The ICS attachment and its "Ps" line wait for the ICS export work (F37).
 */
final class AdminEmail
{
    public static function compose(string $notification, EmailContext $context, ?string $adminUrl, string $linkColor): RenderedEmail
    {
        [$subject, $intro] = match ($notification) {
            'admin_new' => [__('New appointment', 'wappointment'), [__('Great news! You just got booked!', 'wappointment')]],
            'admin_pending' => [__('Pending appointment', 'wappointment'), [
                __('A new appointment is pending!', 'wappointment'),
                __('Please confirm the appointment.', 'wappointment'),
            ]],
            'admin_rescheduled' => [__('Rescheduled appointment', 'wappointment'), [__('A client rescheduled his appointment, find the details below.', 'wappointment')]],
            'admin_canceled' => [__('Cancelled appointment', 'wappointment'), [__('Unfortunately a client cancelled his appointment.', 'wappointment')]],
            default => throw new PermanentJobFailure('Unknown organizer notification "' . $notification . '".'),
        };
        $zone = $context->zone();
        $formats = $context->formats;
        $greeting = $context->staffFirstName !== ''
            /* translators: %s - staff first name. */
            ? sprintf(__('Hi %s,', 'wappointment'), $context->staffFirstName)
            : __('Hi,', 'wappointment');

        $html = self::lines([$greeting, ...$intro]);
        $details = [
            '<u>' . Html::text($subject) . '</u>',
            /* translators: %s is replaced with the date the appointment starts at */
            Html::text(sprintf(__('Date: %s', 'wappointment'), EmailContext::format($context->start, $formats['date_format'], $zone))),
            /* translators: %1$s is replaced with the start time, %2$s is replaced with the end time */
            Html::text(sprintf(__('Time: %1$s - %2$s', 'wappointment'),
                EmailContext::format($context->start, $formats['time_format'], $zone),
                EmailContext::format($context->end, $formats['time_format'], $zone))),
            /* translators: %s is replaced with the staff name */
            Html::text(sprintf(__('Staff: %s', 'wappointment'), $context->staffName)),
            /* translators: %s is replaced with the service name */
            Html::text(sprintf(__('Service: %s', 'wappointment'), $context->serviceName)),
            /* translators: %s is replaced with the location name */
            Html::text(sprintf(__('Location: %s', 'wappointment'), $context->locationName)),
            /* translators: %s is replaced with the client's name */
            Html::text(sprintf(__("Client's name: %s", 'wappointment'), $context->clientName)),
            /* translators: %s is replaced with the client's email */
            Html::text(sprintf(__("Client's email: %s", 'wappointment'), $context->clientEmail)),
        ];
        if ($context->clientPhone !== '') {
            /* translators: %s is replaced with the client's phone */
            $details[] = Html::text(sprintf(__("Client's phone: %s", 'wappointment'), $context->clientPhone));
        }
        $html .= self::box($details);

        if ($notification === 'admin_rescheduled' && $context->previousStart !== null && $context->previousEnd !== null) {
            $html .= self::box([
                '<u>' . Html::text(__('Former appointment', 'wappointment')) . '</u>',
                Html::text(sprintf(__('Date: %s', 'wappointment'), EmailContext::format($context->previousStart, $formats['date_format'], $zone))),
                Html::text(sprintf(__('Time: %1$s - %2$s', 'wappointment'),
                    EmailContext::format($context->previousStart, $formats['time_format'], $zone),
                    EmailContext::format($context->previousEnd, $formats['time_format'], $zone))),
            ]);
        }
        $url = $notification === 'admin_pending' && $adminUrl !== null ? Html::url($adminUrl) : null;
        if ($url !== null) {
            $html .= '<p style="text-align:center;margin:24px 0"><a href="' . $url . '" style="display:inline-block;padding:10px 20px;'
                . 'border-radius:4px;background:' . Html::attr(self::color($linkColor)) . ';color:#ffffff;text-decoration:none">'
                . Html::text(__('Confirm appointment', 'wappointment')) . '</a></p>';
        }
        $html .= self::lines([__('Have a great day!', 'wappointment')]);

        return new RenderedEmail(
            $subject,
            EmailLayout::wrap($html, $subject, ['link_color' => $linkColor]),
            HtmlToText::convert($html)
        );
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
