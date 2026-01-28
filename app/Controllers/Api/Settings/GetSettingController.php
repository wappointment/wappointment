<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\JsonResponse;
use Wappointment\System\Settings;

class GetSettingController
{
    public function __construct(
        private Settings $settings
    ) {}

    public function __invoke(\WP_REST_Request $request): void
    {
        $name = $request->get_param('name');
        
        if (empty($name)) {
            JsonResponse::send(['error' => 'Setting name is required'], 400);
            return;
        }
        
        $value = $this->settings->get($name);
        
        JsonResponse::send(['name' => $name, 'value' => $value]);
    }
}