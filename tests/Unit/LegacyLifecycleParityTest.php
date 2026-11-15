<?php
declare(strict_types=1);

test('legacy lifecycle fixture is pinned to audited master and shared inputs', function () {
    $directory = __DIR__ . '/../Characterization/';
    $cases = require $directory . 'lifecycle-cases.php';
    $golden = json_decode(file_get_contents($directory . 'legacy-lifecycle.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($golden['source'])->toBe('fcf2cfe296a240421ed91f28ff3672ece04e4786')
        ->and($golden['source_sha256'])->toBe([
            'Services/AppointmentNew.php' => 'c364d868a55ef5c40a0c6c551ce9bafea880ea406db2447db076488124fc0310',
            'Services/Client.php' => '7bc5c45d50ba0b2bcf9d50c367b8c0fac702b952668eeb57f323da218944d1c9',
            'Models/Appointment.php' => 'a44f9bfd5c8ee09fff1b78849a731e8c385e02c80e0456cffaf43138da7e5616',
            'Models/CanBook.php' => '1f18583da0b08dd190478dfa4d32fdabf6948302e94c3d274f9a0f7f1caff1fe',
            'Models/Appointment/ManipulateCancelReschedule.php' => '59a29027037ae59b11c0f902346448a9e9c6e8b217f0ac684dfc81e1e1e14397',
        ])
        ->and($golden['cases_sha256'])->toBe(hash_file('sha256', $directory . 'lifecycle-cases.php'))
        ->and(array_keys($golden['results']))->toBe(array_keys($cases['scenarios']));

    foreach ($cases['scenarios'] as $name => $scenario) {
        expect(count($golden['results'][$name]['steps']))->toBe(count($scenario['steps']));
    }
});

test('legacy lifecycle golden records booking state, capacity, and client restoration', function () {
    $directory = __DIR__ . '/../Characterization/';
    $golden = json_decode(file_get_contents($directory . 'legacy-lifecycle.json'), true, 512, JSON_THROW_ON_ERROR);
    $results = $golden['results'];
    $step = static fn(string $scenario, int $index): array => $results[$scenario]['steps'][$index];

    expect($step('automatic-approval-confirms', 0)['stored']['appointments'][0]['status'])->toBe(1)
        ->and($step('manual-approval-pends', 0)['stored']['appointments'][0]['status'])->toBe(0)
        ->and($step('automatic-approval-confirms', 0)['stored']['appointments'][0]['end_offset'])->toBe(2700)
        ->and($step('zero-buffer-reserves-duration-only', 0)['stored']['appointments'][0]['end_offset'])->toBe(1800)
        ->and($step('overlap-inside-buffer-conflict', 1)['result']['result']['message'])->toBe('Slot already booked')
        ->and(count($step('adjacent-after-buffer-allowed', 1)['stored']['appointments']))->toBe(2)
        ->and($step('limit-blocks-second-active', 1)['result']['result']['message'])
            ->toBe('Max active bookings reached! Cancel one of your appointments in order to book a new one.')
        ->and($step('returning-client-new-name', 1)['stored']['clients'][0]['name'])->toBe('Changed Name')
        ->and($step('deleted-client-restored', 2)['stored']['clients'][0]['deleted_at'])->toBeNull();
});

test('legacy lifecycle golden records cancellation and rescheduling transitions', function () {
    $directory = __DIR__ . '/../Characterization/';
    $golden = json_decode(file_get_contents($directory . 'legacy-lifecycle.json'), true, 512, JSON_THROW_ON_ERROR);
    $results = $golden['results'];
    $step = static fn(string $scenario, int $index): array => $results[$scenario]['steps'][$index];

    expect($step('cancel-confirmed-in-time', 1)['result']['result']['status'])->toBe(200)
        ->and($step('cancel-confirmed-in-time', 1)['stored']['appointments'])->toBe([])
        ->and($step('cancel-confirmed-late', 1)['result']['result']['message'])->toBe("Can't cancel appointment anymore")
        ->and($step('cancel-pending-late', 1)['result']['result']['status'])->toBe(200)
        ->and($step('cancel-disabled', 1)['result']['result']['message'])->toBe('Appointment cancellation is not allowed')
        ->and($step('reschedule-in-time', 1)['stored']['appointments'][0]['options']['sequence'])->toBe(1)
        ->and($step('reschedule-keeps-original-buffer', 2)['stored']['appointments'][0]['end_offset'])->toBe(8100)
        ->and($step('reschedule-late', 1)['result']['result']['message'])->toBe("Can't reschedule appointment anymore")
        ->and($step('reschedule-disabled', 1)['result']['result']['message'])->toBe('Appointment rescheduling is not allowed')
        ->and($step('reschedule-pending-stays-pending', 1)['stored']['appointments'][0]['status'])->toBe(0)
        ->and($step('reschedule-onto-occupied', 2)['result']['result']['message'])
            ->toBe('SQLSTATE[23000]: duplicate appointment slot constraint');
});
