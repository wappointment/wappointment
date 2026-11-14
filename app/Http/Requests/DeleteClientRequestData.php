<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Min;

class DeleteClientRequestData extends RequestData
{
    public function __construct(#[Min(1)] public readonly int $id) {}
}
