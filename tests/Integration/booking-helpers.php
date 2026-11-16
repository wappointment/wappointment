<?php
declare(strict_types=1);

// Shared by the disposable booking_test integration suites. Load after wp-load.php.

if (!function_exists('bookingRest')) {
    function bookingRest(string $path, string $method = 'GET', ?array $data = null): array
    {
        $request = new WP_REST_Request($method, '/wappointment/v1' . $path);
        if ($data !== null) {
            $request->set_header('content-type', 'application/json');
            $request->set_body(wp_json_encode($data));
        }
        $response = rest_do_request($request);
        return ['status' => $response->get_status(), 'body' => $response->get_data()];
    }
}

if (!function_exists('bookingFixture')) {
    function bookingFixture(): array
    {
        wp_set_current_user(1);
        $schedule = array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], [[540, 1020]]);
        $configuration = bookingRest('/booking-settings', 'POST', [
            'service_name' => 'Free consultation', 'calendar_name' => 'Organizer', 'location_name' => 'Office',
            'location_type' => 1, 'phone_required' => false, 'duration' => 30, 'booking_days' => 60,
            'timezone' => 'Europe/Paris', 'regav' => $schedule,
        ]);
        expect($configuration['status'])->toBe(200);
        wp_set_current_user(0);
        $input = $configuration['body']['data'] + [
            'duration' => 30, 'date' => gmdate('Y-m-d', time() + 3 * DAY_IN_SECONDS),
            'name' => 'Booking Test', 'email' => 'booking-test@example.invalid',
        ];
        $slots = bookingRest('/booking/availability', 'POST', array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']));
        expect($slots['status'])->toBe(200);
        $input['start'] = $slots['body']['data']['slots'][0]['start'];
        return $input;
    }
}

if (!function_exists('bookingPublishEmailTemplate')) {
    /**
     * Store a published email reminder the way legacy master does: `options.body` holds
     * a TipTap document. Event codes: 2 confirmed, 3 rescheduled, 4 canceled, 5 pending.
     *
     * @param list<string> $paragraphs text that may contain `[model:key]` tags
     */
    function bookingPublishEmailTemplate(int $event, string $subject, array $paragraphs, ?string $lang = null): int
    {
        global $wpdb;
        $body = ['type' => 'doc', 'content' => array_map(
            static fn(string $text): array => $text === ''
                ? ['type' => 'paragraph']
                : ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $text]]],
            $paragraphs
        )];
        $inserted = $wpdb->insert($wpdb->prefix . 'wappo_reminders', [
            'subject' => $subject, 'type' => 1, 'event' => $event, 'published' => 1, 'locked' => 1,
            'options' => wp_json_encode(['body' => $body]), 'lang' => $lang,
        ]);
        expect($inserted)->toBe(1);
        return (int) $wpdb->insert_id;
    }
}
