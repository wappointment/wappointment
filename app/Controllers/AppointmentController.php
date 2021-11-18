<?php

namespace Wappointment\Controllers;

use Wappointment\Models\Appointment as AppointmentModel;
use Wappointment\ClassConnect\Request;
use Wappointment\Services\Settings;
use Wappointment\Services\AppointmentNew;
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

        $appointment = AppointmentModel::select(['id', 'start_at', 'status', 'end_at', 'type', 'client_id', 'options', 'staff_id', 'service_id', 'location_id'])
            ->where('status', '>=', AppointmentModel::STATUS_AWAITING_CONFIRMATION)
            ->where('edit_key', $request->input('appointmentkey'))
            ->first();

        if (empty($appointment)) {
            throw new \WappointmentException(__('Can\'t find appointment', 'wappointment'), 1);
        }
        $appointmentData = $appointment->toArraySpecial();
        $appointmentData['edit_key'] = $request->input('appointmentkey');

        $isLegacy = !VersionDB::atLeast(VersionDB::CAN_CREATE_SERVICES);
        $service = $isLegacy ? Service::get() : Central::get('ServiceModel')::find((int)$appointment->service_id);
        $client = $appointment->client()->select(['name', 'email', 'options'])->first();

        return apply_filters('wappointment_appointment_load', [
            'appointment' => $appointmentData,
            'client' => $client,
            'service' => $service,
            'staff' => $isLegacy ? (new \Wappointment\WP\StaffLegacy($appointment->getStaffId()))->toArray() : (new \Wappointment\WP\Staff($appointment->getStaffId()))->toArray(),
            'date_format' => Settings::get('date_format'),
            'time_format' => Settings::get('time_format'),
            'date_time_union' => Settings::get('date_time_union', ' - '),
            'zoom_browser' => Settings::get('zoom_browser'),
            'display' => [
                '[h2]getText(title)[/h2]',
                empty($client) ? '' : sprintf(__('%1$s - %2$s', 'wappointment'), '[b]' . $client->name . '[/b]', $client->email),
                sprintf(__('%1$s - %2$s', 'wappointment'), '[b]' . $service->name . '[/b]', $appointment->getDuration())
            ],
        ], $appointment, $request);
    }

    public function cancel(Request $request)
    {
        $result = AppointmentNew::tryCancel($request);

        if ($result) {
            return ['message' => __('Appointment has been canceled', 'wappointment')];
        }
        throw new \WappointmentException("Error Cancelling appointment", 1);
    }
}
