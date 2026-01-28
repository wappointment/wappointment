<?php
declare(strict_types=1);

namespace Wappointment\Http;

class JsonResponse
{
    public static function success(array $data, int $statusCode = 200): void
    {
        wp_send_json_success($data, $statusCode);
    }

    public static function error(array $data, int $statusCode = 400): void
    {
        wp_send_json_error($data, $statusCode);
    }

    public static function send(array $data, int $statusCode = 200): void
    {
        if ($statusCode >= 400) {
            self::error($data, $statusCode);
        } else {
            self::success($data, $statusCode);
        }
    }
}
