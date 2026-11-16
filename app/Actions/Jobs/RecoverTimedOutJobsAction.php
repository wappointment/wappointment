<?php
declare(strict_types=1);

namespace Wappointment\Actions\Jobs;

use Wappointment\Jobs\JobRegistry;
use Wappointment\Jobs\QueuedJob;
use Wappointment\Jobs\RetryPolicy;
use Wappointment\Jobs\UnsupportedJobException;
use Wappointment\Repositories\JobQueueRepository;
use Wappointment\System\Container;

/**
 * Handle reservations left behind by a worker that stopped mid-job. Jobs whose effect
 * cannot be detected afterwards (email) are moved to failed jobs instead of being run
 * again, so a crash can never send a duplicate. Must run while holding the worker lock.
 */
class RecoverTimedOutJobsAction
{
    public function __construct(private JobQueueRepository $queue, private Container $container) {}

    /** @return array{recovered: int, abandoned: int} */
    public function handle(int $now): array
    {
        $result = ['recovered' => 0, 'abandoned' => 0];
        foreach ($this->queue->timedOut(JobRegistry::queues(), $now - RetryPolicy::RESERVATION_TIMEOUT, 50) as $row) {
            try {
                $type = QueuedJob::fromRow($row)->type;
                $retry = $this->container->make(JobRegistry::handlerFor($type))->retriesAfterTimeout();
            } catch (UnsupportedJobException) {
                $retry = false;
            }
            $next = $retry ? RetryPolicy::nextAttemptAt((int) $row['attempts'], $now) : null;
            if ($next !== null) {
                if ($this->queue->releaseTimedOut((int) $row['id'], (int) $row['reserved_at'], $next)) {
                    $result['recovered']++;
                }
                continue;
            }
            $this->queue->fail($row, [
                'reason' => 'timeout',
                'message' => $retry
                    ? 'The worker stopped during the final attempt.'
                    : 'The worker stopped while this job was running. Its outcome is unknown, so it was not retried to avoid a duplicate delivery.',
                'attempts' => (int) $row['attempts'],
                'reserved_at' => (int) $row['reserved_at'],
            ], $now);
            $result['abandoned']++;
        }
        return $result;
    }
}
