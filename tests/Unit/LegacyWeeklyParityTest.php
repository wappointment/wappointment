<?php
declare(strict_types=1);

use Wappointment\Booking\Availability;
use Wappointment\Booking\WeeklySchedule;

test('legacy golden files match their exact inputs and audited source', function () {
    $directory = __DIR__ . '/../Characterization/';
    $weekly = json_decode(file_get_contents($directory . 'legacy-weekly.json'), true, 512, JSON_THROW_ON_ERROR);
    $slots = json_decode(file_get_contents($directory . 'legacy-slots.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($weekly['source'])->toBe('fcf2cfe296a240421ed91f28ff3672ece04e4786')
        ->and($slots['source'])->toBe($weekly['source'])
        ->and($weekly['cases_sha256'])->toBe(hash_file('sha256', $directory . 'weekly-cases.php'))
        ->and($slots['cases_sha256'])->toBe(hash_file('sha256', $directory . 'slot-cases.json'))
        ->and($slots['weekly_sha256'])->toBe(hash_file('sha256', $directory . 'legacy-weekly.json'));
});

test('v3 matches captured master slots for supported weekly schedules and busy free intervals', function (string $name, string $scenario) {
    $directory = __DIR__ . '/../Characterization/';
    $case = (require $directory . 'weekly-cases.php')[$name];
    $input = json_decode(file_get_contents($directory . 'slot-cases.json'), true, 512, JSON_THROW_ON_ERROR)[$scenario];
    $golden = json_decode(file_get_contents($directory . 'legacy-slots.json'), true, 512, JSON_THROW_ON_ERROR);
    $schedule = array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], [$case['range']]);
    if ($case['precise'] !== null) {
        $schedule['precise'] = $case['precise'];
    }
    $intervals = static fn(array $rows): array => array_map(
        static fn(array $row): array => [strtotime($row[0]), strtotime($row[1])], $rows
    );
    $calendar = ['options' => ['timezone' => $case['timezone'], 'avb' => 3, 'regav' => $schedule]];
    $before = $calendar;
    $slots = (new Availability())->slots(
        $case['date'], $calendar, $input['duration'], $input['buffer'],
        $input['settings'] + ['hours_before_booking_allowed' => 0],
        $intervals($case['busy'] ?? []), $intervals($case['extra'] ?? []), strtotime($case['now'])
    );
    expect(array_column($slots, 'start'))->toBe($golden['slots'][$name][$scenario])
        ->and($calendar)->toBe($before);
    foreach ($slots as $slot) {
        expect($slot)->toBe([
            'start' => $slot['start'],
            'end' => $slot['start'] + $input['duration'] * 60,
            'reserved_until' => $slot['start'] + ($input['duration'] + $input['buffer']) * 60,
        ]);
    }
})->with(function () {
    $directory = __DIR__ . '/../Characterization/';
    $scenarios = json_decode(file_get_contents($directory . 'slot-cases.json'), true, 512, JSON_THROW_ON_ERROR);
    foreach (require $directory . 'weekly-cases.php' as $name => $case) {
        foreach (array_keys($scenarios) as $scenario) {
            yield $name . ' / ' . $scenario => [$name, $scenario];
        }
    }
});

test('stored weekly precision is explicit and API projection always uses minutes', function () {
    expect(WeeklySchedule::minutes(['monday' => [[9, 12]]])['monday'])->toBe([[540, 720]])
        ->and(WeeklySchedule::minutes(['monday' => [[9, 12]], 'precise' => false])['monday'])->toBe([[540, 720]])
        ->and(WeeklySchedule::minutes(['monday' => [[9, 12]], 'precise' => true])['monday'])->toBe([[9, 12]])
        ->and(WeeklySchedule::minutes(['monday' => [['9', '12']], 'precise' => 0])['monday'])->toBe([[540, 720]])
        ->and(WeeklySchedule::minutes([])['sunday'])->toBe([]);
});

test('stored weekly precision refuses invalid ambiguous or conditional data without guessing', function (mixed $stored) {
    expect(fn() => WeeklySchedule::minutes($stored))->toThrow(InvalidArgumentException::class);
})->with([
    'null' => [null],
    'scalar' => ['invalid'],
    'unknown day' => [['holiday' => []]],
    'null weekday' => [['monday' => null]],
    'invalid precision' => [['precise' => ['minutes']]],
    'unmarked minute values' => [['monday' => [[540, 720]]]],
    'explicit hours over 24' => [['monday' => [[9, 25]], 'precise' => false]],
    'minutes over 1440' => [['monday' => [[0, 1441]], 'precise' => true]],
    'conditional' => [['monday' => [[9, 12, ['services' => [1]]]]]],
    'empty third value' => [['monday' => [[9, 12, []]]]],
    'reversed' => [['monday' => [[12, 9]]]],
    'zero after legacy truncation' => [['monday' => [[9.25, 9.75]]]],
    'negative' => [['monday' => [[-1, 12]]]],
    'not numeric' => [['monday' => [['morning', 12]]]],
]);
