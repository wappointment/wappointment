<?php
declare(strict_types=1);

namespace Wappointment\Actions\Settings;

use Wappointment\System\Settings;

class SaveSettingsAction
{
    public function __construct(private Settings $settings) {}

    public function handle(array $data): array
    {
        if ($data === []) {
            throw new \InvalidArgumentException('Invalid settings data');
        }
        if (!$this->settings->setMultiple($data)) {
            throw new \RuntimeException('Failed to save settings');
        }
        return ['success' => true, 'message' => 'Settings saved successfully'];
    }
}
