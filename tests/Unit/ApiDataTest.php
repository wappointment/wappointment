<?php
declare(strict_types=1);

use Wappointment\Http\Requests\BookingRequestData;
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
    $selection = array_intersect_key(contractBookingInput(), BookingRequestData::schema()['properties']);
    expect(GetAvailabilityRequestData::from($selection)->toArray())->toEqual($selection);
    $reschedule = ['edit_key' => str_repeat('a', 32), 'date' => '2030-01-02'];
    expect(GetAvailabilityRequestData::from($reschedule)->toArray())->toEqual($reschedule);
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
    expect(SaveSettingsRequestData::from($settings)->toArray())->toEqual($settings);
    expect(fn() => SaveSettingsRequestData::from([]))->toThrow(InvalidArgumentException::class);
    expect(fn() => SaveSettingsRequestData::from(['typo' => true]))->toThrow(InvalidArgumentException::class);
    expect(fn() => SaveSettingsRequestData::from(['allow_cancellation' => 'false']))->toThrow(InvalidArgumentException::class);
    expect(SaveSettingsRequestData::from(['refreshavb_at' => 23])->refreshavb_at)->toBe(23)
        ->and(fn() => SaveSettingsRequestData::from(['refreshavb_at' => 24]))->toThrow(InvalidArgumentException::class);
    expect(SaveSettingsRequestData::from(['week_starts_on' => 6])->week_starts_on)->toBe(6)
        ->and(fn() => SaveSettingsRequestData::from(['week_starts_on' => 7]))->toThrow(InvalidArgumentException::class);
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
    $booking = [
        'id' => 1, 'status' => 1, 'start' => 1893571200, 'end' => 1893573000,
        'edit_key' => str_repeat('c', 32), 'service_id' => 1, 'calendar_id' => 2,
        'location_id' => 3, 'duration' => 30, 'notification_sent' => false,
    ];
    expect(CreateBookingResponseData::from($booking)->toArray())->toBe($booking);
    expect(fn() => CreateBookingResponseData::from($booking + ['private' => 'secret']))->toThrow(UnexpectedValueException::class);
    expect(fn() => CreateBookingResponseData::from(array_replace($booking, ['id' => '1'])))->toThrow(UnexpectedValueException::class);
    expect(fn() => CreateBookingResponseData::from([]))->toThrow(UnexpectedValueException::class);
    expect(fn() => GetAvailabilityResponseData::from(['timezone' => 'UTC', 'slots' => [['start' => 1, 'end' => 2]]]))->toThrow(UnexpectedValueException::class);
});

test('contract validation distinguishes lists from objects and validates declared dynamic properties', function () {
    expect(fn() => ContractValidator::validate(['not-a-list' => 1], ['type' => 'array', 'items' => ['type' => 'integer']]))->toThrow(InvalidArgumentException::class);
    expect(fn() => ContractValidator::validate([1, 2], ['type' => 'object', 'additionalProperties' => true]))->toThrow(InvalidArgumentException::class);
    expect(fn() => ContractValidator::validate(['monday' => true], ['type' => 'object', 'additionalProperties' => ['type' => 'string']]))->toThrow(InvalidArgumentException::class);
    expect(fn() => ContractValidator::validate(INF, ['type' => 'number']))->toThrow(InvalidArgumentException::class);
    $emptyObject = ['type' => 'object', 'properties' => [], 'additionalProperties' => false];
    expect(fn() => ContractValidator::validate([], $emptyObject, 'request', true, true))->toThrow(InvalidArgumentException::class);
    expect(ContractValidator::validate((object) [], $emptyObject, 'request', true, true))->toBe([]);
});

test('Data properties and nested collections hydrate as concrete typed objects', function () {
    $request = CreateBookingRequestData::from(contractBookingInput());
    expect($request->service_id)->toBe(1)
        ->and($request->name)->toBe('Customer')
        ->and($request->phone)->toBe(\Wappointment\System\Optional::Missing);
    $settings = SaveSettingsRequestData::from(['mail_config' => ['method' => 'wpmail']]);
    expect($settings->mail_config)->toBeInstanceOf(\Wappointment\Http\Data\MailSettingsData::class)
        ->and($settings->mail_config->method)->toBe('wpmail')
        ->and($settings->toArray())->toBe(['mail_config' => ['method' => 'wpmail']]);
    $availability = GetAvailabilityResponseData::from([
        'timezone' => 'UTC',
        'slots' => [['start' => 1, 'end' => 2, 'reserved_until' => 3]],
    ]);
    expect($availability->slots[0])->toBeInstanceOf(\Wappointment\Http\Data\AvailableSlotData::class)
        ->and($availability->slots[0]->reserved_until)->toBe(3)
        ->and($availability->toArray()['slots'][0])->toBe(['start' => 1, 'end' => 2, 'reserved_until' => 3]);
});

