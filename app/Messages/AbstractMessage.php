<?php

namespace Wappointment\Messages;

abstract class AbstractMessage implements InterfaceMessage
{
    public $messageLines = [];

    public function line($line)
    {
        return $this->messageLines[] = $line;
    }
}
