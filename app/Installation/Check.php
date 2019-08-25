<?php

namespace Wappointment\Installation;

class Check
{
    public function getErrors()
    {
        return array_merge(
            (new \Wappointment\Installation\Checks\Php())->getErrors(),
            (new \Wappointment\Installation\Checks\Database())->getErrors(),
            (new \Wappointment\Installation\Checks\Files())->getErrors()
        );
    }
}
