<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\JsonResponse;
use Wappointment\System\Settings;

class GetSettingsController
{
    public function __construct(
        private Settings $settings
    ) {}

    public function __invoke(\WP_REST_Request $request): void
    {
        $allSettings = $this->settings->all();
        
        // Group settings by tab for the frontend
        $grouped = [
            'general' => [],
            'notifications' => [],
            'advanced' => []
        ];
        
        foreach ($allSettings as $key => $value) {
            // Categorize settings by prefix or known keys
            if (str_starts_with($key, 'notify_') || str_starts_with($key, 'email_') || in_array($key, ['mail_status', 'mail_config'])) {
                $grouped['notifications'][$key] = $value;
            } elseif (in_array($key, ['buffer_time', 'scheduler_mode', 'force_ugly_permalinks', 'cache'])) {
                $grouped['advanced'][$key] = $value;
            } else {
                $grouped['general'][$key] = $value;
            }
        }
        
        JsonResponse::send(['settings' => $grouped]);
    }
}