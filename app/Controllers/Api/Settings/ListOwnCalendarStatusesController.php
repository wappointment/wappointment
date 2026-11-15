<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\Requests\GetOwnCalendarStatusesRequestData;
use Wappointment\Http\Responses\ListOwnCalendarStatusesResponseData;
use Wappointment\UseCases\Settings\ListOwnCalendarStatusesUseCase;

class ListOwnCalendarStatusesController
{
    public function __construct(private ListOwnCalendarStatusesUseCase $useCase) {}

    public function __invoke(GetOwnCalendarStatusesRequestData $requestData): ListOwnCalendarStatusesResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
