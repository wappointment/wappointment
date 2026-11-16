<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

use Wappointment\Actions\Notifications\DeliverAdminSummaryAction;

final class AdminSummaryJob implements JobHandler
{
    public function __construct(private DeliverAdminSummaryAction $deliver) {}

    public function handle(QueuedJob $job): void
    {
        // The worker stamps the claim with its clock; the period checks use the same time.
        $this->deliver->handle($job->params, $job->reservedAt > 0 ? $job->reservedAt : time());
    }

    public function retriesAfterTimeout(): bool
    {
        // An interrupted send may already have reached the recipients.
        return false;
    }
}
