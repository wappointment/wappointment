<?php
declare(strict_types=1);

use Wappointment\Actions\Auth\RegisterRolesAction;
use Wappointment\Actions\Booking\ListAppointmentsAction;
use Wappointment\Actions\Booking\UpdateAppointmentStatusAction;
use Wappointment\Auth\AccessPolicy;
use Wappointment\Database\BookingSchema;
use Wappointment\Repositories\BookingRepository;
use Wappointment\System\Container;

if (!getenv('WAPPOINTMENT_WP_ROOT')) {
    test('permission integration requires disposable WordPress')->skip('Set WAPPOINTMENT_WP_ROOT inside a disposable local booking_test container.');
    return;
}

$_SERVER['HTTP_HOST'] ??= getenv('WAPPOINTMENT_TEST_HOST') ?: '127.0.0.1:18089';
$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
require_once rtrim(getenv('WAPPOINTMENT_WP_ROOT'), '/') . '/wp-load.php';
if (DB_NAME !== 'booking_test' || wp_get_environment_type() !== 'local') {
    throw new RuntimeException('Permission integration tests may only run against the disposable local booking_test database.');
}

function permissionRest(string $path, string $method = 'GET', ?array $payload = null): array
{
    $request = new WP_REST_Request($method, '/wappointment/v1' . $path);
    if ($payload !== null) {
        $request->set_header('content-type', 'application/json');
        $request->set_body(wp_json_encode($payload));
    }
    $response = rest_do_request($request);
    return ['status' => $response->get_status(), 'body' => $response->get_data()];
}

function permissionUser(int $id): void
{
    wp_set_current_user(0);
    wp_set_current_user($id);
}

function withPermissionFixture(callable $test): void
{
    global $wpdb;
    $original = get_current_user_id();
    $users = [];
    $calendars = [];
    $appointments = [];
    $clients = [];
    try {
        foreach (['manager' => 'wappointment_manager', 'staff' => 'wappointment_staff', 'other' => 'wappointment_staff', 'subscriber' => 'subscriber'] as $name => $role) {
            $id = wp_insert_user([
                'user_login' => 'permission-' . $name . '-' . bin2hex(random_bytes(4)),
                'user_pass' => wp_generate_password(), 'role' => $role,
            ]);
            if (is_wp_error($id)) {
                throw new RuntimeException($id->get_error_message());
            }
            $users[$name] = $id;
        }
        foreach (['staff', 'other'] as $name) {
            expect($wpdb->insert($wpdb->prefix . 'wappo_calendars', [
                'name' => 'Permission ' . $name, 'wp_uid' => $users[$name],
                'options' => '{}', 'status' => 1,
            ]))->not->toBeFalse();
            $calendars[$name] = (int) $wpdb->insert_id;
            expect($wpdb->insert($wpdb->prefix . 'wappo_clients', [
                'email' => 'permission-' . $name . '-' . bin2hex(random_bytes(4)) . '@example.invalid',
                'name' => 'Permission ' . $name, 'options' => '{}',
            ]))->not->toBeFalse();
            $clients[$name] = (int) $wpdb->insert_id;
            expect($wpdb->insert($wpdb->prefix . 'wappo_appointments', [
                'staff_id' => $calendars[$name], 'client_id' => $clients[$name],
                'start_at' => '2030-01-02 09:00:00', 'end_at' => '2030-01-02 09:30:00',
                'status' => 0, 'options' => '{}', 'edit_key' => bin2hex(random_bytes(16)),
            ]))->not->toBeFalse();
            $appointments[$name] = (int) $wpdb->insert_id;
        }
        $test($users, $calendars, $appointments, $clients);
    } finally {
        foreach ($appointments as $id) {
            $wpdb->delete($wpdb->prefix . 'wappo_appointments', ['id' => $id]);
        }
        foreach ($calendars as $id) {
            $wpdb->delete($wpdb->prefix . 'wappo_statuses', ['staff_id' => $id]);
            $wpdb->delete($wpdb->prefix . 'wappo_calendars', ['id' => $id]);
        }
        foreach ($clients as $id) {
            $wpdb->delete($wpdb->prefix . 'wappo_clients', ['id' => $id]);
        }
        permissionUser(1);
        foreach ($users as $id) {
            if (is_multisite()) {
                require_once ABSPATH . 'wp-admin/includes/ms.php';
                wpmu_delete_user($id);
            } else {
                require_once ABSPATH . 'wp-admin/includes/user.php';
                wp_delete_user($id);
            }
        }
        permissionUser($original);
    }
}

function permissionMenuPages(): array
{
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    global $menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages;
    $saved = [$menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages];
    $menu = $submenu = $admin_page_hooks = $_registered_pages = $_parent_pages = [];
    try {
        (new \Wappointment\Routes\AdminMenu())->register();
        $pages = [];
        foreach ($submenu as $parent => $items) {
            foreach ($items as $item) {
                if (str_starts_with($item[2], 'wappointment-')) {
                    expect(current_user_can($item[1]))->toBeTrue();
                    $pages[] = $item[2];
                }
            }
        }
        return array_values(array_unique($pages));
    } finally {
        [$menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages] = $saved;
    }
}

test('new roles retain legacy names and defaults without administrator escalation', function () {
    $staff = get_role('wappointment_staff');
    $manager = get_role('wappointment_manager');
    expect($staff)->not->toBeNull()->and($manager)->not->toBeNull();
    expect($staff->has_cap('wappo_calendar_man'))->toBeTrue()
        ->and($staff->has_cap('wappo_calendar_confirm'))->toBeTrue()
        ->and($staff->has_cap('wappo_clients_edit'))->toBeTrue()
        ->and($staff->has_cap('manage_options'))->toBeFalse()
        ->and($manager->has_cap('manage_options'))->toBeFalse();
})->group('permissions-integration');

test('role registration preserves explicit role and per-user restrictions on rerun', function () {
    withPermissionFixture(function ($users) {
        $staff = get_role('wappointment_staff');
        $original = $staff->capabilities;
        $user = get_userdata($users['staff']);
        $user->add_cap('wappo_calendar_confirm', false);
        try {
            $staff->add_cap('wappo_calendar_cancel', false);
            (new RegisterRolesAction())->handle();
            expect(get_role('wappointment_staff')->capabilities)->toBe(array_replace($original, ['wappo_calendar_cancel' => false]));
        } finally {
            foreach ($original as $cap => $grant) {
                $staff->add_cap($cap, $grant);
            }
        }
        permissionUser($users['staff']);
        expect((new AccessPolicy())->can('appointments.confirm'))->toBeFalse();
    });
})->group('permissions-integration');

