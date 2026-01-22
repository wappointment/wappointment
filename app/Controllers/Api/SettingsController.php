<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

use Wappointment\Repositories\SettingRepository;

class SettingsController extends BaseApiController
{
    public function __construct(
        private SettingRepository $repository
    ) {}

    /**
     * Get all settings
     */
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
        
        $this->sendJson(['settings' => $grouped]);
    }

    /**
     * Save settings
     */
    public function save(\WP_REST_Request $request): void
    {
        $data = $request->get_json_params();
        
        if (empty($data) || !is_array($data)) {
            $this->sendJson(['error' => 'Invalid settings data'], 400);
            return;
        }
        
        $success = $this->repository->setMultiple($data);
        
        if ($success) {
            $this->sendJson(['success' => true, 'message' => 'Settings saved successfully']);
        } else {
            $this->sendJson(['error' => 'Failed to save some settings'], 500);
        }
    }

    /**
     * Get a specific setting
     */
    public function getSetting(\WP_REST_Request $request): void
    {
        $name = $request->get_param('name');
        
        if (empty($name)) {
            $this->sendJson(['error' => 'Setting name is required'], 400);
            return;
        }
        
        $value = $this->repository->get($name);
        
        $this->sendJson(['name' => $name, 'value' => $value]);
    }
}
