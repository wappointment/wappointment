<?php

namespace Wappointment\Services;
// phpcs:ignoreFile
abstract class AbstractFile
{
    protected $path = '';
    protected $name = '';
    protected $handler;

    public function __construct($name = '')
    {
        $this->name = $this->setName($name);
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->name;
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    public function write($content)
    {
        fwrite($this->handler, $content);
    }

    public function setHandler()
    {
        $this->handler = fopen($this->path, 'w+');
    }

    public function release()
    {
        if (file_exists($this->path)) {
            wp_delete_file($this->path);
        }
    }

    protected function setName($name)
    {
        return $this->name = $name;
    }
}
