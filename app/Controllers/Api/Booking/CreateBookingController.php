<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\Http\Requests\CreateBookingRequestData;
use Wappointment\Http\Responses\CreateBookingResponseData;
use Wappointment\UseCases\Booking\CreateBookingUseCase;

class CreateBookingController
{
    public function __construct(private CreateBookingUseCase $useCase) {}

    public function __invoke(CreateBookingRequestData $requestData): CreateBookingResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
