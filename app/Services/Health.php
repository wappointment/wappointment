<?php

namespace Wappointment\Services;

use Wappointment\Helpers\Get;
use Wappointment\Jobs\CleanPendingPaymentAppointment;
use Wappointment\Models\Appointment;
use Wappointment\Models\Job;
use Wappointment\System\Status;

class Health
{
    private $data = [];
    private $checks = [];

    public function __construct()
    {
        if (CurrentUser::isAdmin() && Status::isInstalled()) {
            $this->set('lang', Get::list('translations_health'));
            $this->checkUnreliable();
            $this->checkLateRun();
            $this->checkLateTasks();
            $this->checkCleanJob();
            $this->set('checks', $this->checks);
            $this->setSolutions();
        }
    }

    public function get()
    {
        return $this->data;
    }

    public function checkCleanJob()
    {
        CleanPendingPaymentAppointment::registerJob();
    }

    public function set($key, $value)
    {
        return $this->data[$key] = $value;
    }

    public function checkUnreliable()
    {
        $this->checks['cron_unreliable'] = $this->cronUnreliable();
    }

    public function checkLateRun()
    {
        $last_run = $this->cronLastRun();
        $this->checks['cron_late_run'] = $last_run + (10 * 60) < time(); //late after 10 minutes
        $this->set('cron_last_run', $last_run);
    }

    public function checkLateTasks()
    {
        $pendingTasks = $this->pendingTasks();
        $this->checks['cron_late_tasks'] = count($pendingTasks['late']) > 0;
        $this->set('cron_jobs', $pendingTasks);
    }

    public function setSolutions()
    {
        $task_on_time = 'https://wappointment.com/docs/wp-cron/';
        $this->set('solutions', [
            'cron_late_tasks' => $task_on_time,
            'cron_late_run' => $task_on_time,
            'cron_unreliable' => $task_on_time,
        ]);
    }


    protected function cronUnreliable()
    {
        return defined('DISABLE_WP_CRON') ? !DISABLE_WP_CRON : true;
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
        $never_ending_jobs = $reserved_jobs->filter(function ($job) {
            return $job->reserved_at < time() - (60 * 10);
        });
        $not_reserved_jobs = $jobs->filter(function ($job) {
            return (int)$job->reserved_at === 0;
        });
        $old_jobs = $not_reserved_jobs->filter(function ($job) {
            return ((int)$job->available_at === 0 && (int)$job->created_at < time() - 3600 * 24) ||
                (int)$job->available_at > 0 && (int)$job->available_at < time() - 3600 * 24;
        });

        $late_jobs = $not_reserved_jobs->filter(function ($job) {
            return ((int)$job->available_at === 0 &&  (int)$job->created_at < time() - 60) ||
                ((int)$job->available_at < time() - 60 && (int)$job->available_at > time() - 3600 * 24);
        });
        return [
            'reserved' => $reserved_jobs->all(),
            'never_ending' => $never_ending_jobs->all(),
            'old' => $old_jobs->all(),
            'late' => $late_jobs->all(),
        ];
    }
}
