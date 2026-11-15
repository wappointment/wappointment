<?php
declare(strict_types=1);

if (!getenv('WAPPOINTMENT_WP_ROOT')) {
    test('booking integration requires a disposable WordPress database')->skip('Set WAPPOINTMENT_WP_ROOT inside the disposable booking_test WordPress container.');
    return;
}

require_once rtrim(getenv('WAPPOINTMENT_WP_ROOT'), '/') . '/wp-load.php';
if (DB_NAME !== 'booking_test' || wp_get_environment_type() !== 'local') {
    throw new RuntimeException('Booking integration tests may only run against the disposable local booking_test database.');
}

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

beforeEach(function () {
    global $wpdb;
    wp_set_current_user(0);
    foreach (['appointments', 'statuses', 'calendar_service', 'service_location', 'clients', 'services', 'locations', 'calendars'] as $table) {
        $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'wappo_' . $table);
    }
    update_option('wappointment_settings', [
        'hours_before_booking_allowed' => 0, 'hours_before_cancellation_allowed' => 0,
        'hours_before_rescheduling_allowed' => 0, 'mail_status' => false,
        'buffer_time' => 15,
    ], false);
    \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class)->clearCache();
});

test('only booking endpoints are anonymous', function () {
    foreach (['/booking-settings', '/appointments', '/clients'] as $path) {
        expect(bookingRest($path)['status'])->toBeIn([401, 403]);
    }
    expect(bookingRest('/booking/catalog')['status'])->toBe(200);
})->group('booking-integration');

test('saves a real booking with UTC times and the original buffer contract', function () {
    global $wpdb;
    $input = bookingFixture();
    $result = bookingRest('/booking', 'POST', $input);
    expect($result['status'])->toBe(201);
    $booking = $result['body']['data'];
    expect($booking['status'])->toBe(1)
        ->and($booking['notification_sent'])->toBeFalse()
        ->and($booking['end'] - $booking['start'])->toBe(1800);
    $stored = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_appointments WHERE id = %d", $booking['id']), ARRAY_A);
    expect($stored['start_at'])->toBe(gmdate('Y-m-d H:i:s', $input['start']))
        ->and(strtotime($stored['end_at'] . ' UTC') - $input['start'])->toBe(2700)
        ->and(strlen($stored['edit_key']))->toBe(32);
    $conflict = bookingRest('/booking', 'POST', $input);
    expect($conflict['status'])->toBe(409)
        ->and((int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wappo_appointments"))->toBe(1);
})->group('booking-integration');

test('legacy schedule precision is projected consistently and public bookings retain stored source data', function (string $precision) {
    global $wpdb;
    $input = bookingFixture();
    $table = $wpdb->prefix . 'wappo_calendars';
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $input['calendar_id']), ARRAY_A);
    $options = json_decode($row['options'], true);
    $options['private'] = ['preserved' => 'unchanged'];
    $options['regav'] = array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
        [$precision === 'minutes' ? [540, 720] : [9, 12]]);
    if ($precision !== 'unmarked-hours') {
        $options['regav']['precise'] = $precision === 'minutes';
    }
    $stored = wp_json_encode($options);
    $wpdb->update($table, ['options' => $stored], ['id' => $input['calendar_id']]);
    wp_set_current_user(1);
    $settings = bookingRest('/booking-settings');
    expect($settings['status'])->toBe(200)
        ->and($settings['body']['data']['calendar']['options']['regav']['monday'])->toBe([[540, 720]])
        ->and($settings['body']['data']['calendar']['options']['regav'])->not->toHaveKey('precise');
    wp_set_current_user(0);
    $response = bookingRest('/booking/availability', 'POST',
        array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']));
    expect($response['status'])->toBe(200);
    $slots = $response['body']['data']['slots'];
    $day = new DateTimeImmutable($input['date'], new DateTimeZone('Europe/Paris'));
    $start = $day->setTime(9, 0)->getTimestamp();
    expect(array_column($slots, 'start'))->toBe([$start, $start + 2700, $start + 5400, $start + 8100]);
    $input['start'] = $start;
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(201)
        ->and($wpdb->get_var($wpdb->prepare("SELECT options FROM {$table} WHERE id = %d", $input['calendar_id'])))->toBe($stored);
    wp_set_current_user(1);
    $request = [
        'service_name' => 'Free consultation', 'calendar_name' => 'Organizer', 'location_name' => 'Office',
        'location_type' => 1, 'phone_required' => false, 'duration' => 30, 'booking_days' => 60,
        'timezone' => 'Europe/Paris', 'regav' => $settings['body']['data']['calendar']['options']['regav'],
    ];
    expect(bookingRest('/booking-settings', 'POST', $request)['status'])->toBe(200);
    $saved = json_decode($wpdb->get_var($wpdb->prepare("SELECT options FROM {$table} WHERE id = %d", $input['calendar_id'])), true);
    expect($saved['regav']['precise'])->toBeTrue()
        ->and($saved['regav']['monday'])->toBe([[540, 720]])
        ->and($saved['private'])->toBe(['preserved' => 'unchanged']);
})->with(['unmarked-hours', 'explicit-hours', 'minutes'])->group('booking-integration');

