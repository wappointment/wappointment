<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\Requests\DeleteOwnCalendarStatusRequestData;
use Wappointment\Http\Responses\DeleteOwnCalendarStatusResponseData;
use Wappointment\UseCases\Settings\DeleteOwnCalendarStatusUseCase;

class DeleteOwnCalendarStatusController
{
    public function __construct(private DeleteOwnCalendarStatusUseCase $useCase) {}

    public function __invoke(DeleteOwnCalendarStatusRequestData $requestData): DeleteOwnCalendarStatusResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
