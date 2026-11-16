<?php
declare(strict_types=1);

use Wappointment\Actions\Jobs\QueueBookingNotificationsAction;
use Wappointment\Jobs\JobRegistry;
use Wappointment\Jobs\QueuedJob;
use Wappointment\Jobs\RetryPolicy;
use Wappointment\Repositories\JobQueueRepository;
use Wappointment\System\Container;
use Wappointment\System\JobScheduler;
use Wappointment\System\Settings;
use Wappointment\UseCases\Jobs\ProcessJobQueueUseCase;

if (!getenv('WAPPOINTMENT_WP_ROOT')) {
    test('job queue integration requires a disposable WordPress database')->skip('Set WAPPOINTMENT_WP_ROOT inside the disposable booking_test WordPress container.');
    return;
}

require_once rtrim(getenv('WAPPOINTMENT_WP_ROOT'), '/') . '/wp-load.php';
if (DB_NAME !== 'booking_test' || wp_get_environment_type() !== 'local') {
    throw new RuntimeException('Job queue integration tests may only run against the disposable local booking_test database.');
}

require_once __DIR__ . '/booking-helpers.php';

function queueTable(string $name = 'jobs'): string
{
    global $wpdb;
    return $wpdb->prefix . 'wappo_' . $name;
}

function queueWorker(int $now): array
{
    return Container::getInstance()->make(ProcessJobQueueUseCase::class)->execute($now);
}

function queueSettings(array $values): void
{
    Container::getInstance()->make(Settings::class)->setMultiple($values);
}

/** Publish legacy email reminders for the given events (2 confirmed, 3 rescheduled, 4 canceled, 5 pending). */
function queuePublishReminders(int ...$events): void
{
    global $wpdb;
    foreach ($events as $event) {
        $wpdb->insert(queueTable('reminders'), ['subject' => 'Reminder ' . $event, 'type' => 1, 'event' => $event, 'published' => 1]);
    }
}

function queuedNotifications(): array
{
    global $wpdb;
    return array_map(
        static fn(array $row): string => QueuedJob::fromRow($row)->params['notification'],
        $wpdb->get_results('SELECT * FROM ' . queueTable() . ' ORDER BY id', ARRAY_A)
    );
}

function queueBookingPage(): int
{
    $page = wp_insert_post([
        'post_title' => 'Disposable queue booking page', 'post_status' => 'publish',
        'post_type' => 'page', 'post_content' => '[wap_widget]',
    ], true);
    expect(is_wp_error($page))->toBeFalse();
    queueSettings(['booking_page' => $page]);
    return $page;
}

beforeEach(function () {
    global $wpdb;
    wp_set_current_user(0);
    foreach (['jobs', 'failed_jobs', 'reminders', 'appointments', 'statuses', 'calendar_service', 'service_location', 'clients', 'services', 'locations', 'calendars'] as $table) {
        $wpdb->query('DELETE FROM ' . queueTable($table));
    }
    update_option('wappointment_settings', [
        'hours_before_booking_allowed' => 0, 'hours_before_cancellation_allowed' => 0,
        'hours_before_rescheduling_allowed' => 0, 'buffer_time' => 15,
        'mail_status' => true, 'email_notifications' => 'ops@example.invalid, OPS@example.invalid',
        'notify_new_appointments' => true, 'notify_pending_appointments' => true,
        'notify_rescheduled_appointments' => true, 'notify_canceled_appointments' => true,
    ], false);
    Container::getInstance()->make(Settings::class)->clearCache();
    $this->mail = [];
    $this->mailResult = true;
    $this->captureMail = function ($return, $attributes) {
        $this->mail[] = $attributes;
        return $this->mailResult;
    };
    add_filter('pre_wp_mail', $this->captureMail, 10, 2);
});

afterEach(function () {
    remove_filter('pre_wp_mail', $this->captureMail, 10);
});

test('a booking and its notification jobs commit together and nothing is sent synchronously', function () {
    queuePublishReminders(2);
    $input = bookingFixture();
    $booking = bookingRest('/booking', 'POST', $input);
    expect($booking['status'])->toBe(201)
        ->and($booking['body']['data']['notification_queued'])->toBeTrue()
        ->and(queuedNotifications())->toBe(['customer_confirmed', 'admin_new'])
        ->and($this->mail)->toBe([]);

    global $wpdb;
    $row = $wpdb->get_row('SELECT * FROM ' . queueTable() . ' ORDER BY id LIMIT 1', ARRAY_A);
    expect($row['queue'])->toBe(JobRegistry::NOTIFICATIONS_QUEUE)
        ->and((int) $row['appointment_id'])->toBe($booking['body']['data']['id'])
        ->and((int) $row['attempts'])->toBe(0)
        ->and($row['payload'])->not->toContain($input['email']);

    // A rejected booking (same slot) leaves no job behind.
    expect(bookingRest('/booking', 'POST', $input)['status'])->toBe(409)
        ->and(queuedNotifications())->toHaveCount(2);
})->group('booking-integration');