test('busy boundaries reanchor public slots and custom spacing requires more starts enabled', function () {
    global $wpdb;
    $input = bookingFixture();
    $day = new DateTimeImmutable($input['date'], new DateTimeZone('Europe/Paris'));
    $start = $day->setTime(9, 0)->getTimestamp();
    $wpdb->insert($wpdb->prefix . 'wappo_statuses', [
        'staff_id' => $input['calendar_id'], 'start_at' => gmdate('Y-m-d H:i:s', $start + 4200),
        'end_at' => gmdate('Y-m-d H:i:s', $start + 6000), 'type' => 1, 'recur' => 0, 'muted' => 0,
    ]);
    $settings = \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class);
    $settings->set('starting_each', 10);
    $body = array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']);
    $response = bookingRest('/booking/availability', 'POST', $body);
    expect($response['status'])->toBe(200);
    $starts = array_column($response['body']['data']['slots'], 'start');
    expect(array_slice($starts, 0, 3))->toBe([$start, $start + 6000, $start + 8700]);
    $settings->set('more_st', true);
    $response = bookingRest('/booking/availability', 'POST', $body);
    expect($response['status'])->toBe(200);
    $starts = array_column($response['body']['data']['slots'], 'start');
    expect(array_slice($starts, 0, 3))->toBe([$start, $start + 600, $start + 1200])
        ->and($starts)->toContain($start + 6000);
})->group('booking-integration');

test('recurring busy and free statuses affect public slots in their calendar timezone', function () {
    global $wpdb;
    $input = bookingFixture();
    $table = $wpdb->prefix . 'wappo_statuses';
    $timezone = new DateTimeZone('Europe/Paris');
    $input['date'] = (new DateTimeImmutable('first day of next month', $timezone))
        ->modify('+14 days')->format('Y-m-d');
    $targetDay = new DateTimeImmutable($input['date'], $timezone);
    $anchorDay = $targetDay->modify('-30 days');

    foreach ([
        ['type' => 1, 'hour' => 9, 'recur' => 1, 'duration' => '+1 hour', 'rules' => ['interval' => 1], 'anchor' => $anchorDay],
        ['type' => 0, 'hour' => 18, 'recur' => 1, 'duration' => '+2 hours', 'rules' => ['interval' => 1], 'anchor' => $anchorDay],
        ['type' => 1, 'hour' => 11, 'recur' => 3, 'duration' => '+1 hour', 'rules' => ['bymonthday' => 15], 'anchor' => $targetDay->modify('-1 month')],
    ] as $fixture) {
        $localStart = $fixture['anchor']->setTime($fixture['hour'], 0);
        $localEnd = $localStart->modify($fixture['duration']);
        $wpdb->insert($table, [
            'staff_id' => $input['calendar_id'],
            'start_at' => $localStart->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            'end_at' => $localEnd->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            'type' => $fixture['type'],
            'recur' => $fixture['recur'],
            'muted' => 0,
            'options' => wp_json_encode($fixture['rules'] + [
                'origin_tz' => 'Europe/Paris',
                'origin_start' => $localStart->format('Y-m-d H:i:s'),
            ]),
        ]);
    }

    $request = array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']);
    $response = bookingRest('/booking/availability', 'POST', $request);
    expect($response['status'])->toBe(200);
    $starts = array_column($response['body']['data']['slots'], 'start');
    $busyStart = $targetDay->setTime(9, 0)->getTimestamp();
    $monthlyBusyStart = $targetDay->setTime(11, 0)->getTimestamp();
    $freeStart = $targetDay->setTime(18, 0)->getTimestamp();

    expect($starts)->not->toContain($busyStart)
        ->and($starts)->not->toContain($busyStart + 45 * 60)
        ->and($starts)->toContain($busyStart + 60 * 60)
        ->and($starts)->not->toContain($monthlyBusyStart)
        ->and($starts)->not->toContain($monthlyBusyStart + 45 * 60)
        ->and($starts)->toContain($monthlyBusyStart + 60 * 60)
        ->and($starts)->toContain($freeStart)
        ->and($starts)->toContain($freeStart + 45 * 60);
})->group('booking-integration');

test('organizers can add and remove owned one-off busy and free times', function () {
    global $wpdb;
    $input = bookingFixture();
    $calendarId = $input['calendar_id'];
    $wpdb->update($wpdb->prefix . 'wappo_calendars', ['wp_uid' => 1], ['id' => $calendarId]);
    $path = '/me/calendars/' . $calendarId . '/statuses';
    $day = new DateTimeImmutable($input['date'], new DateTimeZone('Europe/Paris'));
    $busyStart = $day->setTime(9, 0)->getTimestamp();
    $busyEnd = $day->setTime(10, 0)->getTimestamp();
    $freeStart = $day->setTime(18, 0)->getTimestamp();
    $freeEnd = $day->setTime(19, 0)->getTimestamp();

    expect(bookingRest($path)['status'])->toBeIn([401, 403]);
    wp_set_current_user(1);
    $busy = bookingRest($path, 'POST', ['type' => 1, 'start' => $busyStart, 'end' => $busyEnd]);
    expect($busy['status'])->toBe(201)
        ->and($busy['body']['data']['status'])->toMatchArray([
            'type' => 1, 'start' => $busyStart, 'end' => $busyEnd,
        ]);
    $busyId = $busy['body']['data']['status']['id'];
    expect($wpdb->get_var($wpdb->prepare(
        "SELECT options FROM {$wpdb->prefix}wappo_statuses WHERE id = %d", $busyId
    )))->toBe('{}');

    $free = bookingRest($path, 'POST', ['type' => 0, 'start' => $freeStart, 'end' => $freeEnd]);
    expect($free['status'])->toBe(201);
    $freeId = $free['body']['data']['status']['id'];
    expect(bookingRest($path)['body']['data']['statuses'])->toHaveCount(2);

    wp_set_current_user(0);
    $selection = array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']);
    $slots = bookingRest('/booking/availability', 'POST', $selection);
    $starts = array_column($slots['body']['data']['slots'], 'start');
    expect($slots['status'])->toBe(200)
        ->and($starts)->not->toContain($busyStart)
        ->and($starts)->toContain($busyEnd)
        ->and($starts)->toContain($freeStart);

    wp_set_current_user(1);
    expect(bookingRest($path . '/' . $busyId . '/delete', 'POST')['status'])->toBe(200)
        ->and(bookingRest($path . '/' . $freeId . '/delete', 'POST')['status'])->toBe(200)
        ->and(bookingRest($path)['body']['data']['statuses'])->toBe([]);
    wp_set_current_user(0);
    $slots = bookingRest('/booking/availability', 'POST', $selection);
    expect(array_column($slots['body']['data']['slots'], 'start'))->toContain($busyStart)
        ->and(array_column($slots['body']['data']['slots'], 'start'))->not->toContain($freeStart);
})->group('booking-integration');

