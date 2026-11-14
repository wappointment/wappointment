<?php
declare(strict_types=1);

namespace Wappointment\Http\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class In implements Constraint
{
    public function __construct(private array $values) {}

    public function rules(): array
    {
        return ['enum' => $this->values];
    }
}