test('a rolled-back booking transaction also removes the jobs it queued', function () {
    global $wpdb;
    queuePublishReminders(2);
    bookingFixture();
    $repository = Container::getInstance()->make(\Wappointment\Repositories\BookingRepository::class);
    $queue = Container::getInstance()->make(QueueBookingNotificationsAction::class);
    try {
        $repository->exclusive(function () use ($queue): void {
            $queue->handle('confirmed', ['id' => 999999, 'start_at' => '2031-01-01 10:00:00', 'end_at' => '2031-01-01 10:30:00', 'options' => '{}']);
            throw new RuntimeException('Simulated failure after queueing.');
        });
    } catch (RuntimeException $error) {
        expect($error->getMessage())->toBe('Simulated failure after queueing.');
    }
    expect((int) $wpdb->get_var('SELECT COUNT(*) FROM ' . queueTable()))->toBe(0);
})->group('booking-integration');

test('legacy toggles decide which notifications each transition queues', function () {
    queuePublishReminders(5);
    queueSettings(['approval_mode' => 0, 'notify_new_appointments' => false]);
    $input = bookingFixture();
    $pending = bookingRest('/booking', 'POST', $input)['body']['data'];
    expect($pending['status'])->toBe(0)
        ->and($pending['notification_queued'])->toBeTrue()
        ->and(queuedNotifications())->toBe(['customer_pending', 'admin_pending']);

    // No published "confirmed" reminder and admin_new turned off: confirming queues nothing.
    wp_set_current_user(1);
    expect(bookingRest('/appointments', 'POST', ['id' => $pending['id'], 'status' => 1])['status'])->toBe(200)
        ->and(queuedNotifications())->toBe(['customer_pending', 'admin_pending']);

    queueSettings(['mail_status' => false]);
    expect(bookingRest('/appointments', 'POST', ['id' => $pending['id'], 'status' => -1])['status'])->toBe(200)
        ->and(queuedNotifications())->toBe(['customer_pending', 'admin_pending']);
})->group('booking-integration');

test('confirm, reschedule and cancel each deliver one customer and one organizer email', function () {
    global $wpdb;
    $page = queueBookingPage();
    try {
        queuePublishReminders(2, 3, 4, 5);
        queueSettings(['approval_mode' => 0]);
        $input = bookingFixture();
        $wpdb->update(queueTable('calendars'), ['wp_uid' => 1], ['id' => $input['calendar_id']]);
        $booking = bookingRest('/booking', 'POST', $input)['body']['data'];
        wp_set_current_user(1);
        expect(bookingRest('/appointments', 'POST', ['id' => $booking['id'], 'status' => 1])['status'])->toBe(200);
        wp_set_current_user(0);
        $slots = bookingRest('/booking/availability', 'POST', ['edit_key' => $booking['edit_key'], 'date' => $input['date']]);
        $newStart = $slots['body']['data']['slots'][3]['start'];
        expect(bookingRest('/booking/manage', 'POST', [
            'action' => 'reschedule', 'edit_key' => $booking['edit_key'], 'date' => $input['date'], 'start' => $newStart,
        ])['status'])->toBe(200)
            ->and(bookingRest('/booking/manage', 'POST', ['action' => 'cancel', 'edit_key' => $booking['edit_key']])['status'])->toBe(200);
        // Canceling again through the admin endpoint is not a new transition.
        wp_set_current_user(1);
        expect(bookingRest('/appointments', 'POST', ['id' => $booking['id'], 'status' => -1])['status'])->toBe(200);

        expect(queuedNotifications())->toBe([
            'customer_pending', 'admin_pending', 'customer_confirmed', 'admin_new',
            'customer_rescheduled', 'admin_rescheduled', 'customer_canceled', 'admin_canceled',
        ]);
        $report = queueWorker(time());
        expect($report['completed'])->toBe(8)->and($report['failed'])->toBe(0)
            ->and($this->mail)->toHaveCount(8)
            ->and(queuedNotifications())->toBe([]);

        $admin = get_userdata(1)->user_email;
        foreach ([1, 3, 5, 7] as $index) {
            expect($this->mail[$index]['to'])->toEqualCanonicalizing(['ops@example.invalid', $admin])
                ->and(implode("\n", (array) $this->mail[$index]['headers']))->toContain('Reply-To: "Booking Test" <' . $input['email'] . '>');
        }
        foreach ([0, 2, 4, 6] as $index) {
            expect($this->mail[$index]['to'])->toBe([$input['email']]);
        }
        expect($this->mail[4]['message'])->toContain('Previously:')
            ->and($this->mail[4]['message'])->toContain('#wappointment=' . $booking['edit_key'])
            ->and($this->mail[6]['message'])->not->toContain('#wappointment=');

        // Re-running the worker (for example after a restart) sends nothing more.
        expect(queueWorker(time() + 3600)['completed'])->toBe(0)->and($this->mail)->toHaveCount(8);
    } finally {
        wp_delete_post($page, true);
    }
})->group('booking-integration');

