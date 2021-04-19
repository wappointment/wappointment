<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\SingleDayTime;

use Wappointment\Achse\Comparable\IComparable;
use Wappointment\Achse\Math\Interval\Boundary;
use Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable;
use Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundary;
use Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval;
use Wappointment\Achse\Math\Interval\Interval;
use Wappointment\Achse\Math\Interval\IntervalRangesInvalidException;
use Wappointment\Achse\Math\Interval\ModificationNotPossibleException;
use DateTimeInterface;
final class SingleDayTimeInterval extends \Wappointment\Achse\Math\Interval\Interval
{
    /**
     * @inheritdoc
     */
    public function __construct(\Wappointment\Achse\Math\Interval\Boundary $left, \Wappointment\Achse\Math\Interval\Boundary $right)
    {
        $this->validateBoundaryChecks($left, $right, \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundary::class);
        if ($left->isGreaterThan($right) && !$this->isEndingAtMidnightNextDay($left, $right)) {
            throw new \Wappointment\Achse\Math\Interval\IntervalRangesInvalidException('Left endpoint cannot be greater then Right endpoint.');
        }
        $this->left = $left;
        $this->right = $right;
        // intentionally, no parent constructor calling
    }
    /**
     * @inheritdoc
     */
    protected function isContainingElementRightCheck(\Wappointment\Achse\Comparable\IComparable $element) : bool
    {
        return $this->isRightOpened() && $this->getRight()->getValue()->isGreaterThan($element) || $this->isRightClosed() && $this->getRight()->getValue()->isGreaterThanOrEqual($element) || $this->isEndingAtMidnightNextDay($this->left, $this->right);
    }
    /**
     * @param string $from
     * @param string $till
     * @return SingleDayTimeInterval
     */
    public static function fromString(string $from, string $till) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeInterval
    {
        return new static(new \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundary(\Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime::from($from), \Wappointment\Achse\Math\Interval\Boundary::CLOSED), new \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundary(\Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime::from($till), \Wappointment\Achse\Math\Interval\Boundary::OPENED));
    }
    /**
     * @param DateTimeImmutableInterval $interval
     * @param DateTimeInterface $date
     * @return static
     */
    public static function fromDateTimeInterval(\Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval $interval, \DateTimeInterface $date) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeInterval
    {
        $thisDayInterval = self::buildWholeDayInterval($date);
        /** @var DateTimeImmutableInterval $intersection */
        $intersection = $thisDayInterval->intersection($interval);
        $left = \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime::fromDateTime($intersection->getLeft()->getValue());
        $right = \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime::fromDateTime($intersection->getRight()->getValue());
        return new static(new \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundary($left, $intersection->getLeft()->getState()), new \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundary($right, $intersection->getRight()->getState()));
    }
    /**
     * @param DateTimeInterface $date
     * @return DateTimeImmutableInterval
     */
    protected static function buildWholeDayInterval(\DateTimeInterface $date) : \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval
    {
        $start = \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable::from($date)->setTime(0, 0, 0);
        $ends = \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable::from($date)->setTime(23, 59, 59);
        $thisDayInterval = new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval(new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundary($start, \Wappointment\Achse\Math\Interval\Boundary::CLOSED), new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundary($ends, \Wappointment\Achse\Math\Interval\Boundary::CLOSED));
        return $thisDayInterval;
    }
    /**
     * @param SingleDayTimeInterval $other
     * @param string $precision
     * @return bool
     */
    public function isFollowedByWithPrecision(\Wappointment\Achse\Math\Interval\Interval $other, $precision) : bool
    {
        try {
            $this->getRight()->getValue()->modify('+' . $precision);
            $other->getLeft()->getValue()->modify('-' . $precision);
        } catch (\Wappointment\Achse\Math\Interval\ModificationNotPossibleException $e) {
            return FALSE;
        }
        $dummyDay = new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable('2001-01-01 00:00:00');
        return $this->toDateTimeInterval($dummyDay)->isFollowedByWithPrecision($other->toDateTimeInterval($dummyDay), $precision);
    }
    /**
     * @return SingleDayTimeBoundary
     */
    public function getRight() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return parent::getRight();
    }
    /**
     * @return SingleDayTimeBoundary
     */
    public function getLeft() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return parent::getLeft();
    }
    /**
     * @param DateTimeInterface $day
     * @return DateTimeImmutableInterval
     */
    public function toDateTimeInterval(\DateTimeInterface $day) : \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval
    {
        $left = new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundary($this->getLeft()->getValue()->toDateTime($day), $this->getLeft()->getState());
        $right = new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableBoundary($this->getRight()->getValue()->toDateTime($day), $this->getRight()->getState());
        return new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutableInterval($left, $right);
    }
    /**
     * @param IComparable $element
     * @param bool $state
     * @return SingleDayTimeBoundary
     */
    protected function buildBoundary(\Wappointment\Achse\Comparable\IComparable $element, bool $state) : \Wappointment\Achse\Math\Interval\Boundary
    {
        return new \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTimeBoundary($element, $state);
    }
    /**
     * @param Boundary $left
     * @param Boundary $right
     * @return bool
     */
    private function isEndingAtMidnightNextDay(\Wappointment\Achse\Math\Interval\Boundary $left, \Wappointment\Achse\Math\Interval\Boundary $right) : bool
    {
        return !($left->isOpened() && $left->getValue()->isEqual($right->getValue())) && $right->isOpened() && $right->getValue()->isEqual($this->getZeroElement());
    }
    /**
     * @return SingleDayTime
     */
    private function getZeroElement() : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime
    {
        return new \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime(0, 0, 0);
    }
}
