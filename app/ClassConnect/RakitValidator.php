<?php

namespace Wappointment\ClassConnect;

use Wappointment\Validators\HasValues;
use Wappointment\Validators\IsString;
use Wappointment\Validators\RequiredIfHas;

class RakitValidator extends \Rakit\Validation\Validator
{
    public function __construct(array $messages = [])
    {
        parent::__construct($messages);
        $this->addValidator('hasvalues', new HasValues);
        $this->addValidator('required_if_has', new RequiredIfHas);
        $this->addValidator('is_string', new IsString);
    }
}
