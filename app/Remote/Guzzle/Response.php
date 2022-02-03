<?php

namespace Wappointment\Remote\Guzzle;

use Wappointment\Remote\InterfaceResponse;

class Response implements InterfaceResponse
{
    public $response = null;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function getContent()
    {
        return $this->response->getBody()->getContents();
    }

    public function getHeaderLine($headerName)
    {
        return $this->response->getHeaderLine($headerName);
    }
}
