<?php

namespace Wappointment\System;

class Container
{
    private $bindings = [];

    public function bind($bindingName, $instance)
    {
        $this->bindings[] = [
            'name' => $bindingName,
            'instance' => $instance,
        ];
    }

    public function resolve($bindingName)
    {
        return array_first($this->bindings, fn($item) => $item['name'] === $bindingName)['instance'] ?? null;
    }

}
