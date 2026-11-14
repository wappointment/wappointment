<?php
declare(strict_types=1);

use Wappointment\Actions\Database\InspectDatabaseSchemaAction;
use Wappointment\Database\MasterSchema;
use Wappointment\Database\SchemaColumn;
use Wappointment\Http\Requests\GetDatabaseSchemaRequestData;
use Wappointment\Repositories\DatabaseSchemaRepository;
use Wappointment\UseCases\Database\GetDatabaseSchemaUseCase;

function masterSchemaSnapshot(): array
{
    $snapshot = [];
    foreach (MasterSchema::tables() as $name => $definition) {
        $columns = [];
        foreach ($definition['columns'] as $column => $value) {
            $columns[$column] = [
                'Field' => $column, 'Type' => $value->type, 'Null' => $value->nullable ? 'YES' : 'NO',
                'Default' => $value->default === null ? null : (string) $value->default,
                'Extra' => $value->autoIncrement ? 'auto_increment' : '',
            ];
        }
        $foreignKeys = [];
        foreach ($definition['foreignKeys'] as $column => $target) {
            $foreignKeys[] = [
                'COLUMN_NAME' => $column, 'target' => $target, 'REFERENCED_COLUMN_NAME' => 'id',
                'DELETE_RULE' => 'RESTRICT', 'UPDATE_RULE' => 'NO ACTION',
            ];
        }
        $snapshot[$name] = [
            'engine' => 'InnoDB', 'columns' => $columns,
            'indexes' => $definition['indexes'], 'foreignKeys' => $foreignKeys,
        ];
    }
    return $snapshot;
}

function schemaInspector(array $snapshot): InspectDatabaseSchemaAction
{
    $repository = test()->createMock(DatabaseSchemaRepository::class);
    $repository->expects(test()->once())->method('snapshot')->willReturn($snapshot);
    return new InspectDatabaseSchemaAction($repository);
}

test('master schema characterizes all core tables rather than only booking tables', function () {
    $tables = MasterSchema::tables();
    expect(array_keys($tables))->toBe([
        'clients', 'services', 'locations', 'calendars', 'appointments', 'statuses',
        'service_location', 'calendar_service', 'jobs', 'failed_jobs', 'reminders',
        'logs', 'prices', 'orders', 'order_price', 'migrations',
    ])->and(MasterSchema::REFERENCE)->toBe('fcf2cfe296a240421ed91f28ff3672ece04e4786');
    foreach (['clients', 'services', 'locations', 'calendars', 'appointments', 'logs', 'orders'] as $name) {
        expect($tables[$name]['columns']['options']->type)->toBe('text')
            ->and($tables[$name]['columns']['created_at']->type)->toBe('timestamp')
            ->and($tables[$name]['columns']['created_at']->nullable)->toBeTrue();
    }
    foreach (['service_location', 'calendar_service'] as $name) {
        expect($tables[$name]['columns']['id']->autoIncrement)->toBeTrue()
            ->and($tables[$name]['indexes']['PRIMARY']['columns'])->toBe(['id']);
    }
    expect($tables['appointments']['columns']['recurrent']->type)->toBe('tinyint unsigned')
        ->and($tables['order_price']['columns']['quantity']->type)->toBe('smallint')
        ->and($tables['order_price']['columns']['quantity']->nullable)->toBeTrue()
        ->and($tables['order_price']['columns']['quantity']->default)->toBe(1)
        ->and($tables['reminders']['columns']['lang']->type)->toBe('varchar(6)')
        ->and($tables['jobs']['columns']['id']->type)->toBe('bigint unsigned')
        ->and($tables['jobs']['columns']['created_at']->type)->toBe('int unsigned');
});

