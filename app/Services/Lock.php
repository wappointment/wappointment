<?php

namespace Wappointment\Services;

class Lock extends AbstractFile
{
    protected $timeout = 60;

    public function alreadySet()
    {
        return file_exists($this->path) && time() < filemtime($this->path) + $this->timeout;
    }

    public function set()
    {
        $this->setHandler();
    }

    protected function setName($name)
    {
        return $this->name = empty($name) ? 'wappo.lock' : $name;
    }
}
