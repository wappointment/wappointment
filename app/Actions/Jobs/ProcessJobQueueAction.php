<?php
declare(strict_types=1);

namespace Wappointment\Actions\Jobs;

use Wappointment\Jobs\JobRegistry;
use Wappointment\Jobs\PermanentJobFailure;
use Wappointment\Jobs\QueuedJob;
use Wappointment\Jobs\RetryPolicy;
use Wappointment\Jobs\UnsupportedJobException;
use Wappointment\Repositories\JobQueueRepository;
use Wappointment\System\Container;

/**
 * One worker pass: recover stale reservations, keep organizer summaries scheduled,
 * then claim and run due v3 jobs.
 * Only one worker runs at a time per site; a second concurrent pass returns `locked`.
 */
class ProcessJobQueueAction
{
    public const BATCH = 10;
    public const TIME_BUDGET_SECONDS = 20;

    public function __construct(
        private JobQueueRepository $queue,
        private RecoverTimedOutJobsAction $recover,
        private ScheduleSummariesAction $summaries,
        private Container $container
    ) {}

    /**
     * @return array{locked: bool, completed: int, retried: int, failed: int, quarantined: int,
     *               recovered: int, abandoned: int, scheduled: int, unscheduled: int,
     *               legacy_pending: array<string, int>}
     */
    public function handle(int $now): array
    {
        $report = ['locked' => false, 'completed' => 0, 'retried' => 0, 'failed' => 0, 'quarantined' => 0,
            'recovered' => 0, 'abandoned' => 0, 'scheduled' => 0, 'unscheduled' => 0, 'legacy_pending' => []];
        $ran = $this->queue->withWorkerLock(function () use (&$report, $now): void {
            $report = array_merge($report, $this->recover->handle($now));
            try {
                $report = array_merge($report, $this->summaries->handle($now));
            } catch (\Throwable $error) {
                // Summary scheduling must never hold up the jobs that are already due.
                error_log('Wappointment: organizer summaries could not be scheduled: ' . $error->getMessage());
            }
            $deadline = microtime(true) + self::TIME_BUDGET_SECONDS;
            foreach ($this->queue->available(JobRegistry::queues(), $now, self::BATCH) as $row) {
                if (microtime(true) > $deadline) {
                    break;
                }
                if (!$this->queue->claim((int) $row['id'], $now)) {
                    continue;
                }
                $row['attempts'] = (int) $row['attempts'] + 1;
                $row['reserved_at'] = $now;
                $report[$this->run($row, $now)]++;
            }
        });
        $report['locked'] = !$ran;
        $report['legacy_pending'] = $this->queue->pendingOutside(JobRegistry::queues());
        return $report;
    }

    /** @return 'completed'|'retried'|'failed'|'quarantined' */
    private function run(array $row, int $now): string
    {
        try {
            $job = QueuedJob::fromRow($row);
            if (JobRegistry::queueFor($job->type) !== $job->queue) {
                throw new UnsupportedJobException('Job type "' . $job->type . '" is not accepted on queue "' . $job->queue . '".');
            }
            $this->container->make(JobRegistry::handlerFor($job->type))->handle($job);
        } catch (UnsupportedJobException $error) {
            $this->queue->fail($row, $this->describe('quarantined', $error, $row), $now);
            return 'quarantined';
        } catch (PermanentJobFailure $error) {
            $this->queue->fail($row, $this->describe('failed', $error, $row), $now);
            return 'failed';
        } catch (\Throwable $error) {
            $next = RetryPolicy::nextAttemptAt((int) $row['attempts'], $now);
            error_log('Wappointment: job ' . $row['id'] . ' attempt ' . $row['attempts'] . ' failed: ' . $error->getMessage());
            if ($next === null) {
                $this->queue->fail($row, $this->describe('failed', $error, $row), $now);
                return 'failed';
            }
            $this->queue->release((int) $row['id'], $next);
            return 'retried';
        }
        // Outside the try: if removing a delivered job fails, the reservation is left to
        // timeout recovery, which never re-sends, rather than being released for a retry.
        $this->queue->complete((int) $row['id']);
        return 'completed';
    }

    private function describe(string $reason, \Throwable $error, array $row): array
    {
        return [
            'reason' => $reason,
            'message' => $error->getMessage(),
            'exception' => $error::class,
            'attempts' => (int) $row['attempts'],
        ];
    }
}
