<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\Integer;

use Wappointment\Achse\Comparable\ComparisonMethods;
use Wappointment\Achse\Comparable\IComparable;
use Wappointment\Achse\Math\Interval\Utils;
use InvalidArgumentException;
final class Integer implements \Wappointment\Achse\Comparable\IComparable
{
    use ComparisonMethods;
    /**
     * @var int
     */
    private $internal;
    /**
     * @param int $internal
     */
    public function __construct(int $internal)
    {
        $this->internal = $internal;
    }
    /**
     * @param string $string
     * @return static
     */
    public static function fromString(string $string) : \Wappointment\Achse\Math\Interval\Integer\Integer
    {
        if (!\is_numeric($string) || (string) (int) $string !== $string) {
            throw new \InvalidArgumentException(\sprintf('\'%s\' in not integer-like.', $string));
        }
        return new static((int) $string);
    }
    /**
     * @inheritdoc
     */
    public function compare(\Wappointment\Achse\Comparable\IComparable $other) : int
    {
        /** @var static $other */
        \Wappointment\Achse\Math\Interval\Utils::validateClassType(static::class, $other);
        return \Wappointment\Achse\Math\Interval\Utils::numberCmp($this->internal, $other->toInt());
    }
    /**
     * @return int
     */
    public function toInt() : int
    {
        return $this->internal;
    }
    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string) $this->toInt();
    }
}
