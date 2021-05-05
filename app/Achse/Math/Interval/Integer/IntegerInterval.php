<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\Integer;

use Wappointment\Achse\Comparable\IComparable;
use Wappointment\Achse\Math\Interval\Boundary;
use Wappointment\Achse\Math\Interval\Interval;
final class IntegerInterval extends \Wappointment\Achse\Math\Interval\Interval
{
    /**
     * @inheritdoc
     */
    public function __construct(\Wappointment\Achse\Math\Interval\Boundary $left, \Wappointment\Achse\Math\Interval\Boundary $right)
    {
        $this->validateBoundaryChecks($left, $right, \Wappointment\Achse\Math\Interval\Integer\IntegerBoundary::class);
        parent::__construct($left, $right);
    }
    /**
     * @return IntegerBoundary
     */
    public function getLeft() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return parent::getLeft();
    }
    /**
     * @return IntegerBoundary
     */
    public function getRight() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return parent::getRight();
    }
    /**
     * @param IComparable $element
     * @param bool $state
     * @return IntegerBoundary
     */
    protected function buildBoundary(\Wappointment\Achse\Comparable\IComparable $element, bool $state) : \Wappointment\Achse\Math\Interval\Boundary
    {
        return new \Wappointment\Achse\Math\Interval\Integer\IntegerBoundary($element, $state);
    }
}
