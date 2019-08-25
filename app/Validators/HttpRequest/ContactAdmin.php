<?php

namespace Wappointment\Validators\HttpRequest;

class ContactAdmin extends AbstractProcessor
{
    protected $autoResponse = true;
    protected function validationMessages()
    {
        return [
            'email' => 'Your email is not valid',
            'message' => 'Your message is empty',
        ];
    }

    protected function validationRules()
    {
        return [
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
            'options' => '',
        ];
    }
}
