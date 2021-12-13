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
        $attendees = $this->getAttendees();
        $toDotcom = [
            'title' => $this->getTitle(false),
            'type' => $this->type,
            'video' => $this->getLocationVideo(),
            'starts_at' => $this->start_at->timestamp,
            'appointment_id' => $this->id,
            'duration' => $this->getFullDurationInSec(),
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
        if ($this->isSkype()) {
            $toDotcom['skype']  = $this->getClientMethodOrEmpty('getSkype');
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
                'name' => $this->getClientMethodOrEmpty('getEmailForDotcom'),
            ]
        ], $this);
    }
}
