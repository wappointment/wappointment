<?php
declare(strict_types=1);

namespace Wappointment\Actions\Notifications;

use Wappointment\Jobs\BookingNotificationMessage;
use Wappointment\Jobs\BookingNotificationPlan;
use Wappointment\Jobs\PermanentJobFailure;
use Wappointment\Repositories\NotificationRepository;
use Wappointment\System\Mailer;
use Wappointment\System\Settings;

/** Sends one queued booking notification. Runs only inside the job worker. */
class DeliverBookingNotificationAction
{
    public function __construct(
        private NotificationRepository $repository,
        private Settings $settings,
        private Mailer $mailer
    ) {}

    public function handle(array $params): void
    {
        $notification = is_string($params['notification'] ?? null) ? $params['notification'] : '';
        if (!BookingNotificationPlan::exists($notification)) {
            throw new PermanentJobFailure('Unknown booking notification "' . $notification . '".');
        }
        foreach (['appointment_id', 'start', 'end'] as $key) {
            if (!is_int($params[$key] ?? null) || $params[$key] <= 0) {
                throw new PermanentJobFailure('Booking notification parameter "' . $key . '" is invalid.');
            }
        }
        if (!$this->settings->get('mail_status', true)) {
            throw new PermanentJobFailure('Email notifications were turned off before this notification was sent.');
        }
        $context = $this->repository->appointmentContext($params['appointment_id']);
        if ($context === null) {
            throw new PermanentJobFailure('Appointment ' . $params['appointment_id'] . ' no longer exists.');
        }

        $customer = BookingNotificationPlan::isCustomer($notification);
        $timezone = $customer && $context['client_timezone'] !== '' ? $context['client_timezone'] : $context['calendar_timezone'];
        $message = BookingNotificationMessage::compose($notification, [
            'client_name' => $context['client_name'],
            'client_email' => $context['client_email'],
            'client_phone' => $context['client_phone'],
            'service_name' => $context['service_name'],
            'location_name' => $context['location_name'],
            'start' => $params['start'],
            'end' => $params['end'],
            'previous_start' => is_int($params['previous_start'] ?? null) ? $params['previous_start'] : null,
            'previous_end' => is_int($params['previous_end'] ?? null) ? $params['previous_end'] : null,
            'timezone' => $timezone,
        ], $customer && $notification !== 'customer_canceled' ? $this->manageUrl($context['edit_key']) : null);

        if ($customer) {
            if (!is_email($context['client_email'])) {
                throw new PermanentJobFailure('The client has no valid email address.');
            }
            $this->mailer->send([$context['client_email']], $message['subject'], $message['body']);
            return;
        }
        $this->mailer->send(
            $this->organizerRecipients($context['calendar_wp_uid']),
            $message['subject'],
            $message['body'],
            ['email' => $context['client_email'], 'name' => $context['client_name']]
        );
    }

    private function manageUrl(string $editKey): string
    {
        $pageId = (int) $this->settings->get('booking_page', 0);
        $page = $pageId > 0 ? get_post($pageId) : null;
        $url = $page && $page->post_status === 'publish' && has_shortcode($page->post_content, 'wap_widget')
            ? get_permalink($pageId) : false;
        if (!$url) {
            throw new PermanentJobFailure('No published booking page with a widget is configured for the appointment management link.');
        }
        return $url . '#wappointment=' . rawurlencode($editKey);
    }

    /** Legacy recipients: every `email_notifications` address plus the calendar owner's address. */
    private function organizerRecipients(int $calendarUserId): array
    {
        $configured = $this->settings->get('email_notifications', '');
        $addresses = is_array($configured) ? $configured : explode(',', (string) $configured);
        if ($calendarUserId > 0) {
            $owner = get_userdata($calendarUserId);
            if ($owner) {
                $addresses[] = $owner->user_email;
            }
        }
        $valid = [];
        foreach ($addresses as $address) {
            $address = sanitize_email(trim((string) $address));
            if ($address !== '' && is_email($address)) {
                $valid[strtolower($address)] = $address;
            }
        }
        return array_values($valid);
    }
}
