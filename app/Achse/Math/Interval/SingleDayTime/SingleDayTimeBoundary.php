<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\SingleDayTime;

use Wappointment\Achse\Comparable\IComparable;
use Wappointment\Achse\Math\Interval\Boundary;
use Wappointment\Achse\Math\Interval\Utils;
final class SingleDayTimeBoundary extends \Wappointment\Achse\Math\Interval\Boundary
{
    /**
     * @inheritdoc
     */
    public function __construct(\Wappointment\Achse\Comparable\IComparable $element, bool $state)
    {
        $this->validateElement($element, \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime::class);
        parent::__construct($element, $state);
    }
    /**
     * @return SingleDayTime
     */
    public function getValue() : \Wappointment\Achse\Comparable\IComparable
    {
        return parent::getValue();
    }
}
