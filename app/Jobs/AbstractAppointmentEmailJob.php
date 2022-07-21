<?php

namespace Wappointment\Jobs;

abstract class AbstractAppointmentEmailJob extends AbstractEmailJob
{
    use IsAppointmentJob;

    public function handle()
    {
        $emailObject = $this->generateContent();
        //if the email is empty that means there was no result so we can just skip sending the email
        if (!empty($emailObject->body)) {
            $this->transport->to($this->client->mailableAddress());
            $this->addReplyTo();

            if (!$this->transport->send($emailObject)) {
                throw new \WappointmentException('Error while sending email 1', 1);
            }
        }
    }

    protected function addReplyTo()
    {
        if (!empty($this->appointment)) {
            $this->transport->reply(
                apply_filters('wappointment_replyto_email', $this->client->email),
                apply_filters('wappointment_replyto_name', $this->client->name)
            );
        }
    }

    protected function generateContent()
    {
        $emailClass = static::CONTENT;
        $data = [
            'client' => $this->client,
            'appointment' => $this->appointment,
            'order' => $this->order,
        ];

        if ($this->reminder_id) {
            $data['reminder_id'] = $this->reminder_id;
        }
        return new $emailClass($data);
    }
}
