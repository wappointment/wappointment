<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\SingleDayTime;

use Wappointment\Achse\Math\Interval\Boundary;
use Wappointment\Achse\Math\Interval\Interval;
use Wappointment\Achse\Math\Interval\IntervalStringParser;
final class SingleDayTimeIntervalStringParser extends \Wappointment\Achse\Math\Interval\IntervalStringParser
{
    /**
     * @param string $string
     * @return SingleDayTimeInterval
     */
    public static function parse(string $string) : \Wappointment\Achse\Math\Interval\Interval
    {
        list($left, $right) = self::parseBoundariesStringsFromString($string);
        return new \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeInterval(self::parseBoundary($left), self::parseBoundary($right));
    }
    /**
     * @param string $string
     * @return SingleDayTimeBoundary
     */
    protected static function parseBoundary(string $input) : \Wappointment\Achse\Math\Interval\Boundary
    {
        list($elementString, $state) = self::parseBoundaryDataFromString($input);
        return new \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundary(\Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime::from($elementString), $state);
    }
}
