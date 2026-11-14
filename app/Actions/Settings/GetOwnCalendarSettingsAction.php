<?php
declare(strict_types=1);

namespace Wappointment\Actions\Settings;

use Wappointment\Auth\AccessPolicy;
use Wappointment\Repositories\OwnCalendarSettingsRepository;

class GetOwnCalendarSettingsAction
{
    public function __construct(private AccessPolicy $access, private OwnCalendarSettingsRepository $repository) {}

    public function handle(): array
    {
        $this->access->assert('self.view');
        return ['calendars' => $this->repository->all(get_current_user_id())];
    }
}
