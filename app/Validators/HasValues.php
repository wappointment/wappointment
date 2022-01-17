<?php

namespace Wappointment\Validators;

class HasValues extends \Rakit\Validation\Rule
{
    protected $message = ':attribute :value has been used';

    public function fillParameters(array $params)
    {
        if (count($params) == 1 && is_array($params[0])) {
            $params = $params[0];
        }
        $this->params['allowed_values'] = $params;
        return $this;
    }

    public function check($values)
    {
        $this->requireParameters(['allowed_values']);

        $allowed_values = $this->parameter('allowed_values');

        foreach ($values as $value) {
            if (!in_array($value, $allowed_values)) {
                return false;
            }
        }
        return true;
    }
}
