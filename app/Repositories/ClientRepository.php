<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

class ClientRepository
{
    private \wpdb $db;

    public function __construct(private BookingRepository $bookings)
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function listVisible(string $search, int $page, int $perPage, ?int $owner): array
    {
        $prefix = $this->prefix();
        $where = 'c.deleted_at IS NULL' . $this->scope($owner);
        $values = [];
        if ($search !== '') {
            $term = '%' . $this->db->esc_like($search) . '%';
            $where .= ' AND (c.name LIKE %s OR c.email LIKE %s OR c.options LIKE %s)';
            $values = [$term, $term, $term];
        }
        $rows = $this->rows($this->db->prepare(
            "SELECT c.* FROM {$prefix}clients c WHERE {$where} ORDER BY c.id DESC LIMIT %d OFFSET %d",
            [...$values, $perPage, ($page - 1) * $perPage]
        ));
        $countSql = "SELECT COUNT(*) FROM {$prefix}clients c WHERE {$where}";
        $total = $this->db->get_var($values ? $this->db->prepare($countSql, $values) : $countSql);
        $this->checkError();
        return [
            'data' => $rows, 'total' => (int) $total, 'page' => $page,
            'per_page' => $perPage, 'total_pages' => (int) ceil((int) $total / $perPage),
        ];
    }

    public function create(array $data): array
    {
        return $this->bookings->exclusive(function () use ($data): array {
            $this->assertEmailAvailable($data['email']);
            if ($this->db->insert($this->prefix() . 'clients', $data) === false) {
                $this->failure();
            }
            return $this->findVisible((int) $this->db->insert_id, null);
        });
    }

    public function updateVisible(int $id, array $data, ?int $owner): array
    {
        return $this->bookings->exclusive(function () use ($id, $data, $owner): array {
            $this->findVisible($id, $owner, true);
            if (isset($data['email'])) {
                $this->assertEmailAvailable($data['email'], $id);
            }
            if ($this->db->update($this->prefix() . 'clients', $data, ['id' => $id]) === false) {
                $this->failure();
            }
            return $this->findVisible($id, $owner);
        });
    }

    public function deleteVisible(int $id, ?int $owner): void
    {
        $this->bookings->exclusive(function () use ($id, $owner): void {
            $this->findVisible($id, $owner, true);
            $now = gmdate('Y-m-d H:i:s');
            if ($this->db->update($this->prefix() . 'clients', [
                'deleted_at' => $now, 'updated_at' => $now,
            ], ['id' => $id]) === false) {
                $this->failure();
            }
        });
    }

    private function findVisible(int $id, ?int $owner, bool $lock = false): array
    {
        $prefix = $this->prefix();
        $join = '';
        $where = $this->db->prepare('c.id = %d AND c.deleted_at IS NULL', $id);
        if ($owner !== null) {
            // Lock the ownership relation too, so reassignment cannot race the mutation.
            $join = " JOIN {$prefix}appointments a ON a.client_id = c.id
                      JOIN {$prefix}calendars owner ON owner.id = a.staff_id";
            $where .= $this->db->prepare(' AND owner.wp_uid = %d AND owner.deleted_at IS NULL', $owner);
        }
        $sql = "SELECT c.* FROM {$prefix}clients c {$join} WHERE {$where} LIMIT 1";
        $rows = $this->rows($sql . ($lock ? ' FOR UPDATE' : ''));
        if (!$rows) {
            throw new \RuntimeException(__('Client not found', 'wappointment'), 404);
        }
        return $rows[0];
    }

    private function scope(?int $owner): string
    {
        if ($owner === null) {
            return '';
        }
        $prefix = $this->prefix();
        return $this->db->prepare(
            " AND EXISTS (SELECT 1 FROM {$prefix}appointments a
              JOIN {$prefix}calendars owner ON owner.id = a.staff_id
              WHERE a.client_id = c.id AND owner.wp_uid = %d AND owner.deleted_at IS NULL)",
            $owner
        );
    }

    private function assertEmailAvailable(string $email, int $except = 0): void
    {
        $prefix = $this->prefix();
        $rows = $this->rows($this->db->prepare(
            "SELECT id FROM {$prefix}clients WHERE email = %s AND id <> %d LIMIT 1", $email, $except
        ));
        if ($rows) {
            throw new \RuntimeException(__('This email is already assigned to a client.', 'wappointment'), 409);
        }
    }

    private function prefix(): string
    {
        return $this->db->prefix . 'wappo_';
    }

    private function rows(string $sql): array
    {
        $rows = $this->db->get_results($sql, ARRAY_A);
        $this->checkError();
        return $rows ?? [];
    }

    private function checkError(): void
    {
        if ($this->db->last_error !== '') {
            $this->failure();
        }
    }

    private function failure(): never
    {
        error_log('Wappointment client database error: ' . $this->db->last_error);
        throw new \RuntimeException(__('Client data could not be saved or loaded. Please contact the site administrator.', 'wappointment'), 500);
    }
}
