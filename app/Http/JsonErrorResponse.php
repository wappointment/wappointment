<?php
declare(strict_types=1);

namespace Wappointment\Http;

class JsonErrorResponse
{
    public static function send(string $message, int $statusCode = 400): void
    {
        JsonResponse::send(['error' => $message], $statusCode);
    }
}
