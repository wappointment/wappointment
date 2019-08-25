<?php

namespace Wappointment\Validators;

class RequiredIfHas extends \Rakit\Validation\Rules\Required
{
    protected $implicit = true;

    protected $message = ':attribute is required';

    public function fillParameters(array $params)
    {
        $this->params['field'] = array_shift($params);
        $this->params['values'] = $params;
        return $this;
    }

    public function check($value)
    {
        $this->requireParameters(['field', 'values']);

        $anotherAttribute = $this->parameter('field');
        $definedValues = $this->parameter('values');
        $anotherValues = $this->getAttribute()->getValue($anotherAttribute);

        $validator = $this->validation->getValidator();
        $required_validator = $validator('required');

        if (is_array($anotherValues) && count($anotherValues) > 0) {
            foreach ($anotherValues as $anotherValue) {
                if (in_array($anotherValue, $definedValues)) {
                    $this->setAttributeAsRequired();
                    return $required_validator->check($value, []);
                }
            }
        }

        return true;
    }
}
