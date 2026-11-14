<?php
declare(strict_types=1);

use Wappointment\Actions\Clients\CreateClientAction;
use Wappointment\Actions\Clients\DeleteClientAction;
use Wappointment\Actions\Clients\ListClientsAction;
use Wappointment\Actions\Clients\UpdateClientAction;
use Wappointment\Actions\Jobs\ListJobsAction;
use Wappointment\Actions\Settings\GetSettingAction;
use Wappointment\Actions\Settings\GetSettingsAction;
use Wappointment\Actions\Settings\SaveSettingsAction;
use Wappointment\Http\Requests\ClientCreateRequestData;
use Wappointment\Http\Requests\ListClientsRequestData;
use Wappointment\Http\Requests\UpdateClientRequestData;
use Wappointment\Http\Requests\DeleteClientRequestData;
use Wappointment\Http\Requests\ListJobsRequestData;
use Wappointment\Http\Requests\GetSettingsRequestData;
use Wappointment\Http\Requests\GetSettingRequestData;
use Wappointment\Http\Requests\SaveSettingsRequestData;
use Wappointment\UseCases\Clients\CreateClientUseCase;
use Wappointment\UseCases\Clients\DeleteClientUseCase;
use Wappointment\UseCases\Clients\ListClientsUseCase;
use Wappointment\UseCases\Clients\UpdateClientUseCase;
use Wappointment\UseCases\Jobs\ListJobsUseCase;
use Wappointment\UseCases\Settings\GetSettingUseCase;
use Wappointment\UseCases\Settings\GetSettingsUseCase;
use Wappointment\UseCases\Settings\SaveSettingsUseCase;

test('job UseCase forwards the requested pagination and returns the Action result', function () {
    $result = ['data' => [], 'total' => 17, 'page' => 2, 'per_page' => 5, 'total_pages' => 4];
    $action = $this->createMock(ListJobsAction::class);
    $action->expects($this->once())->method('handle')->with(2, 5)->willReturn($result);
    expect((new ListJobsUseCase($action))->execute(ListJobsRequestData::from(['page_num' => 2, 'per_page' => 5]))->toArray())->toBe($result);
});

test('client listing UseCase preserves the search request data', function () {
    $request = new ListClientsRequestData(3, 5, 'customer');
    $result = ['data' => [], 'total' => 0, 'page' => 3, 'per_page' => 5, 'total_pages' => 0];
    $action = $this->createMock(ListClientsAction::class);
    $action->expects($this->once())->method('handle')->with($this->identicalTo($request))->willReturn($result);
    expect((new ListClientsUseCase($action))->execute($request)->toArray())->toBe($result);
});

test('client creation UseCase forwards the typed data without changing its response', function () {
    $request = new ClientCreateRequestData('Customer', 'customer@example.com');
    $result = ['id' => 7, 'name' => 'Customer', 'email' => 'customer@example.com', 'options' => '{}', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null];
    $action = $this->createMock(CreateClientAction::class);
    $action->expects($this->once())->method('handle')->with($this->identicalTo($request))->willReturn($result);
    expect((new CreateClientUseCase($action))->execute($request)->toArray())->toEqual($result);
});

test('client update UseCase forwards partial request data', function () {
    $request = new UpdateClientRequestData(7, 'Updated Customer');
    $result = ['id' => 7, 'name' => 'Updated Customer', 'email' => 'customer@example.com', 'options' => null, 'created_at' => null, 'updated_at' => null, 'deleted_at' => null];
    $action = $this->createMock(UpdateClientAction::class);
    $action->expects($this->once())->method('handle')->with($this->identicalTo($request))->willReturn($result);
    expect((new UpdateClientUseCase($action))->execute($request)->toArray())->toEqual($result);
});

test('client deletion UseCase keeps the existing result contract', function () {
    $result = ['success' => true, 'message' => 'Client deleted'];
    $action = $this->createMock(DeleteClientAction::class);
    $action->expects($this->once())->method('handle')->with(7)->willReturn($result);
    expect((new DeleteClientUseCase($action))->execute(DeleteClientRequestData::from(['id' => 7]))->toArray())->toBe($result);
});

test('client UseCase preserves failure types and status codes', function () {
    $failure = new RuntimeException('Client not found', 404);
    $action = $this->createMock(DeleteClientAction::class);
    $action->expects($this->once())->method('handle')->with(7)->willThrowException($failure);
    try {
        (new DeleteClientUseCase($action))->execute(DeleteClientRequestData::from(['id' => 7]));
        $this->fail('The failed Action must not produce a successful result.');
    } catch (RuntimeException $error) {
        expect($error)->toBe($failure)->and($error->getCode())->toBe(404);
    }
});

test('settings UseCases preserve grouped, single and saved response contracts', function () {
    $grouped = ['settings' => ['general' => ['approval_mode' => 0], 'notifications' => [], 'advanced' => []]];
    $getAll = $this->createMock(GetSettingsAction::class);
    $getAll->expects($this->once())->method('handle')->willReturn($grouped);
    expect((new GetSettingsUseCase($getAll))->execute(GetSettingsRequestData::from([]))->toArray())->toBe($grouped);

    $single = ['name' => 'approval_mode', 'value' => 0];
    $getOne = $this->createMock(GetSettingAction::class);
    $getOne->expects($this->once())->method('handle')->with('approval_mode')->willReturn($single);
    expect((new GetSettingUseCase($getOne))->execute(GetSettingRequestData::from(['name' => 'approval_mode']))->toArray())->toBe($single);

    $saved = ['success' => true, 'message' => 'Settings saved successfully'];
    $save = $this->createMock(SaveSettingsAction::class);
    $save->expects($this->once())->method('handle')->with(['approval_mode' => 0])->willReturn($saved);
    expect((new SaveSettingsUseCase($save))->execute(SaveSettingsRequestData::from(['approval_mode' => 0]))->toArray())->toBe($saved);
});
