<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Booking\BookingException;

abstract class BookingRequestData extends RequestData
{
    public function text(string $key, int $length, ?string $default = null): string
    {
        $value = $this->toArray()[$key] ?? $default;
        if (!is_string($value) || strlen($value) > $length || str_contains($value, "\0")) {
            throw new BookingException(sprintf(__('Invalid booking field: %s.', 'wappointment'), $key));
        }
        return sanitize_text_field(trim($value));
    }

    public function integer(string $key, int $min, int $max = PHP_INT_MAX): int
    {
        $value = $this->toArray()[$key] ?? null;
        if (filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]) === false) {
            throw new BookingException(sprintf(__('Invalid booking field: %s.', 'wappointment'), $key));
        }
        return (int) $value;
    }
}
