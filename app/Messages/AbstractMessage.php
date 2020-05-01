<?php

namespace Wappointment\Messages;

abstract class AbstractMessage implements InterfaceMessage
{
    public $body = '';
    public function __construct(...$params)
    {
        $this->loadContent(...$params);
    }

    protected function parseBody()
    {
        if (method_exists($this, 'replaceTags')) {
            $this->replaceTags();
        }
    }

    public function renderMessage()
    {
        return [
            'body' => $this->renderBody(),
        ];
    }

    public function renderBody()
    {
        return $this->finalWrap();
    }

    public function finalWrap()
    {

        $this->parseBody();

        return $this->body;
    }
}
