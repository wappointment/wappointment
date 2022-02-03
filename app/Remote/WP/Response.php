<?php

namespace Wappointment\Remote\WP;

use Wappointment\Remote\InterfaceResponse;

class Response implements InterfaceResponse
{
    public $response = null;

    public function __construct($response)
    {
        if (!is_array($response)) {
            throw new \WappointmentException("WP remote response error", 1);
        }
        $this->response = $response;
    }

    public function getStatusCode()
    {
        return (int)$this->response['response']['code'];
    }

    public function getContent()
    {
        return $this->response['body'];
    }

    public function getHeaderLine($headerName)
    {
        return isset($this->response['headers'][$headerName]) ? $this->response['headers'][$headerName] : false;
    }
}
