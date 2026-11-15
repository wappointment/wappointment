<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Email;

class CustomerHistoryRequestData extends RequestData
{
    public function __construct(#[Email] public readonly string $email) {}
}
