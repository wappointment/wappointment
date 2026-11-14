<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;
use Wappointment\ValueObjects\Email;

class ClientCreateRequestData extends RequestData
{
    public function __construct(
        public readonly string $name,
        public readonly Email $email,
        public readonly array $options = [],
    ) {}

    public static function schema(): array
    {
        return ApiSchemas::object([
            'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100],
            'email' => ['type' => 'string', 'format' => 'email', 'maxLength' => 191],
            'options' => ApiSchemas::object([], [], true),
        ], ['name', 'email']);
    }
}
