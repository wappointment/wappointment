<?php

namespace Wappointment\Messages;

abstract class AbstractMessage implements InterfaceMessage
{
    public $body = '';
    public function __construct(...$params)
    {
        $this->loadEmail(...$params);
    }

    public function returnBody()
    {
        return $this->body;
    }

    protected function finalProcess()
    {
        if (method_exists($this, 'replaceTags')) {
            $this->replaceTags();
        }
    }
}
