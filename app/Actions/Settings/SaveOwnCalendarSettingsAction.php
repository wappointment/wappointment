<?php
declare(strict_types=1);

namespace Wappointment\Actions\Settings;

use Wappointment\Auth\AccessPolicy;
use Wappointment\Http\Data\CalendarWeeklySettingsData;
use Wappointment\Http\Requests\SaveOwnCalendarSettingsRequestData;
use Wappointment\Repositories\OwnCalendarSettingsRepository;

class SaveOwnCalendarSettingsAction
{
    public function __construct(private AccessPolicy $access, private OwnCalendarSettingsRepository $repository) {}

    public function handle(SaveOwnCalendarSettingsRequestData $requestData): array
    {
        $this->access->assert('self.weekly');
        $settings = CalendarWeeklySettingsData::from($requestData->settings->toArray());
        return ['calendar' => $this->repository->save($requestData->id, get_current_user_id(), $settings)];
    }
}
