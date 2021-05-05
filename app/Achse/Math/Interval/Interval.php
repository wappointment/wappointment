<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval;

use Wappointment\Achse\Comparable\IComparable;
class Interval
{
    const STRING_DELIMITER = ',';
    /**
     * @var Boundary
     */
    protected $left;
    /**
     * @var Boundary
     */
    protected $right;
    /**
     * @param Boundary $left
     * @param Boundary $right
     */
    public function __construct(\Wappointment\Achse\Math\Interval\Boundary $left, \Wappointment\Achse\Math\Interval\Boundary $right)
    {
        if ($left->isGreaterThan($right)) {
            throw new \Wappointment\Achse\Math\Interval\IntervalRangesInvalidException('Left endpoint cannot be greater then Right endpoint.');
        }
        $this->left = $left;
        $this->right = $right;
    }
    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->getLeftBracket() . $this->getLeft()->getValue() . self::STRING_DELIMITER . ' ' . $this->getRight()->getValue() . $this->getRightBracket();
    }
    /**
     * @return Boundary
     */
    public function getLeft() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return $this->left;
    }
    /**
     * @return Boundary
     */
    public function getRight() : \Wappointment\Achse\Math\Interval\Boundary
    {
        return $this->right;
    }
    /**
     * @param Boundary $left
     * @return static
     */
    public function withLeft(\Wappointment\Achse\Math\Interval\Boundary $left) : \Wappointment\Achse\Math\Interval\Interval
    {
        return new static($left, $this->right);
    }
    /**
     * @param Boundary $right
     * @return static
     */
    public function withRight(\Wappointment\Achse\Math\Interval\Boundary $right) : \Wappointment\Achse\Math\Interval\Interval
    {
        return new static($this->left, $right);
    }
    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->isLeftOpened() && $this->left->getValue()->isEqual($this->right->getValue());
    }
    /**
     * A degenerate interval is any set consisting of a single element.
     *
     * @return bool
     */
    public function isDegenerate() : bool
    {
        return $this->isClosed() && $this->left->isEqual($this->right);
    }
    /**
     * @return bool
     */
    public function isProper() : bool
    {
        return !$this->isEmpty() && !$this->isDegenerate();
    }
    /**
     * @return bool
     */
    public function isOpened() : bool
    {
        return $this->isLeftOpened() && $this->isRightOpened();
    }
    /**
     * @return bool
     */
    public function isLeftOpened() : bool
    {
        return $this->getLeft()->isOpened();
    }
    /**
     * @return bool
     */
    public function isRightOpened() : bool
    {
        return $this->getRight()->isOpened();
    }
    /**
     * @return bool
     */
    public function isClosed() : bool
    {
        return $this->isLeftClosed() && $this->isRightClosed();
    }
    /**
     * @return bool
     */
    public function isLeftClosed() : bool
    {
        return $this->getLeft()->isClosed();
    }
    /**
     * @return bool
     */
    public function isRightClosed() : bool
    {
        return $this->getRight()->isClosed();
    }
    /**
     * @param IComparable $element
     * @return bool
     */
    public function isContainingElement(\Wappointment\Achse\Comparable\IComparable $element) : bool
    {
        return $this->isContainingElementLeftCheck($element) && $this->isContainingElementRightCheck($element);
    }
    /**
     * # Examples:
     *
     *     This:  □□□□□□□□□□□■■■■■■■■■■■■■■■■■■■■■■■■□□□□□□□□□□
     *     Other: □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *
     *     This:  □□□□□□□□□□□■■■■■■■■■■■■■■■■■■■■■■■■□□□□□□□□□□
     *     Other: □□□□□□□□□□□■■■■■■■■■■■■■■■■■■■■■■■■□□□□□□□□□□
     *
     * # False example:
     *
     *     This:  □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□■■■■■■■■■■■■■■■■■■■■■■■■□□□□□□□□□□
     *
     * @param Interval $other
     * @return bool
     */
    public function isContaining(\Wappointment\Achse\Math\Interval\Interval $other) : bool
    {
        return ($this->isContainingElement($other->getLeft()->getValue()) || $other->isLeftOpened() && $this->isElementLeftOpenedBorder($other->getLeft()->getValue())) && ($this->isContainingElement($other->getRight()->getValue()) || $other->isRightOpened() && $this->isElementRightOpenedBorder($other->getRight()->getValue()));
    }
    /**
     * # Example:
     *
     *     This:  □□□□□□□□□□□■■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *
     * # False examples:
     *
     *     This:  □□□□□□□□□□□■■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□■■■■■■■■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *
     *     This:  □□□□□□□□□□□■■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□□□□□□□□□□□□□□■■■■■■■■■■■■□□□□□□□□□
     *
     * @param Interval $other
     * @return bool
     */
    public function isOverlappedFromRightBy(\Wappointment\Achse\Math\Interval\Interval $other) : bool
    {
        return $this->isContainingElement($other->getLeft()->getValue()) && $other->isContainingElement($this->getRight()->getValue());
    }
    /**
     * If intervals don't overlaps than returned Interval will be "empty" - check via Interval::isEmpty() method
     *
     * @param Interval $other
     * @return static
     */
    public function intersection(\Wappointment\Achse\Math\Interval\Interval $other)
    {
        if ($this->isContaining($other)) {
            return $other;
        } elseif ($other->isContaining($this)) {
            return $this;
        } elseif ($this->isOverlappedFromRightBy($other)) {
            // This:  □□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□
            // Other: □□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□
            //    $other->from   |   | <- $this-till
            return new static($other->getLeft(), $this->getRight());
        } elseif ($other->isOverlappedFromRightBy($this)) {
            // This:  □□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□□
            // Other: □□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□□□
            //    $this->from -> |   | <- $other-till
            return new static($this->getLeft(), $other->getRight());
        }
        $dummy = $this->left->asOpened();
        return new static($dummy, $dummy);
    }
    /**
     * # Examples:
     *
     *     This:  □□□□□□□□□□□■■■■■■■■■■■■■■■■■■■■■■■■□□□□□□□□□□
     *     Other: □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *
     *     This:  □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□■■■■■■■■■■■■■■■■■■■■■■■■□□□□□□□□□□
     *
     *     This:  □□□□□□□□□□□■■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *
     *     This:  □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□■■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□
     *
     * # False example:
     *
     *     This:  □□□□□□■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□□□□□□□
     *     Other: □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
     *
     * @param Interval $other
     * @return bool
     */
    public function isColliding(\Wappointment\Achse\Math\Interval\Interval $other) : bool
    {
        return $this->isOverlappedFromRightBy($other) || $other->isOverlappedFromRightBy($this);
    }
    /**
     * @param Interval $other
     * @return Interval[]
     */
    public function difference(\Wappointment\Achse\Math\Interval\Interval $other) : array
    {
        if (($other = $this->intersection($other)) === NULL) {
            return [$this];
        }
        $result = [];
        if (!$other->getLeft()->isEqual($this->getLeft())) {
            // intentionally not only values but also states
            $result[] = new static($this->getLeft(), $this->buildBoundary($other->getLeft()->getValue(), !$other->getLeft()->getState()));
        }
        if (!$other->getRight()->isEqual($this->getRight())) {
            // intentionally not only values but also states
            $result[] = new static($this->buildBoundary($other->getRight()->getValue(), !$other->getRight()->getState()), $this->getRight());
        }
        return $result;
    }
    /**
     * @param Interval $other
     * @return bool
     */
    public function isFollowedBy(\Wappointment\Achse\Math\Interval\Interval $other) : bool
    {
        return $this->right->getValue()->isEqual($other->left->getValue()) && ($this->right->isClosed() || $other->getLeft()->isClosed());
    }
    /**
     * @param Interval $other
     * @return Interval[]
     */
    public function union(\Wappointment\Achse\Math\Interval\Interval $other) : array
    {
        if ($this->isOverlappedFromRightBy($other) || $this->isFollowedBy($other)) {
            return [new static($this->left, $other->getRight())];
        } elseif ($other->isOverlappedFromRightBy($this) || $other->isFollowedBy($this)) {
            return [new static($other->getLeft(), $this->right)];
        }
        return [$this, $other];
    }
    /**
     * @param IComparable $element
     * @param bool $state
     * @return Boundary
     */
    protected function buildBoundary(\Wappointment\Achse\Comparable\IComparable $element, bool $state) : \Wappointment\Achse\Math\Interval\Boundary
    {
        return new \Wappointment\Achse\Math\Interval\Boundary($element, $state);
    }
    /**
     * @param Boundary $left
     * @param Boundary $right
     * @param string $type
     */
    protected function validateBoundaryChecks(\Wappointment\Achse\Math\Interval\Boundary $left, \Wappointment\Achse\Math\Interval\Boundary $right, string $type)
    {
        if (!$left instanceof $type) {
            throw new \Wappointment\Achse\Math\Interval\InvalidBoundaryTypeException(\sprintf('$left have to be instance of %s.', $type));
        }
        if (!$right instanceof $type) {
            throw new \Wappointment\Achse\Math\Interval\InvalidBoundaryTypeException(\sprintf('$right have to be instance of %s.', $type));
        }
    }
    /**
     * @return string
     */
    protected function getLeftBracket() : string
    {
        return $this->isLeftOpened() ? \Wappointment\Achse\Math\Interval\Boundary::STRING_OPENED_LEFT : \Wappointment\Achse\Math\Interval\Boundary::STRING_CLOSED_LEFT;
    }
    /**
     * @return string
     */
    protected function getRightBracket() : string
    {
        return $this->isRightOpened() ? \Wappointment\Achse\Math\Interval\Boundary::STRING_OPENED_RIGHT : \Wappointment\Achse\Math\Interval\Boundary::STRING_CLOSED_RIGHT;
    }
    /**
     * @param IComparable $element
     * @return bool
     */
    protected function isContainingElementLeftCheck(\Wappointment\Achse\Comparable\IComparable $element) : bool
    {
        return $this->isLeftOpened() && $this->getLeft()->getValue()->isLessThan($element) || $this->isLeftClosed() && $this->getLeft()->getValue()->isLessThanOrEqual($element);
    }
    /**
     * @param IComparable $element
     * @return bool
     */
    protected function isContainingElementRightCheck(\Wappointment\Achse\Comparable\IComparable $element) : bool
    {
        return $this->isRightOpened() && $this->getRight()->getValue()->isGreaterThan($element) || $this->isRightClosed() && $this->getRight()->getValue()->isGreaterThanOrEqual($element);
    }
    /**
     * @param IComparable $element
     * @return bool
     */
    private function isElementLeftOpenedBorder(\Wappointment\Achse\Comparable\IComparable $element) : bool
    {
        return $this->isLeftOpened() && $this->getLeft()->getValue()->isEqual($element);
    }
    /**
     * @param IComparable $element
     * @return bool
     */
    private function isElementRightOpenedBorder(\Wappointment\Achse\Comparable\IComparable $element) : bool
    {
        return $this->isRightOpened() && $this->getRight()->getValue()->isEqual($element);
    }
}
