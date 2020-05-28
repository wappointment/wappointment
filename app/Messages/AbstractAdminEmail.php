<?php

namespace Wappointment\Messages;

abstract class AbstractAdminEmail extends AbstractEmail
{
    protected $admin = true;

    public function addRoundedSquare($lines, $separator = 'body-border radius')
    {
        $this->addBlock('roundedSquare', $lines, $separator);
    }

    public function addButton($title, $action, $center = true)
    {
        $this->messageBlocks[] = [
            'type' => 'button',
            'content' => $title,
            'action' => $action,
            'center' => $center
        ];
    }

    public function addBr()
    {
        $this->messageBlocks[] = [
            'type' => 'spacer',
        ];
    }

    public function addLines($lines = [])
    {
        $this->messageBlocks[] = [
            'type' => 'content',
            'content' => $lines,
        ];
    }
}
