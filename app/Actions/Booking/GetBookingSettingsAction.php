<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Repositories\BookingRepository;
use Wappointment\System\Settings;

class GetBookingSettingsAction
{
    public function __construct(private BookingRepository $repository, private Settings $settings) {}

    public function handle(): array
    {
        $catalog = $this->repository->catalog();
        $project = static fn(?array $row, array $keys): ?array => $row === null ? null : array_intersect_key($row, array_flip($keys));
        $keys = ['id', 'name', 'options'];
        return [
            'service' => $project($catalog['services'][0] ?? null, $keys),
            'calendar' => $project($catalog['calendars'][0] ?? null, $keys),
            'location' => $project($catalog['locations'][0] ?? null, [...$keys, 'type']),
            'booking_page' => (int) $this->settings->get('booking_page', 0),
        ];
    }
}
