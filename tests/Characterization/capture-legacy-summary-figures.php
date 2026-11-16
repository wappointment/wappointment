<?php
declare(strict_types=1);

// Runs the unmodified master `Services\Sections` slot and coverage methods on the cases
// in summary-figure-cases.php. Only the source file is needed (no vendor):
//   git archive fcf2cfe296a240421ed91f28ff3672ece04e4786 app/Services/Sections.php | tar -x -C <dir>
//   WAPPOINTMENT_LEGACY_ROOT=<dir> php tests/Characterization/capture-legacy-summary-figures.php \
//     > tests/Characterization/legacy-summary-figures.json

$root = getenv('WAPPOINTMENT_LEGACY_ROOT');
$file = $root ? $root . '/app/Services/Sections.php' : '';
$expected = '9a9654ad917c0e007021fba4e4a92afb18db4a9d64d35561fedc5c7990f7d6e8';
if ($file === '' || !is_file($file) || hash_file('sha256', $file) !== $expected) {
    throw new RuntimeException('Set WAPPOINTMENT_LEGACY_ROOT to a directory holding the audited master app/Services/Sections.php.');
}
if (!class_exists('WappointmentException')) {
    eval('class WappointmentException extends \Exception {}');
}
require $file;

$cases = require __DIR__ . '/summary-figure-cases.php';
$results = [];
foreach ($cases as $name => $case) {
    $sections = (new ReflectionClass(\Wappointment\Services\Sections::class))->newInstanceWithoutConstructor();
    $sections->availabilities = $case['free'];
    $sections->appointments = array_map(static fn(array $row): object => (object) [
        'start_at' => (object) ['timestamp' => $row[0]],
        'end_at' => (object) ['timestamp' => $row[1]],
    ], $case['booked']);
    $results[$name] = [
        'free_slots' => (int) $sections->getFreeSlots($case['duration']),
        'coverage' => $sections->getCoverage($case['duration']),
    ];
}

echo json_encode([
    'source' => 'fcf2cfe296a240421ed91f28ff3672ece04e4786',
    'source_sha256' => ['Services/Sections.php' => $expected],
    'cases_sha256' => hash_file('sha256', __DIR__ . '/summary-figure-cases.php'),
    'results' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
