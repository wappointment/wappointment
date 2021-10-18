<?php

namespace Wappointment\Services;

use Wappointment\Helpers\Get;

class Phone
{
    public $phone = '';
    /**
     * Constructor
     *
     * @param String $phone
     */
    public function __construct($phone)
    {
        $this->phone = str_replace(' ', '', $phone);
    }
    /**
     * validating e164 phone number
     *
     * @param String $phone
     * @return void
     */
    public function validate()
    {
        return (bool)preg_match('/^\+?[1-9]\d{1,14}$/', $this->phone, $output_array);
    }

    public function countryIsAccepted($countries)
    {
        $countries_prefixes = Get::list('countries_prefixes');
        foreach ($countries as $country_isocode) {
            if (strpos($this->phone, $countries_prefixes[$country_isocode]) !== false) {
                return true;
            }
        }
        return false;
    }
}