test('staff list only their own appointments and cannot override the owner with request parameters', function () {
    withPermissionFixture(function ($users, $calendars, $appointments) {
        permissionUser($users['staff']);
        $listed = permissionRest('/appointments');
        expect($listed['status'])->toBe(200)
            ->and(array_map('intval', array_column($listed['body']['data']['appointments'], 'id')))->toBe([$appointments['staff']]);
        $request = new WP_REST_Request('GET', '/wappointment/v1/appointments');
        $request->set_query_params(['calendar_id' => $calendars['other']]);
        expect(rest_do_request($request)->get_status())->toBe(400);
        permissionUser($users['other']);
        expect(array_map('intval', array_column(permissionRest('/appointments')['body']['data']['appointments'], 'id')))
            ->toBe([$appointments['other']]);
    });
})->group('permissions-integration');

test('confirmation and cancellation require their own capability as well as calendar access', function () {
    withPermissionFixture(function ($users, $calendars, $appointments) {
        $user = get_userdata($users['staff']);
        $user->add_cap('wappo_calendar_cancel', false);
        permissionUser($users['staff']);
        expect(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => -1])['status'])->toBe(403)
            ->and(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => 1])['status'])->toBe(200);
        $user->add_cap('wappo_calendar_cancel', true);
        $user->add_cap('wappo_calendar_confirm', false);
        permissionUser($users['staff']);
        expect(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => 1])['status'])->toBe(403)
            ->and(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => -1])['status'])->toBe(200);
        $user->add_cap('wappo_calendar_man', false);
        permissionUser($users['staff']);
        expect(permissionRest('/appointments')['status'])->toBe(403)
            ->and(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => -1])['status'])->toBe(403);
    });
})->group('permissions-integration');

test('calendar view-only grants never authorize either mutation', function () {
    withPermissionFixture(function ($users, $calendars, $appointments) {
        $user = get_userdata($users['staff']);
        $user->add_cap('wappo_calendar_confirm', false);
        $user->add_cap('wappo_calendar_cancel', false);
        permissionUser($users['staff']);
        expect(permissionRest('/appointments')['status'])->toBe(200);
        foreach ([-1, 1] as $status) {
            expect(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => $status])['status'])->toBe(403);
            expect(fn() => Container::getInstance()->make(UpdateAppointmentStatusAction::class)->handle([
                'id' => $appointments['staff'], 'status' => $status,
            ]))->toThrow(RuntimeException::class, 'You are not allowed');
        }
    });
})->group('permissions-integration');

test('ownership filtering happens before the 100 row limit and does not hide unpublished calendar history', function () {
    withPermissionFixture(function ($users, $calendars, $appointments) {
        global $wpdb;
        $added = [];
        try {
            for ($offset = 0; $offset < 105; $offset++) {
                $start = (new DateTimeImmutable('2031-01-01 09:00:00'))->modify('+' . $offset . ' days');
                expect($wpdb->insert($wpdb->prefix . 'wappo_appointments', [
                    'staff_id' => $calendars['other'], 'status' => 0,
                    'start_at' => $start->format('Y-m-d H:i:s'),
                    'end_at' => $start->modify('+30 minutes')->format('Y-m-d H:i:s'),
                    'options' => '{}',
                ]))->not->toBeFalse();
                $added[] = (int) $wpdb->insert_id;
            }
            $wpdb->update($wpdb->prefix . 'wappo_calendars', ['status' => 0], ['id' => $calendars['staff']]);
            permissionUser($users['staff']);
            expect(array_map('intval', array_column(permissionRest('/appointments')['body']['data']['appointments'], 'id')))
                ->toBe([$appointments['staff']]);
            $wpdb->update($wpdb->prefix . 'wappo_appointments', ['staff_id' => 2147483647], ['id' => $appointments['staff']]);
            expect(permissionRest('/appointments')['body']['data']['appointments'])->toBe([]);
        } finally {
            foreach ($added as $id) {
                $wpdb->delete($wpdb->prefix . 'wappo_appointments', ['id' => $id]);
            }
        }
    });
})->group('permissions-integration');

test('staff cannot change guessed appointments owned by another user', function () {
    withPermissionFixture(function ($users, $calendars, $appointments) {
        global $wpdb;
        permissionUser($users['staff']);
        foreach ([-1, 1] as $status) {
            expect(permissionRest('/appointments', 'POST', ['id' => $appointments['other'], 'status' => $status])['status'])->toBe(404);
        }
        expect($wpdb->get_var($wpdb->prepare("SELECT status FROM {$wpdb->prefix}wappo_appointments WHERE id = %d", $appointments['other'])))->toBe('0');
        expect(fn() => Container::getInstance()->make(UpdateAppointmentStatusAction::class)->handle([
            'id' => $appointments['other'], 'status' => 1,
        ]))->toThrow(\Wappointment\Booking\BookingException::class, 'Appointment not found');
    });
})->group('permissions-integration');

test('missing deleted and reassigned calendars do not grant access through a stale policy or repository', function () {
    withPermissionFixture(function ($users, $calendars, $appointments) {
        global $wpdb;
        permissionUser($users['staff']);
        $action = Container::getInstance()->make(ListAppointmentsAction::class);
        expect($action->handle()['appointments'])->toHaveCount(1);
        $wpdb->update($wpdb->prefix . 'wappo_calendars', ['deleted_at' => '2026-01-01 00:00:00'], ['id' => $calendars['staff']]);
        expect($action->handle()['appointments'])->toBe([]);
        expect(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => 1])['status'])->toBe(404);
        $wpdb->update($wpdb->prefix . 'wappo_calendars', [
            'deleted_at' => null, 'wp_uid' => $users['other'],
        ], ['id' => $calendars['staff']]);
        expect($action->handle()['appointments'])->toBe([]);
        permissionUser($users['other']);
        expect($action->handle()['appointments'])->toHaveCount(2);
    });
})->group('permissions-integration');

test('manager role restores site-wide operations without needing manage_options', function () {
    withPermissionFixture(function ($users, $calendars, $appointments) {
        permissionUser($users['manager']);
        expect(current_user_can('manage_options'))->toBeFalse();
        $listed = permissionRest('/appointments');
        expect($listed['status'])->toBe(200)
            ->and(array_map('intval', array_column($listed['body']['data']['appointments'], 'id')))
            ->toContain($appointments['staff'], $appointments['other']);
        expect(permissionRest('/appointments', 'POST', ['id' => $appointments['other'], 'status' => 1])['status'])->toBe(200)
            ->and(permissionRest('/clients')['status'])->toBe(200)
            ->and(permissionRest('/settings')['status'])->toBe(200)
            ->and(permissionRest('/booking-settings')['status'])->toBe(200)
            ->and(permissionRest('/system/schema')['status'])->toBe(200);
        $policy = new AccessPolicy();
        expect(permissionMenuPages())->toEqualCanonicalizing($policy->allowedPages())
            ->and($policy->appointmentOwner())->toBeNull();
    });
})->group('permissions-integration');

