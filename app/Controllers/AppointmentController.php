<?php

namespace Wappointment\Controllers;


use Wappointment\Models\Appointment as AppointmentModel;
use Wappointment\ClassConnect\Request;
use Wappointment\Services\Settings;
use Wappointment\Services\Appointment;
use Wappointment\Services\Service;

class AppointmentController extends RestController
{
    public function get(Request $request)
    {
        $appointment = AppointmentModel::select(['start_at', 'status', 'end_at', 'type', 'client_id'])
            ->where('status', '>=', AppointmentModel::STATUS_AWAITING_CONFIRMATION)
            ->where('edit_key', $request->input('appointmentkey'))
            ->first();

        if (empty($appointment)) {
            throw new \WappointmentException("Can't find appointment", 1);
        }
        $appointmentData = [
            'start_at' => $appointment->start_at->getTimestamp(),
            'end_at' => $appointment->end_at->getTimestamp(),
            'type' => $appointment->getLocationSlug(),
        ];
        if (Settings::get('allow_rescheduling')) {
            $appointmentData['canRescheduleUntil'] = $appointment->canRescheduleUntilTimestamp();
        }
        if (Settings::get('allow_cancellation')) {
            $appointmentData['canCancelUntil'] = $appointment->canCancelUntilTimestamp();
        }
        return [
            'appointment' => $appointmentData,
            'client' => $appointment->client()->select(['name', 'email', 'options'])->first(),
            'service' => Service::get(),
            'staff' => (new \Wappointment\WP\Staff($appointment->getStaffId()))->toArray(),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
        ];
    }

    public function cancel(Request $request)
    {
        $result = Appointment::tryCancel($request->input('appointmentkey'));

        if ($result) {
            return ['message' => 'Appointment has been canceled'];
        }
        return $result;
    }
}
