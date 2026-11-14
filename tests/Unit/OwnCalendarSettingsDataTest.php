<?php
declare(strict_types=1);

use Wappointment\Http\Data\CalendarWeeklySettingsData;
use Wappointment\Http\Requests\SaveOwnCalendarSettingsRequestData;
use Wappointment\Http\Responses\GetOwnCalendarSettingsResponseData;

function weeklyDataFixture(): array
{
    return [
        'timezone' => 'UTC', 'booking_days' => 60,
        'regav' => array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], [[0, 1440]]),
    ];
}

test('own weekly Data hydrates and serializes the exact typed contract', function () {
    $values = weeklyDataFixture();
    $request = SaveOwnCalendarSettingsRequestData::from(['id' => 15, 'settings' => $values]);
    expect($request->settings)->toBeInstanceOf(CalendarWeeklySettingsData::class)
        ->and($request->toArray())->toBe(['id' => 15, 'settings' => $values]);
    $response = GetOwnCalendarSettingsResponseData::from(['calendars' => [[
        'id' => 15, 'name' => 'Own calendar', 'settings' => $values, 'problem' => null,
    ]]]);
    expect($response->toArray()['calendars'][0]['settings'])->toBe($values);
});

test('own weekly Data rejects invalid timezones ranges and unknown properties', function (array $changes) {
    expect(fn() => CalendarWeeklySettingsData::from(array_replace(weeklyDataFixture(), $changes)))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'timezone' => [['timezone' => 'Mars/Olympus']],
    'horizon minimum' => [['booking_days' => 0]],
    'horizon maximum' => [['booking_days' => 366]],
    'unknown property' => [['wp_uid' => 123]],
    'reversed range' => [['regav' => array_replace(weeklyDataFixture()['regav'], ['monday' => [[700, 600]]])]],
    'empty range' => [['regav' => array_replace(weeklyDataFixture()['regav'], ['monday' => [[600, 600]]])]],
    'conditional range' => [['regav' => array_replace(weeklyDataFixture()['regav'], ['monday' => [[600, 700, []]]])]],
    'too many ranges' => [['regav' => array_replace(weeklyDataFixture()['regav'], ['monday' => array_fill(0, 25, [600, 700])])]],
]);
