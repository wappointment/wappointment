<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;

class TagsReplacement
{
    public $client = null;
    public $appointment = null;

    public function __construct(Client $client, Appointment $appointment)
    {
        $this->client = $client;
        $this->appointment = $appointment;
    }

    public function replace($subject)
    {
        return str_replace(
            [
                '[client:name]',
                '[client:email]',
                '[client:phone]',
                '[client:skype]',

                '[appointment:duration]',
                '[appointment:starts]',
                '[appointment:linkAddEventToCalendar]',
                '[appointment:linkRescheduleEvent]',
                '[appointment:linkCancelEvent]',
                '[appointment:linkNew]',

                '[service:name]',
                '[service:address]',
            ],
            [
                $this->client->name,
                $this->client->email,
                $this->client->getPhone(),
                $this->client->getSkype(),

                $this->appointment->getDuration(),
                $this->appointment->getStartsDayAndTime($this->client->getTimezone()),
                $this->appointment->getLinkAddEventToCalendar(),
                $this->appointment->getLinkRescheduleEvent(),
                $this->appointment->getLinkCancelEvent(),
                $this->appointment->getLinkNewEvent(),

                $this->appointment->getServiceName(),
                $this->appointment->getServiceAddress(),
            ],
            $subject
        );
    }
}
