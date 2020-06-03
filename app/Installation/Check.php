<?php

namespace Wappointment\Installation;

class Check
{
    public function getErrors()
    {
        $errors = array_merge(
            (new \Wappointment\Installation\Checks\Php())->getErrors(),
            (new \Wappointment\Installation\Checks\Database())->getErrors(),
            (new \Wappointment\Installation\Checks\Files())->getErrors()
        );
        if (empty($errors)) {
            return (new \Wappointment\Installation\Checks\DatabasePrivileges())->getErrors();
        }

        return $errors;
    }
}
