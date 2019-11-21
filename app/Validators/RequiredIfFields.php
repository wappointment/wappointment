<?php

namespace Wappointment\Validators;


class RequiredIfFields extends \Rakit\Validation\Rules\Required
{
    protected $implicit = true;

    protected $message = "The :attribute is fully required";

    public function fillParameters(array $params)
    {
        $this->params['field'] = array_shift($params);
        $this->params['values'] = $params;
        return $this;
    }

    public function check($value)
    {
        $this->requireParameters(['field', 'values']);

        if ($this->isRequired($this->parameter('values'))) {

            $validator = $this->validation->getValidator();
            $required_validator = $validator('required');

            $this->setAttributeAsRequired();
            return $required_validator->check($value, []);
        }

        return true;
    }

    protected function isRequired($definedValues)
    {
        $requiresCheck = false;
        $conditions = [];
        foreach ($definedValues as $key => $defvalue) {
            $conditions[] = explode(';', str_replace(['[', ']'], '', $defvalue));
        }
        $cdd = [];
        foreach ($conditions as $key => $condition) {
            $subvalue = $this->getAttribute()->getValue($condition[0]);
            $cdd[$condition[0]] = ['value' => $subvalue, 'cond' => $condition[1]];
            if (($condition[1] == 'empty' && !empty($subvalue)) || ($condition[1] == 'true' && (bool) $subvalue)
            ) {
                $requiresCheck = true;
            }
        }
        return $requiresCheck;
    }
}
