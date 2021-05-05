<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTimeImmutable;

use Wappointment\Achse\Math\Interval\Boundary;
use Wappointment\Achse\Math\Interval\Interval;
use Wappointment\Achse\Math\Interval\IntervalStringParser;
final class DateTimeImmutableIntervalStringParser extends \Wappointment\Achse\Math\Interval\IntervalStringParser
{
    /**
     * @param string $string
     * @return DateTimeImmutableInterval
     */
    public static function parse(string $string) : \Wappointment\Achse\Math\Interval\Interval
    {
        list($left, $right) = self::parseBoundariesStringsFromString($string);
        return new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval(self::parseBoundary($left), self::parseBoundary($right));
    }
    /**
     * @param string $string
     * @return DateTimeImmutableBoundary
     */
    protected static function parseBoundary(string $input) : \Wappointment\Achse\Math\Interval\Boundary
    {
        list($elementString, $state) = self::parseBoundaryDataFromString($input);
        /** @var DateTimeImmutable $dateTime */
        $dateTime = \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable::createFromFormat(\DateTime::ATOM, $elementString);
        return new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundary(\Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable::from($dateTime), $state);
    }
}
