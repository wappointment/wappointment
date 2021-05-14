<?php

namespace Wappointment\Events;

use Wappointment\Models\Reminder;
use Wappointment\Services\Wappointment\DotCom;
use Wappointment\Services\Settings;

class AppointmentBookedEvent extends AbstractEvent
{
    const NAME = 'appointment.booked';

    protected $appointment;
    protected $client;
    protected $oldAppointment;
    protected $reminders;

    public function __construct($args)
    {
        $this->appointment = $args['appointment'];
        $this->client = $args['client'];
        if (!empty($args['oldAppointment'])) {
            $this->oldAppointment = $args['oldAppointment'];
        }

        do_action('wappointment_appointment_booked', $this->appointment, $this->client, self::NAME);

        $this->triggerAPI();
        $this->reminders = Reminder::select('id', 'event', 'type', 'options')
            ->where('published', 1)
            ->whereIn('type', Reminder::getTypes('code'))
            ->get();
    }

    public function triggerAPI()
    {
        $acs_id = Settings::get('activeStaffId');
        $staff_id = empty($this->appointment->staff_id) ? $acs_id : (int)$this->appointment->staff_id;
        $dotcomapi = new DotCom;
        $dotcomapi->setStaff($staff_id);

        if ($dotcomapi->isConnected()) {
            if (static::NAME == 'appointment.canceled') {
                $dotcomapi->delete($this->appointment);
            } else {
                if (!empty($this->oldAppointment)) {
                    $dotcomapi->update($this->appointment);
                } else {
                    $dotcomapi->create($this->appointment);
                }
            }
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getAppointment()
    {
        return $this->appointment;
    }

    public function getOldAppointment()
    {
        return $this->oldAppointment;
    }

    public function getReminders()
    {
        return $this->reminders;
    }
}
