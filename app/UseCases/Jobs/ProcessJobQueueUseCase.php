<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Jobs;

use Wappointment\Actions\Jobs\ProcessJobQueueAction;

/** Cron entry point: one bounded pass of the durable job queue. */
class ProcessJobQueueUseCase
{
    public function __construct(private ProcessJobQueueAction $process) {}

    public function execute(int $now): array
    {
        return $this->process->handle($now);
    }
}
