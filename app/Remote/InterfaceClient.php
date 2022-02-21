<?php

namespace Wappointment\Remote;

interface InterfaceClient
{
    public function get($url);
    public function post($url);
    public function getCalendar($url);
    public function getContent();
    public function headerIsEqual($headerName, $valueTest);
    public function hasHeader($headerName);
}