test('staff menus expose supported scoped pages and leave global operations blocked', function () {
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        permissionUser($users['staff']);
        expect(permissionMenuPages())->toBe(['wappointment-appointments', 'wappointment-clients', 'wappointment-my-availability']);
        foreach (['/settings', '/booking-settings', '/jobs', '/system/schema'] as $path) {
            expect(permissionRest($path)['status'])->toBe(403);
        }
        foreach ([
            ['/clients', 'POST', ['name' => 'Denied', 'email' => 'denied@example.invalid']],
            ['/settings', 'POST', ['approval_mode' => 0]],
        ] as [$path, $method, $payload]) {
            expect(permissionRest($path, $method, $payload)['status'])->toBe(403);
        }
    });
})->group('permissions-integration');

test('anonymous subscribers and unassigned staff cannot acquire other calendars through numeric IDs', function () {
    withPermissionFixture(function ($users, $calendars, $appointments) {
        foreach ([0, $users['subscriber']] as $id) {
            permissionUser($id);
            expect(permissionRest('/appointments')['status'])->toBeIn([401, 403])
                ->and(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => -1])['status'])->toBeIn([401, 403])
                ->and(permissionMenuPages())->toBe([]);
            expect(fn() => Container::getInstance()->make(ListAppointmentsAction::class)->handle())->toThrow(RuntimeException::class);
        }
        get_userdata($users['subscriber'])->add_cap('wappo_calendar_man', true);
        get_userdata($users['subscriber'])->add_cap('wappo_calendar_confirm', true);
        permissionUser($users['subscriber']);
        expect(permissionRest('/appointments')['body']['data']['appointments'])->toBe([])
            ->and(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => 1])['status'])->toBe(404);
    });
})->group('permissions-integration');

test('permissions change immediately when reusing the policy object for a different user', function () {
    withPermissionFixture(function ($users) {
        $policy = new AccessPolicy();
        permissionUser($users['manager']);
        expect($policy->can('manage'))->toBeTrue()->and($policy->appointmentOwner())->toBeNull();
        permissionUser($users['staff']);
        expect($policy->can('manage'))->toBeFalse()->and($policy->appointmentOwner())->toBe($users['staff']);
        permissionUser(0);
        expect($policy->can('appointments.view'))->toBeFalse()
            ->and(fn() => $policy->can('typo'))->toThrow(LogicException::class);
    });
})->group('permissions-integration');

test('the legacy switch_themes heuristic does not grant global booking access', function () {
    withPermissionFixture(function ($users) {
        get_userdata($users['subscriber'])->add_cap('switch_themes', true);
        permissionUser($users['subscriber']);
        expect(current_user_can('switch_themes'))->toBeTrue()
            ->and((new AccessPolicy())->isManager())->toBeFalse()
            ->and(permissionRest('/appointments')['status'])->toBe(403)
            ->and(permissionMenuPages())->toBe([]);
    });
})->group('permissions-integration');

test('direct admin rendering denies inaccessible pages before enqueuing the app', function () {
    withPermissionFixture(function ($users) {
        permissionUser($users['staff']);
        $handler = static fn() => static function ($message): void {
            throw new RuntimeException($message, 403);
        };
        add_filter('wp_die_handler', $handler);
        try {
            foreach (['wappointment-settings', 'wappointment-diagnostics', 'unknown'] as $page) {
                expect(fn() => Container::getInstance()->make(\Wappointment\Actions\Admin\RenderAdminAppAction::class)->handle($page))
                    ->toThrow(RuntimeException::class, 'You are not allowed');
            }
        } finally {
            remove_filter('wp_die_handler', $handler);
        }
    });
})->group('permissions-integration');

test('the admin bootstrap exposes only the current users pages and exact operation grants', function () {
    withPermissionFixture(function ($users) {
        global $wp_scripts;
        $saved = $wp_scripts;
        try {
            $wp_scripts = new WP_Scripts();
            get_userdata($users['staff'])->add_cap('wappo_calendar_cancel', false);
            permissionUser($users['staff']);
            ob_start();
            try {
                Container::getInstance()->make(\Wappointment\Actions\Admin\RenderAdminAppAction::class)
                    ->handle('wappointment-appointments');
            } finally {
                ob_end_clean();
            }
            expect(preg_match('/var wappointmentData = (.+);/', $wp_scripts->get_data('wappointment-react-app', 'data'), $matches))->toBe(1);
            $data = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
            expect($data['allowedPages'])->toBe(['wappointment-appointments', 'wappointment-clients', 'wappointment-my-availability'])
                ->and($data['permissions'])->toBe([
                    'appointmentsConfirm' => true, 'appointmentsCancel' => false,
                    'clientsCreate' => false, 'clientsEdit' => true, 'clientsDelete' => true,
                    'selfWeekly' => true, 'selfStatuses' => true,
                ]);
        } finally {
            $wp_scripts = $saved;
        }
    });
})->group('permissions-integration');

test('staff client listing search and counts use owned appointments before pagination', function () {
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        global $wpdb;
        $extra = [];
        try {
            for ($offset = 0; $offset < 12; $offset++) {
                expect($wpdb->insert($wpdb->prefix . 'wappo_clients', [
                    'name' => 'Unowned pagination client', 'email' => bin2hex(random_bytes(6)) . '@example.invalid',
                ]))->not->toBeFalse();
                $extra[] = (int) $wpdb->insert_id;
            }
            permissionUser($users['staff']);
            $request = new WP_REST_Request('GET', '/wappointment/v1/clients');
            $request->set_query_params(['per_page' => 1]);
            $response = rest_do_request($request);
            expect($response->get_status())->toBe(200);
            $data = $response->get_data()['data'];
            expect($data['total'])->toBe(1)->and($data['total_pages'])->toBe(1)
                ->and((int) $data['data'][0]['id'])->toBe($clients['staff']);
            $request->set_query_params(['per_page' => 1, 'page_num' => 2]);
            $data = rest_do_request($request)->get_data()['data'];
            expect($data['total'])->toBe(1)->and($data['data'])->toBe([]);
            foreach (['other', 'Unowned pagination', "' OR 1=1 --", '%s', '%', '_'] as $term) {
                $request->set_query_params(['search' => $term]);
                $result = rest_do_request($request);
                expect($result->get_status())->toBe(200)
                    ->and($result->get_data()['data']['total'])->toBe(0);
            }
            $wpdb->update($wpdb->prefix . 'wappo_clients', ['options' => '{"phone":"555-1234"}'], ['id' => $clients['staff']]);
            $request->set_query_params(['search' => '555-1234']);
            expect(rest_do_request($request)->get_data()['data']['total'])->toBe(1);
            permissionUser($users['other']);
            expect((int) permissionRest('/clients')['body']['data']['data'][0]['id'])->toBe($clients['other']);
        } finally {
            foreach ($extra as $id) {
                $wpdb->delete($wpdb->prefix . 'wappo_clients', ['id' => $id]);
            }
        }
    });
})->group('permissions-integration');

