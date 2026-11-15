<?php
declare(strict_types=1);

$cases = [];
$cases['notice-hour-rounding'] = [
    'now' => '2026-11-16 08:02:37 UTC', 'timezone' => 'UTC', 'date' => '2026-11-16',
    'range' => [480, 720], 'precise' => true, 'days' => 3,
    'settings' => ['hours_before_booking_allowed' => 1.25, 'availability_fluid' => false],
];
$cases['notice-no-fluid-buffer'] = array_replace($cases['notice-hour-rounding'], [
    'settings' => ['hours_before_booking_allowed' => 0.1, 'availability_fluid' => false],
]);
$cases['notice-no-fluid-small-buffer'] = array_replace($cases['notice-hour-rounding'], [
    'settings' => ['hours_before_booking_allowed' => 0.1, 'availability_fluid' => false],
]);
$cases['notice-fluid-quarter-hour'] = array_replace($cases['notice-hour-rounding'], [
    'settings' => ['hours_before_booking_allowed' => 1.25, 'availability_fluid' => true],
]);
$cases['notice-fluid-no-hours'] = array_replace($cases['notice-hour-rounding'], [
    'settings' => ['hours_before_booking_allowed' => 0, 'availability_fluid' => true],
]);
$cases['notice-over-midnight'] = [
    'now' => '2026-11-16 23:58:37 UTC', 'timezone' => 'UTC', 'date' => '2026-11-16',
    'range' => [1320, 1440], 'precise' => true, 'days' => 3,
    'settings' => ['hours_before_booking_allowed' => 1.25, 'availability_fluid' => true],
];
$cases['notice-window-ended'] = [
    'now' => '2026-11-16 08:02:37 UTC', 'timezone' => 'UTC', 'date' => '2026-11-16',
    'range' => [480, 540], 'precise' => true, 'days' => 3,
    'settings' => ['hours_before_booking_allowed' => 2, 'availability_fluid' => true],
];
foreach ([
    ['past-date', '2026-11-16 08:02:37 UTC', 'UTC', 2, false, 23, '2026-11-15'],
    ['normal-horizon-before', '2026-11-14 17:00:00 UTC', 'UTC', 2, false, 23, '2026-11-16'],
    ['normal-horizon-at-end', '2026-11-14 17:00:00 UTC', 'UTC', 2, false, 23, '2026-11-16'],
    ['normal-horizon-next-day', '2026-11-14 17:00:00 UTC', 'UTC', 2, false, 23, '2026-11-17'],
    ['daily-refresh-before', '2026-11-14 22:59:59 UTC', 'UTC', 2, true, 23, '2026-11-16'],
    ['daily-refresh-at', '2026-11-14 23:00:00 UTC', 'UTC', 2, true, 23, '2026-11-16'],
    ['daily-refresh-before-midnight', '2026-11-14 23:59:59 UTC', 'UTC', 2, true, 23, '2026-11-16'],
    ['daily-refresh-after-midnight', '2026-11-15 00:00:00 UTC', 'UTC', 2, true, 23, '2026-11-16'],
    ['dst-calendar-day', '2026-03-28 23:30:00 Europe/Paris', 'Europe/Paris', 1, false, 23, '2026-03-29'],
    ['dst-calendar-beyond', '2026-03-28 23:30:00 Europe/Paris', 'Europe/Paris', 1, false, 23, '2026-03-30'],
] as [$name, $now, $timezone, $days, $refresh, $refreshAt, $date]) {
    $cases[$name] = [
        'now' => $now, 'timezone' => $timezone, 'date' => $date,
        'range' => [0, 1440], 'precise' => true, 'days' => $days,
        'settings' => [
            'hours_before_booking_allowed' => 0, 'availability_fluid' => false,
            'allow_refreshavb' => $refresh, 'refreshavb_at' => $refreshAt,
        ],
    ];
}
return $cases;
