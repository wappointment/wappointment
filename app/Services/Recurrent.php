<?php

namespace Wappointment\Services;

use Wappointment\Models\Appointment;
use Wappointment\Repositories\CalendarsBack;
use Wappointment\ClassConnect\Carbon;

class Recurrent
{
    public function isActive()
    {
        return VersionDB::canRecurrent();
    }

    public function generate()
    {
        if (!$this->isActive()) {
            return;
        }

        //get all recurring appointments and generate new children for each
        foreach ($this->getRecurrents() as $recurrentController) {
            $this->generateChild($recurrentController);
        }
    }

    public function getRecurrents()
    {
        return Appointment::recurrentControllers()->get();
    }

    public function generateChild(Appointment $recurrentController)
    {
        $generate_from = $this->getMinDate($recurrentController);
        $generate_until = $this->getMaxDate($recurrentController);
    }

    protected function getMinDate(Appointment $recurrentController)
    {
        $calendar = CalendarsBack::findById($recurrentController->staff_id);
        return $recurrentController->options['recurrent']
    }
    protected function getMaxDate(Appointment $recurrentController)
    {
        $calendar = CalendarsBack::findById($recurrentController->staff_id);
        return Carbon::now($calendar['timezone'])->addDays($calendar['avb'])->endOfDay();
    }
}
