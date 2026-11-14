<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\System\Settings;

class SendBookingConfirmationAction
{
    public function __construct(private Settings $settings) {}

    public function handle(string $email, array $appointment): bool
    {
        if (!$this->settings->get('mail_status', true)) {
            return false;
        }
        if (($this->settings->get('mail_config')['method'] ?? 'wpmail') !== 'wpmail') {
            error_log('Wappointment: booking recorded but the configured email transport has not been migrated.');
            return false;
        }
        $status = $appointment['status'] === 1
            ? __('Your appointment is confirmed.', 'wappointment')
            : __('Your appointment is awaiting approval.', 'wappointment');
        $pageId = (int) $this->settings->get('booking_page', 0);
        $page = $pageId > 0 ? get_post($pageId) : null;
        $url = $page && $page->post_status === 'publish' && has_shortcode($page->post_content, 'wap_widget')
            ? get_permalink($pageId) : false;
        if (!$url) {
            error_log('Wappointment: booking recorded but no published booking page with a widget is configured for the confirmation link.');
            return false;
        }
        $url .= '#wappointment=' . rawurlencode($appointment['edit_key']);
        $message = $status . "\n" . gmdate('Y-m-d H:i', $appointment['start']) . " UTC\n"
            . __('Manage your appointment:', 'wappointment') . ' ' . $url;
        $sent = wp_mail($email, __('Your Wappointment booking', 'wappointment'), $message);
        if (!$sent) {
            error_log('Wappointment: booking recorded but the confirmation email could not be sent.');
        }
        return $sent;
    }
}
