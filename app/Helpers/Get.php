<?php

namespace Wappointment\Helpers;

class Get
{

    public static function list($listName)
    {
        if (strlen($listName) > 20) {
            throw new \WappointmentException("File does not exist ", 1);
        }
        $filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lists' . DIRECTORY_SEPARATOR . $listName . '.php';
        if (!file_exists($filename)) {
            throw new \WappointmentException("File does not exist " . $filename, 1);
        }
        return include($filename);
    }
}
