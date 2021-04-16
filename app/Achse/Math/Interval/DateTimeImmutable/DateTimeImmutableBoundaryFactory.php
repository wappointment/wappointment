<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTimeImmutable;

use DateTimeInterface;
final class DateTimeImmutableBoundaryFactory
{
    /**
     * @param DateTimeInterface $element
     * @param bool $state
     * @return DateTimeImmutableBoundary
     */
    public static function create(\DateTimeInterface $element, bool $state) : \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundary
    {
        return new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundary(\Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable::from($element), $state);
    }
}
