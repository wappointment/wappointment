<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

use Wappointment\Database\WpDbConnector;
use Wappointment\Models\Client;

/**
 * Client Repository - Handles wp_wappo_clients table operations
 */
class ClientRepository extends BaseRepository
{
    public function __construct(
        WpDbConnector $db,
        Client $model
    ) {
        parent::__construct($db, $model);
    }
}
