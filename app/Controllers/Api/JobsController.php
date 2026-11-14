<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

use Wappointment\Http\Requests\ListJobsRequestData;
use Wappointment\Http\Responses\ListJobsResponseData;
use Wappointment\UseCases\Jobs\ListJobsUseCase;

class JobsController
{
    public function __construct(
        private ListJobsUseCase $useCase
    ) {}

    public function __invoke(ListJobsRequestData $requestData): ListJobsResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
