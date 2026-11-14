<?php
declare(strict_types=1);

use Wappointment\Actions\Database\InspectDatabaseSchemaAction;
use Wappointment\Database\BookingSchema;
use Wappointment\Database\MasterSchema;
use Wappointment\Repositories\DatabaseSchemaRepository;

if (!getenv('WAPPOINTMENT_WP_ROOT')) {
    test('schema integration requires disposable WordPress')->skip('Set WAPPOINTMENT_WP_ROOT inside a disposable local booking_test container.');
    return;
}

$_SERVER['HTTP_HOST'] ??= getenv('WAPPOINTMENT_TEST_HOST') ?: '127.0.0.1:18089';
$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
require_once rtrim(getenv('WAPPOINTMENT_WP_ROOT'), '/') . '/wp-load.php';
if (DB_NAME !== 'booking_test' || wp_get_environment_type() !== 'local') {
    throw new RuntimeException('Schema integration tests may only run against the disposable local booking_test database.');
}

function inspectSchema(): \Wappointment\Http\Data\SchemaReportData
{
    return (new InspectDatabaseSchemaAction(new DatabaseSchemaRepository()))->handle();
}

function withIsolatedSchema(callable $test, string $fixtureName = 'schema'): void
{
    global $wpdb;
    $originalPrefix = $wpdb->prefix;
    $markers = [];
    $missing = new stdClass();
    foreach ([
        'wappointment_booking_schema', 'wappointment_installation_completed',
        'wappointment_db_version', 'wappointment_settings',
    ] as $option) {
        $markers[$option] = get_option($option, $missing);
        delete_option($option);
    }
    $prefix = $originalPrefix . $fixtureName . '_' . bin2hex(random_bytes(3)) . '_';
    $wpdb->prefix = $prefix;
    try {
        $test();
    } finally {
        foreach (array_reverse([...array_keys(MasterSchema::tables()), 'addon_records']) as $suffix) {
            $wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %i', $prefix . 'wappo_' . $suffix));
        }
        $wpdb->prefix = $originalPrefix;
        foreach ($markers as $option => $value) {
            if ($value === $missing) {
                delete_option($option);
            } else {
                update_option($option, $value, false);
            }
        }
    }
}

function schemaCreateStatements(): array
{
    global $wpdb;
    $statements = [];
    foreach (MasterSchema::tables() as $suffix => $_) {
        $table = $wpdb->prefix . 'wappo_' . $suffix;
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
        if ($exists === $table) {
            $statements[$suffix] = $wpdb->get_row($wpdb->prepare('SHOW CREATE TABLE %i', $table), ARRAY_N)[1];
        }
    }
    return $statements;
}

test('fresh installation creates all master tables with legacy types relations and pivot ids', function () {
    withIsolatedSchema(function () {
        global $wpdb;
        expect(inspectSchema()->state)->toBe('empty');
        BookingSchema::install();
        $report = inspectSchema();
        expect($report->state)->toBe('compatible')->and($report->issues)->toBe([])
            ->and(schemaCreateStatements())->toHaveCount(16);
        foreach (['service_location', 'calendar_service'] as $suffix) {
            $columns = $wpdb->get_results($wpdb->prepare('SHOW FULL COLUMNS FROM %i', $wpdb->prefix . 'wappo_' . $suffix), ARRAY_A);
            expect(array_column($columns, 'Field'))->toContain('id')
                ->and($columns[0]['Extra'])->toContain('auto_increment');
        }
        $ddl = schemaCreateStatements();
        expect($ddl['clients'])->toContain('`options` text', '`created_at` timestamp NULL')
            ->and($ddl['appointments'])->toContain('unique_staff_id_status_start_end')
            ->and($ddl['order_price'])->toContain('wappo_orderp_oid_foreign', '`quantity` smallint')
            ->and($ddl['jobs'])->toContain('`payload` longtext', 'wappo_jobs_queue_reserved_at_index');
    });
})->group('schema-integration');

test('schema inspection uses metadata queries only and bootstrap preserves existing rows and DDL', function () {
    withIsolatedSchema(function () {
        global $wpdb;
        BookingSchema::install();
        $wpdb->insert($wpdb->prefix . 'wappo_clients', [
            'email' => 'schema-private@example.invalid', 'name' => 'Private fixture',
            'options' => '{"extension":{"keep":true}}',
        ]);
        $rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM %i', $wpdb->prefix . 'wappo_clients'), ARRAY_A);
        $before = schemaCreateStatements();
        $queries = [];
        $capture = static function (string $sql) use (&$queries): string {
            $queries[] = $sql;
            return $sql;
        };
        add_filter('query', $capture);
        try {
            expect(inspectSchema()->state)->toBe('compatible');
        } finally {
            remove_filter('query', $capture);
        }
        expect($queries)->not->toBe([]);
        foreach ($queries as $query) {
            expect(preg_match('/^\s*(SHOW|SELECT)\b/i', $query))->toBe(1)
                ->and($query)->not->toContain('SELECT * FROM', 'GET_LOCK', 'UPDATE ', 'INSERT ', 'ALTER ');
        }
        BookingSchema::install();
        expect(schemaCreateStatements())->toBe($before)
            ->and($wpdb->get_results($wpdb->prepare('SELECT * FROM %i', $wpdb->prefix . 'wappo_clients'), ARRAY_A))->toBe($rows);
    });
})->group('schema-integration');

