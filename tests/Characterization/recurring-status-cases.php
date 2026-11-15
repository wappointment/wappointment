<?php
declare(strict_types=1);

$timestamp = static fn(string $value): int => (new DateTimeImmutable($value, new DateTimeZone('UTC')))->getTimestamp();

return [
    'daily-interval-with-exdate-and-until' => [
        'now' => '2030-01-01 00:00:00 UTC',
        'recur' => 1,
        'start_utc' => '2030-01-02 09:00:00',
        'end_utc' => '2030-01-02 10:00:00',
        'options' => [
            'interval' => 2,
            'origin_tz' => 'UTC',
            'origin_start' => '2030-01-02 09:00:00',
            'until' => $timestamp('2030-01-06 00:00:00 UTC'),
            'exdate' => [$timestamp('2030-01-04 09:00:00 UTC')],
        ],
        'query_until' => $timestamp('2030-01-08 00:00:00 UTC'),
    ],
    'weekly-tuesday-thursday' => [
        'now' => '2030-01-01 00:00:00 UTC',
        'recur' => 2,
        'start_utc' => '2030-01-01 09:00:00',
        'end_utc' => '2030-01-01 10:00:00',
        'options' => [
            'interval' => 2,
            'byday' => [2, 4],
            'origin_tz' => 'UTC',
            'origin_start' => '2030-01-01 09:00:00',
        ],
        'query_until' => $timestamp('2030-01-25 00:00:00 UTC'),
    ],
    'weekly-wall-time-across-dst' => [
        'now' => '2030-03-01 00:00:00 UTC',
        'recur' => 2,
        'start_utc' => '2030-03-08 14:00:00',
        'end_utc' => '2030-03-08 15:00:00',
        'options' => [
            'interval' => 1,
            'byday' => [5],
            'origin_tz' => 'America/New_York',
            'origin_start' => '2030-03-08 09:00:00',
        ],
        'query_until' => $timestamp('2030-03-23 00:00:00 UTC'),
    ],
    'monthly-day-fifteenth' => [
        'now' => '2030-01-01 00:00:00 UTC',
        'recur' => 3,
        'start_utc' => '2030-01-15 09:00:00',
        'end_utc' => '2030-01-15 10:00:00',
        'options' => [
            'interval' => 1,
            'bymonthday' => 15,
            'origin_tz' => 'UTC',
            'origin_start' => '2030-01-15 09:00:00',
        ],
        'query_until' => $timestamp('2030-05-01 00:00:00 UTC'),
    ],
    'monthly-day-thirty-first' => [
        'now' => '2030-01-01 00:00:00 UTC',
        'recur' => 3,
        'start_utc' => '2030-01-31 09:00:00',
        'end_utc' => '2030-01-31 10:00:00',
        'options' => [
            'interval' => 1,
            'bymonthday' => 31,
            'origin_tz' => 'UTC',
            'origin_start' => '2030-01-31 09:00:00',
        ],
        'query_until' => $timestamp('2030-07-01 00:00:00 UTC'),
    ],
    'monthly-second-monday' => [
        'now' => '2030-01-01 00:00:00 UTC',
        'recur' => 3,
        'start_utc' => '2030-01-14 09:00:00',
        'end_utc' => '2030-01-14 10:00:00',
        'options' => [
            'interval' => 1,
            'byday' => ['each' => 2, 'day' => 1],
            'origin_tz' => 'UTC',
            'origin_start' => '2030-01-14 09:00:00',
        ],
        'query_until' => $timestamp('2030-05-01 00:00:00 UTC'),
    ],
    'yearly-leap-day' => [
        'now' => '2030-01-01 00:00:00 UTC',
        'recur' => 4,
        'start_utc' => '2032-02-29 09:00:00',
        'end_utc' => '2032-02-29 10:00:00',
        'options' => [
            'interval' => 1,
            'origin_tz' => 'UTC',
            'origin_start' => '2032-02-29 09:00:00',
        ],
        'query_until' => $timestamp('2037-01-01 00:00:00 UTC'),
    ],
];
