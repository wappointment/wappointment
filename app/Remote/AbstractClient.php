<?php

namespace Wappointment\Remote;

abstract class AbstractClient
{
    public $headers = [];

    public function getParams()
    {
        return $this->params;
    }

    public function hasHeaders()
    {
        return !empty($this->headers);
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function failed()
    {
        return $this->response->getStatusCode() != 200;
    }

    public function headerIsEqual($headerName, $valueTest)
    {
        $headerValue = $this->hasHeader($headerName);
        return !empty($headerValue) && strpos($headerValue, $valueTest) !== false;
    }

    public function hasHeader($headerName)
    {
        return $this->response->getHeaderLine($headerName);
    }
}
