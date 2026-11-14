<?php
declare(strict_types=1);

namespace Wappointment\Database;

final class SchemaColumn
{
    public function __construct(
        public readonly string $type,
        public readonly bool $nullable = false,
        public readonly string|int|null $default = null,
        public readonly bool $autoIncrement = false,
    ) {}

    public function sql(): string
    {
        $sql = $this->type . ($this->nullable ? ' NULL' : ' NOT NULL');
        if ($this->autoIncrement) {
            return $sql . ' AUTO_INCREMENT';
        }
        if ($this->default !== null) {
            $sql .= ' DEFAULT ' . ($this->default === 'CURRENT_TIMESTAMP'
                ? 'CURRENT_TIMESTAMP' : "'" . str_replace("'", "''", (string) $this->default) . "'");
        } elseif ($this->nullable) {
            $sql .= ' DEFAULT NULL';
        }
        return $sql;
    }
}
