<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Booking\WeeklySchedule;
use Wappointment\Repositories\BookingRepository;
use Wappointment\System\Settings;

class SaveBookingSettingsAction
{
    public function __construct(private BookingRepository $repository, private Settings $settings) {}

    public function handle(array $data): array
    {
        foreach (['service_name', 'calendar_name', 'location_name', 'timezone'] as $field) {
            if (!isset($data[$field]) || !is_string($data[$field]) || trim($data[$field]) === '' || strlen($data[$field]) > 255) {
                throw new BookingException(sprintf(__('Invalid configuration field: %s.', 'wappointment'), $field));
            }
        }
        if (!in_array($data['timezone'], \DateTimeZone::listIdentifiers(), true) && $data['timezone'] !== 'UTC') {
            throw new BookingException(__('Choose a valid timezone.', 'wappointment'));
        }
        foreach (['duration' => [5, 1440], 'booking_days' => [1, 365], 'location_type' => [1, 4]] as $field => [$min, $max]) {
            if (filter_var($data[$field] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]) === false) {
                throw new BookingException(sprintf(__('Invalid configuration field: %s.', 'wappointment'), $field));
            }
        }
        if (!in_array((int) $data['location_type'], [1, 2, 4], true) || !is_bool($data['phone_required'] ?? null)) {
            throw new BookingException(__('Invalid location type or phone requirement.', 'wappointment'));
        }
        $bookingPage = $data['booking_page'] ?? $this->settings->get('booking_page', 0);
        if (filter_var($bookingPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
            throw new BookingException(__('Invalid booking page ID.', 'wappointment'));
        }
        if ((int) $bookingPage > 0) {
            $page = get_post((int) $bookingPage);
            if (!$page || $page->post_status !== 'publish' || !has_shortcode($page->post_content, 'wap_widget')) {
                throw new BookingException(__('The booking page must be published and contain the wap_widget shortcode.', 'wappointment'));
            }
        }
        $regav = $data['regav'] ?? null;
        if (!is_array($regav)) {
            throw new BookingException(__('Weekly availability must be an object.', 'wappointment'));
        }
        $schedule = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            if (!isset($regav[$day]) || !is_array($regav[$day]) || count($regav[$day]) > 24) {
                throw new BookingException(__('Supply valid availability for each weekday.', 'wappointment'));
            }
            $schedule[$day] = [];
            foreach ($regav[$day] as $block) {
                if (!is_array($block) || count($block) !== 2 || !isset($block[0], $block[1])
                    || !is_int($block[0]) || !is_int($block[1])
                    || $block[0] < 0 || $block[1] > 1440 || $block[0] >= $block[1]) {
                    throw new BookingException(__('Availability ranges must use increasing minute values between 0 and 1440.', 'wappointment'));
                }
                $schedule[$day][] = $block;
            }
        }
        $existing = $this->repository->catalog();
        if (count($existing['services']) > 1 || count($existing['calendars']) > 1 || count($existing['locations']) > 1
            || count($existing['services'][0]['options']['durations'] ?? []) > 1
            || array_diff($existing['services'][0]['options']['fields'] ?? [], ['name', 'email', 'phone'])
            || array_diff($existing['locations'][0]['options']['fields'] ?? [], ['name', 'email', 'phone'])) {
            throw new BookingException(__('This setup editor only supports a single free service, calendar and location. Existing multi-service data has not been changed.', 'wappointment'), 409);
        }
        $options = $existing['calendars'][0]['options'] ?? [];
        try {
            WeeklySchedule::minutes(array_key_exists('regav', $options) ? $options['regav'] : []);
        } catch (\InvalidArgumentException $error) {
            throw new BookingException(__('Stored weekly availability needs review before using the setup editor. Nothing has been changed.', 'wappointment'), 409);
        }
        $result = $this->repository->configure(
            ['name' => sanitize_text_field($data['service_name']), 'options' => [
                'durations' => [array_merge($existing['services'][0]['options']['durations'][0] ?? [], ['duration' => (int) $data['duration']])],
                'fields' => $data['phone_required'] ? ['name', 'email', 'phone'] : ['name', 'email'],
            ]],
            ['name' => sanitize_text_field($data['calendar_name']), 'options' => [
                'timezone' => $data['timezone'], 'avb' => (int) $data['booking_days'], 'regav' => $schedule + ['precise' => true],
            ]],
            ['name' => sanitize_text_field($data['location_name']), 'type' => (int) $data['location_type'],
                'options' => ['fields' => (int) $data['location_type'] === 2 ? ['phone'] : []]]
        );
        if (!$this->settings->set('booking_page', (int) $bookingPage)) {
            throw new BookingException(__('The calendar was saved, but the booking page setting could not be saved. Please try again.', 'wappointment'), 503);
        }
        return $result;
    }
}