test('Services add-on status conditions block the affected day instead of opening restricted slots', function () {
    global $wpdb;
    $input = bookingFixture();
    $table = $wpdb->prefix . 'wappo_statuses';
    $day = new DateTimeImmutable($input['date'], new DateTimeZone('Europe/Paris'));
    $utc = new DateTimeZone('UTC');
    $options = wp_json_encode(['conditions' => ['locations' => [], 'services' => [$input['service_id'] + 1]]]);
    $wpdb->insert($table, [
        'staff_id' => $input['calendar_id'],
        'start_at' => $day->setTime(18, 0)->setTimezone($utc)->format('Y-m-d H:i:s'),
        'end_at' => $day->setTime(20, 0)->setTimezone($utc)->format('Y-m-d H:i:s'),
        'type' => 0, 'recur' => 0, 'muted' => 0, 'options' => $options,
    ]);
    $request = array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']);

    $blocked = bookingRest('/booking/availability', 'POST', $request);
    expect($blocked['status'])->toBe(409)
        ->and(bookingRest('/booking', 'POST', $input)['status'])->toBe(409)
        ->and((int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wappo_appointments"))->toBe(0)
        ->and($wpdb->get_var("SELECT options FROM {$table}"))->toBe($options);

    $request['date'] = $day->modify('+1 day')->format('Y-m-d');
    expect(bookingRest('/booking/availability', 'POST', $request)['status'])->toBe(200);
})->group('booking-integration');

test('public availability applies fluid notice and calendar-day refresh horizon without rewriting options', function () {
    global $wpdb;
    $input = bookingFixture();
    $calendarTable = $wpdb->prefix . 'wappo_calendars';
    $serviceTable = $wpdb->prefix . 'wappo_services';
    $options = json_decode($wpdb->get_var($wpdb->prepare(
        "SELECT options FROM {$calendarTable} WHERE id = %d", $input['calendar_id']
    )), true);
    $options['timezone'] = 'UTC';
    $options['avb'] = 1;
    $options['regav'] = array_fill_keys(
        ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
        [[0, 1440]]
    ) + ['precise' => true];
    $storedOptions = wp_json_encode($options);
    $wpdb->update($calendarTable, ['options' => $storedOptions], ['id' => $input['calendar_id']]);
    $serviceOptions = json_decode($wpdb->get_var($wpdb->prepare(
        "SELECT options FROM {$serviceTable} WHERE id = %d", $input['service_id']
    )), true);
    $serviceOptions['durations'][0]['duration'] = 5;
    $wpdb->update($serviceTable, ['options' => wp_json_encode($serviceOptions)], ['id' => $input['service_id']]);
    $localNow = (new DateTimeImmutable('@' . time()))->setTimezone(new DateTimeZone('UTC'));
    $hour = (int) $localNow->format('G');
    $refreshAt = $hour < 23 ? $hour + 1 : 23;
    $settings = \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class);
    $settings->setMultiple([
        'hours_before_booking_allowed' => 0.1, 'availability_fluid' => true,
        'allow_refreshavb' => false, 'buffer_time' => 0, 'more_st' => false,
        'allow_cancellation' => true, 'allow_rescheduling' => true,
    ]);
    $today = $localNow->format('Y-m-d');
    $request = array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']);
    $request['duration'] = 5;
    $request['date'] = $today;
    $todayResult = bookingRest('/booking/availability', 'POST', $request);
    expect($todayResult['status'])->toBe(200);
    $todayStarts = array_column($todayResult['body']['data']['slots'], 'start');
    if ($todayStarts) {
        expect($todayStarts[0])->toBeGreaterThanOrEqual(time())
            ->and((int) gmdate('i', $todayStarts[0]) % 5)->toBe(0);
    }

    $settings->setMultiple(['allow_refreshavb' => true, 'refreshavb_at' => $refreshAt]);
    $tomorrow = $localNow->modify('+1 day')->format('Y-m-d');
    $request['date'] = $tomorrow;
    $tomorrowResult = bookingRest('/booking/availability', 'POST', $request);
    expect($tomorrowResult['status'])->toBe(200);
    if ($hour >= $refreshAt) {
        expect($tomorrowResult['body']['data']['slots'])->not->toBe([]);
    } else {
        expect($tomorrowResult['body']['data']['slots'])->toBe([]);
    }
    $request['date'] = $localNow->modify('+2 days')->format('Y-m-d');
    expect(bookingRest('/booking/availability', 'POST', $request)['body']['data']['slots'])->toBe([])
        ->and($wpdb->get_var($wpdb->prepare(
            "SELECT options FROM {$calendarTable} WHERE id = %d", $input['calendar_id']
        )))->toBe($storedOptions);
})->group('booking-integration');

test('unsupported stored precision blocks slots and setup overwrites without changing records', function () {
    global $wpdb;
    $input = bookingFixture();
    $table = $wpdb->prefix . 'wappo_calendars';
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $input['calendar_id']), ARRAY_A);
    $options = json_decode($row['options'], true);
    unset($options['regav']['precise']);
    $wpdb->update($table, ['options' => wp_json_encode($options)], ['id' => $input['calendar_id']]);
    $before = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id", ARRAY_A);
    $body = array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']);
    expect(bookingRest('/booking/availability', 'POST', $body)['status'])->toBe(409);
    wp_set_current_user(1);
    expect(bookingRest('/booking-settings')['status'])->toBe(409);
    expect(bookingRest('/booking-settings', 'POST', [
        'service_name' => 'Must not change', 'calendar_name' => 'Must not change', 'location_name' => 'Office',
        'location_type' => 1, 'phone_required' => false, 'duration' => 30, 'booking_days' => 60,
        'timezone' => 'Europe/Paris',
        'regav' => array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], []),
    ])['status'])->toBe(409)
        ->and($wpdb->get_results("SELECT * FROM {$table} ORDER BY id", ARRAY_A))->toBe($before);
})->group('booking-integration');