test('long WordPress prefixes preserve relationships without exceeding foreign-key identifier limits', function () {
    withIsolatedSchema(function () {
        BookingSchema::install();
        expect(inspectSchema()->state)->toBe('compatible');
        $before = schemaCreateStatements();
        BookingSchema::install();
        expect(schemaCreateStatements())->toBe($before);
    }, 'schema_long_prefix');
})->group('schema-integration');

test('partial installations are refused without filling missing tables or marking setup complete', function () {
    withIsolatedSchema(function () {
        global $wpdb;
        delete_option('wappointment_booking_schema');
        $wpdb->query($wpdb->prepare('CREATE TABLE %i (id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY)', $wpdb->prefix . 'wappo_clients'));
        $before = schemaCreateStatements();
        expect(fn() => BookingSchema::install())->toThrow(RuntimeException::class, 'No existing tables were changed');
        expect(schemaCreateStatements())->toBe($before)
            ->and(get_option('wappointment_booking_schema'))->toBeFalse()
            ->and(inspectSchema()->state)->toBe('incompatible');
    });
})->group('schema-integration');

test('existing installation markers prevent recreating missing business tables', function (string $option) {
    withIsolatedSchema(function () use ($option) {
        update_option($option, $option === 'wappointment_settings' ? ['preserve' => true] : '3.0.0', false);
        expect(fn() => BookingSchema::install())->toThrow(RuntimeException::class, 'installation records exist');
        expect(inspectSchema()->state)->toBe('empty');
    });
})->with([
    'legacy installation' => ['wappointment_installation_completed'],
    'legacy version' => ['wappointment_db_version'],
    'v3 readiness' => ['wappointment_booking_schema'],
    'stored settings' => ['wappointment_settings'],
])->group('schema-integration');

test('additional extension tables are reported and left untouched', function () {
    withIsolatedSchema(function () {
        global $wpdb;
        BookingSchema::install();
        $name = $wpdb->prefix . 'wappo_addon_records';
        $wpdb->query($wpdb->prepare('CREATE TABLE %i (id int NOT NULL PRIMARY KEY, payload text NULL)', $name));
        $wpdb->insert($name, ['id' => 7, 'payload' => 'keep extension data']);
        BookingSchema::install();
        $report = inspectSchema();
        expect($report->state)->toBe('compatible')->and($report->additionalTables)->toBe(['addon_records'])
            ->and($wpdb->get_var($wpdb->prepare('SELECT payload FROM %i WHERE id = 7', $name)))->toBe('keep extension data');
    });
})->group('schema-integration');

test('a failed readiness-marker write is explicit and can be retried without recreating tables', function () {
    withIsolatedSchema(function () {
        $preventWrite = static fn($new, $old) => $old;
        add_filter('pre_update_option_wappointment_booking_schema', $preventWrite, 10, 2);
        try {
            expect(fn() => BookingSchema::install())->toThrow(RuntimeException::class, 'readiness marker could not be saved');
        } finally {
            remove_filter('pre_update_option_wappointment_booking_schema', $preventWrite, 10);
        }
        $ddl = schemaCreateStatements();
        expect(inspectSchema()->state)->toBe('compatible')
            ->and(get_option('wappointment_booking_schema'))->toBeFalse();
        BookingSchema::install();
        expect(schemaCreateStatements())->toBe($ddl)
            ->and(get_option('wappointment_booking_schema'))->toBe(BookingSchema::VERSION);
    });
})->group('schema-integration');

test('a failed table creation leaves a visible partial installation and releases its lock', function () {
    withIsolatedSchema(function () {
        global $wpdb;
        $table = $wpdb->prefix . 'wappo_reminders';
        $failCreate = static fn(string $sql): string => str_starts_with($sql, 'CREATE TABLE `' . $table . '`')
            ? $sql . ' INVALID_SCHEMA_TEST_SYNTAX' : $sql;
        $previousErrors = $wpdb->suppress_errors(true);
        add_filter('query', $failCreate);
        try {
            expect(fn() => BookingSchema::install())->toThrow(RuntimeException::class, 'Could not create Wappointment table: reminders');
        } finally {
            remove_filter('query', $failCreate);
            $wpdb->suppress_errors($previousErrors);
        }
        expect(get_option('wappointment_booking_schema'))->toBeFalse()
            ->and(inspectSchema()->state)->toBe('incompatible');
        $before = schemaCreateStatements();
        expect(fn() => BookingSchema::install())->toThrow(RuntimeException::class, 'No existing tables were changed');
        expect(schemaCreateStatements())->toBe($before);
        $key = 'wappo_schema_' . substr(hash('sha256', DB_NAME . ':' . $wpdb->prefix), 0, 40);
        expect($wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)', $key)))->toBeNull();
    });
})->group('schema-integration');