test('a rejected delivery is retried with backoff and lands in failed jobs after the last attempt', function () {
    global $wpdb;
    queueSettings(['notify_new_appointments' => true]);
    $booking = bookingRest('/booking', 'POST', bookingFixture())['body']['data'];
    expect(queuedNotifications())->toBe(['admin_new']);
    $this->mailResult = false;
    $now = time();

    $first = queueWorker($now);
    $row = $wpdb->get_row('SELECT * FROM ' . queueTable(), ARRAY_A);
    expect($first['retried'])->toBe(1)
        ->and((int) $row['attempts'])->toBe(1)
        ->and((int) $row['reserved_at'])->toBe(0)
        ->and((int) $row['available_at'])->toBe($now + RetryPolicy::BACKOFF_SECONDS);
    expect(queueWorker($now + 60)['retried'])->toBe(0);

    $second = queueWorker($now + RetryPolicy::BACKOFF_SECONDS);
    expect($second['retried'])->toBe(1)
        ->and((int) $wpdb->get_var('SELECT available_at FROM ' . queueTable()))->toBe($now + 3 * RetryPolicy::BACKOFF_SECONDS);

    $third = queueWorker($now + 3 * RetryPolicy::BACKOFF_SECONDS);
    $failed = $wpdb->get_row('SELECT * FROM ' . queueTable('failed_jobs'), ARRAY_A);
    $exception = json_decode($failed['exception'], true, 512, JSON_THROW_ON_ERROR);
    expect($third['failed'])->toBe(1)
        ->and(queuedNotifications())->toBe([])
        ->and($this->mail)->toHaveCount(3)
        ->and($failed['queue'])->toBe(JobRegistry::NOTIFICATIONS_QUEUE)
        ->and((int) $failed['appointment_id'])->toBe($booking['id'])
        ->and(QueuedJob::fromRow($failed + ['id' => 0, 'attempts' => 0, 'reserved_at' => 0, 'available_at' => 0])->params['notification'])->toBe('admin_new')
        ->and($exception['reason'])->toBe('failed')
        ->and($exception['attempts'])->toBe(3);
})->group('booking-integration');

test('permanent failures skip retries and keep the booking saved', function () {
    global $wpdb;
    queueSettings(['mail_config' => ['method' => 'smtp']]);
    $booking = bookingRest('/booking', 'POST', bookingFixture())['body']['data'];
    $report = queueWorker(time());
    $exception = json_decode($wpdb->get_var('SELECT exception FROM ' . queueTable('failed_jobs')), true, 512, JSON_THROW_ON_ERROR);
    expect($report['failed'])->toBe(1)->and($report['retried'])->toBe(0)
        ->and($exception['message'])->toContain('"smtp" has not been migrated')
        ->and($this->mail)->toBe([])
        ->and((int) $wpdb->get_var($wpdb->prepare('SELECT status FROM ' . queueTable('appointments') . ' WHERE id = %d', $booking['id'])))->toBe(1);
})->group('booking-integration');

test('a reservation abandoned by a stopped worker is reported, never re-sent', function () {
    global $wpdb;
    bookingRest('/booking', 'POST', bookingFixture());
    $now = time();
    $wpdb->query($wpdb->prepare(
        'UPDATE ' . queueTable() . ' SET reserved_at = %d, attempts = 1',
        $now - RetryPolicy::RESERVATION_TIMEOUT - 1
    ));
    $report = queueWorker($now);
    $exception = json_decode($wpdb->get_var('SELECT exception FROM ' . queueTable('failed_jobs')), true, 512, JSON_THROW_ON_ERROR);
    expect($report['abandoned'])->toBe(1)->and($report['completed'])->toBe(0)
        ->and($exception['reason'])->toBe('timeout')
        ->and($this->mail)->toBe([])
        ->and(queuedNotifications())->toBe([]);

    // A fresh reservation is left alone.
    bookingRest('/booking', 'POST', bookingFixture());
    $wpdb->query($wpdb->prepare('UPDATE ' . queueTable() . ' SET reserved_at = %d, attempts = 1', $now - 10));
    expect(queueWorker($now)['abandoned'])->toBe(0)->and(queuedNotifications())->toHaveCount(1);
})->group('booking-integration');

