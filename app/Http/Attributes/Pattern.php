<?php
declare(strict_types=1);

namespace Wappointment\Http\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class Pattern implements Constraint
{
    public function __construct(private string $value) {}

    public function rules(): array
    {
        return ['pattern' => $this->value];
    }
}
