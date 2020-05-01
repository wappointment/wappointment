<?php

namespace Wappointment\Services;

class Lock
{
    public $fp = null;
    public $lock_name = '';
    public $lock_timeout = 60;

    public function __construct($lock_file = 'wappo.lock')
    {
        $this->lock_name = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $lock_file;
    }

    public function alreadySet()
    {

        if (file_exists($this->lock_name) && time() < filemtime($this->lock_name) + $this->lock_timeout) {
            return true;
        }

        return false;
    }

    public function set()
    {
        fopen($this->lock_name, 'w+');
    }

    public function release()
    {
        unlink($this->lock_name);
    }
}
