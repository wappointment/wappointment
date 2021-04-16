<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTime;

use Wappointment\Achse\Comparable\IComparable;
use Wappointment\Achse\Math\Interval\Boundary;
use Wappointment\Achse\Math\Interval\Interval;
use Wappointment\Achse\Math\Interval\Utils;
/**
 * @deprecated Use DateTimeImmutable, always!
 */
final class DateTimeInterval extends \Wappointment\Achse\Math\Interval\Interval
{
    /**
     * @inheritdoc
     */
    public function __construct(\Wappointment\Achse\Math\Interval\Boundary $left, \Wappointment\Achse\Math\Interval\Boundary $right)
    {
        $this->validateBoundaryChecks($left, $right, \Wappointment\Achse\Math\Interval\DateTime\DateTimeBoundary::class);
        parent::__construct($left, $right);
    }
    /**
     * # Example:
     *
     *     This:  □□□□□□■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *
     * # False examples:
     *
     *     This:  □□□□□□■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□
     *
     *     This:  □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *     Other: □□□□□□■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□□□□□□□
     *
     * @param DateTimeInterval $other
     * @param string $precision
     * @return bool
     */
    public function isFollowedByWithPrecision(\Wappointment\Achse\Math\Interval\DateTime\DateTimeInterval $other, string $precision) : bool
    {
        if ($this->getLeft() > $other->getRight()) {
            // intentionally compares boundaries
            return FALSE;
        }
        /** @var DateTime $modifiedPlus */
        $modifiedPlus = clone $this->getRight()->getValue();
        $modifiedPlus = $modifiedPlus->modify('+' . $precision);
        /** @var DateTime $modifiedMinus */
        $modifiedMinus = clone $other->getLeft()->getValue();
        $modifiedMinus = $modifiedMinus->modify('-' . $precision);
        return $modifiedPlus->isGreaterThanOrEqual($other->getLeft()->getValue()) && $modifiedPlus->isLessThanOrEqual($other->getRight()->getValue()) && $modifiedMinus->isLessThanOrEqual($this->getRight()->getValue()) && $modifiedMinus->isGreaterThanOrEqual($this->getLeft()->getValue());
    }
    /**
     * @return DateTimeBoundary
     */
    public function getLeft() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return parent::getLeft();
    }
    /**
     * @return DateTimeBoundary
     */
    public function getRight() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return parent::getRight();
    }
    /**
     * # Example:
     *
     *     This:  □□□□□□■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *                    23:59:59 >< 00:00:00
     *
     * @param DateTimeInterval $other
     * @return bool
     */
    public function isFollowedByAtMidnight(\Wappointment\Achse\Math\Interval\DateTime\DateTimeInterval $other) : bool
    {
        $left = clone $other->getLeft()->getValue();
        return \Wappointment\Achse\Math\Interval\Utils::isSameDate($this->getRight()->getValue(), $left->modify('-1 day')) && $this->getRight()->getValue()->format('H:i:s') === '23:59:59' && $other->getLeft()->getValue()->format('H:i:s') === '00:00:00';
    }
    /**
     * @return string
     */
    public function __toString() : string
    {
        /** @var DateTime $left */
        $left = $this->getLeft()->getValue();
        /** @var DateTime $right */
        $right = $this->getRight()->getValue();
        return $this->getLeftBracket() . $left->format(\DateTime::ATOM) . self::STRING_DELIMITER . ' ' . $right->format(\DateTime::ATOM) . $this->getRightBracket();
    }
    /**
     * @param IComparable $element
     * @param bool $state
     * @return DateTimeBoundary
     */
    protected function buildBoundary(\Wappointment\Achse\Comparable\IComparable $element, bool $state) : \Wappointment\Achse\Math\Interval\Boundary
    {
        return new \Wappointment\Achse\Math\Interval\DateTime\DateTimeBoundary($element, $state);
    }
}
