<?php

namespace  Wappointment\Jobs;

use Wappointment\ClassConnect\Carbon;
use Wappointment\Services\AppointmentNew as AppointmentService;
use Wappointment\Services\Settings;
use Wappointment\Jobs\JobInterface;
use Wappointment\Services\Queue;
use Wappointment\Models\Job;
use Wappointment\Models\Order;
use Wappointment\Services\Payment;

class CleanPendingPaymentAppointment implements JobInterface
{
    public function handle()
    {
        if (Payment::isWooActive()) { //there is already an auto clean job for woocommerce
            return false;
        }
        // 1 - get orders that are pending for more than  the last X minutes

        $orders = Order::pending()->where('updated_at', '<', Carbon::now()->subSeconds(
            static::getDelayInSeconds()
        )->format('Y-m-d H:i'))->get();


        // 3 - delete the connected appointments
        if (!empty($orders)) {
            foreach ($orders as $order) {
                foreach ($order->appointments as $appointment) {
                    if ($appointment->isPending()) {
                        AppointmentService::cancel($appointment);
                    }
                }
                $order->setAutoCancelled();
            }
        }

        static::registerJob(true);
    }

    public static function registerJob($skipCheck = false)
    {
        if ($skipCheck || (self::lastCheckExpired() && !self::hasJobInQueue('pending_clean'))) {
            Job::where('queue', 'pending_clean')
                ->delete();
            Queue::push('Wappointment\Jobs\CleanPendingPaymentAppointment', [], 'pending_clean', static::next());
        }
    }


    public static function lastCheckExpired()
    {
        return Settings::get('clean_last_check') === false || Settings::get('clean_last_check') < time() - (5 * 60);
    }

    public static function hasJobInQueue($queue_key)
    {
        Settings::save('clean_last_check', time());
        return \Wappointment\Models\Job::where('queue', $queue_key)
            ->first();
    }

    public static function next()
    {
        return time() + (static::getDelayInSeconds());
    }

    public static function getDelayInSeconds()
    {
        return Settings::get('clean_pending_every') * 60;
    }
}
