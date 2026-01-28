<?php
declare(strict_types=1);

namespace Wappointment\Database;

class WpDbConnector
{
    private \wpdb $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function select(string $table, string $orderBy = 'id DESC'): array
    {
        return $this->wpdb->get_results(
            "SELECT * FROM {$table} ORDER BY {$orderBy}",
            ARRAY_A
        ) ?? [];
    }

    public function find(string $table, int $id): ?array
    {
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return $result ?: null;
    }

    public function paginate(string $table, int $page, int $perPage, string $orderBy = 'id DESC', array $where = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause
        $whereClause = '';
        $whereValues = [];
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $condition) {
                $conditions[] = $condition['clause'];
                $whereValues = array_merge($whereValues, $condition['values']);
            }
            $whereClause = ' WHERE ' . implode(' AND ', $conditions);
        }
        
        // Get results with pagination
        $query = "SELECT * FROM {$table}{$whereClause} ORDER BY {$orderBy} LIMIT %d OFFSET %d";
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                $query,
                array_merge($whereValues, [$perPage, $offset])
            ),
            ARRAY_A
        );

        // Get total count
        $countQuery = "SELECT COUNT(*) FROM {$table}{$whereClause}";
        if (!empty($whereValues)) {
            $total = $this->wpdb->get_var(
                $this->wpdb->prepare($countQuery, $whereValues)
            );
        } else {
            $total = $this->wpdb->get_var($countQuery);
        }

        return [
            'data' => $results ?? [],
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    public function getPrefix(): string
    {
        return $this->wpdb->prefix;
    }
    
    public function escapeWildcards(string $value): string
    {
        return $this->wpdb->esc_like($value);
    }
    
    public function insert(string $table, array $data): int
    {
        $this->wpdb->insert($table, $data);
        return (int) $this->wpdb->insert_id;
    }
    
    public function update(string $table, array $data, array $where): int
    {
        return $this->wpdb->update($table, $data, $where);
    }
    
    public function delete(string $table, array $where): int
    {
        return $this->wpdb->delete($table, $where);
    }
}
