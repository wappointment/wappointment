<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

/** A job that v3 will not execute; it is quarantined in `wappo_failed_jobs`, never retried. */
class UnsupportedJobException extends PermanentJobFailure {}
