<?php
declare(strict_types=1);

namespace Wappointment\Actions\Settings;

use Wappointment\Auth\AccessPolicy;
use Wappointment\Http\Requests\DeleteOwnCalendarStatusRequestData;
use Wappointment\Repositories\OwnCalendarStatusRepository;

class DeleteOwnCalendarStatusAction
{
    public function __construct(private AccessPolicy $access, private OwnCalendarStatusRepository $repository) {}

    public function handle(DeleteOwnCalendarStatusRequestData $requestData): array
    {
        $this->access->assert('self.statuses');
        $this->repository->delete($requestData->id, $requestData->status_id, get_current_user_id());
        return ['deleted' => true];
    }
}
