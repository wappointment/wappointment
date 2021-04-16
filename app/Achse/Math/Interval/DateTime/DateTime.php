<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\DateTime;

use Wappointment\Achse\Comparable\ComparisonMethods;
use Wappointment\Achse\Comparable\IComparable;
use Wappointment\Achse\Math\Interval\Utils;
/**
 * @deprecated Use DateTimeImmutable, always!
 */
final class DateTime extends \DateTime implements \Wappointment\Achse\Comparable\IComparable
{
    use ComparisonMethods;
    /**
     * @param \DateTimeInterface $dateTime
     * @return static
     */
    public static function from(\DateTimeInterface $dateTime) : \Wappointment\Achse\Math\Interval\DateTime\DateTime
    {
        return new static($dateTime->format('Y-m-d H:i:s.u'), $dateTime->getTimezone());
    }
    /**
     * @inheritdoc
     */
    public function compare(\Wappointment\Achse\Comparable\IComparable $other) : int
    {
        /** @var static $other */
        \Wappointment\Achse\Math\Interval\Utils::validateClassType(static::class, $other);
        return \Wappointment\Achse\Math\Interval\Utils::numberCmp($this->getTimestamp(), $other->getTimestamp());
    }
    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->format(\DateTime::ATOM);
    }
}
