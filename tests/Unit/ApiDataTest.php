<?php
declare(strict_types=1);

use Wappointment\Http\ApiSchemas;
use Wappointment\Http\ContractValidator;
use Wappointment\Http\Requests\ClientCreateRequestData;
use Wappointment\Http\Requests\CreateBookingRequestData;
use Wappointment\Http\Requests\GetAvailabilityRequestData;
use Wappointment\Http\Requests\ListClientsRequestData;
use Wappointment\Http\Requests\ManageBookingRequestData;
use Wappointment\Http\Requests\SaveBookingSettingsRequestData;
use Wappointment\Http\Requests\SaveSettingsRequestData;
use Wappointment\Http\Requests\UpdateClientRequestData;
use Wappointment\Http\Responses\CreateBookingResponseData;
use Wappointment\Http\Responses\GetAvailabilityResponseData;

function contractBookingInput(): array
{
    return [
        'service_id' => 1, 'calendar_id' => 2, 'location_id' => 3,
        'duration' => 30, 'date' => '2030-01-02', 'start' => 1893571200,
        'name' => 'Customer', 'email' => 'customer@example.com',
    ];
}

test('booking contracts normalize integer strings without losing numeric types in UseCases', function () {
    $input = contractBookingInput();
    $input['service_id'] = '1';
    $input['start'] = '1893571200';
    expect(CreateBookingRequestData::from($input)->toArray())->toBe(contractBookingInput());
});

test('booking request rejects malformed missing and unknown fields', function (array $replacement, ?string $missing = null) {
    $input = array_replace(contractBookingInput(), $replacement);
    if ($missing !== null) {
        unset($input[$missing]);
    }
    expect(fn() => CreateBookingRequestData::from($input))->toThrow(InvalidArgumentException::class);
})->with([
    'missing id' => [[], 'service_id'],
    'unknown property' => [['unrecognized' => true]],
    'null name' => [['name' => null]],
    'empty name' => [['name' => '']],
    'array name' => [['name' => ['bad']]],
    'invalid email' => [['email' => 'not-an-email']],
    'empty email' => [['email' => '']],
    'nul string' => [['name' => "bad\0name"]],
    'oversized name' => [['name' => str_repeat('a', 101)]],
    'invalid date' => [['date' => '2030-02-31']],
    'floating id' => [['service_id' => 1.5]],
    'boolean id' => [['service_id' => true]],
    'zero id' => [['service_id' => 0]],
    'array id' => [['service_id' => [1]]],
    'negative id' => [['service_id' => -1]],
    'leading zero id' => [['service_id' => '01']],
    'trailing junk' => [['service_id' => '1abc']],
    'overflow' => [['service_id' => str_repeat('9', 50)]],
    'exponent' => [['service_id' => '1e2']],
    'float string' => [['service_id' => '1.0']],
    'invalid UTF-8' => [['name' => "\x80"]],
]);

test('availability contract accepts only a selection or a secret-key date pair', function () {
    $selection = array_intersect_key(contractBookingInput(), ApiSchemas::selection());
    expect(GetAvailabilityRequestData::from($selection)->toArray())->toBe($selection);
    $reschedule = ['edit_key' => str_repeat('a', 32), 'date' => '2030-01-02'];
    expect(GetAvailabilityRequestData::from($reschedule)->toArray())->toBe($reschedule);
    expect(fn() => GetAvailabilityRequestData::from($selection + ['start' => 1893571200]))->toThrow(InvalidArgumentException::class);
    expect(fn() => GetAvailabilityRequestData::from($reschedule + ['service_id' => 1]))->toThrow(InvalidArgumentException::class);
});

test('management request variants do not accept unrelated reschedule fields', function () {
    $cancel = ['action' => 'cancel', 'edit_key' => str_repeat('b', 32)];
    expect(ManageBookingRequestData::from($cancel)->toArray())->toBe($cancel);
    expect(fn() => ManageBookingRequestData::from($cancel + ['date' => '2030-01-02']))->toThrow(InvalidArgumentException::class);
    expect(fn() => ManageBookingRequestData::from(['action' => 'reschedule', 'edit_key' => $cancel['edit_key']]))->toThrow(InvalidArgumentException::class);
});

test('client requests preserve API values and map wire pagination names', function () {
    $input = ['name' => 'Customer', 'email' => 'customer@example.com', 'options' => ['notes' => 'Private']];
    expect(ClientCreateRequestData::from($input)->toArray())->toBe($input);
    $pagination = ListClientsRequestData::from(['page_num' => '2', 'per_page' => '5', 'search' => 'Customer']);
    expect($pagination->page)->toBe(2)->and($pagination->perPage)->toBe(5)->and($pagination->search)->toBe('Customer');
    expect(UpdateClientRequestData::from(['id' => '3', 'options' => []])->options)->toBe([]);
    expect(fn() => UpdateClientRequestData::from(['id' => 3, 'email' => null]))->toThrow(InvalidArgumentException::class);
    expect(fn() => ListClientsRequestData::from(['per_page' => 101]))->toThrow(InvalidArgumentException::class);
});

