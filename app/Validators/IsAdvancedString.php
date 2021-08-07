<?php

namespace Wappointment\Validators;

class IsAdvancedString extends \Rakit\Validation\Rule
{

    protected $message = ":attribute is using forbidden characters";

    public function check($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN\s\'\+\?\=\¿\!\¡\"\%\&\$\(\)\[\]\*\´\,\`\;\:\.\@\/\~\#\^-]+$/u', $value) > 0;
    }
}
