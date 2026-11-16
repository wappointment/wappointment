<?php
declare(strict_types=1);

// Inputs for legacy `Services\Sections::getFreeSlots` / `getCoverage` (summary emails).
// Times are Unix seconds; `booked` are appointment rows including their buffer.
return [
    'empty day' => ['free' => [], 'booked' => [], 'duration' => 3600],
    'free shorter than one slot' => ['free' => [[0, 1800]], 'booked' => [], 'duration' => 3600],
    'all free' => ['free' => [[0, 28800]], 'booked' => [], 'duration' => 3600],
    'partial slots are dropped per window' => ['free' => [[0, 5400], [7200, 12600]], 'booked' => [], 'duration' => 3600],
    'fully booked' => ['free' => [], 'booked' => [[0, 3600], [3600, 7200]], 'duration' => 3600],
    'one third booked' => ['free' => [[3600, 10800]], 'booked' => [[0, 3600]], 'duration' => 3600],
    'rounding half up' => ['free' => [[0, 5400]], 'booked' => [[5400, 7200], [7200, 9000]], 'duration' => 1800],
    'buffered appointments count their buffer' => ['free' => [[0, 2700]], 'booked' => [[2700, 4500]], 'duration' => 1800],
    'booked shorter than one slot' => ['free' => [], 'booked' => [[0, 900]], 'duration' => 1800],
];