test('Optional distinguishes an omitted update from nullable response values', function () {
    $update = UpdateClientRequestData::from(['id' => 7]);
    expect($update->name)->toBe(\Wappointment\System\Optional::Missing)
        ->and($update->toArray())->toBe(['id' => 7]);
    expect(fn() => UpdateClientRequestData::from(['id' => 7, 'name' => null]))->toThrow(InvalidArgumentException::class);
    $setup = \Wappointment\Http\Responses\GetBookingSettingsResponseData::from([
        'service' => null, 'calendar' => null, 'location' => null, 'booking_page' => 0, 'booking_pages' => [],
    ]);
    expect($setup->service)->toBeNull()->and($setup->toArray())->toHaveKey('service', null);
});

test('native constructors support named arguments and immutable properties', function () {
    $request = new BookingRequestData(service_id: 1, calendar_id: 2, location_id: 3, duration: 30, date: '2030-01-02');
    expect($request->duration)->toBe(30);
    expect(function () use ($request) { $request->duration = 45; })->toThrow(Error::class);
    $response = new GetAvailabilityResponseData('UTC', [new \Wappointment\Http\Data\AvailableSlotData(1, 2, 3)]);
    expect(json_encode($response, JSON_THROW_ON_ERROR))->toBe('{"timezone":"UTC","slots":[{"start":1,"end":2,"reserved_until":3}]}');
    $invalid = new GetAvailabilityResponseData('UTC', [new \Wappointment\Http\Data\AvailableSlotData(0, 2, 3)]);
    expect(fn() => $invalid->jsonSerialize())->toThrow(UnexpectedValueException::class);
});

test('schemas are derived from types defaults mapping and inherited attributes', function () {
    $schema = CreateBookingRequestData::schema();
    expect($schema['properties']['service_id'])->toBe(['type' => 'integer', 'minimum' => 1])
        ->and($schema['properties']['date'])->toBe(['type' => 'string', 'format' => 'date'])
        ->and($schema['properties']['email']['format'])->toBe('email')
        ->and($schema['required'])->toContain('service_id', 'start', 'name')->not->toContain('email', 'phone')
        ->and($schema['additionalProperties'])->toBeFalse();
    $pagination = ListClientsRequestData::from(['page_num' => 2, 'per_page' => 5]);
    expect($pagination->toArray())->toBe(['page_num' => 2, 'per_page' => 5, 'search' => '']);
    $response = GetAvailabilityResponseData::schema();
    expect($response['properties']['slots']['items']['properties']['reserved_until'])->toBe(['type' => 'integer', 'minimum' => 1]);
});

test('collections validate each scalar item and preserve scalar alternatives in unions', function () {
    expect(SaveSettingsRequestData::from(['servicesDefault' => true])->servicesDefault)->toBeTrue();
    expect(SaveSettingsRequestData::from(['servicesDefault' => [1, 2]])->servicesDefault)->toBe([1, 2]);
    expect(SaveSettingsRequestData::from(['service' => ['type' => 'phone']])->service->type)->toBe('phone');
    expect(SaveSettingsRequestData::from(['service' => ['type' => ['phone']]])->service->type)->toBe(['phone']);
    expect(fn() => SaveSettingsRequestData::from(['calendar_roles' => [1]]))->toThrow(InvalidArgumentException::class);
    expect(fn() => SaveSettingsRequestData::from(['servicesDefault' => [true]]))->toThrow(InvalidArgumentException::class);
    expect(fn() => SaveSettingsRequestData::from(['service' => ['type' => [true]]]))->toThrow(InvalidArgumentException::class);
});

test('invalid Data definitions fail explicitly instead of dropping constructor parameters', function () {
    $invalid = new class(7) extends \Wappointment\System\Data {
        public function __construct(private readonly int $id) {}
    };
    expect(fn() => $invalid::schema())->toThrow(LogicException::class, 'must have a public readonly property');
});

test('response serialization preserves empty JSON objects instead of emitting lists', function () {
    $response = \Wappointment\Http\Responses\GetSettingsResponseData::from([
        'settings' => ['general' => [], 'notifications' => [], 'advanced' => []],
    ]);
    expect(json_encode($response, JSON_THROW_ON_ERROR))->toBe('{"settings":{"general":{},"notifications":{},"advanced":{}}}');
    $availability = GetAvailabilityResponseData::from(['timezone' => 'UTC', 'slots' => []]);
    expect(json_encode($availability, JSON_THROW_ON_ERROR))->toBe('{"timezone":"UTC","slots":[]}');
});
