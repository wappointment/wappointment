<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTimeImmutable;

use Wappointment\Achse\Comparable\IComparable;
use Wappointment\Achse\Math\Interval\Boundary;
final class DateTimeImmutableBoundary extends \Wappointment\Achse\Math\Interval\Boundary
{
    /**
     * @inheritdoc
     */
    public function __construct(\Wappointment\Achse\Comparable\IComparable $element, bool $state)
    {
        $this->validateElement($element, \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable::class);
        parent::__construct($element, $state);
    }
    /**
     * @return DateTimeImmutable
     */
    public function getValue() : \Wappointment\Achse\Comparable\IComparable
    {
        return parent::getValue();
    }
}
