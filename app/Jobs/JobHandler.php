<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

interface JobHandler
{
    /**
     * Perform the job's single effect. Throw PermanentJobFailure for failures that must
     * not be retried; any other throwable is retried under the RetryPolicy.
     */
    public function handle(QueuedJob $job): void;

    /**
     * Whether a reservation that timed out (worker died mid-run) may be run again.
     * Return false for effects that cannot be detected afterwards, such as email,
     * so a crash can never cause a duplicate delivery.
     */
    public function retriesAfterTimeout(): bool;
}
