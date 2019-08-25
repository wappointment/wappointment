<?php

namespace Wappointment\Validators\HttpRequest;

interface InterfaceProcessor
{
    public function get(string $field);

    public function getData(): array;
}
