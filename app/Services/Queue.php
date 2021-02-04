<?php

namespace Wappointment\Services;

use Wappointment\Models\Job;
use Wappointment\Models\FailedJob;
use Wappointment\Helpers\Debug;

class Queue
{
    const MAX_FAILURE = 3;
    const DELAY_AFTER_FAIL = 120; //in seconds
    const DELAY_TIMEOUT_RETRY = 120;

    public static function process($take = 10)
    {
        try { // avoid issue when uninstalling tables
            $jobs = self::loadBatch($take);
        } catch (\Throwable $th) {
            $jobs = [];
        }
        if (count($jobs) > 0) {
            foreach ($jobs as $job) {
                self::run($job);
            }
        }
    }

    private static function reserveBatch($reserve_at, $take)
    {
        return Job::where('reserved_at', 0)
            ->where('available_at', '<', $reserve_at)
            ->take($take)
            ->update(['reserved_at' => $reserve_at]);
    }

    private static function loadBatch($take)
    {
        $reserve_at = time();

        //reserve the batch of jobs
        self::reserveBatch($reserve_at, $take);

        //and load it
        return Job::where('reserved_at', $reserve_at)
            ->take($take)
            ->get();
    }

    public static function pushMultiple($jobsObject)
    {
        return Job::insert($jobsObject);
    }

    public static function tryPush($job, $params, $queue = 'availability', $available_at = 0)
    {
        $query = Job::where('queue', $queue)->where('reserved_at', 0);
        if (!empty($params['staff_id'])) {
            $query->where('appointment_id', (int)$params['staff_id']);
        }
        if ($query->exists()) {
            return;
        }
        self::push($job, $params, $queue, $available_at);
    }

    public static function push($job, $params, $queue = 'default', $available_at = 0)
    {
        $jobInsert = [
            'payload' => ['job' => $job, 'params' => $params],
            'created_at' => time(),
            'queue' => $queue,
            'reserved_at' => 0,
            'available_at' => $available_at
        ];
        if (!empty($params['staff_id'])) {
            $jobInsert['appointment_id'] = $params['staff_id'];
        }
        if (!empty($params['appointment']['id'])) {
            $jobInsert['appointment_id'] = $params['appointment']['id'];
        }

        return Job::create($jobInsert);
    }

    public static function resetTimedoutJobs()
    {

        $jobsTimedOut = Job::where('reserved_at', '>', 0)
            ->where('reserved_at', '<', time() - static::DELAY_TIMEOUT_RETRY)
            ->get();

        foreach ($jobsTimedOut as $key => $job) {
            $job->attempts++;
            if ($job->attempts >= self::MAX_FAILURE) {
                self::jobFailed($job, ['timedout' => 'timedout']);
            } else {
                $job->reserved_at = 0;
                $job->available_at = time() + self::DELAY_AFTER_FAIL;
                $job->save();
            }
        }
    }

    public static function pushAndRun($job, $params)
    {
        $job = self::push($job, $params);
        if (!$job) {
            throw new \WappointmentException('Error Queueing job', 1);
        }
        $job->update(['reserved_at' => time()]);
        self::run($job);
    }

    private static function run(Job $job)
    {
        //process it
        $jobClassName = $job->payload['job'];

        if (!class_exists($jobClassName)) {
            throw new \WappointmentException('Error Job ' . $jobClassName . ' cannot be found ', 1);
        }

        // control the kind of job class passed
        $interfaces = class_implements($jobClassName);

        if (!isset($interfaces['Wappointment\Jobs\JobInterface'])) {
            throw new \WappointmentException(
                'Job ' . $jobClassName . ' doesn\'t implement required interface',
                1
            );
        }

        // process the job
        try {
            (new $jobClassName($job->payload['params']))->handle();
            // once the job has been processed and there is no error we delete it
            $job->delete();
        } catch (\Exception $e) {
            self::whenJobFails($job, $e);
        }
    }

    private static function whenJobFails(Job $job, \Exception $e)
    {
        $attempts = $job->attempts + 1;

        if ($attempts >= self::MAX_FAILURE) {
            // MAX_FAILURE reached we keep a record in the failures table and delete the original job
            self::jobFailed($job, Debug::convertExceptionToArray($e));
        } else {
            $job->update([
                'attempts' => $attempts,
                'payload' => $job->payload,
                'reserved_at' => 0,
                'available_at' => time() + self::DELAY_AFTER_FAIL
            ]);
        }
    }

    protected static function jobFailed(Job $job, $arrayException)
    {
        $failed_job = [
            'queue' => $job->queue,
            'payload' => $job->payload,
            'exception' => $arrayException
        ];
        if (!empty($job->appointment_id)) {
            $failed_job['appointment_id'] = $job->appointment_id;
        }
        FailedJob::create($failed_job);
        $job->delete();
    }

    public static function loadAndRun($jobId)
    {
        $job = Job::find($jobId);
        if (!$job) {
            throw new \WappointmentException('Cannot load job', 1);
        }

        self::run($job);
    }

    public static function cancelAppointmentJob($appointment_id)
    {
        Job::where('queue', '!=', 'availability')
            ->where('appointment_id', (int)$appointment_id)
            ->delete();
    }

    public static function cancelDailyJob()
    {
        Job::where('queue', 'daily')
            ->delete();
    }

    public static function queueDailyJob()
    {
        self::cancelDailyJob();

        $available_at = \Wappointment\ClassConnect\Carbon::createFromTime(
            Settings::get('daily_summary_time'),
            0,
            0,
            Settings::getStaff('timezone')
        );

        if ($available_at->timestamp < time()) {
            $available_at->addDay();
        }

        self::push('Wappointment\Jobs\AdminEmailDailySummary', [], 'daily', $available_at->timestamp);
    }

    public static function cancelWeeklyJob()
    {
        Job::where('queue', 'weekly')
            ->delete();
    }

    public static function queueWeeklyJob()
    {
        self::cancelWeeklyJob();

        $available_at = \Wappointment\ClassConnect\Carbon::createFromTime(
            Settings::get('weekly_summary_time'),
            0,
            0,
            Settings::getStaff('timezone')
        );

        while ($available_at->timestamp < time() || !$available_at->isDayOfWeek(Settings::get('weekly_summary_day'))) {
            $available_at->addDay();
        }

        self::push('Wappointment\Jobs\AdminEmailWeeklySummary', [], 'weekly', $available_at->timestamp);
    }
}
