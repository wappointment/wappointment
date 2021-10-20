<?php

namespace Wappointment\Controllers;

use Wappointment\Models\Appointment as AppointmentModel;
use Wappointment\ClassConnect\Request;
use Wappointment\Services\Settings;
use Wappointment\Services\AppointmentNew as Appointment;
use Wappointment\Services\Service;
use Wappointment\Services\VersionDB;
use Wappointment\Managers\Central;

class AppointmentController extends RestController
{
    public function get(Request $request)
    {
        if (is_array($request->input('appointmentkey'))) {
            throw new \WappointmentException("Malformed parameter", 1);
        }

        $appointment = AppointmentModel::select(['start_at', 'status', 'end_at', 'type', 'client_id', 'options', 'staff_id', 'service_id', 'location_id'])
            ->where('status', '>=', AppointmentModel::STATUS_AWAITING_CONFIRMATION)
            ->where('edit_key', $request->input('appointmentkey'))
            ->first();

        if (empty($appointment)) {
            throw new \WappointmentException("Can't find appointment", 1);
        }
        $appointmentData = $appointment->toArraySpecial();
        $appointmentData['edit_key'] = $request->input('appointmentkey');
        // if (Settings::get('allow_rescheduling')) {
        //     $appointmentData['canRescheduleUntil'] = $appointment->canRescheduleUntilTimestamp();
        // }
        // if (Settings::get('allow_cancellation')) {
        //     $appointmentData['canCancelUntil'] = $appointment->canCancelUntilTimestamp();
        // }
        $isLegacy = !VersionDB::atLeast(VersionDB::CAN_CREATE_SERVICES);

        return [
            'appointment' => $appointmentData,
            'client' => $appointment->client()->select(['name', 'email', 'options'])->first(),
            'service' => $isLegacy ? Service::get() : Central::get('ServiceModel')::find((int)$appointment->service_id),
            'staff' => $isLegacy ? (new \Wappointment\WP\StaffLegacy($appointment->getStaffId()))->toArray() : (new \Wappointment\WP\Staff($appointment->getStaffId()))->toArray(),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'zoom_browser' => Settings::get('zoom_browser'),
        ];
    }

    public function cancel(Request $request)
    {
        $result = Appointment::tryCancel($request->input('appointmentkey'));

        if ($result) {
            return ['message' => 'Appointment has been canceled'];
        }
        throw new \WappointmentException("Error Cancelling appointment", 1);
    }
}
