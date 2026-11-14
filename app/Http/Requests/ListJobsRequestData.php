<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Max;
use Wappointment\Http\Attributes\Min;

class ListJobsRequestData extends RequestData
{
    public function __construct(
        #[Min(1)] public readonly int $page_num = 1,
        #[Min(1), Max(100)] public readonly int $per_page = 10,
    ) {}
}
