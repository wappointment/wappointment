<?php

namespace Wappointment\Jobs;

abstract class AbstractTransportableJob implements JobInterface
{
    public $transport = null;

    protected $params = [];

    abstract public function setTransport();

    public function __construct($params)
    {
        $this->setTransport();
        $this->parseParams($params);
    }

    protected function parseParams($params)
    {
        $this->params = $params;
    }

    protected function generateContent()
    {
        $contentClass = static::CONTENT;

        return new $contentClass($this->params);
    }
}