test('client permissions are independent of calendar permissions and require their own parent grant', function () {
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        $user = get_userdata($users['staff']);
        $user->add_cap('wappo_calendar_man', false);
        $user->add_cap('wappo_clients_edit', false);
        $user->add_cap('wappo_clients_del', false);
        permissionUser($users['staff']);
        expect(permissionMenuPages())->toBe(['wappointment-clients', 'wappointment-my-availability'])
            ->and(permissionRest('/appointments')['status'])->toBe(403)
            ->and(permissionRest('/clients')['status'])->toBe(200)
            ->and(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Denied'])['status'])->toBe(403)
            ->and(permissionRest('/clients/' . $clients['staff'] . '/delete', 'POST')['status'])->toBe(403);
        $user->add_cap('wappo_clients_edit', true);
        permissionUser($users['staff']);
        expect(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Allowed edit'])['status'])->toBe(200)
            ->and(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Allowed edit'])['status'])->toBe(200)
            ->and(permissionRest('/clients/' . $clients['staff'] . '/delete', 'POST')['status'])->toBe(403);
        $user->add_cap('wappo_clients_edit', false);
        $user->add_cap('wappo_clients_del', true);
        permissionUser($users['staff']);
        expect(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Denied'])['status'])->toBe(403)
            ->and(permissionRest('/clients/' . $clients['staff'] . '/delete', 'POST')['status'])->toBe(200);
        $user->add_cap('wappo_clients_man', false);
        permissionUser($users['staff']);
        expect(permissionMenuPages())->toBe(['wappointment-my-availability'])
            ->and(permissionRest('/clients')['status'])->toBe(403)
            ->and(permissionRest('/clients/' . $clients['other'] . '/delete', 'POST')['status'])->toBe(403);
    });
})->group('permissions-integration');

test('client mutations reject guessed unowned IDs and never change their records', function () {
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        global $wpdb;
        permissionUser($users['staff']);
        $before = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $clients['other']), ARRAY_A);
        foreach ([$clients['other'], 2147483647] as $id) {
            expect(permissionRest('/clients/' . $id . '/put', 'POST', ['name' => 'Denied'])['status'])->toBe(404)
                ->and(permissionRest('/clients/' . $id . '/delete', 'POST')['status'])->toBe(404);
        }
        expect($wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $clients['other']), ARRAY_A))->toBe($before);
        expect(fn() => Container::getInstance()->make(\Wappointment\Actions\Clients\UpdateClientAction::class)->handle(
            new \Wappointment\Http\Requests\UpdateClientRequestData($clients['other'], name: 'Denied')
        ))->toThrow(RuntimeException::class, 'Client not found');
        expect(fn() => Container::getInstance()->make(\Wappointment\Actions\Clients\DeleteClientAction::class)->handle($clients['other']))
            ->toThrow(RuntimeException::class, 'Client not found');
    });
})->group('permissions-integration');

test('a shared client is listed once and editable through either owners historical appointments', function () {
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'wappo_appointments', [
            'client_id' => $clients['staff'], 'status' => -1,
        ], ['id' => $appointments['other']]);
        $extra = 0;
        try {
            $wpdb->insert($wpdb->prefix . 'wappo_appointments', [
                'staff_id' => $calendars['staff'], 'client_id' => $clients['staff'],
                'start_at' => '2030-01-03 09:00:00', 'end_at' => '2030-01-03 09:30:00',
                'status' => 1, 'options' => '{}',
            ]);
            $extra = (int) $wpdb->insert_id;
            $wpdb->update($wpdb->prefix . 'wappo_calendars', ['status' => 0], ['id' => $calendars['other']]);
            foreach (['staff', 'other'] as $name) {
                permissionUser($users[$name]);
                $list = permissionRest('/clients')['body']['data'];
                expect($list['total'])->toBe(1)->and($list['data'])->toHaveCount(1)
                    ->and((int) $list['data'][0]['id'])->toBe($clients['staff'])
                    ->and(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Shared ' . $name])['status'])->toBe(200);
            }
            expect($wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $clients['staff'])))->toBe('Shared other');
        } finally {
            if ($extra) {
                $wpdb->delete($wpdb->prefix . 'wappo_appointments', ['id' => $extra]);
            }
        }
    });
})->group('permissions-integration');

test('soft deletion hides the shared client everywhere while preserving all appointment references', function () {
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'wappo_appointments', ['client_id' => $clients['staff']], ['id' => $appointments['other']]);
        $before = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_appointments ORDER BY id", ARRAY_A);
        permissionUser($users['staff']);
        expect(permissionRest('/clients/' . $clients['staff'] . '/delete', 'POST')['status'])->toBe(200)
            ->and(permissionRest('/clients')['body']['data']['total'])->toBe(0)
            ->and(permissionRest('/clients/' . $clients['staff'] . '/delete', 'POST')['status'])->toBe(404)
            ->and(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Deleted'])['status'])->toBe(404);
        $stored = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $clients['staff']), ARRAY_A);
        expect($stored)->not->toBeNull()->and($stored['deleted_at'])->not->toBeNull()
            ->and($wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_appointments ORDER BY id", ARRAY_A))->toBe($before);
        foreach (['other', 'manager'] as $name) {
            permissionUser($users[$name]);
            $listed = array_map('intval', array_column(permissionRest('/clients')['body']['data']['data'], 'id'));
            expect($listed)->not->toContain($clients['staff']);
        }
    });
})->group('permissions-integration');

test('client ownership follows all current calendars and loses access on deletion reassignment or missing relation', function () {
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        global $wpdb;
        $action = Container::getInstance()->make(\Wappointment\Actions\Clients\ListClientsAction::class);
        $request = new \Wappointment\Http\Requests\ListClientsRequestData();
        permissionUser($users['staff']);
        expect($action->handle($request)['total'])->toBe(1);
        $wpdb->update($wpdb->prefix . 'wappo_calendars', ['deleted_at' => '2025-01-01 00:00:00'], ['id' => $calendars['staff']]);
        expect($action->handle($request)['total'])->toBe(0)
            ->and(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Denied'])['status'])->toBe(404);
        $wpdb->update($wpdb->prefix . 'wappo_calendars', ['deleted_at' => null, 'wp_uid' => $users['other']], ['id' => $calendars['staff']]);
        expect($action->handle($request)['total'])->toBe(0);
        permissionUser($users['other']);
        expect($action->handle($request)['total'])->toBe(2);
        $wpdb->update($wpdb->prefix . 'wappo_appointments', ['staff_id' => 2147483647], ['id' => $appointments['staff']]);
        expect($action->handle($request)['total'])->toBe(1);
        permissionUser($users['manager']);
        expect($action->handle($request)['total'])->toBeGreaterThanOrEqual(2);
    });
})->group('permissions-integration');

