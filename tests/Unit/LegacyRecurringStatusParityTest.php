<?php
declare(strict_types=1);

use Wappointment\Booking\RecurringStatusOccurrences;

if (!getenv('WAPPOINTMENT_WP_ROOT') && !function_exists('__')) {
    function __(string $message, string $domain = ''): string
    {
        return $message;
    }
}

test('legacy recurring status fixtures match their inputs and audited source', function () {
    $directory = __DIR__ . '/../Characterization/';
    $golden = json_decode(file_get_contents($directory . 'legacy-recurring-statuses.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($golden['source'])->toBe('fcf2cfe296a240421ed91f28ff3672ece04e4786')
        ->and($golden['source_sha256'])->toBe([
            'Services/Status.php' => 'ed97322a82286e357a2fc5e25573680dc78eba3ec8bde18e293853437344cf63',
            'Models/Status.php' => '202bc614979d3cb4507cd627ae8d16ab7f9a735faa4d361e4d9f3bada58c92d8',
        ])
        ->and($golden['cases_sha256'])->toBe(hash_file('sha256', $directory . 'recurring-status-cases.php'));
});

test('recurring busy and free occurrences match captured master rules', function (string $name) {
    $directory = __DIR__ . '/../Characterization/';
    $case = (require $directory . 'recurring-status-cases.php')[$name];
    $golden = json_decode(file_get_contents($directory . 'legacy-recurring-statuses.json'), true, 512, JSON_THROW_ON_ERROR);
    $timestamp = static fn(string $value): int => (new DateTimeImmutable($value, new DateTimeZone('UTC')))->getTimestamp();
    $status = [
        'recur' => $case['recur'],
        'start_at' => $case['start_utc'],
        'end_at' => $case['end_utc'],
        'options' => $case['options'],
    ];

    expect((new RecurringStatusOccurrences())->expand(
        $status,
        $timestamp($case['now']),
        $case['query_until']
    ))->toBe($golden['occurrences'][$name]);
})->with(function () {
    foreach (array_keys(require __DIR__ . '/../Characterization/recurring-status-cases.php') as $name) {
        yield $name => [$name];
    }
});

test('malformed recurring status data fails closed', function (array $status) {
    expect(fn() => (new RecurringStatusOccurrences())->expand($status, 1893456000, 1893542400))
        ->toThrow(\Wappointment\Booking\BookingException::class);
})->with([
    'missing origin timezone' => [[
        'recur' => 1, 'start_at' => '2030-01-02 09:00:00', 'end_at' => '2030-01-02 10:00:00',
        'options' => ['origin_start' => '2030-01-02 09:00:00'],
    ]],
    'invalid origin datetime' => [[
        'recur' => 1, 'start_at' => '2030-01-02 09:00:00', 'end_at' => '2030-01-02 10:00:00',
        'options' => ['origin_tz' => 'UTC', 'origin_start' => 'tomorrow'],
    ]],
    'invalid interval' => [[
        'recur' => 1, 'start_at' => '2030-01-02 09:00:00', 'end_at' => '2030-01-02 10:00:00',
        'options' => ['interval' => -1, 'origin_tz' => 'UTC', 'origin_start' => '2030-01-02 09:00:00'],
    ]],
    'invalid weekday' => [[
        'recur' => 2, 'start_at' => '2030-01-02 09:00:00', 'end_at' => '2030-01-02 10:00:00',
        'options' => ['byday' => [7], 'origin_tz' => 'UTC', 'origin_start' => '2030-01-02 09:00:00'],
    ]],
    'non-positive duration' => [[
        'recur' => 1, 'start_at' => '2030-01-02 10:00:00', 'end_at' => '2030-01-02 09:00:00',
        'options' => ['origin_tz' => 'UTC', 'origin_start' => '2030-01-02 09:00:00'],
    ]],
]);
