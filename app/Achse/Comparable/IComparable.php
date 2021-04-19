<?php

declare(strict_types=1);

namespace Wappointment\Achse\Comparable;

interface IComparable
{
    /**
     * $this <  $other =>  returns less then 0
     * $this == $other =>  returns 0
     * $this >  $other =>  returns greater then 0
     *
     * @param IComparable $other
     * @return int (-1, 0, 1)
     */
    public function compare(\Wappointment\Achse\Comparable\IComparable $other): int;
    /**
     * @param IComparable $other
     * @return bool
     */
    public function isEqual(\Wappointment\Achse\Comparable\IComparable $other): bool;
    /**
     * @param IComparable $other
     * @return bool
     */
    public function isLessThan(\Wappointment\Achse\Comparable\IComparable $other): bool;
    /**
     * @param IComparable $other
     * @return bool
     */
    public function isLessThanOrEqual(\Wappointment\Achse\Comparable\IComparable $other): bool;
    /**
     * @param IComparable $other
     * @return bool
     */
    public function isGreaterThan(\Wappointment\Achse\Comparable\IComparable $other): bool;
    /**
     * @param IComparable $other
     * @return bool
     */
    public function isGreaterThanOrEqual(\Wappointment\Achse\Comparable\IComparable $other): bool;
}