test('rejects forged timestamps, duration and location relationships without writing clients', function () {
    global $wpdb;
    $input = bookingFixture();
    foreach ([
        ['start' => $input['start'] + 1], ['duration' => 45], ['location_id' => 99999],
        ['email' => 'invalid'], ['name' => ['not-a-string']], ['start' => ['not-an-integer']],
    ] as $invalid) {
        expect(bookingRest('/booking', 'POST', array_merge($input, $invalid))['status'])->toBeGreaterThanOrEqual(400);
    }
    expect((int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wappo_clients"))->toBe(0);
})->group('booking-integration');

test('rescheduling excludes the current booking and preserves its original buffer', function () {
    global $wpdb;
    $input = bookingFixture();
    $booking = bookingRest('/booking', 'POST', $input)['body']['data'];
    $settings = \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class);
    $settings->set('buffer_time', 0);
    $slots = bookingRest('/booking/availability', 'POST', ['edit_key' => $booking['edit_key'], 'date' => $input['date']]);
    expect($slots['body']['data']['slots'][0]['start'])->toBe($input['start']);
    $newStart = $slots['body']['data']['slots'][1]['start'];
    $result = bookingRest('/booking/manage', 'POST', [
        'action' => 'reschedule', 'edit_key' => $booking['edit_key'], 'date' => $input['date'], 'start' => $newStart,
    ]);
    $optionsAfterFirstChange = json_decode($wpdb->get_var($wpdb->prepare(
        "SELECT options FROM {$wpdb->prefix}wappo_appointments WHERE id = %d",
        $booking['id']
    )), true, 512, JSON_THROW_ON_ERROR);
    $nextStart = $slots['body']['data']['slots'][2]['start'];
    $secondChange = bookingRest('/booking/manage', 'POST', [
        'action' => 'reschedule', 'edit_key' => $booking['edit_key'], 'date' => $input['date'], 'start' => $nextStart,
    ]);
    $optionsAfterSecondChange = json_decode($wpdb->get_var($wpdb->prepare(
        "SELECT options FROM {$wpdb->prefix}wappo_appointments WHERE id = %d",
        $booking['id']
    )), true, 512, JSON_THROW_ON_ERROR);
    expect($result['status'])->toBe(200)
        ->and($result['body']['data']['start'])->toBe($newStart)
        ->and($result['body']['data']['end'] - $newStart)->toBe(1800)
        ->and($optionsAfterFirstChange['sequence'])->toBe(1)
        ->and($secondChange['status'])->toBe(200)
        ->and($secondChange['body']['data']['start'])->toBe($nextStart)
        ->and($optionsAfterSecondChange['sequence'])->toBe(2);
})->group('booking-integration');

test('cancellation frees availability and respects time limits', function () {
    $input = bookingFixture();
    $booking = bookingRest('/booking', 'POST', $input)['body']['data'];
    $settings = \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class);
    $settings->set('hours_before_cancellation_allowed', 1000);
    $request = ['action' => 'cancel', 'edit_key' => $booking['edit_key']];
    expect(bookingRest('/booking/manage', 'POST', $request)['status'])->toBe(403);
    $settings->set('hours_before_cancellation_allowed', 0);
    $result = bookingRest('/booking/manage', 'POST', $request);
    expect($result['status'])->toBe(200)->and($result['body']['data']['status'])->toBe(-1);
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(201);
})->group('booking-integration');

test('pending bookings block slots and can be confirmed only by an administrator', function () {
    $input = bookingFixture();
    \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class)->set('approval_mode', 0);
    $booking = bookingRest('/booking', 'POST', $input)['body']['data'];
    expect($booking['status'])->toBe(0)
        ->and(bookingRest('/booking', 'POST', $input)['status'])->toBe(409);
    $update = ['id' => $booking['id'], 'status' => 1];
    expect(bookingRest('/appointments', 'POST', $update)['status'])->toBeIn([401, 403]);
    wp_set_current_user(1);
    expect(bookingRest('/appointments', 'POST', $update)['status'])->toBe(200);
    expect(bookingRest('/appointments', 'POST', $update)['status'])->toBe(409);
})->group('booking-integration');

