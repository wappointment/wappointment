<?php

namespace Wappointment\Listeners;

use Wappointment\Models\Reminder;
use Wappointment\Services\Settings;

class AppointmentConfirmedListener extends AbstractJobAppointmentListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailConfirmed';
    protected $reminderClass = '\Wappointment\Jobs\AppointmentEmailReminder';

    protected function queueConfirmationEmail($event)
    {
        $reminders = Reminder::select('id', 'event', 'options')->where('published', 1)
            ->where('type', Reminder::TYPE_EMAIL)
            ->whereIn('event', [Reminder::APPOINTMENT_STARTS, Reminder::APPOINTMENT_CONFIRMED])
            ->get();

        $params = [
            'appointment' => $event->getAppointment(),
            'client' => $event->getClient(),
        ];
        $futureRemindersJobs = [];
        foreach ($reminders as $reminder) {
            if ($this->isReminderInTheFutureOrNotIsReminder($reminder, $params)) {
                $params['reminder_id'] = ($reminder->event == Reminder::APPOINTMENT_STARTS) ? $reminder->id : 0;
                $jobClass = ($reminder->event == Reminder::APPOINTMENT_STARTS) ? $this->reminderClass : $this->jobClass;
                $appointment_id = !empty($params['appointment']->id) ? $params['appointment']->id : null;

                $this->recordJob(
                    $jobClass,
                    $params,
                    'client',
                    $appointment_id,
                    $this->sendReminderOnThe($reminder, $params)
                );
            }
        }
    }

    private function isReminderInTheFutureOrNotIsReminder($reminder, $params)
    {
        if ($reminder->getDelay()) {
            if (time() > $this->reminderShouldBeSentOnThe($reminder, $params)) {
                return false;
            }
        }
        return true;
    }

    private function sendReminderOnThe($reminder, $params)
    {
        return $reminder->getDelay() ? $this->reminderShouldBeSentOnThe($reminder, $params) : 0;
    }

    private function reminderShouldBeSentOnThe($reminder, $params)
    {
        return $params['appointment']->start_at->getTimestamp() + $reminder->getDelay();
    }

    protected function queueAdminNotification($event)
    {
        if (Settings::get('notify_new_appointments')) {
            $this->recordJob(
                '\Wappointment\Jobs\AdminEmailNewAppointment',
                [
                    'appointment' => $event->getAppointment(),
                    'client' => $event->getClient()
                ],
                'admin'
            );
        }
    }
}
