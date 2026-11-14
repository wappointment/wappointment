<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Data\ClientData;

class ListClientsResponseData extends ResponseData
{
    public function __construct(
        /** @var list<ClientData> */
        #[CollectionOf(ClientData::class)] public readonly array $data,
        #[Min(0)] public readonly int $total,
        #[Min(1)] public readonly int $page,
        #[Min(1)] public readonly int $per_page,
        #[Min(0)] public readonly int|float $total_pages,
    ) {}
}