test('column SQL preserves nullability defaults and integer sizes', function () {
    expect((new SchemaColumn('int unsigned', autoIncrement: true))->sql())->toBe('int unsigned NOT NULL AUTO_INCREMENT')
        ->and((new SchemaColumn('text', true))->sql())->toBe('text NULL DEFAULT NULL')
        ->and((new SchemaColumn('smallint', true, 1))->sql())->toBe("smallint NULL DEFAULT '1'")
        ->and((new SchemaColumn('timestamp', default: 'CURRENT_TIMESTAMP'))->sql())->toBe('timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
});

test('an empty database is distinct from a compatible database', function () {
    $report = schemaInspector([])->handle();
    expect($report->state)->toBe('empty')->and($report->issues)->toBe([]);
    expect(schemaInspector(masterSchemaSnapshot())->handle()->state)->toBe('compatible');
});

test('metadata report excludes records and does not infer add-on compatibility', function () {
    $snapshot = masterSchemaSnapshot();
    $snapshot['addon_owned'] = ['engine' => 'InnoDB', 'columns' => [], 'indexes' => [], 'foreignKeys' => []];
    $useCase = new GetDatabaseSchemaUseCase(schemaInspector($snapshot));
    $response = $useCase->execute(new GetDatabaseSchemaRequestData());
    $data = $response->jsonSerialize();
    expect($data['report']['state'])->toBe('compatible')
        ->and($data['report']['additionalTables'])->toBe(['addon_owned'])
        ->and(array_keys($data['report']))->toBe(['reference', 'state', 'issues', 'additionalTables']);
    expect(fn() => GetDatabaseSchemaRequestData::fromHttp(['repair' => true]))->toThrow(InvalidArgumentException::class);
});

test('partial databases report each missing core table without pretending to be fresh', function () {
    $snapshot = masterSchemaSnapshot();
    $report = schemaInspector(['clients' => $snapshot['clients']])->handle();
    expect($report->state)->toBe('incompatible')->and($report->issues)->toHaveCount(15);
    foreach ($report->issues as $issue) {
        expect($issue->kind)->toBe('missing_table');
    }
    expect(schemaInspector(['addon_owned' => []])->handle()->state)->toBe('incompatible');
});

test('schema checks ignore integer display widths and equivalent timestamp metadata', function () {
    $snapshot = masterSchemaSnapshot();
    $snapshot['clients']['columns']['id']['Type'] = 'int(10) unsigned';
    $snapshot['orders']['columns']['status']['Type'] = 'tinyint(4)';
    $snapshot['failed_jobs']['columns']['failed_at']['Default'] = 'current_timestamp()';
    $snapshot['failed_jobs']['columns']['failed_at']['Extra'] = 'DEFAULT_GENERATED';
    expect(schemaInspector($snapshot)->handle()->state)->toBe('compatible');
});

test('schema differences are typed and identify the affected member', function (Closure $change, string $table, string $kind, string $member) {
    $snapshot = masterSchemaSnapshot();
    $change($snapshot);
    $report = schemaInspector($snapshot)->handle();
    expect($report->state)->toBe('incompatible')->and($report->issues)->toHaveCount(1)
        ->and($report->issues[0]->table)->toBe($table)
        ->and($report->issues[0]->kind)->toBe($kind)
        ->and($report->issues[0]->member)->toBe($member);
    expect($report->jsonSerialize()['issues'][0]['expected'])->not->toBe('');
})->with([
    'missing column' => [static function (&$s) { unset($s['clients']['columns']['name']); }, 'clients', 'column', 'name'],
    'wrong options size' => [static function (&$s) { $s['clients']['columns']['options']['Type'] = 'longtext'; }, 'clients', 'column', 'options'],
    'wrong unsigned flag' => [static function (&$s) { $s['appointments']['columns']['recurrent']['Type'] = 'tinyint'; }, 'appointments', 'column', 'recurrent'],
    'wrong timestamp type' => [static function (&$s) { $s['clients']['columns']['created_at']['Type'] = 'datetime'; }, 'clients', 'column', 'created_at'],
    'wrong nullability' => [static function (&$s) { $s['order_price']['columns']['quantity']['Null'] = 'NO'; }, 'order_price', 'column', 'quantity'],
    'wrong default' => [static function (&$s) { $s['order_price']['columns']['quantity']['Default'] = '0'; }, 'order_price', 'column', 'quantity'],
    'missing increment' => [static function (&$s) { $s['clients']['columns']['id']['Extra'] = ''; }, 'clients', 'column', 'id'],
    'on update timestamp' => [static function (&$s) { $s['clients']['columns']['updated_at']['Extra'] = 'on update CURRENT_TIMESTAMP'; }, 'clients', 'column', 'updated_at'],
    'nontransactional table' => [static function (&$s) { $s['statuses']['engine'] = 'MyISAM'; }, 'statuses', 'engine', ''],
    'composite pivot primary' => [static function (&$s) { $s['calendar_service']['indexes']['PRIMARY']['columns'] = ['calendar_id', 'service_id']; }, 'calendar_service', 'index', 'PRIMARY'],
    'nonunique email' => [static function (&$s) { $s['clients']['indexes']['wappo_clients_email_unique']['unique'] = false; }, 'clients', 'index', 'wappo_clients_email_unique'],
    'truncated index' => [static function (&$s) { $s['clients']['indexes']['wappo_clients_email_unique']['prefix'] = true; }, 'clients', 'index', 'wappo_clients_email_unique'],
    'wrong index order' => [static function (&$s) { $s['jobs']['indexes']['wappo_jobs_queue_reserved_at_index']['columns'] = ['reserved_at', 'queue']; }, 'jobs', 'index', 'wappo_jobs_queue_reserved_at_index'],
    'wrong FK target site' => [static function (&$s) { $s['logs']['foreignKeys'][0]['target'] = 'external:other_wappo_clients'; }, 'logs', 'foreign_key', 'client_id'],
    'cascading deletion' => [static function (&$s) { $s['logs']['foreignKeys'][0]['DELETE_RULE'] = 'CASCADE'; }, 'logs', 'foreign_key', 'client_id'],
]);

test('schema metadata failures propagate instead of returning an empty or successful report', function () {
    $repository = $this->createMock(DatabaseSchemaRepository::class);
    $repository->method('snapshot')->willThrowException(new RuntimeException('Metadata access denied'));
    expect(fn() => (new InspectDatabaseSchemaAction($repository))->handle())->toThrow(RuntimeException::class, 'Metadata access denied');
});
