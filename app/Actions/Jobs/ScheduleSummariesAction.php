<?php
declare(strict_types=1);

namespace Wappointment\Actions\Jobs;

use Wappointment\Jobs\JobRegistry;
use Wappointment\Jobs\QueuedJob;
use Wappointment\Jobs\SummarySchedule;
use Wappointment\Repositories\JobQueueRepository;
use Wappointment\Repositories\SummaryRepository;
use Wappointment\System\Settings;

/**
 * Keeps exactly one pending summary job per enabled kind and eligible calendar. Runs at
 * the start of every worker pass, under the worker lock, so it never races itself.
 *
 * Legacy master chained jobs: each summary queued the next one when it finished, and
 * saving the settings or a calendar re-queued them (`Settings::*_summarySaved`,
 * `Calendars::autoRecordNotification`). A failed send or a lost row ended the chain.
 * v3 derives the pending job from the current settings on each pass instead:
 *
 * - a pending job is kept while its send time still matches the settings, even if it is
 *   overdue (the worker then sends it, or fails it visibly once its period has begun);
 * - pending jobs for a turned-off kind, a calendar that no longer qualifies, or a send
 *   time that no longer matches are deleted;
 * - otherwise the next period is queued, never one already sent, so moving the send time
 *   cannot produce a second summary for the same day or week.
 *
 * Legacy rows on the `daily`/`weekly` queues are left untouched for the cutover work.
 */
class ScheduleSummariesAction
{
    public function __construct(
        private JobQueueRepository $queue,
        private SummaryRepository $summaries,
        private Settings $settings
    ) {}

    /** @return array{scheduled: int, unscheduled: int} */
    public function handle(int $now): array
    {
        $report = ['scheduled' => 0, 'unscheduled' => 0];
        $queue = JobRegistry::queueFor(JobRegistry::ADMIN_SUMMARY);
        $pending = [];
        foreach ($this->queue->unclaimed($queue) as $row) {
            $job = $this->summaryJob($row);
            if ($job !== null) {
                $pending[SummaryRepository::key($job['kind'], $job['calendar_id'])][] = $job;
            }
        }

        $settings = $this->settings->all();
        $lastSent = $this->summaries->lastSent();
        $calendars = array_filter(SummarySchedule::KINDS, static fn(string $kind): bool => SummarySchedule::enabled($kind, $settings))
            ? $this->summaries->calendars() : [];
        foreach (SummarySchedule::KINDS as $kind) {
            if (!SummarySchedule::enabled($kind, $settings)) {
                continue;
            }
            foreach ($calendars as $calendar) {
                $key = SummaryRepository::key($kind, $calendar['id']);
                $zone = $this->zone($calendar['timezone']);
                $sent = $lastSent[$key] ?? null;
                $kept = false;
                foreach ($pending[$key] ?? [] as $job) {
                    $matches = !$kept
                        && ($sent === null || $job['period_start'] > $sent)
                        && SummarySchedule::sendAt($kind, $settings, $zone, $job['period_start']) === $job['available_at'];
                    if ($matches) {
                        $kept = true;
                    } elseif ($this->queue->deleteUnclaimed($job['id'])) {
                        $report['unscheduled']++;
                    }
                }
                unset($pending[$key]);
                if ($kept) {
                    continue;
                }
                $next = SummarySchedule::next($kind, $settings, $zone, $now, $sent);
                if ($next === null) {
                    continue;
                }
                $this->queue->enqueue(
                    $queue,
                    QueuedJob::encode(JobRegistry::ADMIN_SUMMARY, [
                        'kind' => $kind,
                        'calendar_id' => $calendar['id'],
                        'period_start' => $next['period_start'],
                    ]),
                    null,
                    $next['send_at'],
                    $now
                );
                $report['scheduled']++;
            }
        }

        // Turned-off kinds and calendars that no longer qualify.
        foreach ($pending as $jobs) {
            foreach ($jobs as $job) {
                if ($this->queue->deleteUnclaimed($job['id'])) {
                    $report['unscheduled']++;
                }
            }
        }
        return $report;
    }

    /** @return array{id: int, kind: string, calendar_id: int, period_start: string, available_at: int}|null */
    private function summaryJob(array $row): ?array
    {
        $payload = json_decode((string) ($row['payload'] ?? ''), true);
        if (!is_array($payload) || ($payload['job'] ?? null) !== JobRegistry::ADMIN_SUMMARY) {
            return null;
        }
        $params = is_array($payload['params'] ?? null) ? $payload['params'] : [];
        return [
            'id' => (int) $row['id'],
            'kind' => is_string($params['kind'] ?? null) ? $params['kind'] : '',
            'calendar_id' => is_int($params['calendar_id'] ?? null) ? $params['calendar_id'] : 0,
            'period_start' => is_string($params['period_start'] ?? null) ? $params['period_start'] : '',
            'available_at' => (int) $row['available_at'],
        ];
    }

    /** An invalid calendar timezone is scheduled in UTC so delivery fails visibly instead of never running. */
    private function zone(string $timezone): \DateTimeZone
    {
        try {
            return new \DateTimeZone($timezone !== '' ? $timezone : 'UTC');
        } catch (\Exception) {
            return new \DateTimeZone('UTC');
        }
    }
}
