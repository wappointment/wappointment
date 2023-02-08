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
        return $instance;
    }

    public function resolve($bindingName)
    {
        $result = array_first($this->bindings, function($item) use($bindingName) {
            return $item['name'] === $bindingName;
        });
        return $result['instance'] ?? null;
    }

}
