<?php
declare(strict_types=1);

use Wappointment\Booking\Availability;
use Wappointment\Jobs\JobRegistry;
use Wappointment\Jobs\AdminSummaryJob;
use Wappointment\Jobs\SummaryFigures;
use Wappointment\Jobs\SummarySchedule;
use Wappointment\Notifications\SummaryEmail;

if (!getenv('WAPPOINTMENT_WP_ROOT') && !function_exists('__')) {
    function __(string $message, string $domain = ''): string
    {
        return $message;
    }
}

function summaryAt(string $local, string $zone = 'Europe/Paris'): int
{
    return (new DateTimeImmutable($local, new DateTimeZone($zone)))->getTimestamp();
}

test('legacy summary figure fixtures match their inputs and audited source', function () {
    $directory = __DIR__ . '/../Characterization/';
    $golden = json_decode(file_get_contents($directory . 'legacy-summary-figures.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($golden['source'])->toBe('fcf2cfe296a240421ed91f28ff3672ece04e4786')
        ->and($golden['source_sha256'])->toBe(['Services/Sections.php' => '9a9654ad917c0e007021fba4e4a92afb18db4a9d64d35561fedc5c7990f7d6e8'])
        ->and($golden['cases_sha256'])->toBe(hash_file('sha256', $directory . 'summary-figure-cases.php'));
});

test('free slots and coverage match the master Sections formulas', function (string $name) {
    $directory = __DIR__ . '/../Characterization/';
    $case = (require $directory . 'summary-figure-cases.php')[$name];
    $legacy = json_decode(file_get_contents($directory . 'legacy-summary-figures.json'), true, 512, JSON_THROW_ON_ERROR)['results'][$name];
    $coverage = SummaryFigures::coverage($case['booked'], $case['free'], $case['duration']);
    // Legacy returned 0 for "no availabilities" and "<n>%" otherwise.
    expect(SummaryFigures::freeSlots($case['free'], $case['duration']))->toBe($legacy['free_slots'])
        ->and($coverage === null ? 0 : $coverage . '%')->toBe($legacy['coverage']);
})->with(function () {
    foreach (array_keys(require __DIR__ . '/../Characterization/summary-figure-cases.php') as $name) {
        yield $name => [$name];
    }
});

test('daily summaries are sent at the configured hour for the next local day', function () {
    $paris = new DateTimeZone('Europe/Paris');
    $settings = ['daily_summary' => true, 'daily_summary_time' => 10];
    expect(SummarySchedule::next('daily', $settings, $paris, summaryAt('2030-03-04 09:59:59'), null))
        ->toBe(['period_start' => '2030-03-05', 'send_at' => summaryAt('2030-03-04 10:00')])
        // At the hour itself it is still due today (legacy `generateTime` only skips past times).
        ->and(SummarySchedule::next('daily', $settings, $paris, summaryAt('2030-03-04 10:00'), null)['period_start'])->toBe('2030-03-05')
        ->and(SummarySchedule::next('daily', $settings, $paris, summaryAt('2030-03-04 10:00:01'), null))
        ->toBe(['period_start' => '2030-03-06', 'send_at' => summaryAt('2030-03-05 10:00')])
        // A period already sent is never scheduled again, even when the hour moves later.
        ->and(SummarySchedule::next('daily', ['daily_summary_time' => 18] + $settings, $paris, summaryAt('2030-03-04 11:00'), '2030-03-05'))
        ->toBe(['period_start' => '2030-03-06', 'send_at' => summaryAt('2030-03-05 18:00')]);
});

test('weekly summaries are sent on the configured weekday and cover the next seven days', function () {
    $paris = new DateTimeZone('Europe/Paris');
    // 2030-03-04 is a Monday; weekday 1 is Monday as in legacy Carbon::isDayOfWeek.
    $settings = ['weekly_summary' => true, 'weekly_summary_day' => 1, 'weekly_summary_time' => 10];
    expect(SummarySchedule::next('weekly', $settings, $paris, summaryAt('2030-03-04 11:00'), null))
        ->toBe(['period_start' => '2030-03-12', 'send_at' => summaryAt('2030-03-11 10:00')])
        ->and(SummarySchedule::next('weekly', ['weekly_summary_day' => 0] + $settings, $paris, summaryAt('2030-03-04 11:00'), null))
        ->toBe(['period_start' => '2030-03-11', 'send_at' => summaryAt('2030-03-10 10:00')]);

    $period = SummarySchedule::period('weekly', '2030-03-12', $paris);
    expect($period['dates'])->toBe(['2030-03-12', '2030-03-13', '2030-03-14', '2030-03-15', '2030-03-16', '2030-03-17', '2030-03-18'])
        ->and($period['from'])->toBe(summaryAt('2030-03-12 00:00'))
        ->and($period['to'])->toBe(summaryAt('2030-03-19 00:00'));
    // A period that does not follow the configured weekday has no send time.
    expect(SummarySchedule::sendAt('weekly', $settings, $paris, '2030-03-13'))->toBeNull();
});

test('summary periods follow local days across DST changes', function () {
    $paris = new DateTimeZone('Europe/Paris');
    // 2030-03-31 is 23 hours long in Paris; legacy added a fixed 86400 seconds.
    $period = SummarySchedule::period('daily', '2030-03-31', $paris);
    expect($period['to'] - $period['from'])->toBe(23 * 3600)
        ->and(SummarySchedule::sendAt('daily', ['daily_summary_time' => 10], $paris, '2030-03-31'))->toBe(summaryAt('2030-03-30 10:00'));
    // A send hour inside the spring-forward gap moves forward to the next valid time.
    expect(SummarySchedule::sendAt('daily', ['daily_summary_time' => 2], $paris, '2030-04-01'))->toBe(summaryAt('2030-03-31 03:00'));
});

test('invalid summary settings schedule nothing', function (array $settings, string $kind) {
    expect(SummarySchedule::next($kind, $settings, new DateTimeZone('UTC'), 1893456000, null))->toBeNull();
})->with([
    'hour above 23' => [['daily_summary_time' => 24], 'daily'],
    'negative hour' => [['daily_summary_time' => -1], 'daily'],
    'fractional hour' => [['daily_summary_time' => 9.5], 'daily'],
    'missing hour' => [[], 'daily'],
    'weekday above 6' => [['weekly_summary_time' => 9, 'weekly_summary_day' => 7], 'weekly'],
    'unknown kind' => [['daily_summary_time' => 9], 'monthly'],
]);

test('free windows are the time the slot engine fills, before notice and horizon rules', function () {
    $calendar = ['options' => ['timezone' => 'Europe/Paris', 'avb' => 60, 'regav' => [
        'monday' => [[540, 720], [780, 1020]], 'precise' => true,
    ]]];
    $engine = new Availability();
    $busy = [[summaryAt('2030-03-04 10:00'), summaryAt('2030-03-04 11:15')]];
    $extra = [[summaryAt('2030-03-04 12:00'), summaryAt('2030-03-04 12:30')], [summaryAt('2030-03-03 23:00'), summaryAt('2030-03-04 00:30')]];
    expect($engine->freeWindows('2030-03-04', $calendar, $busy, $extra))->toBe([
        [summaryAt('2030-03-04 00:00'), summaryAt('2030-03-04 00:30')],
        [summaryAt('2030-03-04 09:00'), summaryAt('2030-03-04 10:00')],
        [summaryAt('2030-03-04 11:15'), summaryAt('2030-03-04 12:30')],
        [summaryAt('2030-03-04 13:00'), summaryAt('2030-03-04 17:00')],
    ]);
    $slots = $engine->slots('2030-03-04', $calendar, 60, 0, ['hours_before_booking_allowed' => 0], $busy, $extra, summaryAt('2030-03-01 00:00'));
    expect(array_column($slots, 'start'))->toBe([
        summaryAt('2030-03-04 09:00'), summaryAt('2030-03-04 11:15'),
        summaryAt('2030-03-04 13:00'), summaryAt('2030-03-04 14:00'), summaryAt('2030-03-04 15:00'), summaryAt('2030-03-04 16:00'),
    ]);
    expect($engine->latestBookableDate($calendar, ['allow_refreshavb' => true, 'refreshavb_at' => 23], summaryAt('2030-03-01 22:00')))->toBe('2030-04-29')
        ->and($engine->latestBookableDate($calendar, [], summaryAt('2030-03-01 22:00')))->toBe('2030-04-30');
});

function summaryEmailInput(array $overrides = []): array
{
    $zone = new DateTimeZone('Europe/Paris');
    return $overrides + [
        'kind' => 'daily', 'dates' => ['2030-03-05'], 'timezone' => $zone, 'staff_first_name' => 'Bob',
        'appointments' => [[
            'start' => summaryAt('2030-03-05 09:00'), 'end' => summaryAt('2030-03-05 09:45'), 'buffer' => 900,
            'client_name' => 'Ada <b>', 'client_email' => 'ada@example.invalid', 'location_name' => 'Office',
        ]],
        'free_slots' => 4, 'coverage' => 18, 'duration' => 30, 'calendar_url' => 'https://example.invalid/wp-admin/admin.php?page=wappointment-appointments',
        'date_format' => 'Y-m-d', 'time_format' => 'H:i', 'link_color' => '#123456',
    ];
}

test('the daily summary carries the legacy figures and appointment lines', function () {
    $email = SummaryEmail::compose(summaryEmailInput());
    expect($email->subject)->toBe('Daily summary for 2030-03-05')
        ->and($email->text)->toContain('Hi Bob,')
        ->and($email->text)->toContain('Here is a summary of your appointments for 2030-03-05')
        ->and($email->text)->toContain('New Appointments: 1')
        ->and($email->text)->toContain('Available slots: 4 (duration 30 min)')
        ->and($email->text)->toContain('Coverage: 18%')
        ->and($email->html)->toContain('09:00 Ada &lt;b&gt; / 30 min - Office<br>ada@example.invalid')
        ->and($email->html)->not->toContain('Open new slots')
        ->and($email->text)->toContain('Have a great day!')
        ->and($email->text)->not->toContain('.ics');
});

test('a day without availability says so and offers to open slots', function () {
    $email = SummaryEmail::compose(summaryEmailInput(['appointments' => [], 'free_slots' => 0, 'coverage' => null, 'staff_first_name' => '']));
    expect($email->text)->toContain('Hi,')
        ->and($email->text)->toContain('No availabilities for 2030-03-05')
        ->and($email->text)->not->toContain('Coverage')
        ->and($email->html)->toContain('href="https://example.invalid/wp-admin/admin.php?page=wappointment-appointments"')
        ->and($email->html)->toContain('Open new slots')
        ->and($email->html)->not->toContain('<hr/>');
});

test('the weekly summary lists every day of the week', function () {
    $dates = SummarySchedule::period('weekly', '2030-03-05', new DateTimeZone('Europe/Paris'))['dates'];
    $email = SummaryEmail::compose(summaryEmailInput(['kind' => 'weekly', 'dates' => $dates]));
    expect($email->subject)->toBe('Weekly summary  2030-03-05 - 2030-03-11')
        ->and($email->text)->toContain('Here is a summary of your appointments for this week: 2030-03-05 - 2030-03-11')
        ->and($email->html)->toContain('<strong>Tue 2030-03-05</strong>')
        ->and($email->html)->toContain('<strong>Mon 2030-03-11</strong>')
        ->and(substr_count($email->html, 'No appointments for that day'))->toBe(6)
        ->and($email->text)->toContain('Have a great week!');
});

test('summaries are a registered job on the v3 queue that never re-runs after a timeout', function () {
    expect(JobRegistry::has(JobRegistry::ADMIN_SUMMARY))->toBeTrue()
        ->and(JobRegistry::queueFor(JobRegistry::ADMIN_SUMMARY))->toBe(JobRegistry::NOTIFICATIONS_QUEUE)
        ->and(JobRegistry::handlerFor(JobRegistry::ADMIN_SUMMARY))->toBe(AdminSummaryJob::class)
        ->and(JobRegistry::queues())->toBe([JobRegistry::NOTIFICATIONS_QUEUE])
        ->and((new ReflectionClass(AdminSummaryJob::class))->newInstanceWithoutConstructor()->retriesAfterTimeout())->toBeFalse();
});
