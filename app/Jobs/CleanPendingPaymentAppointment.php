<?php

namespace Wappointment\Jobs;

use Wappointment\ClassConnect\Carbon;
use Wappointment\Services\AppointmentNew;
use Wappointment\Services\Settings;
use Wappointment\Jobs\JobInterface;
use Wappointment\Models\Client;
use Wappointment\Services\Queue;
use Wappointment\Models\Job;
use Wappointment\Models\Order;
use Wappointment\Services\Payment;

class CleanPendingPaymentAppointment implements JobInterface
{
    public function handle()
    {
        $data = !Payment::isWooActive() ? $this->appointmentsToProcess() : \WappointmentAddonWoocommerce\Jobs\CleanPendingPaymentAppointment::getAppointmentsToReview();

        foreach ($data['appointments'] as $appointment) {
            if (empty($appointment->options['slots'])) {
                if ($appointment->isPending()) {
                    AppointmentNew::cancel($appointment);
                }
            } else {
                foreach ($data['orders'] as $orderData) {
                    foreach ($orderData['reservations'] as $reservation) {
                        if (!empty($reservation['appointment_id']) && (int)$reservation['appointment_id'] === (int)$appointment->id) {
                            $ticket = apply_filters('wappointment_appointment_get_ticket', $appointment, $orderData['client_id']);
                            if (!is_null($ticket) && $ticket->is_participant) {
                                do_action('wappointment_cancel_ticket', $ticket, !empty($reservation['slots']) ? $reservation['slots'] : false);
                            }
                        }
                    }
                }
            }
        }
        static::registerJob(true);
    }

    public function appointmentsToProcess()
    {
        // 1 - get orders that are pending for more than  the last X minutes

        $orders = Order::pending()->where('updated_at', '<', Carbon::now()->subSeconds(
            static::getDelayInSeconds()
        )->format('Y-m-d H:i'))->get();

        $appointments = [];
        $ordersData = [];

        // 3 - delete the connected appointments
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $ordersData[] = ['client_id' => $order->client_id, 'reservations' => $order->options['reservations']];
                foreach ($order->prices as $charge) {
                    $appointments[] = $charge->appointment;
                }
                $order->setAutoCancelled();
            }
        }

        return [
            'appointments' => $appointments,
            'orders' => $ordersData
        ];
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
