<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\UseCases\Booking\ListAppointmentsUseCase;
use Wappointment\Http\Requests\ListAppointmentsRequestData;
use Wappointment\Http\Responses\ListAppointmentsResponseData;

class ListAppointmentsController
{
    public function __construct(private ListAppointmentsUseCase $useCase) {}

    public function __invoke(ListAppointmentsRequestData $requestData): ListAppointmentsResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
