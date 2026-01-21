<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

abstract class BaseApiController
{
    abstract public function __invoke(\WP_REST_Request $request): void;

    protected function sendJson(array $data, int $statusCode = 200): void
    {
        if ($statusCode >= 400) {
            wp_send_json_error($data, $statusCode);
        } else {
            wp_send_json_success($data, $statusCode);
        }
    }
}
