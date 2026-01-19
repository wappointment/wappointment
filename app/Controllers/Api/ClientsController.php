<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

use Wappointment\Models\Client;

class ClientsController extends BaseApiController
{
    public function __invoke(): void
    {
        $clientModel = new Client();
        
        $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        
        $result = $clientModel->paginate($page, $perPage);
        
        $this->sendJson($result);
    }
}
