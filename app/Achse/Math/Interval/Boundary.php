<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval;

use Wappointment\Achse\Comparable\ComparisonMethods;
use Wappointment\Achse\Comparable\IComparable;
use LogicException;
class Boundary implements \Wappointment\Achse\Comparable\IComparable
{
    use ComparisonMethods;
    const OPENED = TRUE;
    const CLOSED = FALSE;
    const STRING_OPENED_LEFT = '(';
    const STRING_OPENED_RIGHT = ')';
    const STRING_CLOSED_LEFT = '[';
    const STRING_CLOSED_RIGHT = ']';
    /**
     * @var IComparable
     */
    private $element;
    /**
     * @var bool
     */
    private $state;
    /**
     * @param IComparable $element
     * @param bool $state
     */
    public function __construct(\Wappointment\Achse\Comparable\IComparable $element, bool $state)
    {
        $this->element = $element;
        $this->state = $state;
    }
    /**
     * @return IComparable
     */
    public function getValue() : \Wappointment\Achse\Comparable\IComparable
    {
        return $this->element;
    }
    /**
     * @return bool
     */
    public function isClosed() : bool
    {
        return $this->state === self::CLOSED;
    }
    /**
     * @inheritdoc
     */
    public function compare(\Wappointment\Achse\Comparable\IComparable $other) : int
    {
        /** @var static $other */
        \Wappointment\Achse\Math\Interval\Utils::validateClassType(static::class, $other);
        $comparison = $this->element->compare($other->element);
        if ($comparison === 0) {
            if ($this->state === $other->state) {
                return 0;
            }
            return $this->isOpened() ? -1 : 1;
        }
        return $comparison;
    }
    /**
     * @return bool
     */
    public function isOpened() : bool
    {
        return $this->state === self::OPENED;
    }
    /**
     * @return bool
     */
    public function getState() : bool
    {
        return $this->state;
    }
    /**
     * @return string
     */
    public function __toString() : string
    {
        return ($this->isOpened() ? self::STRING_OPENED_LEFT : self::STRING_CLOSED_LEFT) . $this->element . ($this->isOpened() ? self::STRING_OPENED_RIGHT : self::STRING_CLOSED_RIGHT);
    }
    /**
     * @return static
     */
    public function asOpened() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return new static($this->element, \Wappointment\Achse\Math\Interval\Boundary::OPENED);
    }
    /**
     * @return static
     */
    public function asClosed() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return new static($this->element, \Wappointment\Achse\Math\Interval\Boundary::CLOSED);
    }
    /**
     * @param IComparable $element
     * @param string $type
     */
    protected function validateElement(\Wappointment\Achse\Comparable\IComparable $element, string $type)
    {
        if (!$element instanceof $type) {
            throw new \LogicException(\sprintf('You have to provide %s as element. %s given.', $type, \get_class($element)));
        }
    }
}
