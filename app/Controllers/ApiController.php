<?php
declare(strict_types=1);

namespace Wappointment\Controllers;

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
     * Send JSON response
     */
    private function sendJson(array $data): void
    {
        wp_send_json_success($data);
    }
}
