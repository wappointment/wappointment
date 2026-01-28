<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

use Wappointment\Database\WpDbConnector;
use Wappointment\Models\BaseModel;

/**
 * Base Repository - Abstract class for all repositories
 */
abstract class BaseRepository
{
    protected string $table;

    public function __construct(
        protected WpDbConnector $db,
        protected BaseModel $model
    ) {
        $this->table = $this->db->getPrefix() . $this->model->getTableName();
    }

    public function all(): array
    {
        return $this->db->select($this->table);
    }

    public function find(int $id): ?array
    {
        return $this->db->find($this->table, $id);
    }

    public function paginate(int $page = 1, int $perPage = 10, array $where = []): array
    {
        return $this->db->paginate($this->table, $page, $perPage, 'id DESC', $where);
    }

    public function search(string $term, int $page = 1, int $perPage = 10): array
    {
        $where = $this->model->buildSearchWhereClause($term, fn($str) => $this->db->escapeWildcards($str));

        if (empty($where)) {
            return $this->paginate($page, $perPage);
        }

        return $this->paginate($page, $perPage, $where);
    }

    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }
}
