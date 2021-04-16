<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\Integer;

final class IntegerBoundaryFactory
{
    /**
     * @param int $value
     * @param bool $state
     * @return IntegerBoundary
     */
    public static function create(int $value, bool $state) : \Wappointment\Achse\Math\Interval\Integer\IntegerBoundary
    {
        return new \Wappointment\Achse\Math\Interval\Integer\IntegerBoundary(new \Wappointment\Achse\Math\Interval\Integer\Integer($value), $state);
    }
}
