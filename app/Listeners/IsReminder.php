<?php

namespace Wappointment\Listeners;

trait IsReminder
{
    protected function recordReminder($reminder, $event, $params)
    {
        if ($this->isReminderInTheFutureOrNotIsReminder($reminder, $params)) {
            $params['reminder_id'] = $reminder->id;
            //

            $this->recordJob(
                $this->jobClass,
                $params,
                'client',
                !empty($params['appointment']->id) ? $params['appointment']->id : null,
                $this->sendReminderOnThe($reminder, $params)
            );
        }
    }
    protected function isReminderInTheFutureOrNotIsReminder($reminder, $params)
    {
        if ($reminder->getDelay() && time() > $this->reminderShouldBeSentOnThe($reminder, $params)) {
            return false;
        }
        return true;
    }

    protected function sendReminderOnThe($reminder, $params)
    {
        return $reminder->getDelay() ? $this->reminderShouldBeSentOnThe($reminder, $params) : 0;
    }

    protected function reminderShouldBeSentOnThe($reminder, $params)
    {
        return $params['appointment']->start_at->getTimestamp() + $reminder->getDelay();
    }
}
