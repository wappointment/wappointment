<?php
declare(strict_types=1);

namespace Wappointment\Models;

/**
 * Client Model - Handles wp_wappo_clients table
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
    
    protected function getSearchableColumns(): array
    {
        return ['name', 'email'];
    }
}
