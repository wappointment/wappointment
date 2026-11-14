<?php
declare(strict_types=1);

namespace Wappointment\Http\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class Min implements Constraint
{
    public function __construct(private int|float $value) {}

    public function rules(): array
    {
        return ['minimum' => $this->value];
    }
}
