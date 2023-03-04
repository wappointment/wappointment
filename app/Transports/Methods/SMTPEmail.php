<?php

namespace Wappointment\Transports\Methods;

class SMTPEmail implements InterfaceEmailTransport
{
    public function setMethod($config)
    {
        return (new \WappoSwift_SmtpTransport($config['host'], $config['port'], $config['encryption']))
            ->setUsername($config['username'])
            ->setPassword($config['password']);
    }
}
