<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\Carbon;
use Wappointment\Models\Appointment;

class Sections
{
    private $start_at = false;
    private $end_at = false;
    public $availabilities = [];
    public $appointments = [];

    public function __construct($start_at = false, $end_at = false)
    {
        $this->start_at = $start_at;
        $this->end_at = $end_at;

        $this->setAppointments();
        $this->setAvailabilities();
    }

    public static function tsToDBString(Int $timestamp)
    {
        return Carbon::createFromTimestamp($timestamp)->format(WAPPOINTMENT_DB_FORMAT);
    }

    public function setAppointments()
    {
        $queryAppointments = Appointment::with('client')
            ->where('status', '>=', Appointment::STATUS_AWAITING_CONFIRMATION);

        if ($this->start_at && $this->end_at) {
            $queryAppointments
                ->where('start_at', '>=', self::tsToDBString((int) $this->start_at))
                ->where('end_at', '<=', self::tsToDBString((int) $this->end_at));
        }
        $this->appointments = $queryAppointments->orderBy('start_at')->orderBy('end_at')->get();
    }

    public function setAvailabilities()
    {
        $this->availabilities = (new Availability($this->start_at, $this->end_at))->getStaff(Settings::get('activeStaffId'));
    }

    public function getFreeSlots(Int $duration = 0)
    {
        return $this->getSlots($duration, $this->availabilities);
    }

    public function getBookedSlots(Int $duration = 0)
    {
        $appointmentsArray = [];

        foreach ($this->appointments as $appointment) {
            $appointmentsArray[] = [$appointment->start_at->timestamp, $appointment->end_at->timestamp];
        }
        return $this->getSlots($duration, $appointmentsArray);
    }

    private function getSlots(Int $duration = 0, $timestampsArray)
    {
        if ($duration < 1) {
            throw new \WappointmentException('Missing duration argument', 1);
        }
        $slots = 0;
        foreach ($timestampsArray as $availableSection) {
            $slots += ($availableSection[1] - $availableSection[0]) / $duration;
        }

        return $slots;
    }

    public function getCoverage(Int $duration)
    {
        $bookedTime = 0;
        foreach ($this->appointments as $appointment) {
            $bookedTime += $appointment->end_at->timestamp - $appointment->start_at->timestamp;
        }
        $freeTime = 0;
        foreach ($this->availabilities as $availability) {
            $freeTime += $availability[1] - $availability[0];
        }
        $totalTime = $bookedTime + $freeTime;
        return $totalTime < $duration ? 0 : round($bookedTime / ($totalTime) * 100) . '%';
    }
}
