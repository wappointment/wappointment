<?php
declare(strict_types=1);

namespace Wappointment\Actions\Settings;

use Wappointment\System\Settings;

class GetSettingsAction
{
    public function __construct(private Settings $settings) {}

    public function handle(): array
    {
        $grouped = ['general' => [], 'notifications' => [], 'advanced' => []];
        foreach ($this->settings->all() as $key => $value) {
            if (str_starts_with($key, 'notify_') || str_starts_with($key, 'email_') || in_array($key, ['mail_status', 'mail_config'])) {
                $grouped['notifications'][$key] = $value;
            } elseif (in_array($key, ['buffer_time', 'scheduler_mode', 'force_ugly_permalinks', 'cache'])) {
                $grouped['advanced'][$key] = $value;
            } else {
                $grouped['general'][$key] = $value;
            }
        }
        return ['settings' => $grouped];
    }
}
