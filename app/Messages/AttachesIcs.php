<?php

namespace Wappointment\Messages;

use Wappointment\Services\IcsGenerator;
use Wappointment\Services\Settings;

trait AttachesIcs
{
    protected function areAttachmentsDisabled()
    {
        $mail_config = Settings::get('mail_config');
        return $mail_config['method'] == 'smtp' && $mail_config['attachments_off'] === true;
    }

    public function attachIcs($appointments, $name, $admin = false)
    {
        if ($this->areAttachmentsDisabled()) {
            return;
        }
        $ics = new IcsGenerator($admin);

        $ics->summary($appointments);

        $this->attachData($ics->generate(), $name . '.ics', ['mime' => 'ics', 'as' => $name . '.ics']);
    }

    public function attachCancelled($appointments, $name, $admin = false)
    {
        if ($this->areAttachmentsDisabled()) {
            return;
        }
        $ics = new IcsGenerator($admin);

        $ics->summary($appointments, true);

        $this->attachData($ics->generate(), $name . '.ics', ['mime' => 'ics', 'as' => $name . '.ics']);
    }
}
