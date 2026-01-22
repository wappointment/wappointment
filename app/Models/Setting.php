<?php
declare(strict_types=1);

namespace Wappointment\Models;

/**
 * Setting Model - Defines wp_wappo_options table schema
 */
class Setting extends BaseModel
{
    protected string $tableName = 'wappo_options';
    
    protected array $columns = [
        'id' => 'int',
        'name' => 'string',
        'value' => 'text',
        'type' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}