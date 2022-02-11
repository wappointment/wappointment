<?php

namespace Wappointment\Services;

use Wappointment\Models\Appointment;

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
            $recurrentController->getRecurrence()->generateChilds();
        }
    }

    public function getRecurrents()
    {
        return Appointment::recurrentControllers()->get();
    }
}
