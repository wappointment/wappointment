<?php
declare(strict_types=1);

use Wappointment\Jobs\BookingNotificationJob;
use Wappointment\Jobs\BookingNotificationMessage;
use Wappointment\Jobs\BookingNotificationPlan;
use Wappointment\Jobs\JobRegistry;
use Wappointment\Jobs\PermanentJobFailure;
use Wappointment\Jobs\QueuedJob;
use Wappointment\Jobs\RetryPolicy;
use Wappointment\Jobs\UnsupportedJobException;

if (!getenv('WAPPOINTMENT_WP_ROOT') && !function_exists('__')) {
    function __(string $message, string $domain = ''): string
    {
        return $message;
    }
}

function jobRow(string $payload, array $overrides = []): array
{
    return $overrides + [
        'id' => '7', 'queue' => JobRegistry::NOTIFICATIONS_QUEUE, 'payload' => $payload,
        'appointment_id' => '12', 'attempts' => '1', 'reserved_at' => '0', 'available_at' => '0', 'created_at' => '1',
    ];
}

function notificationSettings(array $overrides = []): array
{
    return $overrides + [
        'mail_status' => true, 'notify_new_appointments' => true, 'notify_pending_appointments' => true,
        'notify_rescheduled_appointments' => true, 'notify_canceled_appointments' => true,
    ];
}

test('v3 job payloads round-trip with typed columns from wpdb strings', function () {
    $payload = QueuedJob::encode(JobRegistry::BOOKING_NOTIFICATION, ['notification' => 'admin_new', 'appointment_id' => 12]);
    $job = QueuedJob::fromRow(jobRow($payload));
    expect($job->id)->toBe(7)
        ->and($job->type)->toBe(JobRegistry::BOOKING_NOTIFICATION)
        ->and($job->params)->toBe(['notification' => 'admin_new', 'appointment_id' => 12])
        ->and($job->appointmentId)->toBe(12)
        ->and($job->attempts)->toBe(1)
        ->and($job->rawPayload)->toBe($payload)
        ->and(json_decode($payload, true)['version'])->toBe(QueuedJob::PAYLOAD_VERSION);
    expect(QueuedJob::fromRow(jobRow(QueuedJob::encode('x', []), ['appointment_id' => null]))->appointmentId)->toBeNull();
    // Empty params stay a JSON object so the decoder accepts them.
    expect(QueuedJob::encode('x', []))->toContain('"params":{}');
});

test('legacy, malformed and unknown-version payloads are refused without resolving classes', function (string $payload, string $message) {
    expect(fn() => QueuedJob::fromRow(jobRow($payload)))->toThrow(UnsupportedJobException::class, $message);
})->with([
    'legacy class with slash' => ['{"job":"\\\\Wappointment\\\\Jobs\\\\AdminEmailNewAppointment","params":{}}', 'Legacy job Wappointment\\Jobs\\AdminEmailNewAppointment'],
    'legacy class without slash' => ['{"job":"Wappointment\\\\Jobs\\\\AvailabilityRegenerate","params":{}}', 'Legacy job'],
    'invalid json' => ['{not json', 'not valid JSON'],
    'no job' => ['{"params":{}}', 'no job type'],
    'wrong version' => ['{"job":"booking_notification","version":2,"params":{}}', 'Unsupported job payload version'],
    'list params' => ['{"job":"booking_notification","version":1,"params":"x"}', 'must be an object'],
]);

test('the registry only resolves declared job types to declared handlers', function () {
    expect(JobRegistry::handlerFor(JobRegistry::BOOKING_NOTIFICATION))->toBe(BookingNotificationJob::class)
        ->and(JobRegistry::queueFor(JobRegistry::BOOKING_NOTIFICATION))->toBe(JobRegistry::NOTIFICATIONS_QUEUE)
        ->and(JobRegistry::queues())->toBe([JobRegistry::NOTIFICATIONS_QUEUE])
        ->and(JobRegistry::has('Wappointment\\System\\Init'))->toBeFalse();
    expect(fn() => JobRegistry::handlerFor('Wappointment\\System\\Init'))->toThrow(UnsupportedJobException::class);
    expect(strlen(JobRegistry::NOTIFICATIONS_QUEUE))->toBeLessThanOrEqual(25);
    expect(in_array(JobRegistry::NOTIFICATIONS_QUEUE, ['client', 'admin', 'api', 'availability', 'daily', 'weekly', 'avb_daily', 'default'], true))->toBeFalse();
    expect(is_subclass_of(UnsupportedJobException::class, PermanentJobFailure::class))->toBeTrue();
});

