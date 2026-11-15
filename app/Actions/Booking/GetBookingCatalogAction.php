<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Repositories\BookingRepository;
use Wappointment\System\Settings;

class GetBookingCatalogAction
{
    public function __construct(private BookingRepository $repository, private Settings $settings) {}

    public function handle(): array
    {
        $catalog = $this->repository->catalog();
        return [
            'services' => array_map(static fn(array $service): array => [
                'id' => (int) $service['id'],
                'name' => $service['name'],
                'durations' => array_map(static fn(array $row): int => (int) $row['duration'], $service['options']['durations'] ?? []),
                'fields' => $service['options']['fields'] ?? ['name', 'email'],
            ], $catalog['services']),
            'calendars' => array_map(static fn(array $calendar): array => [
                'id' => (int) $calendar['id'], 'name' => $calendar['name'],
                'timezone' => $calendar['options']['timezone'] ?? 'UTC',
            ], $catalog['calendars']),
            'locations' => array_map(static fn(array $location): array => [
                'id' => (int) $location['id'], 'name' => $location['name'], 'type' => (int) $location['type'],
                'fields' => $location['options']['fields'] ?? [],
            ], $catalog['locations']),
            'calendar_service' => $catalog['calendar_service'],
            'service_location' => $catalog['service_location'],
            'customer' => $this->customer(),
        ];
    }

    private function customer(): ?array
    {
        if (!is_user_logged_in()) {
            return null;
        }
        $user = wp_get_current_user();
        if (!is_email($user->user_email)) {
            throw new \RuntimeException('The signed-in WordPress account does not have a valid email address.');
        }
        return [
            'name' => sanitize_text_field($user->display_name),
            'email' => sanitize_email($user->user_email),
            'phone' => '',
            'force_email' => (bool) $this->settings->get('forceemail', false),
            'timezone' => $this->repository->clientTimezone($user->user_email),
        ];
    }
}
