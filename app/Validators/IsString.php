<?php

namespace Wappointment\Validators;

class IsString extends \Rakit\Validation\Rule
{

    protected $message = ":attribute is not valid";

    public function check($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN\s_-]+$/u', $value) > 0;
    }
}
