<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Repositories\BookingRepository;

class GetBookingCatalogAction
{
    public function __construct(private BookingRepository $repository) {}

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
        ];
    }
}
