<?php

namespace Wappointment\Messages;

use Wappointment\Services\IcsGenerator;

trait AttachesIcs
{
    public function attachIcs($appointments, $name, $admin = false)
    {
        $ics = new IcsGenerator($admin);

        $ics->summary($appointments);

        $this->attachData($ics->generate(), $name . '.ics', ['mime' => 'ics', 'as' => $name . '.ics']);
    }

    public function attachCancelled($appointments, $name, $admin = false)
    {
        $ics = new IcsGenerator($admin);

        $ics->summary($appointments, true);

        $this->attachData($ics->generate(), $name . '.ics', ['mime' => 'ics', 'as' => $name . '.ics']);
    }
}
