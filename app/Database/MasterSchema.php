<?php
declare(strict_types=1);

namespace Wappointment\Database;

final class MasterSchema
{
    public const REFERENCE = 'fcf2cfe296a240421ed91f28ff3672ece04e4786';

    /**
     * Final free-plugin schema after the 28 master migrations, in creation order.
     *
     * @return array<string, array{
     *     columns: array<string, SchemaColumn>,
     *     indexes: array<string, array{columns: list<string>, unique: bool}>,
     *     foreignKeys: array<string, string>
     * }>
     */
    public static function tables(): array
    {
        $id = new SchemaColumn('int unsigned', autoIncrement: true);
        $options = new SchemaColumn('text', true);
        $timestamps = [
            'created_at' => new SchemaColumn('timestamp', true),
            'updated_at' => new SchemaColumn('timestamp', true),
        ];
        $softDeletes = ['deleted_at' => new SchemaColumn('timestamp', true)];
        $primary = ['PRIMARY' => ['columns' => ['id'], 'unique' => true]];
        $tables = [
            'clients' => [
                'columns' => [
                    'id' => $id,
                    'email' => new SchemaColumn('varchar(191)'),
                    'name' => new SchemaColumn('varchar(255)'),
                    'options' => $options,
                    ...$softDeletes,
                    ...$timestamps,
                ],
                'indexes' => ['wappo_clients_email_unique' => ['columns' => ['email'], 'unique' => true]],
            ],
            'services' => [
                'columns' => [
                    'id' => $id,
                    'name' => new SchemaColumn('varchar(255)'),
                    'sorting' => new SchemaColumn('tinyint unsigned', default: 0),
                    'options' => $options,
                    ...$softDeletes,
                    ...$timestamps,
                ],
            ],
            'locations' => [
                'columns' => [
                    'id' => $id,
                    'name' => new SchemaColumn('varchar(255)'),
                    'type' => new SchemaColumn('tinyint unsigned', default: 1),
                    'status' => new SchemaColumn('tinyint', default: 0),
                    'options' => $options,
                    ...$softDeletes,
                    ...$timestamps,
                ],
            ],
            'calendars' => [
                'columns' => [
                    'id' => $id,
                    'name' => new SchemaColumn('varchar(255)'),
                    'account_key' => new SchemaColumn('varchar(255)', true),
                    'wp_uid' => new SchemaColumn('bigint unsigned', default: 0),
                    'status' => new SchemaColumn('tinyint unsigned', default: 1),
                    'sorting' => new SchemaColumn('tinyint unsigned', default: 0),
                    'options' => $options,
                    'availability' => new SchemaColumn('mediumtext', true),
                    ...$softDeletes,
                    ...$timestamps,
                ],
            ],
            'appointments' => [
                'columns' => [
                    'id' => $id,
                    'start_at' => new SchemaColumn('datetime'),
                    'end_at' => new SchemaColumn('datetime'),
                    'type' => new SchemaColumn('tinyint unsigned', default: 0),
                    'status' => new SchemaColumn('tinyint', default: 0),
                    'edit_key' => new SchemaColumn('varchar(32)', true),
                    ...$timestamps,
                    'options' => $options,
                    'staff_id' => new SchemaColumn('int unsigned', true),
                    'service_id' => new SchemaColumn('int unsigned', true),
                    'client_id' => new SchemaColumn('int unsigned', true),
                    'location_id' => new SchemaColumn('int unsigned', true),
                    'recurrent' => new SchemaColumn('tinyint unsigned', default: 0),
                    'parent' => new SchemaColumn('int unsigned', true),
                ],
                'indexes' => [
                    'unique_staff_id_status_start_end' => [
                        'columns' => ['staff_id', 'status', 'start_at', 'end_at'], 'unique' => true,
                    ],
                ],
                'foreignKeys' => ['client_id' => 'clients', 'service_id' => 'services', 'location_id' => 'locations'],
            ],
            'statuses' => [
                'columns' => [
                    'id' => $id,
                    'type' => new SchemaColumn('tinyint unsigned', default: 0),
                    'start_at' => new SchemaColumn('datetime'),
                    'end_at' => new SchemaColumn('datetime'),
                    'recur' => new SchemaColumn('tinyint unsigned', default: 0),
                    'muted' => new SchemaColumn('tinyint unsigned', default: 0),
                    'source' => new SchemaColumn('varchar(32)', true),
                    'eventkey' => new SchemaColumn('varchar(32)', true),
                    'options' => $options,
                    'staff_id' => new SchemaColumn('int unsigned', true),
                ],
                'indexes' => [
                    'unique_source_eventkey' => ['columns' => ['source', 'eventkey'], 'unique' => true],
                    'unique_staff_id_type_start_end_source_eventkey' => [
                        'columns' => ['staff_id', 'type', 'start_at', 'end_at', 'source', 'eventkey'], 'unique' => true,
                    ],
                ],
            ],
            'service_location' => [
                'columns' => [
                    'id' => $id,
                    'service_id' => new SchemaColumn('int unsigned'),
                    'location_id' => new SchemaColumn('int unsigned'),
                ],
                'foreignKeys' => ['service_id' => 'services', 'location_id' => 'locations'],
            ],
            'calendar_service' => [
                'columns' => [
                    'id' => $id,
                    'service_id' => new SchemaColumn('int unsigned'),
                    'calendar_id' => new SchemaColumn('int unsigned'),
                ],
                'foreignKeys' => ['service_id' => 'services', 'calendar_id' => 'calendars'],
            ],
            'jobs' => [
                'columns' => [
                    'id' => new SchemaColumn('bigint unsigned', autoIncrement: true),
                    'queue' => new SchemaColumn('varchar(25)', true),
                    'payload' => new SchemaColumn('longtext'),
                    'appointment_id' => new SchemaColumn('int unsigned', true),
                    'attempts' => new SchemaColumn('tinyint unsigned', default: 0),
                    'reserved_at' => new SchemaColumn('int unsigned', default: 0),
                    'available_at' => new SchemaColumn('int unsigned', default: 0),
                    'created_at' => new SchemaColumn('int unsigned'),
                ],
                'indexes' => ['wappo_jobs_queue_reserved_at_index' => ['columns' => ['queue', 'reserved_at'], 'unique' => false]],
            ],
            'failed_jobs' => [
                'columns' => [
                    'id' => $id,
                    'queue' => new SchemaColumn('text'),
                    'appointment_id' => new SchemaColumn('int unsigned', true),
                    'payload' => new SchemaColumn('longtext'),
                    'exception' => new SchemaColumn('longtext'),
                    'failed_at' => new SchemaColumn('timestamp', default: 'CURRENT_TIMESTAMP'),
                ],
            ],
            'reminders' => [
                'columns' => [
                    'id' => $id,
                    'subject' => new SchemaColumn('varchar(255)', true),
                    'type' => new SchemaColumn('tinyint', default: 1),
                    'event' => new SchemaColumn('tinyint', default: 1),
                    'locked' => new SchemaColumn('tinyint', default: 0),
                    'published' => new SchemaColumn('tinyint', default: 0),
                    'options' => new SchemaColumn('mediumtext', true),
                    ...$timestamps,
                    'lang' => new SchemaColumn('varchar(6)', true),
                    'parent' => new SchemaColumn('int unsigned', true),
                ],
            ],
            'logs' => [
                'columns' => [
                    'id' => $id,
                    'type' => new SchemaColumn('tinyint unsigned', default: 0),
                    ...$timestamps,
                    'options' => $options,
                    'client_id' => new SchemaColumn('int unsigned', true),
                ],
                'foreignKeys' => ['client_id' => 'clients'],
            ],
            'prices' => [
                'columns' => [
                    'id' => $id,
                    'name' => new SchemaColumn('varchar(255)'),
                    'type' => new SchemaColumn('tinyint unsigned', default: 0),
                    'reference_id' => new SchemaColumn('int unsigned'),
                    'staff_id' => new SchemaColumn('int unsigned', true),
                    'price' => new SchemaColumn('mediumint unsigned', true),
                    'parent' => new SchemaColumn('int unsigned', true),
                    ...$softDeletes,
                    ...$timestamps,
                ],
            ],
            'orders' => [
                'columns' => [
                    'id' => $id,
                    'transaction_id' => new SchemaColumn('varchar(64)', true),
                    'status' => new SchemaColumn('tinyint', default: 0),
                    'total' => new SchemaColumn('mediumint unsigned', true),
                    'tax_percent' => new SchemaColumn('mediumint unsigned', default: 0),
                    'tax_amount' => new SchemaColumn('mediumint unsigned', default: 0),
                    'currency' => new SchemaColumn('varchar(3)', true),
                    'refunded_at' => new SchemaColumn('timestamp', true),
                    'paid_at' => new SchemaColumn('timestamp', true),
                    'payment' => new SchemaColumn('tinyint', default: 0),
                    'client_id' => new SchemaColumn('int unsigned', true),
                    'options' => $options,
                    ...$softDeletes,
                    ...$timestamps,
                ],
                'indexes' => ['wappo_orders_transaction_id_unique' => ['columns' => ['transaction_id'], 'unique' => true]],
            ],
            'order_price' => [
                'columns' => [
                    'id' => $id,
                    'order_id' => new SchemaColumn('int unsigned'),
                    'price_id' => new SchemaColumn('int unsigned'),
                    'item_name' => new SchemaColumn('varchar(255)'),
                    'price_value' => new SchemaColumn('mediumint unsigned'),
                    'appointment_id' => new SchemaColumn('int unsigned', true),
                    ...$softDeletes,
                    ...$timestamps,
                    'quantity' => new SchemaColumn('smallint', true, 1),
                ],
                'foreignKeys' => ['order_id' => 'orders', 'price_id' => 'prices', 'appointment_id' => 'appointments'],
            ],
            'migrations' => [
                'columns' => [
                    'id' => $id,
                    'migration' => new SchemaColumn('varchar(255)'),
                    'batch' => new SchemaColumn('int'),
                ],
            ],
        ];
        foreach ($tables as &$table) {
            $table['indexes'] = $primary + ($table['indexes'] ?? []);
            $table['foreignKeys'] ??= [];
        }
        unset($table);
        return $tables;
    }
}
