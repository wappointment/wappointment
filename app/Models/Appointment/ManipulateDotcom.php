<?php

namespace Wappointment\Models\Appointment;

trait ManipulateDotcom
{
    public function sentToDotCom()
    {
        $options = $this->options;
        $options['providers'] = [];
        $this->options = $options;
        $this->save();
    }

    public function toDotcom($timezone)
    {
        $toDotcom = [
            'title' => $this->getTitle(false),
            'type' => $this->type,
            'video' => $this->getLocationVideo(),
            'starts_at' => $this->start_at->timestamp,
            'appointment_id' => $this->id,
            'duration' => $this->getFullDurationInSec(),
            'location' => $this->type == 0 ? $this->getServiceAddress() : $this->getLocation(),
            'timezone' => $timezone,
            'emails' => [
                $this->getClientModel()->email
            ],
            'cancellink' => $this->getLinkCancelEvent(),
            'reschedulelink' => $this->getLinkRescheduleEvent(),
        ];
        if ($this->isZoom()) {
            $toDotcom['viewlink']  = $this->getLinkViewEvent();
        }
        if ($this->isPhone()) {
            $toDotcom['phone']  = $this->getClientModel()->getPhone();
        }
        if ($this->isSkype()) {
            $toDotcom['skype']  = $this->getClientModel()->getSkype();
        }
        return $toDotcom;
    }
}
