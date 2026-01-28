<?php
declare(strict_types=1);

namespace Wappointment\Models;

/**
 * Job Model - Handles wp_wappo_jobs table
 */
class Job extends BaseModel
{
    protected string $tableName = 'wappo_jobs';
    
    protected array $columns = [
        'id' => 'int',
        'queue' => 'string',
        'payload' => 'json',
        'appointment_id' => 'int',
        'attempts' => 'int',
        'reserved_at' => 'int',
        'available_at' => 'int',
        'created_at' => 'int',
    ];
}