test('schema REST diagnostics require an administrator and expose no stored customer data', function () {
    $request = new WP_REST_Request('GET', '/wappointment/v1/system/schema');
    wp_set_current_user(0);
    expect(rest_do_request($request)->get_status())->toBeIn([401, 403]);
    $subscriber = wp_insert_user([
        'user_login' => 'schema-subscriber-' . bin2hex(random_bytes(3)),
        'user_pass' => wp_generate_password(), 'role' => 'subscriber',
    ]);
    expect(is_wp_error($subscriber))->toBeFalse();
    try {
        wp_set_current_user($subscriber);
        expect(rest_do_request($request)->get_status())->toBe(403);
        wp_set_current_user(1);
        $response = rest_do_request($request);
        expect($response->get_status())->toBe(200)
            ->and($response->get_data()['data']['report']['state'])->toBe('compatible')
            ->and(array_keys($response->get_data()['data']['report']))->toBe(['reference', 'state', 'issues', 'additionalTables']);
        $invalid = new WP_REST_Request('GET', '/wappointment/v1/system/schema');
        $invalid->set_query_params(['repair' => 'true']);
        expect(rest_do_request($invalid)->get_status())->toBe(400);
        expect(rest_do_request(new WP_REST_Request('POST', '/wappointment/v1/system/schema'))->get_status())->toBe(404);
    } finally {
        wp_set_current_user(1);
        if (is_multisite()) {
            require_once ABSPATH . 'wp-admin/includes/ms.php';
            wpmu_delete_user($subscriber);
        } else {
            require_once ABSPATH . 'wp-admin/includes/user.php';
            wp_delete_user($subscriber);
        }
    }
})->group('schema-integration');

test('the obsolete baseline marker cannot authorize API operations before schema verification', function () {
    $previous = get_option('wappointment_booking_schema');
    wp_set_current_user(1);
    update_option('wappointment_booking_schema', '3.0.0', false);
    try {
        $blocked = rest_do_request(new WP_REST_Request('GET', '/wappointment/v1/clients'));
        expect($blocked->get_status())->toBe(503)
            ->and($blocked->get_data()['code'])->toBe('wappointment_schema_not_ready')
            ->and($blocked->get_data()['message'])->toContain('Diagnostics');
        expect(rest_do_request(new WP_REST_Request('GET', '/wappointment/v1/system/schema'))->get_status())->toBe(200);
        $booking = new WP_REST_Request('POST', '/wappointment/v1/booking');
        $booking->set_header('content-type', 'application/json');
        $booking->set_body('{}');
        wp_set_current_user(0);
        expect(rest_do_request($booking)->get_status())->toBe(503);
    } finally {
        wp_set_current_user(1);
        update_option('wappointment_booking_schema', $previous, false);
    }
})->group('schema-integration');

test('schema services use the active blog rather than retaining the original prefix', function () {
    if (!is_multisite()) {
        $this->markTestSkipped('Run again after converting the disposable fixture to multisite.');
    }
    global $wpdb;
    $originalBlog = get_current_blog_id();
    $originalPrefix = $wpdb->prefix;
    $repository = new DatabaseSchemaRepository();
    $inspector = new InspectDatabaseSchemaAction($repository);
    $parentDDL = schemaCreateStatements();
    $blog = wpmu_create_blog('127.0.0.1', '/schema-child-' . bin2hex(random_bytes(3)) . '/', 'Schema child', 1);
    expect(is_wp_error($blog))->toBeFalse();
    switch_to_blog($blog);
    try {
        expect(wp_upload_dir()['error'])->toBeFalse();
        expect($wpdb->prefix)->not->toBe($originalPrefix)
            ->and($inspector->handle()->state)->toBe('empty');
        BookingSchema::install();
        expect($inspector->handle()->state)->toBe('compatible')
            ->and(get_option('wappointment_booking_schema'))->toBe(BookingSchema::VERSION);
        $queries = [];
        $capture = static function (string $sql) use (&$queries): string { $queries[] = $sql; return $sql; };
        add_filter('query', $capture);
        try {
            $repository->snapshot();
        } finally {
            remove_filter('query', $capture);
        }
        foreach ($queries as $query) {
            expect($query)->not->toContain('`' . $originalPrefix . 'wappo_');
        }
    } finally {
        foreach (array_reverse(array_keys(MasterSchema::tables())) as $suffix) {
            $wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %i', $wpdb->prefix . 'wappo_' . $suffix));
        }
        restore_current_blog();
        require_once ABSPATH . 'wp-admin/includes/ms.php';
        wpmu_delete_blog($blog, true);
    }
    expect(get_current_blog_id())->toBe($originalBlog)
        ->and($inspector->handle()->state)->toBe('compatible')
        ->and(schemaCreateStatements())->toBe($parentDDL);
})->group('schema-integration');
