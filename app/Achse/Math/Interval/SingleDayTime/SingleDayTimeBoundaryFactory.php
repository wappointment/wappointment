<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\SingleDayTime;

use DateTimeInterface;
final class SingleDayTimeBoundaryFactory
{
    /**
     * @param DateTimeInterface|string $element
     * @param bool $state
     * @return SingleDayTimeBoundary
     */
    public static function create($element, bool $state) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundary
    {
        return new \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundary(\Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime::from($element), $state);
    }
}