test('retries are bounded with growing backoff and stale reservations are detected', function () {
    expect(RetryPolicy::nextAttemptAt(1, 1000))->toBe(1000 + RetryPolicy::BACKOFF_SECONDS)
        ->and(RetryPolicy::nextAttemptAt(2, 1000))->toBe(1000 + 2 * RetryPolicy::BACKOFF_SECONDS)
        ->and(RetryPolicy::nextAttemptAt(RetryPolicy::MAX_ATTEMPTS, 1000))->toBeNull()
        ->and(RetryPolicy::nextAttemptAt(200, 1000))->toBeNull();
    expect(RetryPolicy::isTimedOut(0, 10_000))->toBeFalse()
        ->and(RetryPolicy::isTimedOut(10_000 - RetryPolicy::RESERVATION_TIMEOUT, 10_000))->toBeFalse()
        ->and(RetryPolicy::isTimedOut(10_000 - RetryPolicy::RESERVATION_TIMEOUT - 1, 10_000))->toBeTrue();
});

test('email jobs are never re-run after a worker timeout', function () {
    $reflection = new ReflectionClass(BookingNotificationJob::class);
    expect($reflection->newInstanceWithoutConstructor()->retriesAfterTimeout())->toBeFalse();
});

test('notification plans follow legacy listener toggles and published reminder events', function (string $transition, array $settings, array $events, array $expected) {
    expect(BookingNotificationPlan::for($transition, notificationSettings($settings), $events))->toBe($expected);
})->with([
    'auto-confirmed booking' => ['confirmed', [], [2], ['customer_confirmed', 'admin_new']],
    'pending booking' => ['booked_pending', [], [5], ['customer_pending', 'admin_pending']],
    'pending without its reminder' => ['booked_pending', [], [2, 3, 4], ['admin_pending']],
    'reschedule' => ['rescheduled', [], ['3'], ['customer_rescheduled', 'admin_rescheduled']],
    'cancel' => ['canceled', [], [4], ['customer_canceled', 'admin_canceled']],
    'admin toggle off' => ['confirmed', ['notify_new_appointments' => false], [2], ['customer_confirmed']],
    'pending toggle off' => ['booked_pending', ['notify_pending_appointments' => 0], [], []],
    'mail disabled' => ['canceled', ['mail_status' => false], [4], []],
]);

test('unknown transitions are programming errors, not silent no-ops', function () {
    expect(fn() => BookingNotificationPlan::for('deleted', notificationSettings(), []))->toThrow(InvalidArgumentException::class);
    expect(BookingNotificationPlan::exists('admin_new'))->toBeTrue()
        ->and(BookingNotificationPlan::exists('customer_reminder'))->toBeFalse()
        ->and(BookingNotificationPlan::isCustomer('customer_canceled'))->toBeTrue()
        ->and(BookingNotificationPlan::isCustomer('admin_canceled'))->toBeFalse();
});

test('notification messages show local times, changes and only the details each recipient needs', function () {
    $context = [
        'client_name' => 'Ada', 'client_email' => 'ada@example.invalid', 'client_phone' => '+32 2 000',
        'service_name' => 'Consultation', 'location_name' => 'Office',
        'start' => 1893574800, 'end' => 1893576600, 'previous_start' => 1893571200, 'previous_end' => 1893573000,
        'timezone' => 'Europe/Brussels',
    ];
    $customer = BookingNotificationMessage::compose('customer_rescheduled', $context, 'https://example.invalid/book#wappointment=abc');
    expect($customer['subject'])->toBe('Your appointment was rescheduled')
        ->and($customer['body'])->toContain('When: Wednesday 2 January 2030, 10:00 - 10:30 (Europe/Brussels)')
        ->and($customer['body'])->toContain('Previously: Wednesday 2 January 2030, 09:00 - 09:30 (Europe/Brussels)')
        ->and($customer['body'])->toContain('Manage your appointment: https://example.invalid/book#wappointment=abc')
        ->and($customer['body'])->not->toContain('ada@example.invalid')
        ->and($customer['body'])->not->toContain('+32');

    $admin = BookingNotificationMessage::compose('admin_new', ['previous_start' => null, 'previous_end' => null, 'timezone' => 'bogus/zone'] + $context, null);
    expect($admin['subject'])->toBe('New appointment: Ada')
        ->and($admin['body'])->toContain('Client: Ada <ada@example.invalid>')
        ->and($admin['body'])->toContain('Phone: +32 2 000')
        ->and($admin['body'])->toContain('(UTC)')
        ->and($admin['body'])->not->toContain('Previously')
        ->and($admin['body'])->not->toContain('Manage your appointment');

    expect(fn() => BookingNotificationMessage::compose('customer_reminder', $context, null))->toThrow(PermanentJobFailure::class);
});
