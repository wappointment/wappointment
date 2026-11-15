<?php
declare(strict_types=1);

$root = getenv('WAPPOINTMENT_LEGACY_ROOT');
if (!$root || !is_file($root . '/vendor/autoload.php')) {
    throw new RuntimeException('Set WAPPOINTMENT_LEGACY_ROOT to an isolated audited master checkout with its locked dependencies restored.');
}
$sourceHashes = [
    'Services/Status.php' => 'ed97322a82286e357a2fc5e25573680dc78eba3ec8bde18e293853437344cf63',
    'Models/Status.php' => '202bc614979d3cb4507cd627ae8d16ab7f9a735faa4d361e4d9f3bada58c92d8',
];
foreach ($sourceHashes as $file => $expected) {
    if (hash_file('sha256', $root . '/app/' . $file) !== $expected) {
        throw new RuntimeException('Legacy ' . $file . ' does not match audited master fcf2cfe296a240421ed91f28ff3672ece04e4786.');
    }
}
require $root . '/vendor/autoload.php';
date_default_timezone_set('UTC');

final class RecurringStatusOracle
{
    private string $rawStart;
    private string $rawEnd;

    public function __construct(
        public int $id,
        public int $recur,
        \Wappointment\ClassConnect\Carbon $start,
        \Wappointment\ClassConnect\Carbon $end,
        public array $options
    ) {
        $this->rawStart = $start->copy()->tz('UTC')->format('Y-m-d H:i:s');
        $this->rawEnd = $end->copy()->tz('UTC')->format('Y-m-d H:i:s');
    }

    public function __get(string $key): mixed
    {
        return match ($key) {
            'start_at' => new \Wappointment\ClassConnect\Carbon($this->rawStart, 'UTC'),
            'end_at' => new \Wappointment\ClassConnect\Carbon($this->rawEnd, 'UTC'),
            default => null,
        };
    }

    public function __set(string $key, mixed $value): void
    {
        if ($key === 'start_at') {
            $this->rawStart = $value->copy()->tz('UTC')->format('Y-m-d H:i:s');
        } elseif ($key === 'end_at') {
            $this->rawEnd = $value->copy()->tz('UTC')->format('Y-m-d H:i:s');
        }
    }

    public function replicate(): self
    {
        return new self($this->id, $this->recur, $this->start_at, $this->end_at, $this->options);
    }
}

$cases = require __DIR__ . '/recurring-status-cases.php';
$results = [];
foreach ($cases as $name => $case) {
    \Wappointment\ClassConnect\Carbon::setTestNow(new \Wappointment\ClassConnect\Carbon($case['now']));
    $status = new RecurringStatusOracle(
        1,
        $case['recur'],
        new \Wappointment\ClassConnect\Carbon($case['start_utc'], 'UTC'),
        new \Wappointment\ClassConnect\Carbon($case['end_utc'], 'UTC'),
        $case['options']
    );
    $events = \Wappointment\Services\Status::expand([$status], $case['query_until']);
    $results[$name] = array_map(
        static fn(object $event): array => [$event->start_at->timestamp, $event->end_at->timestamp],
        $events
    );
}
\Wappointment\ClassConnect\Carbon::setTestNow();

echo json_encode([
    'source' => 'fcf2cfe296a240421ed91f28ff3672ece04e4786',
    'source_sha256' => $sourceHashes,
    'cases_sha256' => hash_file('sha256', __DIR__ . '/recurring-status-cases.php'),
    'scope' => 'Unmodified master Status::expand with frozen Carbon and a minimal cast-on-read Eloquent model shim; synthetic recurrence options only; no WordPress, database, HTTP or lifecycle execution.',
    'occurrences' => $results,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . "\n";
