<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Booking\WeeklySchedule;
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
        $calendar = $project($catalog['calendars'][0] ?? null, $keys);
        if ($calendar !== null) {
            try {
                $calendar['options']['regav'] = WeeklySchedule::minutes($calendar['options']['regav'] ?? []);
            } catch (\InvalidArgumentException $error) {
                throw new BookingException(__('Stored weekly availability needs review before using the setup editor. Nothing has been changed.', 'wappointment'), 409);
            }
        }
        $bookingPages = array_values(array_map(
            static fn(\WP_Post $page): array => [
                'id' => (int) $page->ID,
                'title' => get_the_title($page),
                'url' => get_permalink($page),
            ],
            array_filter(get_pages([
                'post_status' => 'publish',
                'number' => -1,
                'sort_column' => 'post_title',
                'sort_order' => 'ASC',
            ]), static fn(\WP_Post $page): bool =>
                has_shortcode($page->post_content, 'wap_widget')
                || has_shortcode($page->post_content, 'wappointment_page'))
        ));
        return [
            'service' => $project($catalog['services'][0] ?? null, $keys),
            'calendar' => $calendar,
            'location' => $project($catalog['locations'][0] ?? null, [...$keys, 'type']),
            'booking_page' => (int) $this->settings->get('booking_page', 0),
            'booking_pages' => $bookingPages,
        ];
    }
}
