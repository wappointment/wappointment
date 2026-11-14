<?php
declare(strict_types=1);

namespace Wappointment\Http\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class MapInputName
{
    public function __construct(public readonly string $name) {}
}
