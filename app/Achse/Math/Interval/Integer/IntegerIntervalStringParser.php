<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\Integer;

use Wappointment\Achse\Math\Interval\Boundary;
use Wappointment\Achse\Math\Interval\Interval;
use Wappointment\Achse\Math\Interval\IntervalStringParser;
final class IntegerIntervalStringParser extends \Wappointment\Achse\Math\Interval\IntervalStringParser
{
    /**
     * @param string $string
     * @return IntegerInterval
     */
    public static function parse(string $string) : \Wappointment\Achse\Math\Interval\Interval
    {
        list($left, $right) = self::parseBoundariesStringsFromString($string);
        return new \Wappointment\Achse\Math\Interval\Integer\IntegerInterval(self::parseBoundary($left), self::parseBoundary($right));
    }
    /**
     * @param string $string
     * @return IntegerBoundary
     */
    protected static function parseBoundary(string $input) : \Wappointment\Achse\Math\Interval\Boundary
    {
        list($elementString, $state) = self::parseBoundaryDataFromString($input);
        return new \Wappointment\Achse\Math\Interval\Integer\IntegerBoundary(\Wappointment\Achse\Math\Interval\Integer\Integer::fromString($elementString), $state);
    }
}
