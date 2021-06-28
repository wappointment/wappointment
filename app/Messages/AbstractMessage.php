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
        $this->finalRemoveEmptyLines();
    }

    public function finalRemoveEmptyLines()
    {
        $this->body = str_replace('<p></p>', '', $this->body);
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
