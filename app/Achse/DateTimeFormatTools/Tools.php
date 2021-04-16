<?php

namespace Wappointment\Achse\DateTimeFormatTools;

class Tools
{
    const UNSAFE_SYMBOLS = ['d' => 'j', 'm' => 'n'];
    /**
     * @param string[] $symbols
     * @param string $pattern
     * @return bool
     */
    public static function isAnyOfSymbolsInPattern(array $symbols, $pattern)
    {
        foreach ($symbols as $symbol) {
            if (static::isSymbolInPattern($pattern, $symbol)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * @param string $pattern
     * @param string $symbol
     * @return bool
     */
    public static function isSymbolInPattern($pattern, $symbol)
    {
        if ($symbol === '') {
            return FALSE;
        }
        $pattern = \Wappointment\Achse\DateTimeFormatTools\Tools::removeBackSlashedFromPattern($pattern);
        $position = \strpos($pattern, $symbol);
        return $position !== FALSE && ($position === 0 || $pattern[$position - 1] !== '\\');
    }
    /**
     * @param string $pattern
     * @throws NonSafePatternDetectedException
     */
    public static function checkPattern($pattern)
    {
        foreach (self::UNSAFE_SYMBOLS as $symbol => $recommended) {
            if (static::isSymbolInPattern($pattern, $symbol)) {
                throw new \Wappointment\Achse\DateTimeFormatTools\NonSafePatternDetectedException("Potentially unsafe symbol found in Pattern: '{$symbol}'. Use '{$recommended}' " . "if possible set SaveSymbol mode OFF.");
            }
        }
    }
    /**
     * @param string $pattern
     * @return string
     */
    protected static function removeBackSlashedFromPattern($pattern)
    {
        return \str_replace('\\\\', '', $pattern);
    }
}
