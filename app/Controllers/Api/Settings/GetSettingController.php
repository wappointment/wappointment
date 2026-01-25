<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\JsonResponse;
use Wappointment\Repositories\SettingRepository;

class GetSettingController
{
    public function __construct(
        private SettingRepository $repository
    ) {}

    public function __invoke(\WP_REST_Request $request): void
    {
        $name = $request->get_param('name');
        
        if (empty($name)) {
            JsonResponse::send(['error' => 'Setting name is required'], 400);
            return;
        }
        
        $value = $this->repository->get($name);
        
        JsonResponse::send(['name' => $name, 'value' => $value]);
    }
}