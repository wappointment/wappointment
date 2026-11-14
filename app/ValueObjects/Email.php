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
        if (trim($email) === '') {
            throw new \InvalidArgumentException('Email is required');
        }
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        
        return new self($email);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
