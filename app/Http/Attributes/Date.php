<?php
declare(strict_types=1);

namespace Wappointment\Http\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class Date implements Constraint
{
    public function rules(): array
    {
        return ['format' => 'date'];
    }
}
