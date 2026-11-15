<?php

if (DB_NAME !== 'legacy_oracle' || wp_get_environment_type() !== 'local') {
    throw new RuntimeException('Legacy lifecycle capture may only run on the disposable local legacy_oracle database.');
}

$root = WAPPOINTMENT_PATH;
$sourceHashes = [
    'Services/AppointmentNew.php' => 'c364d868a55ef5c40a0c6c551ce9bafea880ea406db2447db076488124fc0310',
    'Services/Client.php' => '7bc5c45d50ba0b2bcf9d50c367b8c0fac702b952668eeb57f323da218944d1c9',
    'Models/Appointment.php' => 'a44f9bfd5c8ee09fff1b78849a731e8c385e02c80e0456cffaf43138da7e5616',
    'Models/CanBook.php' => '1f18583da0b08dd190478dfa4d32fdabf6948302e94c3d274f9a0f7f1caff1fe',
    'Models/Appointment/ManipulateCancelReschedule.php' => '59a29027037ae59b11c0f902346448a9e9c6e8b217f0ac684dfc81e1e1e14397',
];
foreach ($sourceHashes as $file => $expected) {
    if (hash_file('sha256', $root . 'app/' . $file) !== $expected) {
        throw new RuntimeException('Audited master source hash mismatch for app/' . $file . '.');
    }
}

$casesPath = '/characterization/lifecycle-cases.php';
$cases = require $casesPath;
global $wpdb;
$table = static fn(string $name): string => $wpdb->prefix . 'wappo_' . $name;
$calendarId = 1;
$calendar = \Wappointment\Models\Calendar::findOrFail($calendarId);
$locations = \Wappointment\Models\Location::whereIn('id', [2])->get();
if ($locations->isEmpty()) {
    throw new RuntimeException('The master fixture needs its seeded physical location with id 2.');
}

$service = \Wappointment\Models\Service::where('name', 'Lifecycle oracle consultation')->first();
if (!$service) {
    $service = \Wappointment\Services\Services::save([
        'name' => 'Lifecycle oracle consultation',
        'options' => ['durations' => [['duration' => $cases['duration']]], 'fields' => ['email', 'name']],
        'locations_id' => [2],
        'sorting' => 0,
    ]);
}
$calendar->services()->sync([$service->id]);
$calendarOptions = $calendar->options;
$calendarOptions['timezone'] = 'UTC';
$calendarOptions['avb'] = 60;
$calendarOptions['regav'] = array_fill_keys(
    ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
    [[0, 1440]]
) + ['precise' => true];
$calendar->options = $calendarOptions;
$calendar->save();
$regenerate = static function () use ($calendarId): void {
    (new \Wappointment\Services\Availability($calendarId))->regenerate();
    \Wappointment\Services\Reset::refreshCache();
};
$regenerate();

$base = (int) (floor(time() / DAY_IN_SECONDS) * DAY_IN_SECONDS + 2 * DAY_IN_SECONDS + 12 * HOUR_IN_SECONDS);
$clientIds = [];
$editKeys = [];
$results = [];

