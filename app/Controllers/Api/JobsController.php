<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

use Wappointment\Models\Job;

class JobsController extends BaseApiController
{
    public function __construct(
        private Job $model
    ) {}

    public function __invoke(\WP_REST_Request $request): void
    {
        $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        
        $result = $this->model->paginate($page, $perPage);
        
        $this->sendJson($result);
    }
}
