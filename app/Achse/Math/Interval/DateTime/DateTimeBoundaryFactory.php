<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTime;

use DateTimeInterface;
/**
 * @deprecated Use DateTimeImmutable, always!
 */
final class DateTimeBoundaryFactory
{
    /**
     * @param DateTimeInterface $element
     * @param bool $state
     * @return DateTimeBoundary
     */
    public static function create(\DateTimeInterface $element, bool $state) : \Wappointment\Achse\Math\Interval\DateTime\DateTimeBoundary
    {
        return new \Wappointment\Achse\Math\Interval\DateTime\DateTimeBoundary(\Wappointment\Achse\Math\Interval\DateTime\DateTime::from($element), $state);
    }
}
