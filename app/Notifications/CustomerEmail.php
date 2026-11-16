<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

/**
 * Customer email from a stored `wappo_reminders` template, following legacy master
 * `Messages\ClientBookingConfirmationEmail` and its subclasses: the template subject and
 * TipTap body with tags replaced, the site logo, and, for confirmed/rescheduled/reminder
 * emails, the email footer text and save/reschedule/cancel links. Pending and canceled
 * emails carry no footer, as in legacy. Legacy left tags in subjects unreplaced (it read
 * the subject before replacing); v3 replaces them.
 */
final class CustomerEmail
{
    /**
     * @param array{subject: string, body: mixed} $template
     * @param array{footer: bool, test: bool, rtl: bool, logo_url: ?string, link_color: string, site_url: string,
     *              email_footer: string, allow_rescheduling: bool, allow_cancellation: bool,
     *              labels: array{calendar: string, reschedule: string, cancel: string}} $display
     */
    public static function compose(array $template, EmailContext $context, array $display): RenderedEmail
    {
        $tags = $context->tags();
        $subject = trim($tags->replacePlain($template['subject']));
        if ($subject === '') {
            throw new InvalidTemplateException('The email template has no subject.');
        }
        if ($display['test']) {
            $subject = '[TEST_EMAIL]' . $subject;
        }
        $content = (new TipTapRenderer($tags, $context->appointmentType))->render($template['body']);
        if (trim(strip_tags($content)) === '') {
            throw new InvalidTemplateException('The email template has no content.');
        }
        $footer = $display['footer'] ? self::footer($context, $display) : '';
        $html = EmailLayout::wrap($content, $subject, [
            'rtl' => $display['rtl'],
            'logo_url' => $display['logo_url'],
            'link_color' => $display['link_color'],
            'site_url' => $display['site_url'],
            'footer_html' => $footer,
        ]);
        return new RenderedEmail($subject, $html, HtmlToText::convert($content . $footer));
    }

    private static function footer(EmailContext $context, array $display): string
    {
        $footer = '';
        $text = trim(function_exists('wp_strip_all_tags') ? wp_strip_all_tags($display['email_footer']) : strip_tags($display['email_footer']));
        if ($text !== '') {
            $footer .= '<p>' . nl2br(Html::text($text), false) . '</p>';
        }
        $links = [];
        $candidates = [
            ['add_to_calendar', $display['labels']['calendar'], true],
            ['reschedule', $display['labels']['reschedule'], $display['allow_rescheduling']],
            ['cancel', $display['labels']['cancel'], $display['allow_cancellation']],
        ];
        foreach ($candidates as [$key, $label, $enabled]) {
            $url = $enabled && is_string($context->links[$key] ?? null) ? Html::url($context->links[$key]) : null;
            if ($url !== null && trim($label) !== '') {
                $links[] = '<a href="' . $url . '">' . Html::text($label) . '</a>';
            }
        }
        if ($links !== []) {
            $footer .= '<p>' . implode(' | ', $links) . '</p>';
        }
        return $footer;
    }
}
