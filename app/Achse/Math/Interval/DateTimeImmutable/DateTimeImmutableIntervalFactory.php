<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTimeImmutable;

use DateTimeInterface;
final class DateTimeImmutableIntervalFactory
{
    /**
     * @param DateTimeInterface $leftElement
     * @param bool $leftState
     * @param DateTimeInterface $rightElement
     * @param bool $rightState
     * @return DateTimeImmutableInterval
     */
    public static function create(\DateTimeInterface $leftElement, bool $leftState, \DateTimeInterface $rightElement, bool $rightState) : \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval
    {
        return new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval(\Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundaryFactory::create($leftElement, $leftState), \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundaryFactory::create($rightElement, $rightState));
    }
}
