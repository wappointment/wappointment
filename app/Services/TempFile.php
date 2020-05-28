<?php

namespace Wappointment\Services;

class TempFile
{
    protected $file = '';
    protected $name = '';
    protected $handler;

    public function __construct($file_name = '')
    {
        $this->name = empty($file_name) ? uniqid('temp') . '.txt' : $file_name;
        $this->file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->name;
        $this->setHandler();
    }

    public function getPath()
    {
        return $this->file;
    }

    public function write($content)
    {
        fwrite($this->handler, $content);
    }

    public function setHandler()
    {
        $this->handler = fopen($this->file, 'w+');
    }

    public function release()
    {
        unlink($this->file);
    }
}
