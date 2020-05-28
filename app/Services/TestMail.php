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
            case 'sendgrid':
                $validator = 'validateSendGridApi';
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
            if ($mailerConfig['method'] && !empty($mailerConfig['wpmail_html'])) {
                $result = (new MailService($mailerConfig))->sendFast(
                    "Wappointment's test email",
                    '<h2>Yay! Emails are working!</h2>',
                    sanitize_email($recipient),
                    [sanitize_email($mailerConfig['from_address']) => sanitize_text_field($mailerConfig['from_name'])],
                    'text/html'
                );
            } else {
                $result = (new MailService($mailerConfig))->sendFast(
                    "Wappointment's test email",
                    'Yay! Emails are working!',
                    sanitize_email($recipient),
                    [sanitize_email($mailerConfig['from_address']) => sanitize_text_field($mailerConfig['from_name'])]
                );
            }
        } catch (\Exception $e) {
            $result = [];
            $result['error'] = $e->getMessage();
        }
        return $result;
    }

    public static function validateSendGridApi($mailerConfig)
    {
        $validator = new \Rakit\Validation\Validator;
        $validator->setMessages([
            'sgkey' => 'Enter a valid SendGrid API key',
        ]);

        $validationRules = [
            'sgkeyname' => 'required',
            'sgkey' => 'required',
        ];

        $validation = $validator->make($mailerConfig, $validationRules);
        $validation->validate();

        if ($validation->fails()) {
            self::$errors = $validation->errors()->toArray();
        } else {
            return true;
        }
    }

    public static function validateMailgunApi($mailerConfig)
    {
        $validator = new \Rakit\Validation\Validator;
        $validator->setMessages([
            'mgkey' => 'Enter a valid Mailgun API key',
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
