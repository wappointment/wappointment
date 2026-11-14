<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Database;

use Wappointment\Http\Requests\GetDatabaseSchemaRequestData;
use Wappointment\Http\Responses\GetDatabaseSchemaResponseData;
use Wappointment\UseCases\Database\GetDatabaseSchemaUseCase;

class GetDatabaseSchemaController
{
    public function __construct(private GetDatabaseSchemaUseCase $useCase) {}

    public function __invoke(GetDatabaseSchemaRequestData $requestData): GetDatabaseSchemaResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
