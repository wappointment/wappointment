<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\Requests\UpdateOwnCalendarStatusRequestData;
use Wappointment\Http\Responses\UpdateOwnCalendarStatusResponseData;
use Wappointment\UseCases\Settings\UpdateOwnCalendarStatusUseCase;

class UpdateOwnCalendarStatusController
{
    public function __construct(private UpdateOwnCalendarStatusUseCase $useCase) {}

    public function __invoke(UpdateOwnCalendarStatusRequestData $requestData): UpdateOwnCalendarStatusResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
