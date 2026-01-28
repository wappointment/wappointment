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
    protected array $searchableColumns = [];

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

    /**
     * Get the searchable columns
     */
    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    /**
     * Build WHERE clause for search across searchable columns
     */
    public function buildSearchWhereClause(string $term, callable $escapeWildcards): array
    {
        if (empty($this->searchableColumns)) {
            return [];
        }

        $conditions = [];
        $values = [];

        foreach ($this->searchableColumns as $column) {
            $conditions[] = "{$column} LIKE %s";
            $values[] = '%' . $escapeWildcards($term) . '%';
        }

        return [
            [
                'clause' => '(' . implode(' OR ', $conditions) . ')',
                'values' => $values
            ]
        ];
    }
}
