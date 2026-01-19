<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

class Page1Controller extends BaseApiController
{
    public function __invoke(): void
    {
        $this->sendJson([
            'title' => 'Page 1',
            'message' => 'Hello World from Page 1!',
            'description' => 'This is the first admin page in our Wappointment plugin.',
            'timestamp' => time()
        ]);
    }
}
