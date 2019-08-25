<?php

namespace Wappointment\Transports\Methods;

use WappoSwift_SmtpTransport;

class SMTPEmail implements InterfaceEmailTransport
{
    public function setMethod($config)
    {
        return (new WappoSwift_SmtpTransport($config['host'], $config['port']))
        ->setUsername($config['username'])
        ->setPassword($config['password']);
    }
}
