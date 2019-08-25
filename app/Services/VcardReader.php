<?php

namespace Wappointment\Services;

class VcardReader extends \Sabre\VObject\Reader
{
    public static function read($data, $options = 0, $charset = 'UTF-8')
    {
        $parser = new \Wappointment\Services\Parser\MimeDir();
        $parser->setCharset($charset);

        $result = $parser->parse($data, $options);

        return $result;
    }
}
