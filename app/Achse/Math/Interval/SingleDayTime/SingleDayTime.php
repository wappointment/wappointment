<?php

declare (strict_types=1);
namespace Wappointment\Achse\Math\Interval\SingleDayTime;

use Wappointment\Achse\Comparable\ComparisonMethods;
use Wappointment\Achse\Comparable\IComparable;
use Wappointment\Achse\DateTimeFormatTools\Tools;
use Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable;
use Wappointment\Achse\Math\Interval\ModificationNotPossibleException;
use Wappointment\Achse\Math\Interval\Utils;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use LogicException;
final class SingleDayTime implements \Wappointment\Achse\Comparable\IComparable
{
    use ComparisonMethods;
    /**
     * @internal
     */
    const ALLOWED_FORMAT_SYMBOLS = ['a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u'];
    /**
     * @internal
     */
    const NOT_ALLOWED_FORMAT_SYMBOLS = [
        // day
        'd',
        'D',
        'j',
        'l',
        'N',
        'S',
        'w',
        'z',
        // Week
        'W',
        // Month
        'F',
        'm',
        'M',
        'n',
        't',
        // Year
        'L',
        'o',
        'Y',
        'y',
        // Timezone
        'e',
        'I',
        'O',
        'P',
        'T',
        'Z',
        // Full DateTime
        'c',
        'r',
        'U',
    ];
    /**
     * @internal
     */
    const INTERNAL_DATE = '2000-01-01';
    /**
     * @internal
     */
    const TIME_FORMAT = 'H:i:s';
    const FLOAT_SECONDS_PRECISION = 1.0E-5;
    /**
     * @var int
     */
    private $hours = 0;
    /**
     * @var int
     */
    private $minutes = 0;
    /**
     * @var float
     */
    private $seconds = 0.0;
    /**
     * @param int $hours
     * @param int $minutes
     * @param float $seconds
     */
    public function __construct(int $hours, int $minutes, float $seconds)
    {
        if ($hours < 0 || $hours > 23) {
            throw new \InvalidArgumentException('Hours have to be 0-23.');
        }
        if ($minutes < 0 || $minutes > 59) {
            throw new \InvalidArgumentException('Minutes have to be 0-59.');
        }
        if ($seconds < 0 || $seconds > 59) {
            throw new \InvalidArgumentException('Seconds have to be 0-59.');
        }
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
    }
    /**
     * @param DateTimeInterface|SingleDayTime|string $time
     * @return SingleDayTime
     */
    public static function from($time) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime
    {
        if ($time instanceof static) {
            return clone $time;
        } elseif ($time instanceof \DateTimeInterface) {
            return static::fromDateTime($time);
        } elseif (\is_string($time)) {
            $dateTime = \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable::createFromFormat('Y-m-d ' . self::TIME_FORMAT, '2001-01-01 ' . $time);
            if ($dateTime === FALSE || $dateTime->format(self::TIME_FORMAT) !== $time) {
                throw new \InvalidArgumentException(\sprintf('Given string %s not valid %s time.', $time, self::TIME_FORMAT));
            }
            return static::fromDateTime($dateTime);
        }
        throw new \InvalidArgumentException(\sprintf('Argument is not type of DateTimeInterface or SingleDayTime or string. Type: %s given.', \is_object($time) ? \get_class($time) : \gettype($time)));
    }
    /**
     * @param DateTimeInterface $dateTime
     * @return static
     */
    public static function fromDateTime(\DateTimeInterface $dateTime) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime
    {
        return new static((int) $dateTime->format('H'), (int) $dateTime->format('i'), (float) $dateTime->format('s'));
    }
    /**
     * @inheritdoc
     */
    public function compare(\Wappointment\Achse\Comparable\IComparable $other) : int
    {
        /** @var static $other */
        \Wappointment\Achse\Math\Interval\Utils::validateClassType(static::class, $other);
        return \Wappointment\Achse\Math\Interval\Utils::numberCmp($this->toSeconds(), $other->toSeconds());
    }
    /**
     * @return float
     */
    public function toSeconds() : float
    {
        return $this->hours * 3600 + $this->minutes * 60 + $this->seconds;
    }
    /**
     * @param string $modifier
     * @return static
     * @throws ModificationNotPossibleException
     */
    public function modify(string $modifier) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime
    {
        $new = clone $this;
        $thisDateTime = $new->toInternalDateTime();
        $modified = $thisDateTime->modify($modifier);
        if ($thisDateTime->format('Y-m-d') !== $modified->format('Y-m-d')) {
            throw new \Wappointment\Achse\Math\Interval\ModificationNotPossibleException(\sprintf('Modifying this by \'%s\' leaves a single day range.', $modifier));
        }
        return new static((int) $modified->format('H'), (int) $modified->format('i'), (float) $modified->format('s'));
    }
    /**
     * @return DateTimeImmutable
     */
    private function toInternalDateTime() : \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable
    {
        return $this->toDateTime(new \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable(self::INTERNAL_DATE, new \DateTimeZone('UTC')));
    }
    /**
     * @param DateTimeInterface $day
     * @return DateTimeImmutable
     */
    public function toDateTime(\DateTimeInterface $day) : \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable
    {
        $day = \Wappointment\Achse\Math\Interval\DateTimeImmutable\DateTimeImmutable::from($day)->setTime($this->hours, $this->minutes, (int) \round($this->seconds));
        return $day;
    }
    /**
     * @param SingleDayTime $other
     * @return static
     */
    public function add(\Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime $other) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime
    {
        return $this->addOrSub($other, 1);
    }
    /**
     * @param SingleDayTime $other
     * @param int $sign -1 (sub) or 1 (add)
     * @return static
     * @throws ModificationNotPossibleException
     */
    private function addOrSub(\Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime $other, int $sign) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime
    {
        $seconds = $this->seconds;
        $minutes = $this->minutes;
        $hours = $this->hours;
        $seconds += $sign * $other->getSeconds();
        $carryMinutes = 0;
        if ($seconds > 59 || $seconds < 0) {
            $carryMinutes = 1;
            $seconds += -$sign * 60;
        }
        $minutes += $sign * ($other->getMinutes() + $carryMinutes);
        $carryHours = 0;
        if ($minutes > 59 || $minutes < 0) {
            $carryHours = 1;
            $minutes += -$sign * 60;
        }
        $hours += $sign * ($other->getHours() + $carryHours);
        $this->validateModification($hours, $sign);
        return new static($hours, $minutes, $seconds);
    }
    /**
     * @return float
     */
    public function getSeconds() : float
    {
        return $this->seconds;
    }
    /**
     * @param float $seconds
     * @return static
     */
    public function withSeconds(float $seconds) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime
    {
        return new static($this->hours, $this->minutes, $seconds);
    }
    /**
     * @return int
     */
    public function getMinutes() : int
    {
        return $this->minutes;
    }
    /**
     * @param int $minutes
     * @return static
     */
    public function withMinutes(int $minutes) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime
    {
        return new static($this->hours, $minutes, $this->seconds);
    }
    /**
     * @return int
     */
    public function getHours() : int
    {
        return $this->hours;
    }
    /**
     * @param int $hours
     * @return static
     */
    public function withHours(int $hours) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime
    {
        return new static($hours, $this->minutes, $this->seconds);
    }
    /**
     * @param int $hours
     * @param int $sign
     * @throws ModificationNotPossibleException
     */
    private function validateModification(int $hours, int $sign)
    {
        if ($hours > 23 || $hours < 0) {
            throw new \Wappointment\Achse\Math\Interval\ModificationNotPossibleException('By ' . ($sign === 1 ? 'adding' : 'subbing') . ' this Time we would get put of one day!');
        }
    }
    /**
     * @param SingleDayTime $other
     * @return static
     */
    public function sub(\Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime $other) : \Wappointment\Achse\Math\Interval\SingleDayTime\SingleDayTime
    {
        return $this->addOrSub($other, -1);
    }
    /**
     * @param string $format
     * @return string
     */
    public function format(string $format) : string
    {
        if (\Wappointment\Achse\DateTimeFormatTools\Tools::isAnyOfSymbolsInPattern(self::NOT_ALLOWED_FORMAT_SYMBOLS, $format)) {
            throw new \LogicException(\sprintf('Invalid pattern. Only [%s] symbols are allowed.', \implode(', ', self::ALLOWED_FORMAT_SYMBOLS)));
        }
        return $this->toInternalDateTime()->format($format);
    }
    /**
     * @inheritdoc
     */
    public function __toString() : string
    {
        return $this->toInternalDateTime()->format('H:i:s');
    }
}
