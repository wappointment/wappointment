<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTime;

use DateTimeInterface;
/**
 * @deprecated Use DateTimeImmutable, always!
 */
final class DateTimeIntervalFactory
{
    /**
     * @param DateTimeInterface $leftElement
     * @param bool $leftState
     * @param DateTimeInterface $rightElement
     * @param bool $rightState
     * @return DateTimeInterval
     */
    public static function create(\DateTimeInterface $leftElement, bool $leftState, \DateTimeInterface $rightElement, bool $rightState) : \Wappointment\Achse\Math\Interval\DateTime\DateTimeInterval
    {
        return new \Wappointment\Achse\Math\Interval\DateTime\DateTimeInterval(\Wappointment\Achse\Math\Interval\DateTime\DateTimeBoundaryFactory::create($leftElement, $leftState), \Wappointment\Achse\Math\Interval\DateTime\DateTimeBoundaryFactory::create($rightElement, $rightState));
    }
}
