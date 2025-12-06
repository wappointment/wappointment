<?php

namespace Wappointment\Services;
// phpcs:ignoreFile
class Ping
{
    public $domain;
    public $port;
    public $errstr;
    public $errno;
    public $timeout;

    public function __construct($domain, $port = 80, $timeout = 10, $errstr = '', $errno = false)
    {
        $this->domain = $domain;
        $this->port = $port;
        $this->errno = &$errstr;
        $this->errstr = &$errno;
        $this->timeout = $timeout;
    }

    public function run()
    {
        $starttime = microtime(true);
        $file = @fsockopen($this->domain, $this->port, $this->errno, $this->errstr, $this->timeout);
        $stoptime = microtime(true);
        $status = 0;

        if (!$file) {
            $status = -1;
        } else {
            fclose($file);
            $status = ($stoptime - $starttime) * 1000;
            $status = floor($status);
        }
        return $status;
    }
}
