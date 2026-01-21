<?php
declare(strict_types=1);

namespace Wappointment\Models;

/**
 * Base Model - Abstract class for all models
 * Models are simple data structures defining the table schema
 */
abstract class BaseModel
{
    protected string $tableName;
    protected array $columns = [];

    /**
     * Get the table name
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Get the columns definition
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
}
