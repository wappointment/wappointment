<?php
declare(strict_types=1);

$root = getenv('WAPPOINTMENT_LEGACY_ROOT');
if (!$root || !is_file($root . '/vendor/autoload.php')) {
    throw new RuntimeException('Set WAPPOINTMENT_LEGACY_ROOT to an isolated audited master checkout with its locked dependencies restored.');
}
if (hash_file('sha256', $root . '/app/Services/Availability.php') !== 'ff8932079bc211b4ca70ab7d8662d9de0659a08267169fc5f16e3160187b461b') {
    throw new RuntimeException('Legacy Availability source does not match audited master.');
}
if (hash_file('sha256', $root . '/app/Services/Settings.php') !== '1e5e284b5882700aa756c473bf99bb7c5aef5015103f40e03f78b4c2b642ccef') {
    throw new RuntimeException('Legacy Settings source does not match audited master.');
}
require $root . '/vendor/autoload.php';
define('WAPPOINTMENT_DB_FORMAT', 'Y-m-d H:i');
date_default_timezone_set('UTC');

$reflection = new ReflectionClass(\Wappointment\Services\Availability::class);
$generate = $reflection->getMethod('generateAvailabilityWithRA');
$daysRegenerating = $reflection->getMethod('daysRegenerating');
$results = [];
foreach (require __DIR__ . '/notice-horizon-cases.php' as $name => $case) {
    \Wappointment\Services\Settings::$settings = array_merge([
        'hours_before_booking_allowed' => 0, 'allow_refreshavb' => false,
        'refreshavb_at' => 23, 'availability_fluid' => false,
    ], $case['settings']);
    \Wappointment\ClassConnect\Carbon::setTestNow(new \Wappointment\ClassConnect\Carbon($case['now']));
    $engine = $reflection->newInstanceWithoutConstructor();
    $schedule = array_fill_keys(
        ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
        [$case['range']]
    ) + ['precise' => $case['precise']];
    foreach (['timezone' => $case['timezone'], 'regav' => $schedule, 'days' => $case['days']] as $property => $value) {
        $reflection->getProperty($property)->setValue($engine, $value);
    }
    $effectiveDays = $daysRegenerating->invoke($engine);
    $windows = $generate->invoke($engine);
    $timezone = new DateTimeZone($case['timezone']);
    $fromDate = (new DateTimeImmutable($case['now']))->setTimezone($timezone)->format('Y-m-d');
    $toDate = (new DateTimeImmutable($case['now']))->setTimezone($timezone)->modify('+' . $effectiveDays . ' days')->format('Y-m-d');
    $byDate = [];
    foreach ($windows as [$from, $to]) {
        $localDate = (new DateTimeImmutable('@' . $from))->setTimezone($timezone)->format('Y-m-d');
        if ($localDate >= $fromDate && $localDate <= $toDate) {
            $byDate[$localDate][] = [$from, $to];
        }
    }
    $results[$name] = [
        'now' => Carbon\Carbon::parse($case['now'])->getTimestamp(),
        'timezone' => $case['timezone'],
        'effective_days' => $effectiveDays,
        'target_date' => $case['date'],
        'duration_window' => $case['range'],
        'precise' => $case['precise'],
        'settings' => $case['settings'],
        'windows' => $byDate[$case['date']] ?? [],
    ];
}
\Wappointment\ClassConnect\Carbon::setTestNow();
echo json_encode([
    'source' => 'fcf2cfe296a240421ed91f28ff3672ece04e4786',
    'source_sha256' => [
        'Availability.php' => hash_file('sha256', $root . '/app/Services/Availability.php'),
        'Settings.php' => hash_file('sha256', $root . '/app/Services/Settings.php'),
    ],
    'cases_sha256' => hash_file('sha256', __DIR__ . '/notice-horizon-cases.php'),
    'scope' => 'Unmodified PHP weekly generation and daysRegenerating with frozen Carbon; synthetic schedules/settings only; no WordPress, database, HTTP or lifecycle execution.',
    'cases' => $results,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . "\n";
