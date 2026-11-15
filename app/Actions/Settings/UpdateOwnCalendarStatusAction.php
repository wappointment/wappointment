<?php
declare(strict_types=1);

namespace Wappointment\Actions\Settings;

use Wappointment\Auth\AccessPolicy;
use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\UpdateOwnCalendarStatusRequestData;
use Wappointment\Repositories\OwnCalendarStatusRepository;

class UpdateOwnCalendarStatusAction
{
    public function __construct(private AccessPolicy $access, private OwnCalendarStatusRepository $repository) {}

    public function handle(UpdateOwnCalendarStatusRequestData $requestData): array
    {
        $this->access->assert($requestData->scope === 'admin' ? 'manage' : 'self.statuses');
        if ($requestData->start < 0 || $requestData->end > 253402300799
            || $requestData->end <= $requestData->start
            || $requestData->end - $requestData->start > 366 * DAY_IN_SECONDS) {
            throw new BookingException(__('Choose an end time after the start, within one year.', 'wappointment'));
        }
        $owner = $requestData->scope === 'admin' ? null : get_current_user_id();
        return ['status' => $this->repository->update(
            $requestData->id,
            $requestData->status_id,
            $owner,
            $requestData->type,
            $requestData->start,
            $requestData->end,
            $requestData->recur,
            $requestData->interval,
            $requestData->weekdays,
            $requestData->month_day,
            $requestData->until
        )];
    }
}
