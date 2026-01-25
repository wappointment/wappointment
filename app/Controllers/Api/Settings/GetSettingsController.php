<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\JsonResponse;
use Wappointment\Repositories\SettingRepository;

class GetSettingsController
{
    public function __construct(
        private SettingRepository $repository
    ) {}

    public function __invoke(\WP_REST_Request $request): void
    {
        $settings = $this->repository->getAll();
        
        // Group settings by tab for the frontend
        $grouped = [
            'general' => [],
            'notifications' => [],
            'advanced' => []
        ];
        
        foreach ($settings as $key => $value) {
            // Categorize settings by prefix or known keys
            if (str_starts_with($key, 'notification_') || in_array($key, ['admin_email', 'reminder_enabled'])) {
                $grouped['notifications'][$key] = $value;
            } elseif (str_starts_with($key, 'advanced_') || in_array($key, ['buffer_time', 'timezone'])) {
                $grouped['advanced'][$key] = $value;
            } else {
                $grouped['general'][$key] = $value;
            }
        }
        
        JsonResponse::send(['settings' => $grouped]);
    }
}