test('client creation stays manager-only and duplicate emails fail explicitly without partial updates', function () {
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        global $wpdb;
        $email = $wpdb->get_var($wpdb->prepare("SELECT email FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $clients['other']));
        permissionUser($users['staff']);
        expect(permissionRest('/clients', 'POST', ['name' => 'Denied', 'email' => 'denied@example.invalid'])['status'])->toBe(403);
        expect(fn() => Container::getInstance()->make(\Wappointment\Actions\Clients\CreateClientAction::class)->handle(
            new \Wappointment\Http\Requests\ClientCreateRequestData(name: 'Denied', email: 'denied@example.invalid')
        ))->toThrow(RuntimeException::class, 'You are not allowed');
        $before = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $clients['staff']), ARRAY_A);
        expect(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Partial', 'email' => $email])['status'])->toBe(409)
            ->and($wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_clients WHERE id = %d", $clients['staff']), ARRAY_A))->toBe($before);
        permissionUser($users['manager']);
        expect(permissionRest('/clients', 'POST', ['name' => 'Duplicate', 'email' => $email])['status'])->toBe(409);
        $created = permissionRest('/clients', 'POST', ['name' => 'Manager client', 'email' => bin2hex(random_bytes(6)) . '@example.invalid']);
        try {
            expect($created['status'])->toBe(201);
        } finally {
            if ($created['status'] === 201) {
                $wpdb->delete($wpdb->prefix . 'wappo_clients', ['id' => $created['body']['data']['id']]);
            }
        }
    });
})->group('permissions-integration');

test('client grants deny anonymous subscribers and users without a linked appointment', function () {
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        foreach ([0, $users['subscriber']] as $id) {
            permissionUser($id);
            expect(permissionRest('/clients')['status'])->toBeIn([401, 403])
                ->and(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Denied'])['status'])->toBeIn([401, 403])
                ->and(permissionRest('/clients/' . $clients['staff'] . '/delete', 'POST')['status'])->toBeIn([401, 403]);
        }
        $user = get_userdata($users['subscriber']);
        foreach (['wappo_clients_man', 'wappo_clients_edit', 'wappo_clients_del'] as $cap) {
            $user->add_cap($cap, true);
        }
        permissionUser($users['subscriber']);
        expect(permissionRest('/clients')['body']['data']['total'])->toBe(0)
            ->and(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Denied'])['status'])->toBe(404)
            ->and(permissionRest('/clients/' . $clients['staff'] . '/delete', 'POST')['status'])->toBe(404);
    });
})->group('permissions-integration');

test('client query failures return an explicit server error instead of an empty successful list', function () {
    withPermissionFixture(function ($users) {
        global $wpdb;
        permissionUser($users['staff']);
        $suppress = $wpdb->suppress_errors(true);
        $filter = static function (string $sql): string {
            if (str_starts_with($sql, 'SELECT c.* FROM ') && str_contains($sql, 'clients c WHERE')) {
                return str_replace('SELECT c.*', 'SELECT c.wappointment_missing_test_column', $sql);
            }
            return $sql;
        };
        add_filter('query', $filter);
        try {
            $result = permissionRest('/clients');
            expect($result['status'])->toBe(500)->and($result['body'])->not->toHaveKey('success');
        } finally {
            remove_filter('query', $filter);
            $wpdb->suppress_errors($suppress);
        }
        expect(permissionRest('/clients')['status'])->toBe(200);
    });
})->group('permissions-integration');

test('empty client option maps remain JSON objects and omitted fields retain private data', function () {
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'wappo_clients', ['options' => '{"private":"retained"}'], ['id' => $clients['staff']]);
        permissionUser($users['staff']);
        $result = permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Renamed']);
        expect($result['status'])->toBe(200)->and($result['body']['data']['options'])->toBe('{"private":"retained"}');
        $result = permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['options' => (object) []]);
        expect($result['status'])->toBe(200)->and($result['body']['data']['options'])->toBe('{}');
        permissionUser($users['manager']);
        $created = permissionRest('/clients', 'POST', ['name' => 'Empty map', 'email' => bin2hex(random_bytes(6)) . '@example.invalid']);
        try {
            expect($created['status'])->toBe(201)->and($created['body']['data']['options'])->toBe('{}');
        } finally {
            if ($created['status'] === 201) {
                $wpdb->delete($wpdb->prefix . 'wappo_clients', ['id' => $created['body']['data']['id']]);
            }
        }
    });
})->group('permissions-integration');

function ownWeeklySettings(array $changes = []): array
{
    return array_replace([
        'timezone' => 'Europe/Paris', 'booking_days' => 45,
        'regav' => array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], [[600, 720]]),
    ], $changes);
}

