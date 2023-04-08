<?php

namespace Wappointment\Validators;

use Wappointment\Services\Ping;

class IsSmtp extends \Rakit\Validation\Rules\Required
{
    protected $implicit = true;

    protected $message = 'Cannot connect to SMTP server with selected port';

    public function check($value): bool
    {
        if (empty($value)) {
            return false;
        }

        $ports = ['25', '2525', '465', '587'];
        $portTested = $this->getAttribute()->getValue('port');
        if ($portTested <= 65535 || $portTested >= 0 && !in_array($portTested, $ports)) {
            $ports[] = $portTested;
        }

        if ((new Ping($value, $portTested, 5))->run() !== -1) {
            return true;
        }

        $this->message .= ' ' . $portTested;

        return false;
    }
}
