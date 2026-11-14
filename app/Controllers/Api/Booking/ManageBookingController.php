<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\Http\Requests\ManageBookingRequestData;
use Wappointment\Http\Responses\ManageBookingResponseData;
use Wappointment\UseCases\Booking\ManageBookingUseCase;

class ManageBookingController
{
    public function __construct(private ManageBookingUseCase $useCase) {}

    public function __invoke(ManageBookingRequestData $requestData): ManageBookingResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
