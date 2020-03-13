<?php

namespace Wappointment\Transports\Methods;

use Wappointment\Transports\Mailgun;

class MailgunEmail implements InterfaceEmailTransport
{
    public function setMethod($config)
    {
        return new Mailgun(
            new \GuzzleHttp\Client(['connect_timeout' => 60]),
            $config['mgdomain'],
            $config['mgkey'],
            isset($config['mgarea']) && $config['mgarea'] == 'eu' ? $config['mgarea'] : ''
        );
    }
}
