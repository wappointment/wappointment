<?php

namespace Wappointment\Services;

use Wappointment\Models\Job;
use WappoVendor\Carbon\Carbon;

class Health
{
    private $data = [];

    public function __construct()
    {
        $this->set('cron_unreliable', $this->cronUnreliable());
        $this->set('cron_last_run', $this->cronLastRun());
    }

    public function get()
    {
        return $this->data;
    }

    public function set($key, $value)
    {
        return $this->data[$key] = $value;
    }

    protected function cronUnreliable()
    {
        return defined('DISABLE_WP_CRON') ? DISABLE_WP_CRON : false;
    }

    protected function cronLastRun()
    {
        return Flag::get('cronLastRun');
    }

    protected function pendingTasks()
    {
        //split in old and late
        $late = time() - 60;
        $jobs = Job::where('available_at', '<', $late)->get();

        $reserved_jobs = $jobs->filter(function ($job) {
            return $job->reserved_at > 0;
        });
        $not_reserved_jobs = $jobs->filter(function ($job) {
            return (int)$job->reserved_at === 0;
        });
        $old_jobs = $not_reserved_jobs->filter(function ($job) {
            return (int)$job->available_at < time() - 3600 * 24;
        });

        $late_jobs = $not_reserved_jobs->filter(function ($job) {
            return (int)$job->available_at < time() - 60 && (int)$job->available_at > time() - 3600 * 24;
        });
        $this->set('jobs', [
            'reserved' => $reserved_jobs->all(),
            'old_jobs' => $old_jobs->all(),
            'late_jobs' => $late_jobs->all(),
        ]);
    }
}
