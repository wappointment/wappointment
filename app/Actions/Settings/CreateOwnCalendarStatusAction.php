<?php
declare(strict_types=1);

namespace Wappointment\Actions\Settings;

use Wappointment\Booking\BookingException;
use Wappointment\Auth\AccessPolicy;
use Wappointment\Http\Requests\CreateOwnCalendarStatusRequestData;
use Wappointment\Repositories\OwnCalendarStatusRepository;

class CreateOwnCalendarStatusAction
{
    public function __construct(private AccessPolicy $access, private OwnCalendarStatusRepository $repository) {}

    public function handle(CreateOwnCalendarStatusRequestData $requestData): array
    {
        $this->access->assert('self.statuses');
        if ($requestData->end <= $requestData->start || $requestData->end - $requestData->start > 366 * DAY_IN_SECONDS) {
            throw new BookingException(__('Choose an end time after the start, within one year.', 'wappointment'));
        }
        return ['status' => $this->repository->create(
            $requestData->id,
            get_current_user_id(),
            $requestData->type,
            $requestData->start,
            $requestData->end
        )];
    }
}
