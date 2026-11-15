<?php
declare(strict_types=1);

namespace Wappointment\Actions\Settings;

use Wappointment\Auth\AccessPolicy;
use Wappointment\Http\Requests\GetOwnCalendarStatusesRequestData;
use Wappointment\Repositories\OwnCalendarStatusRepository;

class ListOwnCalendarStatusesAction
{
    public function __construct(private AccessPolicy $access, private OwnCalendarStatusRepository $repository) {}

    public function handle(GetOwnCalendarStatusesRequestData $requestData): array
    {
        $this->access->assert($requestData->scope === 'admin' ? 'manage' : 'self.statuses');
        $owner = $requestData->scope === 'admin' ? null : get_current_user_id();
        return ['statuses' => $this->repository->all($requestData->id, $owner)];
    }
}
