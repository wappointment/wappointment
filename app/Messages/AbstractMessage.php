<?php

namespace Wappointment\Messages;

abstract class AbstractMessage implements InterfaceMessage
{
    public $body = '';
    public $params = [];
    public function __construct($params)
    {
        $this->params = $params;
        $this->loadContent();
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
