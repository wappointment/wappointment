<?php
declare(strict_types=1);

namespace Wappointment\Http\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class Length implements Constraint
{
    public function __construct(private int $min = 0, private int $max = PHP_INT_MAX) {}

    public function rules(): array
    {
        return ['minLength' => $this->min, 'maxLength' => $this->max];
    }
}