test('pending bookings can still be canceled after the cancellation deadline', function () {
    global $wpdb;
    $input = bookingFixture();
    $settings = \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class);
    $settings->set('approval_mode', 0);
    $settings->set('hours_before_cancellation_allowed', 1000);
    $booking = bookingRest('/booking', 'POST', $input)['body']['data'];

    $result = bookingRest('/booking/manage', 'POST', [
        'action' => 'cancel', 'edit_key' => $booking['edit_key'],
    ]);
    $storedStatus = $wpdb->get_var($wpdb->prepare(
        "SELECT status FROM {$wpdb->prefix}wappo_appointments WHERE id = %d",
        $booking['id']
    ));

    expect($result['status'])->toBe(200)->and((int) $storedStatus)->toBe(-1);
})->group('booking-integration');

test('enforces active booking limits across distinct available times', function () {
    $input = bookingFixture();
    $settings = \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class);
    $settings->set('max_active_bookings', 1);
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(201);
    $slots = bookingRest('/booking/availability', 'POST', array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']))['body']['data']['slots'];
    $input['start'] = $slots[0]['start'];
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(409);
})->group('booking-integration');

test('never overwrites existing customer data from a public booking', function () {
    global $wpdb;
    $input = bookingFixture();
    $wpdb->insert($wpdb->prefix . 'wappo_clients', [
        'name' => 'Private Name', 'email' => $input['email'], 'options' => '{"private":"retained"}',
    ]);
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(201);
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_clients WHERE email = %s", $input['email']), ARRAY_A);
    expect($client['name'])->toBe('Private Name')->and($client['options'])->toBe('{"private":"retained"}');
})->group('booking-integration');

test('a returning customer restores the soft-deleted client without losing identity or private fields', function () {
    global $wpdb;
    $input = bookingFixture();
    $wpdb->insert($wpdb->prefix . 'wappo_clients', [
        'name' => 'Retained Name', 'email' => $input['email'], 'options' => '{"private":"retained"}',
        'deleted_at' => '2025-01-01 00:00:00',
    ]);
    $id = (int) $wpdb->insert_id;
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(201);
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $id), ARRAY_A);
    expect($client['deleted_at'])->toBeNull()->and($client['name'])->toBe('Retained Name')
        ->and($client['options'])->toBe('{"private":"retained"}')
        ->and((int) $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wappo_appointments LIMIT 1"))->toBe($id)
        ->and((int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wappo_clients"))->toBe(1);
})->group('booking-integration');

test('a failed repeat booking rolls back client restoration and leaves existing appointments intact', function () {
    global $wpdb;
    $input = bookingFixture();
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(201);
    $id = (int) $wpdb->get_var("SELECT id FROM {$wpdb->prefix}wappo_clients LIMIT 1");
    wp_set_current_user(1);
    expect(bookingRest('/clients/' . $id . '/delete', 'POST')['status'])->toBe(200);
    $before = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $id), ARRAY_A);
    \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class)->set('max_active_bookings', 1);
    wp_set_current_user(0);
    $slots = bookingRest('/booking/availability', 'POST', array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']))['body']['data']['slots'];
    $input['start'] = $slots[0]['start'];
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(409)
        ->and($wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $id), ARRAY_A))->toBe($before)
        ->and((int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wappo_appointments"))->toBe(1);
})->group('booking-integration');

test('own availability changes reach the public slots API without changing existing appointments', function () {
    global $wpdb;
    $input = bookingFixture();
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(201);
    $before = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_appointments", ARRAY_A);
    $wpdb->update($wpdb->prefix . 'wappo_calendars', ['wp_uid' => 1], ['id' => $input['calendar_id']]);
    $weekly = [
        'timezone' => 'UTC', 'booking_days' => 60,
        'regav' => array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], [[840, 960]]),
    ];
    $path = '/me/calendars/' . $input['calendar_id'] . '/weekly-settings';
    $selection = array_intersect_key($input, \Wappointment\Http\Requests\BookingRequestData::schema()['properties']);
    wp_set_current_user(1);
    expect(bookingRest($path, 'POST', ['settings' => $weekly])['status'])->toBe(200);
    wp_set_current_user(0);
    $slots = bookingRest('/booking/availability', 'POST', $selection);
    $start = strtotime($input['date'] . ' 14:00:00 UTC');
    expect($slots['status'])->toBe(200)
        ->and(array_column($slots['body']['data']['slots'], 'start'))->toBe([$start, $start + 2700]);
    wp_set_current_user(1);
    expect(bookingRest($path, 'POST', ['settings' => array_replace($weekly, ['booking_days' => 1])])['status'])->toBe(200);
    wp_set_current_user(0);
    expect(bookingRest('/booking/availability', 'POST', $selection)['body']['data']['slots'])->toBe([]);
    wp_set_current_user(1);
    $weekly['regav'] = array_fill_keys(array_keys($weekly['regav']), []);
    expect(bookingRest($path, 'POST', ['settings' => $weekly])['status'])->toBe(200);
    wp_set_current_user(0);
    expect(bookingRest('/booking/availability', 'POST', $selection)['body']['data']['slots'])->toBe([])
        ->and($wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_appointments", ARRAY_A))->toBe($before);
})->group('booking-integration');

