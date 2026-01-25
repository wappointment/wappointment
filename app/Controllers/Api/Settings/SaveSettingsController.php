<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\JsonResponse;
use Wappointment\Repositories\SettingRepository;

class SaveSettingsController
{
    public function __construct(
        private SettingRepository $repository
    ) {}

    public function __invoke(\WP_REST_Request $request): void
    {
        $data = $request->get_json_params();
        
        if (empty($data) || !is_array($data)) {
            JsonResponse::send(['error' => 'Invalid settings data'], 400);
            return;
        }
        
        $success = $this->repository->setMultiple($data);
        
        if ($success) {
            JsonResponse::send(['success' => true, 'message' => 'Settings saved successfully']);
        } else {
            JsonResponse::send(['error' => 'Failed to save some settings'], 500);
        }
    }
}