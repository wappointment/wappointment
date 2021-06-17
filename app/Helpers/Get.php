<?php

namespace Wappointment\Helpers;

class Get
{

    public static function list($listName, $directory = false)
    {
        if (strlen($listName) > 60) {
            throw new \WappointmentException("File does not exist ", 1);
        }
        $directory = $directory === false ? dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Lists' . DIRECTORY_SEPARATOR : $directory;
        $filename =  $directory . $listName . '.php';
        if (!file_exists($filename)) {
            throw new \WappointmentException("File does not exist " . $filename, 1);
        }
        return include($filename);
    }
}
