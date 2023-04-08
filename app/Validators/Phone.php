<?php

namespace Wappointment\Validators;

use Wappointment\Services\Phone as ServicesPhone;

class Phone extends \Rakit\Validation\Rule
{
    protected $message = ':attribute is not a valid Phone number';
    protected $countries = null;

    public function __construct($countriesAllowed)
    {
        $this->countries = $countriesAllowed;
    }

    public function check($value): bool
    {
        if (empty($value)) {
            return false;
        }
        $phone = new ServicesPhone($value);

        if ($phone->validate() === false) {
            return false;
        }

        if (!empty($this->countries) && !$phone->countryIsAccepted($this->countries)) {
            return false;
        }

        return true;
    }

}
