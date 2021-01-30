<?php

namespace Wappointment\Validators\HttpRequest;

class BookingAdmin extends Booking
{

    public static $startKey = 'start';

    public function generateValidation($inputs)
    {
        $this->validationRulesArray = [
            'name' => 'required_if:email',
            'email' => 'present|email',
            'start' => 'required|min:' . time(),
            'end' => 'required|min:' . time(),
            'ctz' => '',
            'location' => 'required|min:1',
            'service' => 'required|min:1',
            'duration' => 'required|min:5',
            'clientid' => ''
        ];
    }
}
