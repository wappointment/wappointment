<?php

namespace Wappointment\Validators\HttpRequest;

interface InterfaceProcessor
{
    public function get($field);

    public function getData(): array;
}