$reset = static function () use ($wpdb, $table): void {
    foreach (['appointments', 'statuses', 'clients', 'jobs', 'failed_jobs', 'logs', 'order_price', 'orders', 'prices'] as $name) {
        $wpdb->query('DELETE FROM ' . $table($name));
    }
    if ((int) $wpdb->get_var('SELECT COUNT(*) FROM ' . $table('appointments')) !== 0) {
        throw new RuntimeException('Could not reset appointments between lifecycle scenarios.');
    }
    update_option('wappo_oracle_mail', [], false);
};
$configure = static function (array $settings) use ($cases, $regenerate): void {
    update_option('wappointment_settings', array_merge($cases['settings'], $settings), false);
    \Wappointment\Services\Settings::$settings = false;
    \Wappointment\Services\Settings::$staffSettings = [];
    $regenerate();
};
$request = static function (string $method, string $path, array $data = []): WP_REST_Response {
    $url = add_query_arg('rest_route', '/wappointment/v1' . $path, site_url('/index.php'));
    $response = wp_remote_request($url, [
        'method' => $method,
        'headers' => ['content-type' => 'application/json'],
        'body' => wp_json_encode($data),
        'timeout' => 30,
    ]);
    if (is_wp_error($response)) {
        return new WP_REST_Response(['code' => $response->get_error_code(), 'message' => $response->get_error_message()], 500);
    }
    $body = json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
    return new WP_REST_Response($body, (int) wp_remote_retrieve_response_code($response));
};
$rows = static function () use ($wpdb, $table, $base): array {
    $appointments = $wpdb->get_results('SELECT * FROM ' . $table('appointments') . ' ORDER BY id', ARRAY_A);
    foreach ($appointments as &$appointment) {
        $appointment = [
            'start_offset' => strtotime($appointment['start_at'] . ' UTC') - $base,
            'end_offset' => strtotime($appointment['end_at'] . ' UTC') - $base,
            'client_email' => $wpdb->get_var($wpdb->prepare(
                'SELECT email FROM ' . $table('clients') . ' WHERE id = %d',
                $appointment['client_id']
            )),
            'status' => (int) $appointment['status'],
            'type' => (int) $appointment['type'],
            'staff_id' => (int) $appointment['staff_id'],
            'location_id' => (int) $appointment['location_id'],
            'options' => json_decode($appointment['options'] ?: '{}', true, 512, JSON_THROW_ON_ERROR),
        ];
    }
    unset($appointment);
    $clients = $wpdb->get_results('SELECT email,name,options,deleted_at FROM ' . $table('clients') . ' ORDER BY id', ARRAY_A);
    foreach ($clients as &$client) {
        $client['options'] = json_decode($client['options'] ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        $client['deleted_at'] = $client['deleted_at'] === null ? null : 'deleted';
    }
    unset($client);
    $jobs = $wpdb->get_results('SELECT queue, appointment_id, payload FROM ' . $table('jobs') . ' ORDER BY id', ARRAY_A);
    $jobSummary = [];
    foreach ($jobs as $job) {
        $payload = json_decode($job['payload'], true);
        $event = $payload['params']['event'] ?? ($payload['job'] ?? 'unknown');
        $jobSummary[$job['queue'] . ':' . $event] = ($jobSummary[$job['queue'] . ':' . $event] ?? 0) + 1;
    }
    ksort($jobSummary);
    return [
        'appointments' => $appointments,
        'clients' => $clients,
        'jobs' => $jobSummary,
        'mail' => get_option('wappo_oracle_mail', []),
    ];
};
$keyForClient = static function (string $alias) use (&$clientIds, &$editKeys, $wpdb, $table): ?string {
    $clientId = $clientIds[$alias] ?? null;
    if (!$clientId) {
        return null;
    }
    $appointment = $wpdb->get_row($wpdb->prepare(
        'SELECT edit_key FROM ' . $table('appointments') . ' WHERE client_id = %d ORDER BY id LIMIT 1',
        $clientId
    ), ARRAY_A);
    if (!$appointment) {
        return $editKeys[$alias] ?? null;
    }
    $editKeys[$alias] = $appointment['edit_key'];
    return $appointment['edit_key'];
};
$normalize = static function (WP_REST_Response $response): array {
    $body = $response->get_data();
    $result = ['status' => $response->get_status()];
    if (is_array($body)) {
        foreach (['code', 'message'] as $key) {
            if (isset($body[$key])) {
                $result[$key] = $body[$key];
            }
        }
        if (isset($result['message']) && str_starts_with($result['message'], 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry')) {
            $result['message'] = 'SQLSTATE[23000]: duplicate appointment slot constraint';
        }
        if (isset($body['data']['errors'])) {
            $result['errors'] = $body['data']['errors'];
        }
    }
    return [
        'result' => $result,
    ];
};

foreach ($cases['scenarios'] as $scenarioName => $scenario) {
    $reset();
    $configure($scenario['settings'] ?? []);
    $clientIds = [];
    $editKeys = [];
    $scenarioResults = [];

    foreach ($scenario['steps'] as $step) {
        $operation = $step[0];
        $clientAlias = $step['client'] ?? '';
        $client = $cases['clients'][$clientAlias] ?? null;
        $timestamp = $base + (int) ($step['slot'] ?? 0) * 60;

        if ($operation === 'settings') {
            $configure($step[1]);
            $stepResult = ['result' => ['status' => 200]];
        } elseif ($operation === 'book') {
            if (!$client) {
                throw new RuntimeException('Unknown lifecycle client alias: ' . $clientAlias);
            }
            $previous = $wpdb->get_var($wpdb->prepare(
                'SELECT id FROM ' . $table('clients') . ' WHERE email = %s',
                $client['email']
            ));
            $response = $request('POST', '/services/booking', [
                'time' => $timestamp,
                'ctz' => 'UTC',
                'location' => 2,
                'service' => $service->id,
                'duration' => $cases['duration'],
                'staff_id' => $calendarId,
                'email' => $client['email'],
                'name' => $step['name'] ?? $client['name'],
            ]);
            $stepResult = $normalize($response);
            $row = $wpdb->get_row($wpdb->prepare(
                'SELECT id,client_id,edit_key FROM ' . $table('appointments') . ' WHERE start_at = %s ORDER BY id DESC LIMIT 1',
                gmdate('Y-m-d H:i:s', $timestamp)
            ), ARRAY_A);
            if ($row && $response->get_status() < 300) {
                $clientIds[$clientAlias] = (int) $row['client_id'];
                $editKeys[$clientAlias] = $row['edit_key'];
            } elseif ($previous) {
                $clientIds[$clientAlias] = (int) $previous;
            }
        } elseif ($operation === 'seed') {
            $clientId = $wpdb->insert($table('clients'), [
                'email' => $client['email'],
                'name' => $client['name'],
                'options' => wp_json_encode(['tz' => 'UTC']),
            ]);
            $clientId = (int) $wpdb->insert_id;
            $clientIds[$clientAlias] = $clientId;
            $wpdb->insert($table('appointments'), [
                'start_at' => gmdate('Y-m-d H:i:s', $timestamp),
                'end_at' => gmdate('Y-m-d H:i:s', $timestamp + $cases['duration'] * 60 + (int) \Wappointment\Services\Settings::get('buffer_time') * 60),
                'type' => 0, 'status' => (int) ($step['status'] ?? 1), 'edit_key' => bin2hex(random_bytes(16)),
                'client_id' => $clientId, 'staff_id' => $calendarId, 'service_id' => $service->id,
                'location_id' => 2, 'options' => wp_json_encode(['buffer_time' => (int) \Wappointment\Services\Settings::get('buffer_time')]),
            ]);
            $stepResult = ['status' => 200, 'body' => ['seeded' => true]];
        } elseif ($operation === 'delete-client') {
            $clientId = $clientIds[$clientAlias] ?? 0;
            $deleted = $clientId ? $wpdb->update($table('clients'), ['deleted_at' => gmdate('Y-m-d H:i:s')], ['id' => $clientId]) : 0;
            $stepResult = ['status' => $deleted ? 200 : 404, 'body' => ['deleted' => (bool) $deleted]];
        } elseif ($operation === 'cancel-unknown') {
            $stepResult = $normalize($request('POST', '/appointment/patch', ['appointmentkey' => 'invalid-lifecycle-key']));
        } elseif (in_array($operation, ['cancel', 'reschedule', 'view'], true)) {
            $key = $keyForClient($step['ref']);
            if ($operation === 'cancel') {
                $response = $request('POST', '/appointment/patch', ['appointmentkey' => $key ?? 'missing-lifecycle-key']);
            } elseif ($operation === 'reschedule') {
                $response = $request('POST', '/booking/patch', ['appointmentkey' => $key ?? 'missing-lifecycle-key', 'time' => $timestamp]);
            } else {
                $response = $request('POST', '/appointment', ['appointmentkey' => $key ?? 'missing-lifecycle-key']);
            }
            $stepResult = $normalize($response);
        } else {
            throw new RuntimeException('Unknown lifecycle operation: ' . $operation);
        }

        $scenarioResults[] = [
            'operation' => $operation,
            'request' => $operation === 'book' ? ['client' => $clientAlias, 'offset_minutes' => (int) $step['slot']] : null,
            'result' => $stepResult,
            'stored' => $rows(),
        ];
    }

    $results[$scenarioName] = [
        'settings' => array_merge($cases['settings'], $scenario['settings'] ?? []),
        'steps' => $scenarioResults,
    ];
}

echo wp_json_encode([
    'source' => 'fcf2cfe296a240421ed91f28ff3672ece04e4786',
    'source_sha256' => $sourceHashes,
    'cases_sha256' => hash_file('sha256', $casesPath),
    'scope' => 'Public WordPress REST lifecycle flows on an isolated legacy_oracle database; cron processing is disabled, queued jobs are recorded, and no mail or external provider is sent.',
    'results' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
