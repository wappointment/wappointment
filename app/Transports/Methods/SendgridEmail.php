<?php

namespace Wappointment\Transports\Methods;

use Wappointment\Transports\Sendgrid;

class SendgridEmail implements InterfaceEmailTransport
{
    public function setMethod($config)
    {
        return new Sendgrid(
            new \GuzzleHttp\Client(['connect_timeout' => 60]),
            $config['sgkeyname'],
            $config['sgkey']
        );
    }
}
