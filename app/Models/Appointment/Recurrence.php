<?php

namespace Wappointment\Models\Appointment;

use Wappointment\Models\Appointment;
use Wappointment\Repositories\CalendarsBack;
use Wappointment\ClassConnect\Carbon;

class Recurrence
{
    public $is_set = false;
    public $start;
    public $end;
    public $master = null;
    public $checkDays = false;

    public function __construct(Appointment $appointment)
    {
        $this->is_set = isset($appointment->options['recurrence']);
        $this->master = $appointment;
        $this->setStart($appointment);
        $this->setEnd($appointment);
        $this->setRules($appointment);
    }

    public function generateChilds()
    {
        //simple case weekly on the same day
        $start_temp = $this->start->copy();
        while ($start_temp->timestamp < $this->end->timestamp) {
            //if is a day 
            if ($this->needsGeneration($start_temp)) {
                $this->generateForDay($start_temp);
            }
            $start_temp->addDay();
        }
    }

    private function generateForDay(Carbon $start_temp)
    {
        //dayOfWeekIso
        $data_new = $this->master->toArray();

        $data_new['start_at'] = $start_temp->timestamp;
        $data_new['end_at'] = $start_temp->timestamp + $this->master->getFullDurationInSec();
        $data_new['parent'] = $this->master->id;
        if (isset($data_new['options']['slots'])) {
            $data_new['options']['slots']['booked'] = 0;
            unset($data_new['options']['providers']);
        }
        Appointment::create($data_new);
        $this->recordLastGen($data_new['end_at']); //so that if we run it again we don't generate the same

    }

    private function needsGeneration(Carbon $start_temp)
    {

        if ($this->checkDays && $this->isDayAllowed($this->checkDays, strtolower($start_temp->englishDayOfWeek))) {
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

    public function dayList()
    {
        return [
            'monday' => false,
            'tuesday' => false,
            'wednesday' => true,
            'thursday' => false,
            'friday' => true,
            'saturday' => false,
            'sunday' => false
        ];
    }

    protected function setRules(Appointment $appointment)
    {

        //$this->checkDays = $appointment->options['recurrence']['weekly'];
        $this->checkDays = $this->dayList();
    }

    private function setStart(Appointment $appointment)
    {
        $this->start = Carbon::createFromTimestamp($this->is_set && isset($appointment->options['recurrence']['last_gen']) ? (int)$appointment->options['recurrence']['last_gen'] : time());
    }

    private function setEnd(Appointment $appointment)
    {
        $calendar = CalendarsBack::findById($appointment->staff_id);
        $this->end = Carbon::now($calendar['timezone'])->addDays($calendar['avb'])->endOfDay();
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
