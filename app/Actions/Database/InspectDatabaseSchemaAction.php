<?php
declare(strict_types=1);

namespace Wappointment\Actions\Database;

use Wappointment\Database\MasterSchema;
use Wappointment\Database\SchemaColumn;
use Wappointment\Http\Data\SchemaIssueData;
use Wappointment\Http\Data\SchemaReportData;
use Wappointment\Repositories\DatabaseSchemaRepository;

class InspectDatabaseSchemaAction
{
    public function __construct(private DatabaseSchemaRepository $repository) {}

    public function handle(): SchemaReportData
    {
        $snapshot = $this->repository->snapshot();
        if ($snapshot === []) {
            return new SchemaReportData(MasterSchema::REFERENCE, 'empty', [], []);
        }
        $issues = [];
        foreach (MasterSchema::tables() as $name => $expected) {
            $actual = $snapshot[$name] ?? null;
            if ($actual === null) {
                $issues[] = new SchemaIssueData($name, 'missing_table', '', 'table', 'missing');
                continue;
            }
            if (strtolower($actual['engine'] ?? '') !== 'innodb') {
                $issues[] = new SchemaIssueData($name, 'engine', '', 'InnoDB', $actual['engine'] ?? 'unknown');
            }
            foreach ($expected['columns'] as $column => $definition) {
                $stored = $actual['columns'][$column] ?? null;
                if ($stored === null || !$this->matchesColumn($definition, $stored)) {
                    $issues[] = new SchemaIssueData(
                        $name, 'column', $column, $definition->sql(),
                        $stored === null ? 'missing' : $this->describeColumn($stored)
                    );
                }
            }
            foreach ($expected['indexes'] as $index => $definition) {
                $found = false;
                foreach ($actual['indexes'] as $key => $stored) {
                    if ($stored['columns'] === $definition['columns'] && $stored['unique'] === $definition['unique']
                        && !($stored['prefix'] ?? false) && (($index === 'PRIMARY') === ($key === 'PRIMARY'))) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $issues[] = new SchemaIssueData(
                        $name, 'index', $index,
                        ($definition['unique'] ? 'unique ' : '') . implode(',', $definition['columns']), 'missing or different'
                    );
                }
            }
            foreach ($expected['foreignKeys'] as $column => $target) {
                $found = false;
                foreach ($actual['foreignKeys'] as $stored) {
                    if ($stored['COLUMN_NAME'] === $column
                        && $stored['target'] === $target
                        && $stored['REFERENCED_COLUMN_NAME'] === 'id'
                        && in_array($stored['DELETE_RULE'], ['RESTRICT', 'NO ACTION'], true)
                        && in_array($stored['UPDATE_RULE'], ['RESTRICT', 'NO ACTION'], true)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $issues[] = new SchemaIssueData($name, 'foreign_key', $column, $target . '.id RESTRICT', 'missing or different');
                }
            }
        }
        return new SchemaReportData(
            MasterSchema::REFERENCE, $issues === [] ? 'compatible' : 'incompatible', $issues,
            array_values(array_diff(array_keys($snapshot), array_keys(MasterSchema::tables())))
        );
    }

    private function matchesColumn(SchemaColumn $expected, array $actual): bool
    {
        $type = preg_replace('/\b(tinyint|smallint|mediumint|int|bigint)\(\d+\)/', '$1', strtolower($actual['Type']));
        return $type === $expected->type
            && ($actual['Null'] === 'YES') === $expected->nullable
            && $this->defaultValue($actual['Default']) === $this->defaultValue($expected->default)
            && str_contains(strtolower($actual['Extra']), 'auto_increment') === $expected->autoIncrement
            && !str_contains(strtolower($actual['Extra']), 'on update')
            && !str_contains(strtolower($actual['Extra']), 'virtual generated')
            && !str_contains(strtolower($actual['Extra']), 'stored generated');
    }

    private function defaultValue(string|int|null $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value, "'");
        return strtolower($value) === 'current_timestamp()' ? 'current_timestamp' : strtolower($value);
    }

    private function describeColumn(array $column): string
    {
        return $column['Type'] . ($column['Null'] === 'YES' ? ' NULL' : ' NOT NULL')
            . ' DEFAULT ' . ($column['Default'] === null ? 'NULL' : $column['Default'])
            . ($column['Extra'] === '' ? '' : ' ' . $column['Extra']);
    }
}
