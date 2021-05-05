<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\SingleDayTime;

final class SingleDayTimeIntervalFactory
{
    /**
     * @param \DateTime|string $leftElement
     * @param bool $leftState
     * @param \DateTime|string $rightElement
     * @param bool $rightState
     * @return SingleDayTimeInterval
     */
    public static function create($leftElement, bool $leftState, $rightElement, bool $rightState) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeInterval
    {
        return new \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeInterval(\Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundaryFactory::create($leftElement, $leftState), \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundaryFactory::create($rightElement, $rightState));
    }
}
