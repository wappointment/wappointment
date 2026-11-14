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
        $input = $requestData->toArray();
        return new ListJobsResponseData($this->action->handle($input['page_num'] ?? 1, $input['per_page'] ?? 10));
    }
}
