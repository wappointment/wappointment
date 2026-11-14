<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Length;
use Wappointment\Http\Attributes\MapInputName;
use Wappointment\Http\Attributes\Max;
use Wappointment\Http\Attributes\Min;

class ListClientsRequestData extends RequestData
{
    public function __construct(
        #[MapInputName('page_num'), Min(1)] public readonly int $page = 1,
        #[MapInputName('per_page'), Min(1), Max(100)] public readonly int $perPage = 10,
        #[Length(max: 255)] public readonly string $search = '',
    ) {}
}
