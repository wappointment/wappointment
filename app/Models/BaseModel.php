<?php
declare(strict_types=1);

namespace Wappointment\Models;

use Wappointment\Database\WpDbConnector;
use Wappointment\System\Container;

/**
 * Base Model - Abstract class for all models
 */
abstract class BaseModel
{
    protected string $table;
    protected string $tableName;
    protected array $columns = [];

    public function __construct(
        protected WpDbConnector $db
    ) {
        $this->table = $this->db->getPrefix() . $this->tableName;
    }

    /**
     * Get all records
     */
    public function all(): array
    {
        return $this->db->select($this->table);
    }

    /**
     * Find record by ID
     */
    public function find(int $id): ?array
    {
        return $this->db->find($this->table, $id);
    }

    /**
     * Get records with pagination
     */
    public function paginate(int $page = 1, int $perPage = 10, array $where = []): array
    {
        return $this->db->paginate($this->table, $page, $perPage, 'id DESC', $where);
    }
    
    /**
     * Get searchable columns for this model
     */
    protected function getSearchableColumns(): array
    {
        return [];
    }
    
    /**
     * Search records with pagination
     */
    public function search(string $term, int $page = 1, int $perPage = 10): array
    {
        $searchableColumns = $this->getSearchableColumns();
        
        if (empty($searchableColumns)) {
            return $this->paginate($page, $perPage);
        }
        
        $conditions = [];
        $values = [];
        
        foreach ($searchableColumns as $column) {
            $conditions[] = "{$column} LIKE %s";
            $values[] = '%' . $this->db->escapeWildcards($term) . '%';
        }
        
        $where = [
            [
                'clause' => '(' . implode(' OR ', $conditions) . ')',
                'values' => $values
            ]
        ];
        
        return $this->paginate($page, $perPage, $where);
    }
    
    /**
     * Create a new record
     */
    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update a record by ID
     */
    public function update(int $id, array $data): int
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }
    
    /**
     * Delete a record by ID
     */
    public function delete(int $id): int
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }
}
