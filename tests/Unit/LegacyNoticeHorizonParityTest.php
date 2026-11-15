<?php
declare(strict_types=1);

use Wappointment\Booking\Availability;

if (!getenv('WAPPOINTMENT_WP_ROOT') && !function_exists('__')) {
    function __(string $message, string $domain = ''): string
    {
        return $message;
    }
}

test('notice and horizon golden files correspond to pinned synthetic cases', function () {
    $directory = __DIR__ . '/../Characterization/';
    $weekly = json_decode(file_get_contents($directory . 'legacy-weekly.json'), true, 512, JSON_THROW_ON_ERROR);
    $horizon = json_decode(file_get_contents($directory . 'legacy-notice-horizon.json'), true, 512, JSON_THROW_ON_ERROR);
    $slots = json_decode(file_get_contents($directory . 'legacy-notice-slots.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($horizon['source'])->toBe($weekly['source'])
        ->and($horizon['source_sha256']['Availability.php'])->toBe('ff8932079bc211b4ca70ab7d8662d9de0659a08267169fc5f16e3160187b461b')
        ->and($horizon['source_sha256']['Settings.php'])->toBe('1e5e284b5882700aa756c473bf99bb7c5aef5015103f40e03f78b4c2b642ccef')
        ->and($horizon['cases_sha256'])->toBe(hash_file('sha256', $directory . 'notice-horizon-cases.php'))
        ->and($slots['source'])->toBe($weekly['source'])
        ->and($slots['source_sha256'])->toBe([
            'DatesExtended.vue' => '148eb77b79157ccf8af08d6bb4b7008d86be62e53fe0db7e213404d21e9e0743',
            'Calendar.vue' => 'e5a5a47f80c56dce7b46335e44f4de8ee18f3827e19cc1eaab7bd94931012d51',
            'DaySlots.vue' => '9591eb679dbbb8ba0600df2ca66b6f6487183f0fdce25594282f9dbb0e1f74b7',
            'IncrementTime.vue' => '53e8417161cc015f02ac8eaafaed06c0be4c63d04afb6bfd6055c28fa546ed38',
        ])
        ->and($slots['cases_sha256'])->toBe($horizon['cases_sha256'])
        ->and($slots['scenarios_sha256'])->toBe(hash_file('sha256', $directory . 'notice-slot-cases.json'))
        ->and($slots['windows_sha256'])->toBe(hash_file('sha256', $directory . 'legacy-notice-horizon.json'));
});

test('v3 returns the captured legacy slots for notice fluid and daily horizon cases', function (string $name, string $scenario) {
    $directory = __DIR__ . '/../Characterization/';
    $case = (require $directory . 'notice-horizon-cases.php')[$name];
    $scenarioData = json_decode(file_get_contents($directory . 'notice-slot-cases.json'), true, 512, JSON_THROW_ON_ERROR)[$scenario];
    $horizon = json_decode(file_get_contents($directory . 'legacy-notice-horizon.json'), true, 512, JSON_THROW_ON_ERROR);
    $golden = json_decode(file_get_contents($directory . 'legacy-notice-slots.json'), true, 512, JSON_THROW_ON_ERROR);
    $schedule = array_fill_keys(
        ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
        [$case['range']]
    ) + ['precise' => true];
    $calendar = ['options' => [
        'timezone' => $case['timezone'],
        'avb' => $case['days'],
        'regav' => $schedule,
    ]];
    $settings = array_merge($case['settings'], [
        'more_st' => $scenarioData['more_st'],
        'starting_each' => $scenarioData['starting_each'],
    ]);
    $slots = (new Availability())->slots(
        $case['date'], $calendar, $scenarioData['duration'], $scenarioData['buffer'],
        $settings, [], [], $horizon['cases'][$name]['now']
    );
    expect(array_column($slots, 'start'))->toBe($golden['slots'][$name][$scenario]);
})->with(function () {
    $directory = __DIR__ . '/../Characterization/';
    $scenarios = json_decode(file_get_contents($directory . 'notice-slot-cases.json'), true, 512, JSON_THROW_ON_ERROR);
    foreach (require $directory . 'notice-horizon-cases.php' as $name => $case) {
        foreach (array_keys($scenarios) as $scenario) {
            yield $name . ' / ' . $scenario => [$name, $scenario];
        }
    }
});

test('minimum notice and refresh boundaries reject malformed stored options explicitly', function (string $field, mixed $value) {
    $settings = ['hours_before_booking_allowed' => 0, 'refreshavb_at' => 23];
    $settings[$field] = $value;
    expect(fn() => (new Availability())->slots(
        '2026-11-16',
        ['options' => ['timezone' => 'UTC', 'avb' => 30, 'regav' => []]],
        30, 0, $settings, [], [], strtotime('2026-11-15 UTC')
    ))->toThrow(\Wappointment\Booking\BookingException::class, 'Invalid');
})->with([
    'negative notice' => ['hours_before_booking_allowed', -1],
    'non-numeric notice' => ['hours_before_booking_allowed', 'soon'],
    'infinite notice' => ['hours_before_booking_allowed', INF],
    'refresh hour below range' => ['refreshavb_at', -1],
    'refresh hour above range' => ['refreshavb_at', 24],
    'refresh switch malformed' => ['allow_refreshavb', 'false'],
]);

test('booking horizon rejects invalid stored day counts', function (mixed $days) {
    expect(fn() => (new Availability())->slots(
        '2026-11-16',
        ['options' => ['timezone' => 'UTC', 'avb' => $days, 'regav' => []]],
        30, 0, ['hours_before_booking_allowed' => 0], [], [], strtotime('2026-11-15 UTC')
    ))->toThrow(\Wappointment\Booking\BookingException::class, 'horizon');
})->with([0, 366, 1.5]);
