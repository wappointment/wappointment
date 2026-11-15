<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\Requests\CreateOwnCalendarStatusRequestData;
use Wappointment\Http\Responses\CreateOwnCalendarStatusResponseData;
use Wappointment\UseCases\Settings\CreateOwnCalendarStatusUseCase;

class CreateOwnCalendarStatusController
{
    public function __construct(private CreateOwnCalendarStatusUseCase $useCase) {}

    public function __invoke(CreateOwnCalendarStatusRequestData $requestData): CreateOwnCalendarStatusResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
