<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\UseCases\Booking\GetBookingCatalogUseCase;
use Wappointment\Http\Requests\GetBookingCatalogRequestData;
use Wappointment\Http\Responses\GetBookingCatalogResponseData;

class CatalogController
{
    public function __construct(private GetBookingCatalogUseCase $useCase) {}

    public function __invoke(GetBookingCatalogRequestData $requestData): GetBookingCatalogResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