test('schema bootstrap does not modify existing booking data', function () {
    global $wpdb;
    $input = bookingFixture();
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(201);
    $before = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_appointments", ARRAY_A);
    \Wappointment\Database\BookingSchema::install();
    expect($wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_appointments", ARRAY_A))->toBe($before);
})->group('booking-integration');

test('concurrent HTTP requests cannot double book the same slot', function () {
    global $wpdb;
    $input = bookingFixture();
    $multi = curl_multi_init();
    $requests = [];
    foreach (['concurrent-one@example.invalid', 'concurrent-two@example.invalid'] as $email) {
        $request = curl_init('http://localhost/?rest_route=/wappointment/v1/booking');
        curl_setopt_array($request, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => wp_json_encode(array_merge($input, ['email' => $email])),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        curl_multi_add_handle($multi, $request);
        $requests[] = $request;
    }
    try {
        do {
            $status = curl_multi_exec($multi, $running);
            if ($status !== CURLM_OK) {
                throw new RuntimeException('Concurrent booking HTTP request failed.');
            }
            if ($running) {
                curl_multi_select($multi, 1);
            }
        } while ($running);
        $statuses = array_map(static fn($request): int => (int) curl_getinfo($request, CURLINFO_RESPONSE_CODE), $requests);
        sort($statuses);
        expect($statuses)->toBe([201, 409])
            ->and((int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wappo_appointments"))->toBe(1)
            ->and((int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wappo_clients"))->toBe(1);
    } finally {
        foreach ($requests as $request) {
            curl_multi_remove_handle($multi, $request);
            curl_close($request);
        }
        curl_multi_close($multi);
    }
})->group('booking-integration');

test('refuses to overwrite a multi-service setup', function () {
    global $wpdb;
    bookingFixture();
    $wpdb->insert($wpdb->prefix . 'wappo_services', ['name' => 'Legacy paid service', 'options' => '{"durations":[{"duration":60}]}']);
    $before = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_services", ARRAY_A);
    wp_set_current_user(1);
    $result = bookingRest('/booking-settings', 'POST', [
        'service_name' => 'Changed', 'calendar_name' => 'Changed', 'location_name' => 'Changed',
        'location_type' => 1, 'phone_required' => false, 'duration' => 30, 'booking_days' => 60,
        'timezone' => 'UTC',
        'regav' => array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], []),
    ]);
    expect($result['status'])->toBe(409)
        ->and($wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_services", ARRAY_A))->toBe($before);
})->group('booking-integration');

test('settings persistence does not report an unsuccessful write as saved', function () {
    $settings = \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class);
    $before = get_option('wappointment_settings');
    $preventWrite = static fn($new, $old) => $old;
    add_filter('pre_update_option_wappointment_settings', $preventWrite, 10, 2);
    try {
        expect($settings->set('buffer_time', 123))->toBeFalse()
            ->and($settings->get('buffer_time'))->toBe(15)
            ->and(get_option('wappointment_settings'))->toBe($before);
    } finally {
        remove_filter('pre_update_option_wappointment_settings', $preventWrite, 10);
    }
})->group('booking-integration');

test('confirmation emails use a working booking page link and report actual mail acceptance', function () {
    $input = bookingFixture();
    $page = wp_insert_post([
        'post_title' => 'Disposable booking page', 'post_status' => 'publish',
        'post_type' => 'page', 'post_content' => '[wap_widget]',
    ], true);
    expect(is_wp_error($page))->toBeFalse();
    $settings = \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class);
    $settings->setMultiple(['mail_status' => true, 'booking_page' => $page]);
    $messages = [];
    $capture = static function ($return, $attributes) use (&$messages) {
        $messages[] = $attributes;
        return true;
    };
    add_filter('pre_wp_mail', $capture, 10, 2);
    try {
        $result = bookingRest('/booking', 'POST', $input);
        expect($result['status'])->toBe(201)
            ->and($result['body']['data']['notification_sent'])->toBeTrue()
            ->and($messages)->toHaveCount(1)
            ->and($messages[0]['to'])->toBe($input['email'])
            ->and($messages[0]['message'])->toContain(get_permalink($page) . '#wappointment=' . $result['body']['data']['edit_key']);
    } finally {
        remove_filter('pre_wp_mail', $capture, 10);
        wp_delete_post($page, true);
    }
})->group('booking-integration');

test('legacy add-on bootstrap hooks are disabled before init', function () {
    if (!class_exists('WappointmentServicesAddon', false)) {
        class WappointmentServicesAddon
        {
            public static function init(): void {}
        }
    }
    add_action('init', ['WappointmentServicesAddon', 'init'], 12);
    expect(has_action('init', ['WappointmentServicesAddon', 'init']))->toBe(12);
    \Wappointment\System\LegacyAddons::block();
    expect(has_action('init', ['WappointmentServicesAddon', 'init']))->toBeFalse();
})->group('booking-integration');

test('the container resolves all controller UseCase and Action dependencies', function () {
    $container = \Wappointment\System\Container::getInstance();
    $routes = require __DIR__ . '/../../app/Routes/api.php';
    foreach ($routes as $route) {
        expect($container->make($route['controller']))->toBeInstanceOf($route['controller']);
    }
    expect($container->make(\Wappointment\Controllers\AdminController::class))->toBeCallable();
})->group('booking-integration');

test('booking admin submenus register under their parent rather than as frontend paths', function () {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    wp_set_current_user(1);
    global $menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages;
    $saved = [$menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages];
    $menu = $submenu = $admin_page_hooks = $_registered_pages = $_parent_pages = [];
    try {
        (new \Wappointment\Routes\AdminMenu())->register();
        foreach (['wappointment-appointments', 'wappointment-booking-setup'] as $slug) {
            $hook = get_plugin_page_hook($slug, 'wappointment-jobs');
            expect($hook)->toBe('wappointment_page_' . $slug)
                ->and($_registered_pages[$hook] ?? false)->toBeTrue()
                ->and($_parent_pages[$slug] ?? null)->toBe('wappointment-jobs');
        }
    } finally {
        [$menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages] = $saved;
    }
})->group('booking-integration');

test('booking request data failures are returned as REST errors before executing the UseCase', function () {
    foreach (['/booking', '/booking/availability', '/booking/manage'] as $path) {
        $request = new WP_REST_Request('POST', '/wappointment/v1' . $path);
        $request->set_header('content-type', 'application/json');
        $request->set_body('null');
        $response = rest_do_request($request);
        expect($response->get_status())->toBe(400)
            ->and($response->get_data()['code'])->toBe('wappointment_invalid_request')
            ->and($response->get_data()['message'])->toContain('JSON object');
    }
})->group('booking-integration');

test('existing client and settings HTTP responses survive the UseCase refactor', function () {
    $credentials = WP_Application_Passwords::create_new_application_password(1, ['name' => 'Disposable UseCase regression test']);
    if (is_wp_error($credentials)) {
        throw new RuntimeException($credentials->get_error_message());
    }
    [$password, $item] = $credentials;
    $username = get_userdata(1)->user_login;
    $http = static function (string $path, string $method = 'GET', ?array $payload = null) use ($username, $password): array {
        $request = curl_init('http://localhost/?rest_route=/wappointment/v1' . $path);
        curl_setopt_array($request, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERPWD => $username . ':' . $password,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        if ($payload !== null) {
            curl_setopt($request, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        }
        try {
            $body = curl_exec($request);
            if ($body === false) {
                throw new RuntimeException(curl_error($request));
            }
            return ['status' => (int) curl_getinfo($request, CURLINFO_RESPONSE_CODE), 'body' => json_decode($body, true, 512, JSON_THROW_ON_ERROR)];
        } finally {
            curl_close($request);
        }
    };
    try {
        $created = $http('/clients', 'POST', ['name' => 'UseCase Client', 'email' => 'usecase@example.invalid']);
        expect($created['status'])->toBe(201)->and($created['body']['success'])->toBeTrue();
        $id = (int) $created['body']['data']['id'];
        $listed = $http('/clients&search=UseCase');
        expect($listed['status'])->toBe(200)
            ->and($listed['body']['data']['total'])->toBe(1)
            ->and($listed['body']['data']['data'][0]['name'])->toBe('UseCase Client');
        $updated = $http('/clients/' . $id . '/put', 'POST', ['name' => 'Updated UseCase Client']);
        expect($updated['status'])->toBe(200)->and($updated['body']['data']['name'])->toBe('Updated UseCase Client');
        $deleted = $http('/clients/' . $id . '/delete', 'POST', ['id' => $id]);
        expect($deleted['status'])->toBe(200)->and($deleted['body']['data']['message'])->toBe('Client deleted');
        $missing = $http('/clients/' . $id . '/delete', 'POST', ['id' => $id]);
        expect($missing['status'])->toBe(404)->and($missing['body']['message'])->toBe('Client not found');
        $invalid = $http('/clients', 'POST', ['name' => '']);
        expect($invalid['status'])->toBe(400);

        $saved = $http('/settings', 'POST', ['approval_mode' => 0]);
        expect($saved['status'])->toBe(200)->and($saved['body']['data']['message'])->toBe('Settings saved successfully');
        $settings = $http('/settings');
        expect($settings['status'])->toBe(200)->and($settings['body']['data']['settings']['general']['approval_mode'])->toBe(0);
        $setting = $http('/settings/approval_mode');
        expect($setting['status'])->toBe(200)->and($setting['body']['data'])->toBe(['name' => 'approval_mode', 'value' => 0]);
        $invalidSettings = $http('/settings', 'POST', []);
        expect($invalidSettings['status'])->toBe(400)->and($invalidSettings['body']['message'])->toContain('JSON object');
    } finally {
        WP_Application_Passwords::delete_application_password(1, $item['uuid']);
    }
})->group('booking-integration');

test('strict request contracts reject unknown parameters conflicting identifiers and malformed bodies without writes', function () {
    global $wpdb;
    wp_set_current_user(1);
    $client = bookingRest('/clients', 'POST', ['name' => 'Contract Client', 'email' => 'contract@example.invalid']);
    expect($client['status'])->toBe(201);
    $id = (int) $client['body']['data']['id'];
    $conflict = bookingRest('/clients/' . $id . '/put', 'POST', ['id' => $id + 1, 'name' => 'Spoofed']);
    expect($conflict['status'])->toBe(400)->and($conflict['body']['message'])->toContain('Conflicting request parameter');
    expect(bookingRest('/clients/1/put', 'POST', ['id' => true, 'name' => 'Invalid boolean'])['status'])->toBe(400);
    $floating = new WP_REST_Request('POST', '/wappointment/v1/clients/' . $id . '/put');
    $floating->set_header('content-type', 'application/json');
    $floating->set_body('{"id":' . $id . '.0,"name":"Invalid float"}');
    expect(rest_do_request($floating)->get_status())->toBe(400);
    expect(bookingRest('/clients/' . $id . '/put', 'POST', ['name' => 'Updated', 'surprise' => true])['status'])->toBe(400);
    expect(bookingRest('/clients', 'POST', ['name' => 'New', 'email' => 'new@example.invalid', 'unknown' => true])['status'])->toBe(400);
    expect(bookingRest('/clients', 'POST', ['name' => 'New', 'email' => 'new@example.invalid', 'options' => []])['status'])->toBe(400);
    expect(bookingRest('/clients/' . $id . '/put', 'POST', ['options' => (object) []])['status'])->toBe(200);
    expect($wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $id)))->toBe('Contract Client');
    expect((int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wappo_clients"))->toBe(1);

    $request = new WP_REST_Request('GET', '/wappointment/v1/clients');
    $request->set_query_params(['page_num' => '2', 'per_page' => '5', '_locale' => 'user', 'rest_route' => '/wappointment/v1/clients']);
    $response = rest_do_request($request);
    expect($response->get_status())->toBe(200)
        ->and($response->get_data()['data']['page'])->toBe(2)
        ->and($response->get_data()['data']['per_page'])->toBe(5);
    $request->set_query_params(['page_num' => '1abc']);
    expect(rest_do_request($request)->get_status())->toBe(400);
    $request->set_query_params(['unknown' => true]);
    expect(rest_do_request($request)->get_status())->toBe(400);

    foreach (['[]', 'null', 'true', '"string"', '{broken'] as $body) {
        $request = new WP_REST_Request('POST', '/wappointment/v1/clients');
        $request->set_header('content-type', 'application/json');
        $request->set_body($body);
        expect(rest_do_request($request)->get_status())->toBe(400);
    }
    $request = new WP_REST_Request('POST', '/wappointment/v1/clients');
    $request->set_header('content-type', 'text/plain');
    $request->set_body('{}');
    expect(rest_do_request($request)->get_status())->toBe(400);
})->group('booking-integration');

test('all admin listing and setup responses satisfy their contracts after booking', function () {
    global $wpdb;
    // The booking baseline does not install the legacy background-job subsystem.
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wappo_jobs (
        id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY, queue varchar(25) DEFAULT NULL,
        payload longtext NOT NULL, appointment_id int unsigned DEFAULT NULL,
        attempts tinyint unsigned NOT NULL DEFAULT 0, reserved_at int unsigned NOT NULL DEFAULT 0,
        available_at int unsigned NOT NULL DEFAULT 0, created_at int unsigned NOT NULL
    ) ENGINE=InnoDB");
    $wpdb->query("DELETE FROM {$wpdb->prefix}wappo_jobs");
    $wpdb->insert($wpdb->prefix . 'wappo_jobs', ['queue' => null, 'payload' => '{"job":"legacy"}', 'created_at' => time()]);
    expect($wpdb->last_error)->toBe('');
    $input = bookingFixture();
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(201);
    wp_set_current_user(1);
    foreach (['/booking-settings', '/appointments', '/jobs', '/clients', '/settings'] as $path) {
        $response = bookingRest($path);
        expect($response['status'])->toBe(200)->and($response['body']['success'])->toBeTrue();
    }
    $setup = bookingRest('/booking-settings')['body']['data'];
    expect(array_keys($setup['calendar']))->toBe(['id', 'name', 'options']);
    $appointments = bookingRest('/appointments')['body']['data']['appointments'];
    expect($appointments)->toHaveCount(1)->and($appointments[0]['client_email'])->toBe($input['email']);
    $jobs = bookingRest('/jobs')['body']['data'];
    expect($jobs['total'])->toBe(1)->and($jobs['data'][0]['queue'])->toBeNull();
})->group('booking-integration');

test('settings defaults validate and partial updates preserve stored extension settings', function () {
    $settings = \Wappointment\System\Container::getInstance()->make(\Wappointment\System\Settings::class);
    expect(\Wappointment\Http\Requests\SaveSettingsRequestData::from($settings->all())->toArray())->toEqual($settings->all());
    $settings->set('legacy_extension_setting', ['preserve' => true]);
    wp_set_current_user(1);
    expect(bookingRest('/settings', 'POST', ['approval_mode' => 0])['status'])->toBe(200);
    expect($settings->get('legacy_extension_setting'))->toBe(['preserve' => true]);
    expect(bookingRest('/settings', 'POST', ['unknown_setting' => true])['status'])->toBe(400);
})->group('booking-integration');

test('invalid Action results become logged server errors rather than successful or client responses', function () {
    $container = \Wappointment\System\Container::getInstance();
    $class = \Wappointment\Actions\Booking\GetBookingCatalogAction::class;
    $original = $container->make($class);
    $invalid = $this->createMock($class);
    $invalid->expects($this->once())->method('handle')->willReturn(['services' => []]);
    $container->bind($class, static fn() => $invalid);
    try {
        $result = bookingRest('/booking/catalog');
        expect($result['status'])->toBe(500)
            ->and($result['body']['code'])->toBe('wappointment_internal_error')
            ->and($result['body']['data']['status'])->toBe(500)
            ->and($result['body']['message'])->not->toContain('Missing response', 'ResponseData');
    } finally {
        $container->bind($class, static fn() => $original);
    }
})->group('booking-integration');
