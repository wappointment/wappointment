<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

use Wappointment\Repositories\JobRepository;

class JobsController extends BaseApiController
{
    public function __construct(
        private JobRepository $repository
    ) {}

    public function __invoke(\WP_REST_Request $request): void
    {
        $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        
        $result = $this->repository->paginate($page, $perPage);
        
        $this->sendJson($result);
    }
}
