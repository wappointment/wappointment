<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\GetBookingCatalogAction;
use Wappointment\Http\Requests\GetBookingCatalogRequestData;
use Wappointment\Http\Responses\GetBookingCatalogResponseData;

class GetBookingCatalogUseCase
{
    public function __construct(private GetBookingCatalogAction $action) {}

    public function execute(GetBookingCatalogRequestData $requestData): GetBookingCatalogResponseData
    {
        return GetBookingCatalogResponseData::from($this->action->handle());
    }
}
