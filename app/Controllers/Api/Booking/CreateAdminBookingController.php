<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\Http\Requests\AdminBookingRequestData;
use Wappointment\Http\Responses\CreateAdminBookingResponseData;
use Wappointment\UseCases\Booking\CreateAdminBookingUseCase;

class CreateAdminBookingController
{
    public function __construct(private CreateAdminBookingUseCase $useCase) {}

    public function __invoke(AdminBookingRequestData $requestData): CreateAdminBookingResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
