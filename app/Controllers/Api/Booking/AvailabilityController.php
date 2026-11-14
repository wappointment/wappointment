<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\Http\Requests\GetAvailabilityRequestData;
use Wappointment\Http\Responses\GetAvailabilityResponseData;
use Wappointment\UseCases\Booking\GetAvailabilityUseCase;

class AvailabilityController
{
    public function __construct(private GetAvailabilityUseCase $useCase) {}

    public function __invoke(GetAvailabilityRequestData $requestData): GetAvailabilityResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
