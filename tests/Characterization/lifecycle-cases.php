<?php
declare(strict_types=1);

/*
 * Shared booking-lifecycle scenarios. Each runner (audited master and v3) maps the
 * same abstract steps onto its own API and records the stored result after every step.
 * Slot offsets are minutes from a base instant two days ahead at 12:00 UTC; the fixture
 * calendar is open around the clock in UTC with a 30-minute service.
 */
$base = [
    'approval_mode' => 1,
    'buffer_time' => 15,
    'hours_before_booking_allowed' => 0,
    'allow_cancellation' => true,
    'allow_rescheduling' => true,
    'hours_before_cancellation_allowed' => 1,
    'hours_before_rescheduling_allowed' => 1,
    'email_notifications' => [],
    'max_active_bookings' => 0,
    'max_active_per_staff' => false,
];
$manual = ['approval_mode' => 2];
$book = static fn(string $client, int $slot, array $extra = []): array => ['book', 'client' => $client, 'slot' => $slot] + $extra;

return [
    'clients' => [
        'ada' => ['email' => 'ada@example.invalid', 'name' => 'Ada Oracle'],
        'bob' => ['email' => 'bob@example.invalid', 'name' => 'Bob Oracle'],
    ],
    'duration' => 30,
    'settings' => $base,
    'scenarios' => [
        'automatic-approval-confirms' => ['steps' => [$book('ada', 0)]],
        'manual-approval-pends' => ['settings' => $manual, 'steps' => [$book('ada', 0)]],
        'zero-buffer-reserves-duration-only' => ['settings' => ['buffer_time' => 0], 'steps' => [$book('ada', 0)]],
        'same-slot-conflict' => ['steps' => [$book('ada', 0), $book('bob', 0)]],
        'overlap-inside-buffer-conflict' => ['steps' => [$book('ada', 0), $book('bob', 30)]],
        'adjacent-after-buffer-allowed' => ['steps' => [$book('ada', 0), $book('bob', 45)]],
        'off-grid-start' => ['steps' => [$book('ada', 20)]],
        'pending-blocks-slot' => ['settings' => $manual, 'steps' => [$book('ada', 0), $book('bob', 0)]],
        'below-notice-rejected' => ['settings' => ['hours_before_booking_allowed' => 72], 'steps' => [$book('ada', 0)]],
        'beyond-horizon-rejected' => ['steps' => [$book('ada', 100 * 1440)]],

        'limit-blocks-second-active' => [
            'settings' => ['max_active_bookings' => 1],
            'steps' => [$book('ada', 0), $book('ada', 45), $book('bob', 45)],
        ],
        'limit-counts-pending' => [
            'settings' => $manual + ['max_active_bookings' => 1],
            'steps' => [$book('ada', 0), $book('ada', 45)],
        ],
        'limit-ignores-past' => [
            'settings' => ['max_active_bookings' => 1],
            'steps' => [['seed', 'client' => 'ada', 'slot' => -5 * 1440, 'status' => 1], $book('ada', 0)],
        ],
        'limit-of-two' => [
            'settings' => ['max_active_bookings' => 2],
            'steps' => [$book('ada', 0), $book('ada', 45), $book('ada', 90)],
        ],
        'limit-freed-by-cancel' => [
            'settings' => ['max_active_bookings' => 1],
            'steps' => [$book('ada', 0), ['cancel', 'ref' => 'ada'], $book('ada', 45)],
        ],

        'cancel-confirmed-in-time' => ['steps' => [$book('ada', 0), ['cancel', 'ref' => 'ada']]],
        'cancel-confirmed-late' => [
            'settings' => ['hours_before_cancellation_allowed' => 72],
            'steps' => [$book('ada', 0), ['cancel', 'ref' => 'ada']],
        ],
        'cancel-pending-late' => [
            'settings' => $manual + ['hours_before_cancellation_allowed' => 72],
            'steps' => [$book('ada', 0), ['cancel', 'ref' => 'ada']],
        ],
        'cancel-disabled' => [
            'settings' => ['allow_cancellation' => false],
            'steps' => [$book('ada', 0), ['cancel', 'ref' => 'ada']],
        ],
        'cancel-unknown-key' => ['steps' => [$book('ada', 0), ['cancel-unknown']]],
        'cancel-twice' => ['steps' => [$book('ada', 0), ['cancel', 'ref' => 'ada'], ['cancel', 'ref' => 'ada']]],
        'cancel-releases-slot' => ['steps' => [$book('ada', 0), ['cancel', 'ref' => 'ada'], $book('bob', 0)]],
        'cancel-same-slot-twice' => [
            'steps' => [$book('ada', 0), ['cancel', 'ref' => 'ada'], $book('bob', 0), ['cancel', 'ref' => 'bob']],
        ],

        'reschedule-in-time' => ['steps' => [$book('ada', 0), ['reschedule', 'ref' => 'ada', 'slot' => 90]]],
        'reschedule-late' => [
            'settings' => ['hours_before_rescheduling_allowed' => 72],
            'steps' => [$book('ada', 0), ['reschedule', 'ref' => 'ada', 'slot' => 90]],
        ],
        'reschedule-disabled' => [
            'settings' => ['allow_rescheduling' => false],
            'steps' => [$book('ada', 0), ['reschedule', 'ref' => 'ada', 'slot' => 90]],
        ],
        'reschedule-pending-stays-pending' => [
            'settings' => $manual,
            'steps' => [$book('ada', 0), ['reschedule', 'ref' => 'ada', 'slot' => 90]],
        ],
        'reschedule-twice' => [
            'steps' => [$book('ada', 0), ['reschedule', 'ref' => 'ada', 'slot' => 90], ['reschedule', 'ref' => 'ada', 'slot' => 135]],
        ],
        'reschedule-frees-old-slot' => [
            'steps' => [$book('ada', 0), ['reschedule', 'ref' => 'ada', 'slot' => 90], $book('bob', 0)],
        ],
        'reschedule-onto-occupied' => [
            'steps' => [$book('ada', 0), $book('bob', 45), ['reschedule', 'ref' => 'ada', 'slot' => 45]],
        ],
        'reschedule-off-grid' => ['steps' => [$book('ada', 0), ['reschedule', 'ref' => 'ada', 'slot' => 100]]],
        'reschedule-to-past' => ['steps' => [$book('ada', 0), ['reschedule', 'ref' => 'ada', 'slot' => -5 * 1440]]],
        'reschedule-beyond-horizon' => ['steps' => [$book('ada', 0), ['reschedule', 'ref' => 'ada', 'slot' => 100 * 1440]]],
        'reschedule-keeps-original-buffer' => [
            'steps' => [$book('ada', 0), ['settings', ['buffer_time' => 0]], ['reschedule', 'ref' => 'ada', 'slot' => 90]],
        ],

        'returning-client-new-name' => [
            'steps' => [$book('ada', 0), $book('ada', 45, ['name' => 'Changed Name'])],
        ],
        'deleted-client-restored' => [
            'steps' => [$book('ada', 0), ['delete-client', 'client' => 'ada'], $book('ada', 45)],
        ],
        'view-booking' => ['steps' => [$book('ada', 0), ['view', 'ref' => 'ada']]],
        'view-after-cancel' => ['steps' => [$book('ada', 0), ['cancel', 'ref' => 'ada'], ['view', 'ref' => 'ada']]],
    ],
];
