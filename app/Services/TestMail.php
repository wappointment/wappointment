<?php

namespace Wappointment\Services;

use Wappointment\Services\Mail as MailService;
use Wappointment\Validators\IsSmtp;

class TestMail
{
    public static $errors = [];

    public static function send($mailerConfig, $recipient)
    {
        switch ($mailerConfig['method']) {
            case 'wpmail':
                $validator = false;
                break;
            case 'mailgun':
                $validator = 'validateMailgunApi';
                break;
            case 'smtp':
                $validator = 'validateSMTP';
                break;
            default:
                return false;
        }
        if ($validator !== false) {
            self::$validator($mailerConfig);
            if (!empty(self::$errors)) {
                return self::$errors;
            }
        }

        try {
            $result = (new MailService($mailerConfig))->sendFast(
                "Wappointment's test email",
                'Yay! Emails are working!',
                $recipient,
                [$mailerConfig['from_address'] => $mailerConfig['from_name']]
            );
        } catch (\Exception $e) {
            $result = [];
            $result['error'] = $e->getMessage();
        }
        return $result;
    }

    public static function validateMailgunApi($mailerConfig)
    {
        $validator = new \Rakit\Validation\Validator;
        $validator->setMessages([
            'mgkey' => 'Enter a valid mailgun API key',
        ]);

        $validationRules = [
            'mgdomain' => 'required',
            'mgkey' => 'required|max:36|regex:/key-([A-Za-z0-9]){32}/',
        ];

        $validation = $validator->make($mailerConfig, $validationRules);
        $validation->validate();

        if ($validation->fails()) {
            self::$errors = $validation->errors()->toArray();
        } else {
            return true;
        }
    }

    public static function validateSMTP($mailerConfig)
    {
        $validator = new \Rakit\Validation\Validator;
        $validator->addValidator('is_smtp', new IsSmtp());

        $validator->setMessages([
            'username' => 'Username is required',
            'password' => 'Password is required',
            'port' => 'Port must be a number between 0 and 65535',
        ]);

        $validationRules = [
            'username' => 'required',
            'password' => 'required',
            'port' => 'required|min:0|max:65535',
            'host' => 'required|is_smtp',
        ];

        $validation = $validator->make($mailerConfig, $validationRules);
        $validation->validate();
        if ($validation->fails()) {
            self::$errors = $validation->errors()->toArray();
        } else {
            return true;
        }
    }
}
