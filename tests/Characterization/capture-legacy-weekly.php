<?php
declare(strict_types=1);

// Run in a separate process: legacy and v3 deliberately share PHP namespaces.
$root = getenv('WAPPOINTMENT_LEGACY_ROOT');
if (!$root || !is_file($root . '/vendor/autoload.php')) {
    throw new RuntimeException('Set WAPPOINTMENT_LEGACY_ROOT to an isolated audited master checkout with its locked dependencies restored.');
}
$source = $root . '/app/Services/Availability.php';
if (hash_file('sha256', $source) !== 'ff8932079bc211b4ca70ab7d8662d9de0659a08267169fc5f16e3160187b461b') {
    throw new RuntimeException('Legacy Availability source does not match audited master fcf2cfe296a240421ed91f28ff3672ece04e4786.');
}
if (hash_file('sha256', $root . '/app/Services/Segment.php') !== '3f4338e74da7b4f14914f893bb124733cb5f29deec17ad5fb293d70017a0d17b') {
    throw new RuntimeException('Legacy Segment source does not match audited master.');
}
require $root . '/vendor/autoload.php';
define('WAPPOINTMENT_DB_FORMAT', 'Y-m-d H:i');
date_default_timezone_set('UTC');

$reflection = new ReflectionClass(\Wappointment\Services\Availability::class);
$generate = $reflection->getMethod('generateAvailabilityWithRA');
$segment = new \Wappointment\Services\Segment();
$results = [];
foreach (require __DIR__ . '/weekly-cases.php' as $name => $case) {
    $schedule = array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], [$case['range']]);
    if ($case['precise'] !== null) {
        $schedule['precise'] = $case['precise'];
    }
    \Wappointment\Services\Settings::$settings = [
        'hours_before_booking_allowed' => 0, 'allow_refreshavb' => false, 'availability_fluid' => false,
    ];
    \Wappointment\ClassConnect\Carbon::setTestNow(new \Wappointment\ClassConnect\Carbon($case['now']));
    // Bypass only the database-backed constructor; execute the unmodified schedule method.
    $engine = $reflection->newInstanceWithoutConstructor();
    foreach (['timezone' => $case['timezone'], 'regav' => $schedule, 'days' => 3] as $property => $value) {
        $reflection->getProperty($property)->setValue($engine, $value);
    }
    $timezone = new DateTimeZone($case['timezone']);
    $day = new DateTimeImmutable($case['date'], $timezone);
    $from = $day->getTimestamp();
    $to = $day->modify('+1 day')->getTimestamp();
    $windows = array_values(array_filter(
        $generate->invoke($engine),
        static fn(array $window): bool => $window[0] < $to && $window[1] > $from
    ));
    $intervals = static fn(array $rows): array => array_map(
        static fn(array $row): array => [strtotime($row[0]), strtotime($row[1])], $rows
    );
    $windows = $segment->flatten(array_merge($windows, $intervals($case['extra'] ?? [])));
    $windows = $segment->substract($windows, $intervals($case['busy'] ?? []));
    $results[$name] = $windows;
}
\Wappointment\ClassConnect\Carbon::setTestNow();
echo json_encode([
    'source' => 'fcf2cfe296a240421ed91f28ff3672ece04e4786',
    'cases_sha256' => hash_file('sha256', __DIR__ . '/weekly-cases.php'),
    'scope' => 'Unmodified PHP weekly generation and Segment union/subtraction; synthetic inputs and frozen Carbon clock; no WordPress, database, HTTP or lifecycle execution.',
    'windows' => $results,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . "\n";
