<?php
declare(strict_types=1);

namespace Wappointment\Http\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class Max implements Constraint
{
    public function __construct(private int|float $value) {}

    public function rules(): array
    {
        return ['maximum' => $this->value];
    }
}
