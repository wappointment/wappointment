<?php
declare(strict_types=1);

namespace Wappointment\Actions\Notifications;

use Wappointment\Jobs\BookingNotificationPlan;
use Wappointment\Jobs\PermanentJobFailure;
use Wappointment\Notifications\AdminEmail;
use Wappointment\Notifications\CustomerEmail;
use Wappointment\Notifications\EmailContext;
use Wappointment\Notifications\InvalidTemplateException;
use Wappointment\Repositories\NotificationRepository;
use Wappointment\System\Mailer;
use Wappointment\System\Settings;

/**
 * Sends one queued booking notification. Runs only inside the job worker. Customer
 * emails render the published `wappo_reminders` template for the event; organizer
 * emails use the fixed legacy content.
 */
class DeliverBookingNotificationAction
{
    /** Customer notifications that carry the footer links (legacy `HasAppointmentFooterLinks`). */
    private const WITH_FOOTER = ['customer_confirmed', 'customer_rescheduled'];

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
        $appointment = $this->repository->appointmentContext($params['appointment_id']);
        if ($appointment === null) {
            throw new PermanentJobFailure('Appointment ' . $params['appointment_id'] . ' no longer exists.');
        }

        $customer = BookingNotificationPlan::isCustomer($notification);
        $timezone = $customer && $appointment['client_timezone'] !== '' ? $appointment['client_timezone'] : $appointment['calendar_timezone'];
        $context = $this->context($appointment, $params, $timezone);
        $linkColor = (string) $this->settings->get('email_link_color', '#6664cb');

        if (!$customer) {
            $email = AdminEmail::compose($notification, $context, admin_url('admin.php?page=wappointment-appointments'), $linkColor);
            $this->mailer->send(
                $this->organizerRecipients($appointment['calendar_wp_uid']),
                $email,
                ['email' => $appointment['client_email'], 'name' => $appointment['client_name']]
            );
            return;
        }

        if (!is_email($appointment['client_email'])) {
            throw new PermanentJobFailure('The client has no valid email address.');
        }
        $event = BookingNotificationPlan::CUSTOMER_EVENTS[$notification];
        $template = $this->repository->emailTemplate($event, $appointment['client_locale']);
        if ($template === null) {
            throw new PermanentJobFailure('No published email template exists for this notification any more.');
        }
        $logo = $this->settings->get('email_logo', false);
        try {
            $email = CustomerEmail::compose($template, $context, [
                'footer' => in_array($notification, self::WITH_FOOTER, true),
                'test' => $appointment['test_appointment'],
                'rtl' => $appointment['client_rtl'],
                'logo_url' => is_array($logo) && is_string($logo['src'] ?? null) ? $logo['src'] : null,
                'link_color' => $linkColor,
                'site_url' => site_url(),
                'email_footer' => (string) $this->settings->get('email_footer', ''),
                'allow_rescheduling' => (bool) $this->settings->get('allow_rescheduling', true),
                'allow_cancellation' => (bool) $this->settings->get('allow_cancellation', true),
                'labels' => [
                    'calendar' => (string) $this->settings->get('save_appointment_text_link', 'Save to calendar'),
                    'reschedule' => (string) $this->settings->get('reschedule_link', 'Reschedule'),
                    'cancel' => (string) $this->settings->get('cancellation_link', 'Cancel'),
                ],
            ]);
        } catch (InvalidTemplateException $error) {
            throw new PermanentJobFailure('Email template ' . $template['id'] . ' cannot be rendered: ' . $error->getMessage(), 0, $error);
        }
        $this->mailer->send([$appointment['client_email']], $email);
    }

    private function context(array $appointment, array $params, string $timezone): EmailContext
    {
        $service = $this->settings->get('service', []);
        $address = $appointment['location_address'] !== ''
            ? $appointment['location_address']
            : (is_array($service) && is_string($service['address'] ?? null) ? $service['address'] : '');
        $owner = $appointment['calendar_wp_uid'] > 0 ? get_userdata($appointment['calendar_wp_uid']) : false;
        return new EmailContext(
            clientName: $appointment['client_name'],
            clientEmail: $appointment['client_email'],
            clientPhone: $appointment['client_phone'],
            serviceName: $appointment['service_name'],
            serviceAddress: $address,
            locationName: $appointment['location_name'],
            appointmentType: $appointment['type'],
            staffName: $appointment['calendar_name'],
            staffFirstName: $owner ? (string) $owner->first_name : '',
            start: $params['start'],
            end: $params['end'],
            previousStart: is_int($params['previous_start'] ?? null) ? $params['previous_start'] : null,
            previousEnd: is_int($params['previous_end'] ?? null) ? $params['previous_end'] : null,
            timezone: $timezone,
            links: $this->links($appointment['edit_key'], $appointment['staff_id']),
            formats: [
                'date_format' => (string) ($this->settings->get('date_format') ?: get_option('date_format', 'F j, Y')),
                'time_format' => (string) ($this->settings->get('time_format') ?: get_option('time_format', 'g:i a')),
                'date_time_union' => (string) $this->settings->get('date_time_union', ' - '),
            ],
        );
    }

    /**
     * Legacy link format (`ManipulateLinks::getPageLink`): `view` and `appointmentkey` query
     * arguments on the booking page, which the v3 widget resolves. Links are null when no
     * published booking page holds the widget; templates then render the link text only.
     */
    private function links(string $editKey, int $staffId): array
    {
        $page = $this->bookingPageUrl();
        $managed = static fn(string $view): ?string => $page !== null && $editKey !== ''
            ? add_query_arg(['view' => $view, 'appointmentkey' => $editKey], $page) : null;
        $new = $page !== null ? add_query_arg('view', 'new-event', $page) : null;
        return [
            'add_to_calendar' => $managed('add-event-to-calendar'),
            'reschedule' => $managed('reschedule-event'),
            'cancel' => $managed('cancel-event'),
            // Provider meeting URLs (legacy linkView for video appointments) wait for F38.
            'view' => $managed('view-event'),
            'new' => $new,
            'new_staff' => $new !== null && $staffId > 0 ? add_query_arg('staff', $staffId, $new) : null,
        ];
    }

    /** The v3 `booking_page`, else the legacy `front_page`, when it is published and holds the widget. */
    private function bookingPageUrl(): ?string
    {
        foreach (['booking_page', 'front_page'] as $setting) {
            $pageId = (int) $this->settings->get($setting, 0);
            $page = $pageId > 0 ? get_post($pageId) : null;
            if ($page && $page->post_status === 'publish' && $page->post_type !== 'wappointment'
                && (has_shortcode($page->post_content, 'wap_widget') || has_shortcode($page->post_content, 'wappointment_page'))) {
                $url = get_permalink($page);
                if (is_string($url) && $url !== '') {
                    return $url;
                }
            }
        }
        return null;
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
