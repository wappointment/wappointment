<?php

namespace Wappointment\Models\Appointment;

trait ManipulateDotcom
{
    // a request has been sent and we're waiting for the answer
    public function sentToDotCom()
    {
        $options = $this->options;
        if ($this->canSendToDotCom()) {
            $options['providers'] = [];
            $this->options = $options;
            $this->save();
        }
    }

    public function canSendToDotCom()
    {
        return !isset($this->options['providers']);
    }

    public function toDotcom($timezone)
    {
        $attendees = $this->getAttendees();
        $toDotcom = [
            'title' => $this->getTitle(false),
            'type' => $this->type,
            'video' => $this->getLocationVideo(),
            'starts_at' => $this->start_at->timestamp,
            'appointment_id' => $this->id,
            'duration' => $this->getDurationInSec(),
            'location' => $this->type == 0 ? $this->getServiceAddress() : $this->getLocation(),
            'timezone' => $timezone,
            'attendees' => $attendees,
            'emails' => $this->getEmailAttendees($attendees),
        ];

        if (empty($this->options['slots'])) {
            $toDotcom['cancellink'] = $this->getLinkCancelEvent();
            $toDotcom['reschedulelink'] = $this->getLinkRescheduleEvent();
        }
        if ($this->isZoom()) {
            $toDotcom['viewlink']  = $this->getLinkViewEvent();
        }
        if ($this->isPhone()) {
            $toDotcom['phone']  = $this->getClientMethodOrEmpty('getPhone');
        }

        return $toDotcom;
    }

    public function getEmailAttendees($attendees)
    {
        $emails = [];

        foreach ($attendees as $attendee) {
            $emails[] = $attendee['email'];
        }

        return $emails;
    }

    public function getAttendees()
    {
        return apply_filters('wappointment_appointment_get_attendees', [
            [
                'email' => $this->getClientMethodOrEmpty('getEmailForDotcom'),
                'name' => $this->getClientMethodOrEmpty('getNameForDotcom'),
            ]
        ], $this);
    }
}
