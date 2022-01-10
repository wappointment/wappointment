<?php

namespace Wappointment\Formatters;

class BookingResult
{
    public static function format($result)
    {
        $result['appointment'] = static::appointmentFormat($result['appointment']);
        return $result;
    }

    public static function formatAppointments($appointments)
    {
        $newAppointments = [];
        foreach ($appointments as $appointment) {
            $newAppointments[] = static::appointmentFormat($appointment);
        }
        return $newAppointments;
    }

    public static function appointmentFormat($appointment)
    {
        return (new \Wappointment\ClassConnect\Collection(
            $appointment->toArraySpecial()
        ))
            ->except(['id', 'client_id']);
    }
}
