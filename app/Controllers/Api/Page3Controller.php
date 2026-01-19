<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

class Page3Controller extends BaseApiController
{
    public function __invoke(): void
    {
        $this->sendJson([
            'title' => 'Page 3',
            'message' => 'Hello World from Page 3!',
            'description' => 'This is the third admin page in our Wappointment plugin.',
            'timestamp' => time()
        ]);
    }
}
