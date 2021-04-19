<?php

declare(strict_types=1);

namespace Wappointment\Achse\Comparable;

trait ComparisonMethods
{
    /**
     * @param IComparable $other
     * @return int (-1, 0, 1)
     */
    public abstract function compare(\Wappointment\Achse\Comparable\IComparable $other): int;
    /**
     * @param IComparable $other
     * @return bool
     */
    public function isEqual(\Wappointment\Achse\Comparable\IComparable $other): bool
    {
        return $this->compare($other) === 0;
    }
    /**
     * @param IComparable $other
     * @return bool
     */
    public function isLessThan(\Wappointment\Achse\Comparable\IComparable $other): bool
    {
        return $this->compare($other) < 0;
    }
    /**
     * @param IComparable $other
     * @return bool
     */
    public function isLessThanOrEqual(\Wappointment\Achse\Comparable\IComparable $other): bool
    {
        return $this->compare($other) <= 0;
    }
    /**
     * @param IComparable $other
     * @return bool
     */
    public function isGreaterThan(\Wappointment\Achse\Comparable\IComparable $other): bool
    {
        return $this->compare($other) > 0;
    }
    /**
     * @param IComparable $other
     * @return bool
     */
    public function isGreaterThanOrEqual(\Wappointment\Achse\Comparable\IComparable $other): bool
    {
        return $this->compare($other) >= 0;
    }
}
