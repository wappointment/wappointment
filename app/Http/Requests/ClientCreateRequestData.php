<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\System\Data;
use Wappointment\ValueObjects\Email;

class ClientCreateRequestData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly Email $email,
        public readonly array $options = [],
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email->value,
            'options' => json_encode($this->options),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];
    }
}
