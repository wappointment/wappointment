<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTimeImmutable;

use Wappointment\Achse\Comparable\IComparable;
use Wappointment\Achse\Math\Interval\Boundary;
use Wappointment\Achse\Math\Interval\Interval;
use Wappointment\Achse\Math\Interval\Utils;
final class DateTimeImmutableInterval extends \Wappointment\Achse\Math\Interval\Interval
{
    /**
     * @inheritdoc
     */
    public function __construct(\Wappointment\Achse\Math\Interval\Boundary $left, \Wappointment\Achse\Math\Interval\Boundary $right)
    {
        $this->validateBoundaryChecks($left, $right, \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundary::class);
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
     * @param DateTimeImmutableInterval $other
     * @param string $precision
     * @return bool
     */
    public function isFollowedByWithPrecision(\Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval $other, string $precision) : bool
    {
        if ($this->getLeft() > $other->getRight()) {
            // intentionally compares boundaries
            return FALSE;
        }
        /** @var DateTimeImmutable $modifiedPlus */
        $modifiedPlus = $this->getRight()->getValue()->modify('+' . $precision);
        /** @var DateTimeImmutable $modifiedMinus */
        $modifiedMinus = $other->getLeft()->getValue()->modify('-' . $precision);
        return $modifiedPlus->isGreaterThanOrEqual($other->getLeft()->getValue()) && $modifiedPlus->isLessThanOrEqual($other->getRight()->getValue()) && $modifiedMinus->isLessThanOrEqual($this->getRight()->getValue()) && $modifiedMinus->isGreaterThanOrEqual($this->getLeft()->getValue());
    }
    /**
     * @return DateTimeImmutableBoundary
     */
    public function getLeft() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return parent::getLeft();
    }
    /**
     * @return DateTimeImmutableBoundary
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
     * @param DateTimeImmutableInterval $other
     * @return bool
     */
    public function isFollowedByAtMidnight(\Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval $other) : bool
    {
        return \Wappointment\Achse\Math\Interval\Utils::isSameDate($this->getRight()->getValue(), $other->getLeft()->getValue()->modify('-1 day')) && $this->getRight()->getValue()->format('H:i:s') === '23:59:59' && $other->getLeft()->getValue()->format('H:i:s') === '00:00:00';
    }
    /**
     * @param IComparable $element
     * @param bool $state
     * @return DateTimeImmutableBoundary
     */
    protected function buildBoundary(\Wappointment\Achse\Comparable\IComparable $element, bool $state) : \Wappointment\Achse\Math\Interval\Boundary
    {
        return new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundary($element, $state);
    }
}
