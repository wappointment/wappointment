<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Database;

use Wappointment\Actions\Database\InspectDatabaseSchemaAction;
use Wappointment\Http\Requests\GetDatabaseSchemaRequestData;
use Wappointment\Http\Responses\GetDatabaseSchemaResponseData;

class GetDatabaseSchemaUseCase
{
    public function __construct(private InspectDatabaseSchemaAction $action) {}

    public function execute(GetDatabaseSchemaRequestData $requestData): GetDatabaseSchemaResponseData
    {
        return new GetDatabaseSchemaResponseData($this->action->handle());
    }
}
