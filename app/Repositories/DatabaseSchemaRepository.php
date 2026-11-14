<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

use Wappointment\Database\MasterSchema;

class DatabaseSchemaRepository
{
    public function snapshot(): array
    {
        global $wpdb;
        $prefix = $wpdb->prefix . 'wappo_';
        $definitions = MasterSchema::tables();
        $tables = $this->rows($wpdb->prepare('SHOW TABLE STATUS LIKE %s', $wpdb->esc_like($prefix) . '%'));
        $snapshot = [];
        foreach ($tables as $table) {
            $suffix = substr($table['Name'], strlen($prefix));
            if (!isset($definitions[$suffix])) {
                $snapshot[$suffix] = ['engine' => $table['Engine'], 'columns' => [], 'indexes' => [], 'foreignKeys' => []];
                continue;
            }
            $columns = [];
            foreach ($this->rows($wpdb->prepare('SHOW FULL COLUMNS FROM %i', $table['Name'])) as $column) {
                $columns[$column['Field']] = $column;
            }
            $indexes = [];
            foreach ($this->rows($wpdb->prepare('SHOW INDEX FROM %i', $table['Name'])) as $index) {
                $indexes[$index['Key_name']]['columns'][(int) $index['Seq_in_index'] - 1] = $index['Column_name'];
                $indexes[$index['Key_name']]['unique'] = !(bool) $index['Non_unique'];
                // A prefix index must not be mistaken for an index of complete values.
                $indexes[$index['Key_name']]['prefix'] = ($indexes[$index['Key_name']]['prefix'] ?? false)
                    || $index['Sub_part'] !== null;
            }
            foreach ($indexes as &$index) {
                ksort($index['columns']);
                $index['columns'] = array_values($index['columns']);
            }
            unset($index);
            $foreignKeys = $this->rows($wpdb->prepare(
                'SELECT k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME,
                        r.DELETE_RULE, r.UPDATE_RULE
                 FROM information_schema.KEY_COLUMN_USAGE k
                 JOIN information_schema.REFERENTIAL_CONSTRAINTS r
                   ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
                  AND r.TABLE_NAME = k.TABLE_NAME AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                 WHERE k.TABLE_SCHEMA = DATABASE() AND k.TABLE_NAME = %s
                   AND k.REFERENCED_TABLE_NAME IS NOT NULL
                   AND (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE c
                        WHERE c.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
                          AND c.TABLE_NAME = k.TABLE_NAME
                          AND c.CONSTRAINT_NAME = k.CONSTRAINT_NAME) = 1',
                $table['Name']
            ));
            foreach ($foreignKeys as &$foreignKey) {
                $foreignKey['target'] = str_starts_with($foreignKey['REFERENCED_TABLE_NAME'], $prefix)
                    ? substr($foreignKey['REFERENCED_TABLE_NAME'], strlen($prefix))
                    : 'external:' . $foreignKey['REFERENCED_TABLE_NAME'];
            }
            unset($foreignKey);
            $snapshot[$suffix] = [
                'engine' => $table['Engine'], 'columns' => $columns,
                'indexes' => $indexes, 'foreignKeys' => $foreignKeys,
            ];
        }
        return $snapshot;
    }

    public function createFresh(): void
    {
        global $wpdb;
        $prefix = $wpdb->prefix . 'wappo_';
        foreach (MasterSchema::tables() as $suffix => $table) {
            $parts = [];
            foreach ($table['columns'] as $name => $column) {
                $parts[] = $wpdb->prepare('%i', $name) . ' ' . $column->sql();
            }
            foreach ($table['indexes'] as $name => $index) {
                $columns = implode(', ', array_map(
                    static fn(string $column): string => $wpdb->prepare('%i', $column), $index['columns']
                ));
                $parts[] = $name === 'PRIMARY'
                    ? "PRIMARY KEY ({$columns})"
                    : ($index['unique'] ? 'UNIQUE ' : '') . $wpdb->prepare('KEY %i', $name) . " ({$columns})";
            }
            foreach ($table['foreignKeys'] as $column => $target) {
                $name = $suffix === 'order_price'
                    ? $prefix . 'orderp_' . ['order_id' => 'oid', 'price_id' => 'pid', 'appointment_id' => 'aid'][$column] . '_foreign'
                    : $prefix . $suffix . '_' . $column . '_foreign';
                if (strlen($name) > 64) {
                    // MySQL identifier limits must not prevent use of long WP prefixes.
                    $name = substr($name, 0, 47) . '_' . substr(hash('sha256', $name), 0, 16);
                }
                $parts[] = $wpdb->prepare(
                    'CONSTRAINT %i FOREIGN KEY (%i) REFERENCES %i (%i)', $name, $column, $prefix . $target, 'id'
                );
            }
            $sql = $wpdb->prepare('CREATE TABLE %i', $prefix . $suffix)
                . ' (' . implode(', ', $parts) . ') ENGINE=InnoDB ' . $wpdb->get_charset_collate();
            if ($wpdb->query($sql) === false) {
                throw new \RuntimeException('Could not create Wappointment table: ' . $suffix . '. ' . $wpdb->last_error);
            }
        }
    }

    public function withInstallationLock(callable $callback): void
    {
        global $wpdb;
        $key = 'wappo_schema_' . substr(hash('sha256', DB_NAME . ':' . $wpdb->prefix), 0, 40);
        $acquired = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 10)', $key));
        if ((string) $acquired !== '1') {
            throw new \RuntimeException('Could not acquire the Wappointment schema installation lock.');
        }
        try {
            $callback();
        } finally {
            if ((string) $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $key)) !== '1') {
                error_log('Wappointment could not release its schema installation lock.');
            }
        }
    }

    private function rows(string $sql): array
    {
        global $wpdb;
        $rows = $wpdb->get_results($sql, ARRAY_A);
        if ($wpdb->last_error !== '' || !is_array($rows)) {
            throw new \RuntimeException('Could not inspect Wappointment database metadata. ' . $wpdb->last_error);
        }
        return $rows;
    }
}
