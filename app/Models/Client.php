<?php
declare(strict_types=1);

namespace Wappointment\Models;

/**
 * Client Model - Defines wp_wappo_clients table schema
 */
class Client extends BaseModel
{
    protected string $tableName = 'wappo_clients';
    
    protected array $columns = [
        'id' => 'int',
        'email' => 'string',
        'name' => 'string',
        'options' => 'json',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected array $searchableColumns = ['name', 'email'];
}
