<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

use Wappointment\Database\WpDbConnector;
use Wappointment\Models\Job;

/**
 * Job Repository - Handles wp_wappo_jobs table operations
 */
class JobRepository extends BaseRepository
{
    public function __construct(
        WpDbConnector $db,
        Job $model
    ) {
        parent::__construct($db, $model);
    }
}
