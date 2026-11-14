<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Jobs;

use Wappointment\Actions\Jobs\ListJobsAction;
use Wappointment\Http\Requests\ListJobsRequestData;
use Wappointment\Http\Responses\ListJobsResponseData;

class ListJobsUseCase
{
    public function __construct(private ListJobsAction $action) {}

    public function execute(ListJobsRequestData $requestData): ListJobsResponseData
    {
        return ListJobsResponseData::from($this->action->handle($requestData->page_num, $requestData->per_page));
    }
}
