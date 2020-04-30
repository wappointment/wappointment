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
            $result = $this->transport
                ->to($this->client->mailableAddress())
                ->send($emailObject);

            if (!$result) {
                throw new \WappointmentException('Error while sending email', 1);
            }
        }
    }

    protected function generateContent()
    {
        $emailClass = static::CONTENT;
        if ($this->reminder_id) {
            return new $emailClass($this->client, $this->appointment, $this->reminder_id);
        }
        return new $emailClass($this->client, $this->appointment);
    }
}