test('settings accept typed known fields and reject unknown keys and string booleans', function () {
    $settings = ['approval_mode' => 0, 'allow_cancellation' => false, 'mail_config' => ['method' => 'wpmail']];
    expect(SaveSettingsRequestData::from($settings)->toArray())->toBe($settings);
    expect(fn() => SaveSettingsRequestData::from([]))->toThrow(InvalidArgumentException::class);
    expect(fn() => SaveSettingsRequestData::from(['typo' => true]))->toThrow(InvalidArgumentException::class);
    expect(fn() => SaveSettingsRequestData::from(['allow_cancellation' => 'false']))->toThrow(InvalidArgumentException::class);
    expect(fn() => SaveSettingsRequestData::from(['mail_config' => ['unknown' => 'value']]))->toThrow(InvalidArgumentException::class);
});

test('nested request schemas validate schedules and arrays', function () {
    $input = [
        'service_name' => 'Consultation', 'calendar_name' => 'Organizer', 'location_name' => 'Office',
        'location_type' => 1, 'duration' => 30, 'booking_days' => 60, 'timezone' => 'UTC',
        'phone_required' => false, 'regav' => array_fill_keys(
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], [[540, 1020]]
        ),
    ];
    expect(SaveBookingSettingsRequestData::from($input)->toArray())->toBe($input);
    $input['regav']['monday'] = [[540, 1020, 1440]];
    expect(fn() => SaveBookingSettingsRequestData::from($input))->toThrow(InvalidArgumentException::class);
    $input['regav'] = ['holiday' => []];
    expect(fn() => SaveBookingSettingsRequestData::from($input))->toThrow(InvalidArgumentException::class);
});

test('response contracts preserve types and treat missing unexpected or wrong fields as server failures', function () {
    $booking = ['id' => 1, 'status' => 1, 'start' => 1893571200, 'end' => 1893573000, 'edit_key' => str_repeat('c', 32), 'notification_sent' => false];
    expect(CreateBookingResponseData::from($booking)->toArray())->toBe($booking);
    expect(fn() => CreateBookingResponseData::from($booking + ['private' => 'secret']))->toThrow(UnexpectedValueException::class);
    expect(fn() => CreateBookingResponseData::from(array_replace($booking, ['id' => '1'])))->toThrow(UnexpectedValueException::class);
    expect(fn() => CreateBookingResponseData::from([]))->toThrow(UnexpectedValueException::class);
    expect(fn() => GetAvailabilityResponseData::from(['timezone' => 'UTC', 'slots' => [['start' => 1, 'end' => 2]]]))->toThrow(UnexpectedValueException::class);
});

test('contract validation distinguishes lists from objects and validates declared dynamic properties', function () {
    expect(fn() => ContractValidator::validate(['not-a-list' => 1], ApiSchemas::list(['type' => 'integer'])))->toThrow(InvalidArgumentException::class);
    expect(fn() => ContractValidator::validate([1, 2], ApiSchemas::object([], [], true)))->toThrow(InvalidArgumentException::class);
    expect(fn() => ContractValidator::validate(['monday' => true], ApiSchemas::object([], [], ['type' => 'string'])))->toThrow(InvalidArgumentException::class);
    expect(fn() => ContractValidator::validate(INF, ['type' => 'number']))->toThrow(InvalidArgumentException::class);
    expect(fn() => ContractValidator::validate([], ApiSchemas::object([]), 'request', true, true))->toThrow(InvalidArgumentException::class);
    expect(ContractValidator::validate((object) [], ApiSchemas::object([]), 'request', true, true))->toBe([]);
});

test('response serialization preserves empty JSON objects instead of emitting lists', function () {
    $response = new \Wappointment\Http\Responses\GetSettingsResponseData([
        'settings' => ['general' => [], 'notifications' => [], 'advanced' => []],
    ]);
    expect(json_encode($response, JSON_THROW_ON_ERROR))->toBe('{"settings":{"general":{},"notifications":{},"advanced":{}}}');
    $availability = new GetAvailabilityResponseData(['timezone' => 'UTC', 'slots' => []]);
    expect(json_encode($availability, JSON_THROW_ON_ERROR))->toBe('{"timezone":"UTC","slots":[]}');
});
