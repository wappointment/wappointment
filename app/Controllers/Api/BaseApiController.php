<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

abstract class BaseApiController
{
    abstract public function __invoke(): void;

    protected function sendJson(array $data): void
    {
        wp_send_json_success($data);
    }
}
