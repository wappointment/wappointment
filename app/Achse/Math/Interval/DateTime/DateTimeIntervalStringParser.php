<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTime;

use Wappointment\Achse\Math\Interval\Boundary;
use Wappointment\Achse\Math\Interval\Interval;
use Wappointment\Achse\Math\Interval\IntervalStringParser;
/**
 * @deprecated Use DateTimeImmutable, always!
 */
final class DateTimeIntervalStringParser extends \Wappointment\Achse\Math\Interval\IntervalStringParser
{
    /**
     * @param string $string
     * @return DateTimeInterval
     */
    public static function parse(string $string) : \Wappointment\Achse\Math\Interval\Interval
    {
        list($left, $right) = self::parseBoundariesStringsFromString($string);
        return new \Wappointment\Achse\Math\Interval\DateTime\DateTimeInterval(self::parseBoundary($left), self::parseBoundary($right));
    }
    /**
     * @param string $string
     * @return DateTimeBoundary
     */
    protected static function parseBoundary(string $input) : \Wappointment\Achse\Math\Interval\Boundary
    {
        list($elementString, $state) = self::parseBoundaryDataFromString($input);
        /** @var DateTime $dateTime */
        $dateTime = \Wappointment\Achse\Math\Interval\DateTime\DateTime::createFromFormat(\DateTime::ATOM, $elementString);
        return new \Wappointment\Achse\Math\Interval\DateTime\DateTimeBoundary(\Wappointment\Achse\Math\Interval\DateTime\DateTime::from($dateTime), $state);
    }
}
