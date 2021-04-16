<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTime;

use Wappointment\Achse\Comparable\IComparable;
use Wappointment\Achse\Math\Interval\Boundary;
/**
 * @deprecated Use DateTimeImmutable, always!
 */
final class DateTimeBoundary extends \Wappointment\Achse\Math\Interval\Boundary
{
    /**
     * @inheritdoc
     */
    public function __construct(\Wappointment\Achse\Comparable\IComparable $element, bool $state)
    {
        $this->validateElement($element, \Wappointment\Achse\Math\Interval\DateTime\DateTime::class);
        parent::__construct($element, $state);
    }
    /**
     * @return DateTime
     */
    public function getValue() : \Wappointment\Achse\Comparable\IComparable
    {
        return parent::getValue();
    }
}
