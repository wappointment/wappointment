<?php

namespace Wappointment\Messages;

use Wappointment\Models\Reminder;

class ClientBookingConfirmationEmail extends AbstractEmail
{
    use HasAppointmentFooterLinks, HasTagsToReplace, AttachesIcs, PreparesClientEmail;

    protected $icsRequired = true;
    public $test = false;

    const EVENT = Reminder::APPOINTMENT_CONFIRMED;

    public function loadContent()
    {

        if (!$this->prepareClientEmail($this->params['client'], $this->params['appointment'], static::EVENT)) {
            return false;
        }

        if (!empty($this->params['client']->options['test_appointment'])) {
            $this->subject = '[TEST_EMAIL]' . $this->subject;
            $this->test = true;
        }

        if ($this->icsRequired) {
            $this->attachIcs([$this->params['appointment']], 'appointment');
        }
    }
}
