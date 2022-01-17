<?php

namespace Wappointment\Models\Appointment;

trait ManipulateIcs
{
    public function getSequence()
    {
        return empty($this->options['sequence']) ? 0 : $this->options['sequence'];
    }

    public function incrementSequence()
    {
        $this->options = $this->getIncrementedSequenceOptions();
        return $this->save();
    }

    public function getIncrementedSequenceOptions()
    {
        $options = $this->options;
        $options['sequence'] = $this->getSequence() + 1;
        return $options;
    }
}
