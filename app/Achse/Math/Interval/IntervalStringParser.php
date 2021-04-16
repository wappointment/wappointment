<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval;

use LogicException;
abstract class IntervalStringParser
{
    /**
     * @var bool[]
     */
    private static $parenthesisToStateTranslationTable = [\Wappointment\Achse\Math\Interval\Boundary::STRING_OPENED_LEFT => \Wappointment\Achse\Math\Interval\Boundary::OPENED, \Wappointment\Achse\Math\Interval\Boundary::STRING_OPENED_RIGHT => \Wappointment\Achse\Math\Interval\Boundary::OPENED, \Wappointment\Achse\Math\Interval\Boundary::STRING_CLOSED_LEFT => \Wappointment\Achse\Math\Interval\Boundary::CLOSED, \Wappointment\Achse\Math\Interval\Boundary::STRING_CLOSED_RIGHT => \Wappointment\Achse\Math\Interval\Boundary::CLOSED];
    /**
     * @param string $string
     * @return Interval
     */
    public static function parse(string $string) : \Wappointment\Achse\Math\Interval\Interval
    {
        throw new \LogicException('Not implemented in this abstract class. Implement this in child.');
    }
    /**
     * @param string $string
     * @return string[]
     * @throws IntervalParseErrorException
     */
    protected static function parseBoundariesStringsFromString(string $string) : array
    {
        $boundaries = \explode(',', $string);
        if (\count($boundaries) != 2) {
            throw new \Wappointment\Achse\Math\Interval\IntervalParseErrorException('Unexpected number of boundaries. Check if given string contains only one delimiter ' . '(' . \Wappointment\Achse\Math\Interval\Interval::STRING_DELIMITER . ').');
        }
        return \array_map('trim', $boundaries);
    }
    /**
     * @param string $string
     * @return array
     * @throws IntervalParseErrorException
     */
    protected static function parseBoundaryDataFromString(string $string) : array
    {
        $letters = \preg_split('//u', $string, -1, \PREG_SPLIT_NO_EMPTY);
        if (\count($letters) < 2) {
            throw new \Wappointment\Achse\Math\Interval\IntervalParseErrorException(\sprintf('Boundary part \'%s\' is too short. It must be at leas 2 character long. Example: \'%s1\' or \'9%s\'.', $string, \Wappointment\Achse\Math\Interval\Boundary::STRING_OPENED_LEFT, \Wappointment\Achse\Math\Interval\Boundary::STRING_CLOSED_RIGHT));
        }
        return self::getElementAndStateData($letters);
    }
    /**
     * @param string[] $letters
     * @return array
     * @throws IntervalParseErrorException
     */
    protected static function getElementAndStateData(array $letters) : array
    {
        if (($firstCharacter = \reset($letters)) === FALSE || ($lastCharacter = \end($letters)) === FALSE) {
            throw new \Wappointment\Achse\Math\Interval\IntervalParseErrorException('No letters given.');
        }
        /** @var string $firstCharacter */
        /** @var string $lastCharacter */
        if (self::isCharacterBoundaryType($firstCharacter)) {
            \array_shift($letters);
            $state = self::getTypeByCharacter($firstCharacter);
        } elseif (self::isCharacterBoundaryType($lastCharacter)) {
            \array_pop($letters);
            $state = self::getTypeByCharacter($lastCharacter);
        } else {
            throw new \Wappointment\Achse\Math\Interval\IntervalParseErrorException('Boundary type character not found.');
        }
        return [\implode('', $letters), $state];
    }
    /**
     * @param string $character
     * @return bool
     */
    protected static function isCharacterBoundaryType(string $character) : bool
    {
        return isset(self::$parenthesisToStateTranslationTable[$character]);
    }
    /**
     * @param string $character
     * @return bool
     */
    protected static function getTypeByCharacter(string $character) : bool
    {
        return self::$parenthesisToStateTranslationTable[$character];
    }
    /**
     * @param string $input
     * @return Boundary
     */
    protected static function parseBoundary(string $input) : \Wappointment\Achse\Math\Interval\Boundary
    {
        throw new \LogicException('Not implemented in this abstract class. Implement this in child.');
    }
}
