<?php

namespace Wappointment\Models\Appointment;

use Wappointment\Models\Appointment;
use Wappointment\Repositories\CalendarsBack;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Services\AppointmentNew;

class Recurrence
{
    public $is_set = false;
    public $start;
    public $end;
    public $master = null;
    public $check_days = false;
    public $calendar = false;

    public function __construct(Appointment $appointment)
    {
        $this->is_set = isset($appointment->options['recurrence']);
        $this->master = $appointment;
        $this->calendar = CalendarsBack::findById($this->master->staff_id);
        $this->setStart($this->master);
        $this->setEnd();
        $this->setRules($this->master);
    }

    public function generateChilds()
    {
        //simple case weekly on the same day
        $start_temp = $this->start->copy();
        $start_temp->addDay();

        while ($start_temp->timestamp < $this->end->timestamp) {
            //if is a day 
            if ($this->needsGeneration($start_temp)) {
                $this->generateForDay($start_temp);
            }
            $start_temp->addDay();
        }

    }

    public function generateEditKey($start_at)
    {
        return md5($start_at);
    }

    private function generateForDay(Carbon $start_temp)
    {
        //dayOfWeekIso
        $data_new = $this->master->toArray();

        $copy = Carbon::createFromTimestamp($this->master->start_at->timestamp)->setTimezone($this->calendar['timezone']);
        //set right starting hour and minute
        $start_temp->hour = $copy->hour;
        $start_temp->minute = $copy->minute;
        $start_temp->second = 0;

        //generate new key 
        $data_new['edit_key'] = $this->generateEditKey($start_temp->timestamp . $this->master->staff_id);

        $data_new['start_at'] = AppointmentNew::unixToDb($start_temp->timestamp);
        $data_new['end_at'] = AppointmentNew::unixToDb($start_temp->timestamp + $this->master->getFullDurationInSec());
        $data_new['parent'] = $this->master->id;
        if (isset($data_new['options']['slots'])) {
            $data_new['options']['slots']['booked'] = 0;
            unset($data_new['options']['providers']);
        }

        try {
            if (AppointmentNew::testExistingEvents($data_new['start_at'], $data_new['end_at'], $this->master->staff_id)) {
                Appointment::create($data_new);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        $this->recordLastGen($data_new['end_at']); //so that if we run it again we don't generate the same

    }

    private function needsGeneration(Carbon $start_temp)
    {

        if ($this->check_days && $this->isDayAllowed($this->check_days, strtolower($start_temp->englishDayOfWeek))) {
            return true;
        }
        return false;
    }

    protected function isDayAllowed($daysAllowed, $dayIsoTocheck)
    {
        foreach ($daysAllowed as $dayname => $required) {
            if ($dayIsoTocheck === $dayname && $required) {
                return true;
            }
        }
        return false;
    }

    public function dayList(Appointment $appointment)
    {
        $days = [
            'monday' => false,
            'tuesday' => false,
            'wednesday' => false,
            'thursday' => false,
            'friday' => false,
            'saturday' => false,
            'sunday' => false
        ];
        foreach ($appointment->options['recurrence']['days'] as $dayname) {
            $days[$dayname] = true;
        }
        return $days;
    }

    protected function setRules(Appointment $appointment)
    {

        $this->check_days = $this->dayList($appointment);
    }

    private function setStart(Appointment $appointment)
    {

        $this->start = Carbon::createFromTimestamp($this->getStartTS($appointment))->setTimezone($this->calendar['timezone']);
    }

    protected function getStartTS($appointment)
    {
        return $this->is_set && isset($appointment->options['recurrence']['last_gen']) ? (int)$appointment->options['recurrence']['last_gen'] : $appointment->start_at->timestamp;
    }

    private function setEnd()
    {
        $this->end = Carbon::now($this->calendar['timezone'])->addDays($this->calendar['avb'])->endOfDay();
    }

    private function recordLastGen($lastGen)
    {
        $options = $this->master->options;
        if (!isset($options['recurrence'])) {
            $options['recurrence'] = [];
        }
        $options['recurrence']['last_gen'] = $lastGen;
        $this->master->options = $options;
        $this->master->save();
    }
}
