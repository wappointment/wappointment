<?php

namespace Wappointment\Services;

use Wappointment\Models\Client;
use Wappointment\Models\Location;
use Wappointment\Models\Appointment as AppointmentModel;

class Appointment extends AppointmentLegacy
{
    public static function tryBook(Client $client, $start_at, $end_at, $type, $service, $staff_id = null)
    {
        if (static::canBook($start_at, $end_at, false, $staff_id)) {
            return static::processBook($client, $start_at, $end_at, $type, $service, false, $staff_id);
        }
    }

    public static function adminBook(Client $client, $start_at, $end_at, $type, $service, $staff_id = null)
    {
        if (static::canBook($start_at, $end_at, true, $staff_id)) {
            return static::processBook($client, $start_at, $end_at, $type, $service, true, $staff_id);
        }
    }

    protected static function processBook(Client $client, $start_at, $end_at, $type, $service, $forceConfirmed = false, $staff_id = null)
    {
        $start_at = static::unixToDb($start_at);
        $end_at = static::unixToDb($end_at);
        $location = Location::where('id', $client->bookingRequest->get('location'))->first();
        $appointmentData = [
            'start_at' => $start_at,
            'end_at' => $end_at,
            'type' => $location->type > 3 ? $location->type : $location->type - 1,
            'client_id' => $client->id,
            'edit_key' => md5($client->id . $start_at),
            'status' => $forceConfirmed ? AppointmentModel::STATUS_CONFIRMED : static::getDefaultStatus($service),
            'service_id' => $client->bookingRequest->get('service'),
            'location_id' => $location->id,
            'duration' => $client->bookingRequest->get('duration'),
            'staff_id' => $staff_id->id
        ];
        return static::book($appointmentData, $client, $forceConfirmed);
    }

    public static function adminCalendarGetAppointments($params)
    {
        $appointmentsQuery  = AppointmentModel::with('client', 'service')
            ->where('status', '>=', AppointmentModel::STATUS_AWAITING_CONFIRMATION)
            ->where('start_at', '>=', $params['start_at'])
            ->where('end_at', '<=', $params['end_at']);

        if (!empty($params['staff_id'])) {
            $appointmentsQuery->where('staff_id', $params['staff_id']);
        }

        return $appointmentsQuery->get();
    }

    public static function adminCalendarUpdateAppointmentArray($addedEvent, $appointment)
    {

        if (!empty($appointment->service)) {
            $addedEvent['service'] = $appointment->service->toArray();
            foreach ($appointment->service->locations as $location) {
                if ($location->id == $appointment->location_id) {
                    $addedEvent['location'] = $location->toArray();
                }
            }
        }

        return $addedEvent;
    }

    public static function getLocation($location, $appointment)
    {
        $location = static::getAppointmentLocation($appointment);

        if (!empty($location)) {
            return $location->name;
        }
        return $location;
    }

    public static function getAddress($address, $appointment)
    {
        $location = static::getAppointmentLocation($appointment);

        if (!empty($location) && !empty($location->options['address'])) {
            return $location->options['address'];
        }
        return $address;
    }

    protected static function getAppointmentLocation($appointment)
    {
        if (!empty($appointment->location_id) && $appointment->location_id > 0) {
            return Location::where('id', $appointment->location_id)->first();
        }
    }
}
