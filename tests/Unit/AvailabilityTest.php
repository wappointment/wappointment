<?php
declare(strict_types=1);

use Wappointment\Booking\Availability;
use Wappointment\Booking\BookingException;

require_once __DIR__ . '/../../app/Booking/BookingException.php';
require_once __DIR__ . '/../../app/Booking/Availability.php';
require_once __DIR__ . '/../../app/Booking/WeeklySchedule.php';

if (!getenv('WAPPOINTMENT_WP_ROOT') && !function_exists('__')) {
    function __(string $message, string $domain = ''): string
    {
        return $message;
    }
}

function bookingCalendar(string $timezone = 'UTC', array $blocks = [[540, 720]]): array
{
    return ['options' => [
        'timezone' => $timezone, 'avb' => 60,
        'regav' => array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], $blocks) + ['precise' => true],
    ]];
}

test('only returns slots fitting the entire duration and buffer', function () {
    $slots = (new Availability())->slots('2026-11-16', bookingCalendar(), 60, 15, [
        'more_st' => true, 'starting_each' => 30, 'hours_before_booking_allowed' => 0,
    ], [], [], strtotime('2026-11-15 UTC'));
    expect(array_column($slots, 'start'))->toBe([
        strtotime('2026-11-16 09:00 UTC'), strtotime('2026-11-16 09:30 UTC'),
        strtotime('2026-11-16 10:00 UTC'), strtotime('2026-11-16 10:30 UTC'),
    ]);
    expect($slots[0]['reserved_until'] - $slots[0]['end'])->toBe(900);
});

test('busy intervals reject overlap but allow touching boundaries', function () {
    $slots = (new Availability())->slots('2026-11-16', bookingCalendar(), 30, 0, [
        'starting_each' => 30, 'hours_before_booking_allowed' => 0,
    ], [[strtotime('2026-11-16 10:00 UTC'), strtotime('2026-11-16 11:00 UTC')]], [], strtotime('2026-11-15 UTC'));
    expect(array_column($slots, 'start'))->toBe([
        strtotime('2026-11-16 09:00 UTC'), strtotime('2026-11-16 09:30 UTC'),
        strtotime('2026-11-16 11:00 UTC'), strtotime('2026-11-16 11:30 UTC'),
    ]);
});

test('honors minimum notice and booking horizon', function () {
    $now = strtotime('2026-11-16 08:15 UTC');
    $slots = (new Availability())->slots('2026-11-16', bookingCalendar(), 30, 0, [
        'starting_each' => 30, 'hours_before_booking_allowed' => 2,
    ], [], [], $now);
    expect($slots[0]['start'])->toBe(strtotime('2026-11-16 10:30 UTC'));
    expect((new Availability())->slots('2027-02-01', bookingCalendar(), 30, 0, [], [], [], $now))->toBe([]);
});

test('uses organizer local time through daylight saving changes', function (string $date, string $expected) {
    $slots = (new Availability())->slots($date, bookingCalendar('Europe/Paris'), 60, 0, [
        'hours_before_booking_allowed' => 0,
    ], [], [], strtotime($date . ' -1 day UTC'));
    expect($slots[0]['start'])->toBe(strtotime($expected));
})->with([
    ['2026-03-28', '2026-03-28 08:00 UTC'],
    ['2026-03-29', '2026-03-29 07:00 UTC'],
    ['2026-10-24', '2026-10-24 07:00 UTC'],
    ['2026-10-25', '2026-10-25 08:00 UTC'],
]);

test('does not duplicate slots in overlapping windows', function () {
    $slots = (new Availability())->slots('2026-11-16', bookingCalendar('UTC', [[540, 660], [600, 720]]), 30, 0, [
        'hours_before_booking_allowed' => 0,
    ], [], [[strtotime('2026-11-16 09:00 UTC'), strtotime('2026-11-16 10:00 UTC')]], strtotime('2026-11-15 UTC'));
    expect(array_column($slots, 'start'))->toHaveCount(6);
});

test('busy exceptions take precedence over additional free time', function () {
    $slots = (new Availability())->slots('2026-11-16', bookingCalendar('UTC', []), 30, 0, [
        'hours_before_booking_allowed' => 0,
    ], [[strtotime('2026-11-16 10:00 UTC'), strtotime('2026-11-16 10:30 UTC')]],
        [[strtotime('2026-11-16 09:00 UTC'), strtotime('2026-11-16 11:00 UTC')]], strtotime('2026-11-15 UTC'));
    expect(array_column($slots, 'start'))->toBe([
        strtotime('2026-11-16 09:00 UTC'), strtotime('2026-11-16 09:30 UTC'), strtotime('2026-11-16 10:30 UTC'),
    ]);
});

test('a booking can span touching weekly availability ranges', function () {
    $slots = (new Availability())->slots('2026-11-16', bookingCalendar('UTC', [[540, 600], [600, 660]]), 60, 0, [
        'more_st' => true, 'starting_each' => 30, 'hours_before_booking_allowed' => 0,
    ], [], [], strtotime('2026-11-15 UTC'));
    expect(array_column($slots, 'start'))->toContain(strtotime('2026-11-16 09:30 UTC'));
});

test('rejects invalid dates and intervals explicitly', function () {
    expect(fn() => (new Availability())->slots('2026-02-31', bookingCalendar(), 30, 0, [], [], [], time()))
        ->toThrow(BookingException::class, 'valid date');
    expect(fn() => (new Availability())->slots('2026-11-16', bookingCalendar(), 30, 0, ['more_st' => true, 'starting_each' => 0], [], [], time()))
        ->toThrow(BookingException::class, 'slot interval');
});

test('a valid duration plus buffer longer than the day has no slots rather than an invalid interval', function () {
    expect((new Availability())->slots('2026-11-16', bookingCalendar('UTC', [[0, 1440]]), 1440, 15, [
        'hours_before_booking_allowed' => 0,
    ], [], [], strtotime('2026-11-15 UTC')))->toBe([]);
});

test('does not silently ignore premium availability conditions', function () {
    expect(fn() => (new Availability())->slots('2026-11-16', bookingCalendar('UTC', [[540, 720, ['services' => [1]]]]),
        30, 0, [], [], [], strtotime('2026-11-15 UTC')))
        ->toThrow(BookingException::class, 'conditions');
});
