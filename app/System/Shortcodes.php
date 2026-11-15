<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Http\Requests\CustomerHistoryRequestData;
use Wappointment\Http\Requests\StaffHistoryRequestData;
use Wappointment\Http\Data\HistoryAppointmentData;
use Wappointment\UseCases\History\ListCustomerAppointmentHistoryUseCase;
use Wappointment\UseCases\History\ListStaffAppointmentHistoryUseCase;

/**
 * Shortcodes registration
 */
class Shortcodes
{
    /**
     * Register all shortcodes
     */
    public function register(): void
    {
        add_shortcode('wap_widget', [$this, 'renderBookingWidget']);
        add_shortcode('wappointment_page', [$this, 'renderLegacyBookingPage']);
        add_shortcode('wap_history', [$this, 'renderCustomerHistory']);
        add_shortcode('wap_staff_history', [$this, 'renderStaffHistory']);
        add_action('widgets_init', static function (): void {
            register_widget(WordPressBookingWidget::class);
        });
    }

    /**
     * Render the booking widget shortcode
     */
    public function renderBookingWidget($atts = []): string
    {
        $rawAttributes = is_array($atts) ? $atts : [];
        $attributes = shortcode_atts([
            'title' => __('Book now', 'wappointment'),
            'button_title' => '',
            'service' => '',
            'service_id' => '',
            'calendar' => '',
            'calendar_id' => '',
            'location' => '',
            'location_id' => '',
            'duration' => '',
        ], $rawAttributes);
        $flags = ['center', 'open', 'large', 'popup', 'close_cross', 'week', 'brc_floats'];
        $configuration = [];
        foreach ($flags as $flag) {
            if (array_key_exists($flag, $rawAttributes) && !is_scalar($rawAttributes[$flag])) {
                return '<p role="alert">' . esc_html__('This booking widget has an invalid display option. Please contact the site owner.', 'wappointment') . '</p>';
            }
            $configuration[$flag] = $this->shortcodeFlag($rawAttributes, $flag);
        }

        foreach ([
            'service_id' => $this->shortcodeValue($attributes['service_id']) ?: $this->shortcodeValue($attributes['service']),
            'calendar_id' => $this->shortcodeValue($attributes['calendar_id']) ?: $this->shortcodeValue($attributes['calendar']),
            'location_id' => $this->shortcodeValue($attributes['location_id']) ?: $this->shortcodeValue($attributes['location']),
        ] as $key => $value) {
            if ($value !== '') {
                if (!ctype_digit((string) $value) || (int) $value < 1) {
                    return '<p role="alert">' . esc_html__('This booking widget has an invalid preselected option. Please contact the site owner.', 'wappointment') . '</p>';
                }
                $configuration[$key] = (int) $value;
            }
        }
        $duration = $this->shortcodeValue($attributes['duration']);
        if ($duration !== '') {
            if (!ctype_digit($duration) || (int) $duration < 5 || (int) $duration > 1440) {
                return '<p role="alert">' . esc_html__('This booking widget has an invalid preselected duration. Please contact the site owner.', 'wappointment') . '</p>';
            }
            $configuration['duration'] = (int) $attributes['duration'];
        }
        $configuration = apply_filters('wappointment_shortcode_attributes', $configuration, $rawAttributes);
        if (!is_array($configuration)) {
            throw new \RuntimeException('The Wappointment shortcode configuration filter must return an array.');
        }
        $this->warnUnsupportedShortcodeAttributes($rawAttributes);
        $title = is_scalar($attributes['title']) ? (string) $attributes['title'] : '';
        $buttonTitle = is_scalar($attributes['button_title']) ? (string) $attributes['button_title'] : '';

        // Enqueue widget assets
        wp_enqueue_script(
            'wappointment-widget',
            plugins_url('dist/widget.js', WAPPOINTMENT_FILE),
            ['wp-element', 'wp-i18n'],
            WAPPOINTMENT_VERSION,
            true
        );

        wp_enqueue_style(
            'wappointment-widget',
            plugins_url('resources/css/widget.css', WAPPOINTMENT_FILE),
            [],
            WAPPOINTMENT_VERSION
        );

        $weekStartsOn = Container::getInstance()->make(Settings::class)->get('week_starts_on', 1);
        if (!is_numeric($weekStartsOn) || (float) $weekStartsOn !== (float) (int) $weekStartsOn
            || (int) $weekStartsOn < 0 || (int) $weekStartsOn > 6) {
            throw new \RuntimeException('The configured first weekday must be an integer between 0 and 6.');
        }

        // Pass data to widget
        wp_localize_script('wappointment-widget', 'wappointmentWidget', [
            'apiUrl' => rest_url('wappointment/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'locale' => str_replace('_', '-', determine_locale()),
            'rtl' => is_rtl(),
            'privacyPolicyUrl' => get_privacy_policy_url(),
            'weekStartsOn' => (int) $weekStartsOn,
        ]);

        // Return the container
        $json = wp_json_encode($configuration, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
        return '<div class="wappointment-widget-root" data-title="' . esc_attr($title)
            . '" data-button-title="' . esc_attr($buttonTitle)
            . '" data-config="' . esc_attr($json) . '"></div>';
    }

    public function renderLegacyBookingPage($atts = []): string
    {
        return $this->renderBookingWidget($atts);
    }

    public function renderCustomerHistory($atts = []): string
    {
        $user = wp_get_current_user();
        if (!$user || !$user->exists() || !is_email($user->user_email)) {
            return esc_html__('Please log in to view your appointment history.', 'wappointment');
        }
        $request = CustomerHistoryRequestData::from(['email' => $user->user_email]);
        $history = Container::getInstance()
            ->make(ListCustomerAppointmentHistoryUseCase::class)
            ->execute($request);
        $atts = shortcode_atts(['date_label' => '', 'service_label' => '', 'staff_label' => ''], is_array($atts) ? $atts : []);
        return $this->renderHistory($history->appointments, $atts, false);
    }

    public function renderStaffHistory($atts = []): string
    {
        $userId = get_current_user_id();
        if ($userId < 1) {
            return esc_html__('Please log in to view your appointment history.', 'wappointment');
        }
        $request = StaffHistoryRequestData::from(['user_id' => $userId]);
        $history = Container::getInstance()
            ->make(ListStaffAppointmentHistoryUseCase::class)
            ->execute($request);
        $atts = shortcode_atts(['date_label' => '', 'service_label' => '', 'client_label' => ''], is_array($atts) ? $atts : []);
        return $this->renderHistory($history->appointments, $atts, true);
    }

    /** @param list<HistoryAppointmentData> $appointments */
    private function renderHistory(array $appointments, array $atts, bool $staff): string
    {
        if (!$appointments) {
            return esc_html__('No appointments found.', 'wappointment');
        }
        $labels = [
            'date_label' => $this->label($atts, 'date_label', __('Date and time', 'wappointment')),
            'service_label' => $this->label($atts, 'service_label', __('Service', 'wappointment')),
            'person_label' => $this->label($atts, $staff ? 'client_label' : 'staff_label', $staff
                ? __('Client', 'wappointment') : __('Staff', 'wappointment')),
        ];
        $html = '<div class="wappointment-history"><table><thead><tr>';
        $html .= '<th scope="col">' . esc_html($labels['date_label']) . '</th>';
        $html .= '<th scope="col">' . esc_html($labels['service_label']) . '</th>';
        $html .= '<th scope="col">' . esc_html($labels['person_label']) . '</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($appointments as $appointment) {
            $timezone = $this->historyTimezone($staff ? $appointment->calendar_timezone : $appointment->customer_timezone);
            $start = strtotime($appointment->start_at . ' UTC');
            $html .= '<tr><td>' . esc_html(wp_date(
                get_option('date_format') . ' ' . get_option('time_format'),
                $start,
                $timezone
            ));
            $html .= $this->renderManagementLink($appointment, $start);
            $html .= '</td><td>' . esc_html($appointment->service_name);
            if ($appointment->location_name !== '') {
                $html .= '<br>' . esc_html($appointment->location_name);
            }
            $html .= '</td><td>';
            if ($staff) {
                $html .= esc_html($appointment->client_name) . '<br><a href="mailto:' .
                    esc_attr($appointment->client_email) . '">' . esc_html($appointment->client_email) . '</a>';
                if ($appointment->client_phone !== '') {
                    $html .= '<br>' . esc_html($appointment->client_phone);
                }
            } else {
                $html .= esc_html($appointment->calendar_name);
            }
            $html .= '</td></tr>';
        }
        return $html . '</tbody></table></div>';
    }

    private function renderManagementLink(HistoryAppointmentData $appointment, int $start): string
    {
        if ($appointment->status < 0 || $appointment->edit_key === '') {
            return '';
        }
        $settings = Container::getInstance()->make(Settings::class);
        $pageId = (int) $settings->get('booking_page', 0);
        $page = $pageId > 0 ? get_post($pageId) : null;
        if (!$page || $page->post_status !== 'publish' ||
            (!has_shortcode($page->post_content, 'wap_widget') && !has_shortcode($page->post_content, 'wappointment_page'))) {
            return '';
        }
        $now = time();
        $links = [];
        $cancelAfter = max(0, (float) $settings->get('hours_before_cancellation_allowed', 24)) * HOUR_IN_SECONDS;
        if ($settings->get('allow_cancellation', true) &&
            ($appointment->status === 0 || $start - $now >= $cancelAfter)) {
            $links[] = $this->historyActionLink($pageId, $appointment->edit_key, 'cancel-event', __('Cancel', 'wappointment'));
        }
        $rescheduleAfter = max(0, (float) $settings->get('hours_before_rescheduling_allowed', 24)) * HOUR_IN_SECONDS;
        if ($settings->get('allow_rescheduling', true) && $start - $now >= $rescheduleAfter) {
            $links[] = $this->historyActionLink($pageId, $appointment->edit_key, 'reschedule-event', __('Reschedule', 'wappointment'));
        }
        if ($appointment->status === 1) {
            $links[] = $this->historyActionLink($pageId, $appointment->edit_key, 'add-to-calendar', __('Add to calendar', 'wappointment'));
        }
        return $links ? '<br>' . implode(' &middot; ', $links) : '';
    }

    private function historyActionLink(int $pageId, string $editKey, string $view, string $label): string
    {
        $url = add_query_arg([
            'view' => $view,
            'appointmentkey' => $editKey,
        ], get_permalink($pageId));
        return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
    }

    private function historyTimezone(string $timezone): \DateTimeZone
    {
        if (!in_array($timezone, \DateTimeZone::listIdentifiers(), true) && $timezone !== 'UTC') {
            return wp_timezone();
        }
        return new \DateTimeZone($timezone);
    }

    private function label(array $atts, string $key, string $default): string
    {
        $value = $atts[$key] ?? '';
        return is_string($value) && $value !== '' ? $value : $default;
    }

    private function shortcodeFlag(array $attributes, string $flag): bool
    {
        if (array_key_exists($flag, $attributes)) {
            return !in_array(strtolower((string) $attributes[$flag]), ['', '0', 'false', 'no'], true);
        }
        return in_array($flag, array_map(static fn(string $value): string => strtolower(trim($value)),
            array_values(array_filter($attributes, 'is_string'))), true);
    }

    private function shortcodeValue($value): string
    {
        return is_scalar($value) ? trim((string) $value) : '__invalid__';
    }

    private function warnUnsupportedShortcodeAttributes(array $attributes): void
    {
        static $reported = [];
        $supported = [
            'title', 'button_title', 'service', 'service_id', 'calendar', 'calendar_id',
            'location', 'location_id', 'duration', 'center', 'open', 'large', 'popup',
            'close_cross', 'week', 'brc_floats',
        ];
        foreach ($attributes as $key => $value) {
            if (is_int($key)) {
                $key = is_string($value) ? strtolower(trim($value)) : '';
            } elseif (in_array($key, $supported, true)) {
                continue;
            } else {
                $key = (string) $key;
            }
            if ($key !== '' && !in_array($key, $supported, true) && empty($reported[$key])) {
                error_log("Wappointment: shortcode option '{$key}' is not migrated in v3 and will be ignored.");
                $reported[$key] = true;
            }
        }
    }
}
