<?php

namespace Wappointment\Services;

class CustomFields
{
    public static $core_fields = [
        [
            'name' => 'E-mail:',
            'type' => 'email',
            'namekey' => 'email',
            'always' => 1,
            'validations' => 'required|email',
            'errors' => [
                'email' => 'Email is not valid'
            ],
            'core' => true
        ],
        [
            'name' => 'Full Name:',
            'type' => 'input',
            'namekey' => 'name',
            'validations' => 'required|max:100',
            'errors' => [
                'max' => 'Name is too long'
            ],
            'core' => true
        ],

        [
            'name' => 'Phone:',
            'type' => 'phone',
            'namekey' => 'phone',
            'validations' => 'required|is_phone',
            'errors' => [
                'is_phone' => 'Phone number is not valid'
            ],
            'core' => true
        ],
        [
            'name' => 'Skype username:',
            'type' => 'input',
            'namekey' => 'skype',
            'validations' => 'required|regex:/^[a-zA-Z][a-zA-Z0-9.\-_]{5,31}$/',
            'errors' => [
                'regex' => 'Skype username is not valid'
            ],
            'core' => true
        ],

    ];


    public static $validationRules = [
        'namekey' => '',
        'name' => 'required',
        'required' => '',
        'type' => 'required',
        'values' => 'required_if:type,checkboxes,select,radios',
        'is_address' => '',
    ];


    public static function get()
    {
        return self::$core_fields;
    }
}
