<?php
declare(strict_types=1);

namespace Wappointment\Http\Attributes;

interface Constraint
{
    public function rules(): array;
}
