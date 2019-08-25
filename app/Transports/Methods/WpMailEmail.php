<?php

namespace Wappointment\Transports\Methods;

use Wappointment\Transports\WpMail;

class WpMailEmail implements InterfaceEmailTransport
{
    public function setMethod($config)
    {
        return new WpMail($config);
    }
}
