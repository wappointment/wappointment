<?php

namespace Wappointment\Remote;

interface InterfaceResponse
{
    public function __construct($response);
    public function getStatusCode();
    public function getHeaderLine($headerName);
    public function getContent();
}
