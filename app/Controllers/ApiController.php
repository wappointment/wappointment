<?php
declare(strict_types=1);

namespace Wappointment\Controllers;

use Wappointment\Models\Job;
use Wappointment\Models\Client;

/**
 * API controller handling JSON endpoints
 */
class ApiController
{
    /**
     * Get data for page 1
     */
    public function getPage1Data(): void
    {
        $this->sendJson([
            'title' => 'Page 1',
            'message' => 'Hello World from Page 1!',
            'description' => 'This is the first admin page in our Wappointment plugin.',
            'timestamp' => time()
        ]);
    }

    /**
     * Get data for page 2
     */
    public function getPage2Data(): void
    {
        $this->sendJson([
            'title' => 'Page 2',
            'message' => 'Hello World from Page 2!',
            'description' => 'This is the second admin page in our Wappointment plugin.',
            'timestamp' => time()
        ]);
    }

    /**
     * Get data for page 3
     */
    public function getPage3Data(): void
    {
        $this->sendJson([
            'title' => 'Page 3',
            'message' => 'Hello World from Page 3!',
            'description' => 'This is the third admin page in our Wappointment plugin.',
            'timestamp' => time()
        ]);
    }

    /**
     * Get jobs data
     */
    public function getJobsData(): void
    {
        $jobModel = new Job();
        
        // Get pagination parameters from request
        $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        
        $result = $jobModel->paginate($page, $perPage);
        
        $this->sendJson($result);
    }

    /**
     * Get clients data
     */
    public function getClientsData(): void
    {
        $clientModel = new Client();
        
        // Get pagination parameters from request
        $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        
        $result = $clientModel->paginate($page, $perPage);
        
        $this->sendJson($result);
    }

    /**
     * Send JSON response
     */
    private function sendJson(array $data): void
    {
        wp_send_json_success($data);
    }
}
