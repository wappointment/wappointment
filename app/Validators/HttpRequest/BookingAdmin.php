<?php

namespace Wappointment\Validators\HttpRequest;

class BookingAdmin extends Booking
{
    public static $startKey = 'start';

    public function generateValidation($inputs)
    {
        $this->validationRulesArray = [
            'name' => 'required_if:email',
            'start' => 'required|min:' . time(),
            'end' => 'required|min:' . time(),
            'ctz' => '',
            'location' => 'required|min:1',
            'service' => 'required|min:1',
            'duration' => 'required|min:5',
            'clientid' => '',
            'staff_id' => '',
            'phone' => '',
            'skype' => '',
            'recurrent' => '',
            'page' => ''
        ];
        if ($inputs['email'] !== '') {
            $this->validationRulesArray['email'] = 'email';
        }

        $this->validationRulesArray = $this->applyMoreRules();
    }
}
