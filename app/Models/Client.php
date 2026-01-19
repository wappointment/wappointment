<?php
declare(strict_types=1);

namespace Wappointment\Models;

/**
 * Client Model - Handles wp_wappo_clients table
 */
class Client
{
    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'wappo_clients';
    }

    /**
     * Get all clients
     */
    public function all(): array
    {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table} ORDER BY id DESC",
            ARRAY_A
        );

        return $results ?? [];
    }

    /**
     * Find client by ID
     */
    public function find(int $id): ?array
    {
        global $wpdb;
        
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );

        return $result ?: null;
    }

    /**
     * Get clients with pagination
     */
    public function paginate(int $page = 1, int $perPage = 10): array
    {
        global $wpdb;
        
        $offset = ($page - 1) * $perPage;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT %d OFFSET %d",
                $perPage,
                $offset
            ),
            ARRAY_A
        );

        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");

        return [
            'data' => $results ?? [],
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
}
