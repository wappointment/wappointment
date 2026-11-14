<?php
declare(strict_types=1);

$cases = [];
foreach ([
    'legacy-hours' => [[9, 12], null],
    'explicit-hours' => [[9, 12], false],
    'precise-minutes' => [[540, 720], true],
    'midnight-minutes' => [[0, 20], true],
    'fractional-hours' => [[9.5, 12.25], false],
] as $name => [$range, $precise]) {
    $cases[$name] = [
        'date' => '2026-11-16', 'now' => '2026-11-15 00:00:00 UTC',
        'timezone' => 'UTC', 'range' => $range, 'precise' => $precise,
    ];
}
foreach (['2026-03-28', '2026-03-29', '2026-10-24', '2026-10-25'] as $date) {
    $cases['paris-' . $date] = [
        'date' => $date, 'now' => $date . ' -1 day 00:00:00 UTC',
        'timezone' => 'Europe/Paris', 'range' => [540, 720], 'precise' => true,
    ];
}
foreach (['2026-03-29', '2026-10-25'] as $date) {
    $cases['paris-transition-' . $date] = [
        'date' => $date, 'now' => $date . ' -1 day 00:00:00 UTC',
        'timezone' => 'Europe/Paris', 'range' => [90, 210], 'precise' => true,
    ];
}
$cases['midnight-endpoint'] = [
    'date' => '2026-11-16', 'now' => '2026-11-15 00:00:00 UTC',
    'timezone' => 'UTC', 'range' => [1380, 1440], 'precise' => true,
];
$cases['busy-splits-window'] = $cases['precise-minutes'] + [
    'busy' => [['2026-11-16 10:10:00 UTC', '2026-11-16 10:40:00 UTC']],
];
$cases['free-extends-window'] = $cases['precise-minutes'] + [
    'extra' => [['2026-11-16 08:00:00 UTC', '2026-11-16 09:30:00 UTC']],
];
$cases['busy-overrides-free'] = $cases['free-extends-window'] + [
    'busy' => [['2026-11-16 08:30:00 UTC', '2026-11-16 10:00:00 UTC']],
];

return $cases;
