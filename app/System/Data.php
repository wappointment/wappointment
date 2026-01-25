<?php
declare(strict_types=1);

namespace Wappointment\System;

abstract class Data
{
    public static function from(array $data): static
    {
        // Get constructor parameters
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            return new static();
        }
        
        $params = $constructor->getParameters();
        $args = [];
        
        foreach ($params as $param) {
            $name = $param->getName();
            $value = $data[$name] ?? null;
            
            // Get the parameter type
            $type = $param->getType();
            
            if ($type && !$type->isBuiltin()) {
                $typeName = $type->getName();
                
                // Check if the type has a static 'from' method
                if (method_exists($typeName, 'from')) {
                    try {
                        $value = $typeName::from($value ?? '');
                    } catch (\InvalidArgumentException $e) {
                        throw $e;
                    }
                }
            }
            
            // Handle default values
            if ($value === null && $param->isDefaultValueAvailable()) {
                $value = $param->getDefaultValue();
            }
            
            $args[] = $value;
        }
        
        return new static(...$args);
    }
    
    public static function fromWpRequest(\WP_REST_Request $request): static
    {
        $data = $request->get_json_params() ?? [];
        return static::from($data);
    }
    
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        
        $array = [];
        
        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $property->getValue($this);
            
            // Handle value objects with a value property
            if (is_object($value) && property_exists($value, 'value')) {
                $array[$name] = $value->value;
            }
            // Handle nested Data objects
            elseif ($value instanceof self) {
                $array[$name] = $value->toArray();
            }
            // Handle arrays of Data objects
            elseif (is_array($value)) {
                $array[$name] = array_map(
                    fn($item) => $item instanceof self ? $item->toArray() : $item,
                    $value
                );
            }
            else {
                $array[$name] = $value;
            }
        }
        
        return $array;
    }
}