test('own availability projects only the actors calendars without exposing private calendar options', function () {
    withPermissionFixture(function ($users, $calendars) {
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'wappo_calendars', [
            'options' => '{"timezone":"UTC","avb":30,"account_secret":"private","regav":{"monday":[[540,600]],"precise":true}}',
            'status' => 0,
        ], ['id' => $calendars['staff']]);
        permissionUser($users['staff']);
        $result = permissionRest('/me/calendars');
        expect($result['status'])->toBe(200);
        $rows = $result['body']['data']['calendars'];
        expect($rows)->toHaveCount(1)->and($rows[0]['id'])->toBe($calendars['staff'])
            ->and(array_keys($rows[0]))->toBe(['id', 'name', 'settings', 'problem'])
            ->and($rows[0]['settings']['booking_days'])->toBe(30)
            ->and($rows[0]['settings']['regav']['monday'])->toBe([[540, 600]])
            ->and($rows[0]['settings']['regav']['tuesday'])->toBe([])
            ->and(wp_json_encode($result['body']))->not->toContain('account_secret', 'private');
        permissionUser($users['manager']);
        expect(permissionRest('/me/calendars')['body']['data']['calendars'])->toBe([])
            ->and(permissionRest('/me/calendars/' . $calendars['staff'] . '/weekly-settings', 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(404);
    });
})->group('permissions-integration');

test('self settings permissions are independent and require both parent and weekly grants to save', function () {
    withPermissionFixture(function ($users, $calendars) {
        $user = get_userdata($users['staff']);
        foreach (['wappo_calendar_man', 'wappo_clients_man', 'wappo_self_weekly'] as $cap) {
            $user->add_cap($cap, false);
        }
        permissionUser($users['staff']);
        $path = '/me/calendars/' . $calendars['staff'] . '/weekly-settings';
        $statusPath = '/me/calendars/' . $calendars['staff'] . '/statuses';
        expect(permissionMenuPages())->toBe(['wappointment-my-availability'])
            ->and(permissionRest('/me/calendars')['status'])->toBe(200)
            ->and(permissionRest($path, 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(403)
            ->and(permissionRest($statusPath)['status'])->toBe(403);
        $user->add_cap('wappo_self_weekly', true);
        permissionUser($users['staff']);
        $otherStatusPath = '/me/calendars/' . $calendars['other'] . '/statuses';
        $status = permissionRest($statusPath, 'POST', [
            'type' => 1, 'start' => time() + 3600, 'end' => time() + 7200,
        ]);
        expect(permissionRest($path, 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(200)
            ->and($status['status'])->toBe(201)
            ->and(permissionRest($statusPath)['status'])->toBe(200)
            ->and(permissionRest($otherStatusPath)['status'])->toBe(404)
            ->and(permissionRest($statusPath, 'POST', [
                'type' => 1, 'start' => time() + 7200, 'end' => time() + 3600,
            ])['status'])->toBe(400)
            ->and(permissionRest($statusPath . '/' . $status['body']['data']['status']['id'] . '/delete', 'POST')['status'])->toBe(200);
        $user->add_cap('wappo_self_man', false);
        permissionUser($users['staff']);
        expect(permissionMenuPages())->toBe([])
            ->and(permissionRest('/me/calendars')['status'])->toBe(403)
            ->and(permissionRest($path, 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(403)
            ->and(permissionRest($statusPath)['status'])->toBe(403);
        expect(fn() => Container::getInstance()->make(\Wappointment\Actions\Settings\SaveOwnCalendarSettingsAction::class)->handle(
            \Wappointment\Http\Requests\SaveOwnCalendarSettingsRequestData::from(['id' => $calendars['staff'], 'settings' => ownWeeklySettings()])
        ))->toThrow(RuntimeException::class, 'You are not allowed');
    });
})->group('permissions-integration');

test('own weekly save preserves other options global settings relationships and existing appointments', function () {
    withPermissionFixture(function ($users, $calendars) {
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'wappo_calendars', [
            'options' => '{"private":{"empty":{},"list":[]},"custom_fields":{"phone":"kept"},"timezone":"UTC"}',
            'availability' => 'legacy-cache', 'account_key' => 'not-exposed',
        ], ['id' => $calendars['staff']]);
        $before = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_appointments ORDER BY id", ARRAY_A);
        $global = get_option('wappointment_settings');
        permissionUser($users['staff']);
        $path = '/me/calendars/' . $calendars['staff'] . '/weekly-settings';
        foreach ([1, 2] as $attempt) {
            $result = permissionRest($path, 'POST', ['settings' => ownWeeklySettings()]);
            expect($result['status'])->toBe(200)->and($result['body']['data']['calendar']['settings'])->toBe(ownWeeklySettings());
        }
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_calendars WHERE id = %d", $calendars['staff']), ARRAY_A);
        $options = json_decode($row['options']);
        expect($options->timezone)->toBe('Europe/Paris')->and($options->avb)->toBe(45)
            ->and($options->regav->precise)->toBeTrue()
            ->and(json_encode($options->private))->toBe('{"empty":{},"list":[]}')
            ->and($options->custom_fields->phone)->toBe('kept')
            ->and($row['availability'])->toBe('legacy-cache')->and($row['account_key'])->toBe('not-exposed')
            ->and(get_option('wappointment_settings'))->toBe($global)
            ->and($wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_appointments ORDER BY id", ARRAY_A))->toBe($before);
    });
})->group('permissions-integration');

test('own weekly editor converts legacy hours read only and saves explicit minute precision', function () {
    withPermissionFixture(function ($users, $calendars) {
        global $wpdb;
        $id = $calendars['staff'];
        $stored = '{"private":{"empty":{},"list":[]},"timezone":"UTC","avb":30,"regav":{"monday":[[9,12]]}}';
        $wpdb->update($wpdb->prefix . 'wappo_calendars', ['options' => $stored], ['id' => $id]);
        permissionUser($users['staff']);
        $calendar = permissionRest('/me/calendars')['body']['data']['calendars'][0];
        expect($calendar['problem'])->toBeNull()
            ->and($calendar['settings']['regav']['monday'])->toBe([[540, 720]])
            ->and($wpdb->get_var($wpdb->prepare("SELECT options FROM {$wpdb->prefix}wappo_calendars WHERE id = %d", $id)))->toBe($stored);
        $result = permissionRest('/me/calendars/' . $id . '/weekly-settings', 'POST', ['settings' => $calendar['settings']]);
        expect($result['status'])->toBe(200)
            ->and($result['body']['data']['calendar']['settings'])->toBe($calendar['settings']);
        $options = json_decode($wpdb->get_var($wpdb->prepare("SELECT options FROM {$wpdb->prefix}wappo_calendars WHERE id = %d", $id)));
        expect($options->regav->precise)->toBeTrue()
            ->and($options->regav->monday)->toBe([[540, 720]])
            ->and(json_encode($options->private))->toBe('{"empty":{},"list":[]}');
    });
})->group('permissions-integration');

test('own settings refuse unowned missing deleted and reassigned calendars with no writes', function () {
    withPermissionFixture(function ($users, $calendars) {
        global $wpdb;
        permissionUser($users['staff']);
        $before = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_calendars ORDER BY id", ARRAY_A);
        foreach ([$calendars['other'], 2147483647] as $id) {
            expect(permissionRest('/me/calendars/' . $id . '/weekly-settings', 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(404);
        }
        expect($wpdb->get_results("SELECT * FROM {$wpdb->prefix}wappo_calendars ORDER BY id", ARRAY_A))->toBe($before);
        $action = Container::getInstance()->make(\Wappointment\Actions\Settings\GetOwnCalendarSettingsAction::class);
        $wpdb->update($wpdb->prefix . 'wappo_calendars', ['deleted_at' => '2025-01-01 00:00:00'], ['id' => $calendars['staff']]);
        expect($action->handle()['calendars'])->toBe([])
            ->and(permissionRest('/me/calendars/' . $calendars['staff'] . '/weekly-settings', 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(404);
        $wpdb->update($wpdb->prefix . 'wappo_calendars', ['deleted_at' => null, 'wp_uid' => $users['other']], ['id' => $calendars['staff']]);
        expect($action->handle()['calendars'])->toBe([]);
        permissionUser($users['other']);
        expect($action->handle()['calendars'])->toHaveCount(2);
    });
})->group('permissions-integration');

test('own availability rejects unknown parameters forged actors malformed ranges and invalid timezones', function () {
    withPermissionFixture(function ($users, $calendars) {
        global $wpdb;
        permissionUser($users['staff']);
        $path = '/me/calendars/' . $calendars['staff'] . '/weekly-settings';
        $original = $wpdb->get_var($wpdb->prepare("SELECT options FROM {$wpdb->prefix}wappo_calendars WHERE id = %d", $calendars['staff']));
        $invalid = [
            ['settings' => ownWeeklySettings(), 'wp_uid' => $users['other']],
            ['settings' => ownWeeklySettings(), 'id' => $calendars['other']],
            ['settings' => ownWeeklySettings() + ['name' => 'Forbidden rename']],
            ['settings' => ownWeeklySettings(['timezone' => 'Mars/Olympus'])],
            ['settings' => ownWeeklySettings(['booking_days' => 0])],
            ['settings' => ownWeeklySettings(['booking_days' => 366])],
            ['settings' => ownWeeklySettings(['regav' => ['monday' => [[600, 500]]]])],
            ['settings' => ownWeeklySettings(['regav' => array_replace(ownWeeklySettings()['regav'], ['monday' => [[600, 500]]])])],
            ['settings' => ownWeeklySettings(['regav' => array_replace(ownWeeklySettings()['regav'], ['monday' => [[600, 700, ['condition']]]])])],
        ];
        foreach ($invalid as $payload) {
            expect(permissionRest($path, 'POST', $payload)['status'])->toBe(400);
        }
        $request = new WP_REST_Request('GET', '/wappointment/v1/me/calendars');
        $request->set_query_params(['wp_uid' => $users['other']]);
        expect(rest_do_request($request)->get_status())->toBe(400)
            ->and($wpdb->get_var($wpdb->prepare("SELECT options FROM {$wpdb->prefix}wappo_calendars WHERE id = %d", $calendars['staff'])))->toBe($original);
    });
})->group('permissions-integration');

test('conditional or unsupported stored schedules are explicitly read-only and cannot be overwritten', function () {
    withPermissionFixture(function ($users, $calendars) {
        global $wpdb;
        permissionUser($users['staff']);
        foreach ([
            '{"timezone":"UTC","regav":{"monday":[[600,700,{"when":"conditional"}]]}}',
            '{"timezone":"Mars/Olympus"}', '{"avb":999}', '{"regav":null}',
            '{"regav":{"monday":[[700,600]]}}', '{"regav":{"unexpected":[]}}',
            '{"regav":{"monday":[[540,720]]}}',
        ] as $options) {
            $wpdb->update($wpdb->prefix . 'wappo_calendars', ['options' => $options], ['id' => $calendars['staff']]);
            $result = permissionRest('/me/calendars');
            expect($result['status'])->toBe(200)
                ->and($result['body']['data']['calendars'][0]['settings'])->toBeNull()
                ->and($result['body']['data']['calendars'][0]['problem'])->toContain('must be migrated')
                ->and(permissionRest('/me/calendars/' . $calendars['staff'] . '/weekly-settings', 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(409)
                ->and($wpdb->get_var($wpdb->prepare("SELECT options FROM {$wpdb->prefix}wappo_calendars WHERE id = %d", $calendars['staff'])))->toBe($options);
        }
        $wpdb->update($wpdb->prefix . 'wappo_calendars', ['options' => '{broken'], ['id' => $calendars['staff']]);
        expect(permissionRest('/me/calendars')['status'])->toBe(500);
    });
})->group('permissions-integration');

test('anonymous subscribers and unassigned self-settings users cannot acquire someone elses calendars', function () {
    withPermissionFixture(function ($users, $calendars) {
        foreach ([0, $users['subscriber']] as $id) {
            permissionUser($id);
            expect(permissionRest('/me/calendars')['status'])->toBeIn([401, 403])
                ->and(permissionRest('/me/calendars/' . $calendars['staff'] . '/weekly-settings', 'POST', ['settings' => ownWeeklySettings()])['status'])->toBeIn([401, 403]);
        }
        foreach (['wappo_self_man', 'wappo_self_weekly'] as $cap) {
            get_userdata($users['subscriber'])->add_cap($cap, true);
        }
        permissionUser($users['subscriber']);
        expect(permissionRest('/me/calendars')['body']['data']['calendars'])->toBe([])
            ->and(permissionRest('/me/calendars/' . $calendars['staff'] . '/weekly-settings', 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(404);
    });
})->group('permissions-integration');

test('saved own weekly settings change live slot output including organizer timezone', function () {
    withPermissionFixture(function ($users, $calendars) {
        global $wpdb;
        permissionUser($users['staff']);
        $path = '/me/calendars/' . $calendars['staff'] . '/weekly-settings';
        expect(permissionRest($path, 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(200);
        $calendar = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wappo_calendars WHERE id = %d", $calendars['staff']), ARRAY_A);
        $calendar['options'] = json_decode($calendar['options'], true);
        $clock = strtotime('2030-01-01 00:00:00 UTC');
        $engine = new \Wappointment\Booking\Availability();
        $slots = $engine->slots('2030-01-07', $calendar, 30, 0, ['hours_before_booking_allowed' => 0], [], [], $clock);
        expect($slots)->toHaveCount(4)->and($slots[0]['start'])->toBe(strtotime('2030-01-07 09:00:00 UTC'));
        expect(permissionRest($path, 'POST', ['settings' => ownWeeklySettings([
            'regav' => array_fill_keys(array_keys(ownWeeklySettings()['regav']), []),
        ])])['status'])->toBe(200);
        $calendar['options'] = json_decode($wpdb->get_var($wpdb->prepare("SELECT options FROM {$wpdb->prefix}wappo_calendars WHERE id = %d", $calendars['staff'])), true);
        expect($engine->slots('2030-01-07', $calendar, 30, 0, ['hours_before_booking_allowed' => 0], [], [], $clock))->toBe([]);
    });
})->group('permissions-integration');

test('own settings database write failures return a logged server error and roll back all changes', function () {
    withPermissionFixture(function ($users, $calendars) {
        global $wpdb;
        permissionUser($users['staff']);
        $table = $wpdb->prefix . 'wappo_calendars';
        $before = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $calendars['staff']), ARRAY_A);
        $suppress = $wpdb->suppress_errors(true);
        $filter = static function (string $sql) use ($table): string {
            if (str_starts_with($sql, "UPDATE `{$table}` SET")) {
                return str_replace('`options`', '`wappointment_missing_test_column`', $sql);
            }
            return $sql;
        };
        add_filter('query', $filter);
        try {
            expect(permissionRest('/me/calendars/' . $calendars['staff'] . '/weekly-settings', 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(500);
        } finally {
            remove_filter('query', $filter);
            $wpdb->suppress_errors($suppress);
        }
        expect($wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $calendars['staff']), ARRAY_A))->toBe($before)
            ->and(permissionRest('/me/calendars/' . $calendars['staff'] . '/weekly-settings', 'POST', ['settings' => ownWeeklySettings()])['status'])->toBe(200);
    });
})->group('permissions-integration');

test('multisite ownership and manager capabilities follow the active site', function () {
    if (!is_multisite()) {
        $this->markTestSkipped('Run on the disposable network fixture.');
    }
    withPermissionFixture(function ($users, $calendars, $appointments, $clients) {
        global $wpdb;
        $policy = new AccessPolicy();
        $action = Container::getInstance()->make(ListAppointmentsAction::class);
        $clientAction = Container::getInstance()->make(\Wappointment\Actions\Clients\ListClientsAction::class);
        $clientRequest = new \Wappointment\Http\Requests\ListClientsRequestData();
        $ownSettings = Container::getInstance()->make(\Wappointment\Actions\Settings\GetOwnCalendarSettingsAction::class);
        $saveSettings = Container::getInstance()->make(\Wappointment\Actions\Settings\SaveOwnCalendarSettingsAction::class);
        $saveRequest = \Wappointment\Http\Requests\SaveOwnCalendarSettingsRequestData::from([
            'id' => $calendars['staff'], 'settings' => ownWeeklySettings(),
        ]);
        permissionUser($users['staff']);
        expect($action->handle()['appointments'])->toHaveCount(1)
            ->and($clientAction->handle($clientRequest)['total'])->toBe(1)
            ->and($ownSettings->handle()['calendars'])->toHaveCount(1);
        $blog = wpmu_create_blog('127.0.0.1', '/permission-child-' . bin2hex(random_bytes(3)) . '/', 'Permissions child', 1);
        expect(is_wp_error($blog))->toBeFalse();
        switch_to_blog($blog);
        try {
            expect(wp_upload_dir()['error'])->toBeFalse();
            (new RegisterRolesAction())->handle();
            BookingSchema::install();
            permissionUser($users['manager']);
            expect($policy->can('manage'))->toBeFalse()->and($policy->can('clients.view'))->toBeFalse();
            permissionUser($users['staff']);
            expect($policy->can('appointments.view'))->toBeFalse()->and($policy->can('clients.view'))->toBeFalse()
                ->and($policy->can('self.view'))->toBeFalse();
            add_user_to_blog($blog, $users['staff'], 'wappointment_staff');
            permissionUser($users['staff']);
            expect($policy->can('appointments.view'))->toBeTrue()->and($action->handle()['appointments'])->toBe([])
                ->and($clientAction->handle($clientRequest)['total'])->toBe(0)
                ->and($ownSettings->handle()['calendars'])->toBe([]);
            expect(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => 1])['status'])->toBe(404);
            expect($wpdb->insert($wpdb->prefix . 'wappo_calendars', [
                'id' => $calendars['staff'], 'wp_uid' => $users['other'], 'name' => 'Child calendar',
            ]))->not->toBeFalse();
            expect($wpdb->insert($wpdb->prefix . 'wappo_clients', [
                'id' => $clients['staff'], 'name' => 'Child client', 'email' => 'child@example.invalid',
            ]))->not->toBeFalse();
            expect($wpdb->insert($wpdb->prefix . 'wappo_appointments', [
                'id' => $appointments['staff'], 'staff_id' => $calendars['staff'],
                'client_id' => $clients['staff'],
                'start_at' => '2030-01-02 09:00:00', 'end_at' => '2030-01-02 09:30:00',
                'status' => 0, 'options' => '{}',
            ]))->not->toBeFalse();
            expect($action->handle()['appointments'])->toBe([])->and($clientAction->handle($clientRequest)['total'])->toBe(0)
                ->and(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Denied'])['status'])->toBe(404)
                ->and(permissionRest('/clients/' . $clients['staff'] . '/delete', 'POST')['status'])->toBe(404)
                ->and(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => 1])['status'])->toBe(404);
            expect(fn() => $saveSettings->handle($saveRequest))->toThrow(RuntimeException::class, 'Calendar not found.');
            $wpdb->update($wpdb->prefix . 'wappo_calendars', ['wp_uid' => $users['staff']], ['id' => $calendars['staff']]);
            expect($saveSettings->handle($saveRequest)['calendar']['settings']['timezone'])->toBe('Europe/Paris')
                ->and($ownSettings->handle()['calendars'][0]['settings']['booking_days'])->toBe(45);
            expect($action->handle()['appointments'])->toHaveCount(1)
                ->and($clientAction->handle($clientRequest)['total'])->toBe(1)
                ->and(permissionRest('/clients/' . $clients['staff'] . '/put', 'POST', ['name' => 'Edited child client'])['status'])->toBe(200)
                ->and(permissionRest('/clients/' . $clients['staff'] . '/delete', 'POST')['status'])->toBe(200)
                ->and($clientAction->handle($clientRequest)['total'])->toBe(0)
                ->and(permissionRest('/appointments', 'POST', ['id' => $appointments['staff'], 'status' => 1])['status'])->toBe(200)
                ->and($wpdb->get_var($wpdb->prepare("SELECT status FROM {$wpdb->prefix}wappo_appointments WHERE id = %d", $appointments['staff'])))->toBe('1');
        } finally {
            foreach (array_reverse(array_keys(\Wappointment\Database\MasterSchema::tables())) as $suffix) {
                $wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %i', $wpdb->prefix . 'wappo_' . $suffix));
            }
            restore_current_blog();
            require_once ABSPATH . 'wp-admin/includes/ms.php';
            wpmu_delete_blog($blog, true);
        }
        permissionUser($users['staff']);
        $parent = $action->handle()['appointments'];
        expect($parent)->toHaveCount(1)->and((int) $parent[0]['status'])->toBe(0);
        $parentClients = $clientAction->handle($clientRequest);
        expect($parentClients['total'])->toBe(1)->and($parentClients['data'][0]['deleted_at'])->toBeNull()
            ->and($parentClients['data'][0]['name'])->toBe('Permission staff');
        expect($ownSettings->handle()['calendars'][0]['settings']['timezone'])->toBe('UTC')
            ->and($ownSettings->handle()['calendars'][0]['settings']['booking_days'])->toBe(60);
    });
})->group('permissions-integration');
