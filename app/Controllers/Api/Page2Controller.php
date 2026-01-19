<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

class Page2Controller extends BaseApiController
{
    public function __invoke(): void
    {
        $this->sendJson([
            'title' => 'Page 2',
            'message' => 'Hello World from Page 2!',
            'description' => 'This is the second admin page in our Wappointment plugin.',
            'timestamp' => time()
        ]);
    }
}
