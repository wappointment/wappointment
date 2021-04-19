<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\Integer;

final class IntegerIntervalFactory
{
    /**
     * @param int $leftElement
     * @param bool $leftState
     * @param int $rightElement
     * @param bool $rightState
     * @return IntegerInterval
     */
    public static function create(int $leftElement, bool $leftState, int $rightElement, bool $rightState) : \Wappointment\Achse\Math\Interval\Integer\IntegerInterval
    {
        return new \Wappointment\Achse\Math\Interval\Integer\IntegerInterval(\Wappointment\Achse\Math\Interval\Integer\IntegerBoundaryFactory::create($leftElement, $leftState), \Wappointment\Achse\Math\Interval\Integer\IntegerBoundaryFactory::create($rightElement, $rightState));
    }
}
