<?php
declare(strict_types=1);

namespace Wappointment\ValueObjects;

class Email
{
    public function __construct(
        public readonly string $value
    ) {}

    public static function from(string $email): self
    {
        $sanitized = sanitize_email($email);
        
        if (empty($sanitized)) {
            throw new \InvalidArgumentException('Email is required');
        }
        
        if (!is_email($sanitized)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        
        return new self($sanitized);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
