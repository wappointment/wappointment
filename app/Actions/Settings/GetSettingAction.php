<?php
declare(strict_types=1);

namespace Wappointment\Actions\Settings;

use Wappointment\System\Settings;

class GetSettingAction
{
    public function __construct(private Settings $settings) {}

    public function handle(string $name): array
    {
        if ($name === '') {
            throw new \InvalidArgumentException('Setting name is required');
        }
        return ['name' => $name, 'value' => $this->settings->get($name)];
    }
}
