<?php
declare(strict_types=1);

namespace Wappointment\Http\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class CollectionOf
{
    /** @param class-string|string $type Data class or scalar type name. */
    public function __construct(public readonly string $type, public readonly ?int $min = null) {}
}
