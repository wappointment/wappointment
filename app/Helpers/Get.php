<?php

namespace Wappointment\Helpers;

class Get
{
    protected static function getFilePath($listName, $directory, $extension)
    {
        if (strlen($listName) > 60) {
            throw new \WappointmentException("File does not exist ", 1);
        }
        $filename =  $directory . $listName . $extension;
        if (!file_exists($filename)) {
            throw new \WappointmentException("File does not exist " . $filename, 1);
        }
        return $filename;
    }

    protected static function getBaseDir($dirname)
    {
        return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR;
    }

    public static function getDir($dirname, $directory)
    {
        return $directory === false ? static::getBaseDir($dirname) : $directory;
    }

    public static function list($listName, $directory = false)
    {
        return include(static::getFilePath($listName, static::getDir('Lists', $directory), '.php'));
    }

    public static function style($listName, $directory = false)
    {
        return '<style>' . file_get_contents(static::getFilePath($listName, static::getDir('Styles', $directory), '.css')) . '</style>';
    }
}