test('legacy rows are left untouched and unsupported payloads on the v3 queue are quarantined', function () {
    global $wpdb;
    $legacy = wp_json_encode(['job' => '\\Wappointment\\Jobs\\AdminEmailNewAppointment', 'params' => ['appointment' => ['id' => 4]]]);
    $wpdb->insert(queueTable(), ['queue' => 'client', 'payload' => $legacy, 'created_at' => time()]);
    $wpdb->insert(queueTable(), ['queue' => JobRegistry::NOTIFICATIONS_QUEUE, 'payload' => $legacy, 'created_at' => time()]);
    $wpdb->insert(queueTable(), ['queue' => JobRegistry::NOTIFICATIONS_QUEUE, 'payload' => QueuedJob::encode('run_anything', []), 'created_at' => time()]);
    $wpdb->insert(queueTable(), ['queue' => JobRegistry::NOTIFICATIONS_QUEUE, 'payload' => '{not json', 'created_at' => time()]);

    $report = queueWorker(time());
    expect($report['quarantined'])->toBe(3)
        ->and($report['legacy_pending'])->toBe(['client' => 1])
        ->and($this->mail)->toBe([])
        ->and($wpdb->get_results('SELECT queue, payload, attempts, reserved_at FROM ' . queueTable(), ARRAY_A))
        ->toBe([['queue' => 'client', 'payload' => $legacy, 'attempts' => '0', 'reserved_at' => '0']]);
    $reasons = array_map(
        static fn(string $json): string => json_decode($json, true)['reason'],
        $wpdb->get_col('SELECT exception FROM ' . queueTable('failed_jobs'))
    );
    expect($reasons)->toBe(['quarantined', 'quarantined', 'quarantined']);
})->group('booking-integration');

test('a second worker does nothing while another holds the queue lock', function () {
    global $wpdb;
    bookingRest('/booking', 'POST', bookingFixture());
    $other = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
    $name = 'wappo_queue_' . substr(hash('sha256', DB_NAME . $wpdb->prefix), 0, 40);
    expect((string) $other->get_var($other->prepare('SELECT GET_LOCK(%s, 0)', $name)))->toBe('1');
    try {
        $report = queueWorker(time());
        expect($report['locked'])->toBeTrue()->and($report['completed'])->toBe(0)
            ->and(queuedNotifications())->toHaveCount(1)->and($this->mail)->toBe([]);
    } finally {
        $other->get_var($other->prepare('SELECT RELEASE_LOCK(%s)', $name));
        $other->close();
    }
    expect(queueWorker(time())['completed'])->toBe(1)->and($this->mail)->toHaveCount(1);
})->group('booking-integration');

test('a claimed job cannot be claimed again', function () {
    bookingRest('/booking', 'POST', bookingFixture());
    global $wpdb;
    $id = (int) $wpdb->get_var('SELECT id FROM ' . queueTable());
    $repository = Container::getInstance()->make(JobQueueRepository::class);
    expect($repository->claim($id, time()))->toBeTrue()
        ->and($repository->claim($id, time()))->toBeFalse()
        ->and((int) $wpdb->get_var('SELECT attempts FROM ' . queueTable()))->toBe(1);
})->group('booking-integration');

test('the worker is scheduled once in WP-cron and skips sites whose schema is not ready', function () {
    JobScheduler::unschedule();
    JobScheduler::ensureScheduled();
    $first = wp_next_scheduled(JobScheduler::HOOK);
    JobScheduler::ensureScheduled();
    expect($first)->toBeInt()
        ->and(wp_next_scheduled(JobScheduler::HOOK))->toBe($first)
        ->and(wp_get_schedules())->toHaveKey(JobScheduler::SCHEDULE);

    bookingRest('/booking', 'POST', bookingFixture());
    $schema = get_option('wappointment_booking_schema');
    update_option('wappointment_booking_schema', 'not-ready');
    try {
        JobScheduler::run();
        expect(queuedNotifications())->toHaveCount(1)->and($this->mail)->toBe([]);
    } finally {
        update_option('wappointment_booking_schema', $schema);
    }
    JobScheduler::run();
    expect(queuedNotifications())->toBe([])->and($this->mail)->toHaveCount(1);
})->group('booking-integration');
