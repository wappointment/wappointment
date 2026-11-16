<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

use Wappointment\Actions\Notifications\DeliverBookingNotificationAction;

final class BookingNotificationJob implements JobHandler
{
    public function __construct(private DeliverBookingNotificationAction $deliver) {}

    public function handle(QueuedJob $job): void
    {
        $this->deliver->handle($job->params);
    }

    public function retriesAfterTimeout(): bool
    {
        // An interrupted send may already have reached the recipient.
        return false;
    }
}
