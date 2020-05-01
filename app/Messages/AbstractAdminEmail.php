<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;

abstract class AbstractAdminEmail extends AbstractEmail
{
    protected $admin = true;

    public function __construct(...$params)
    {
        parent::__construct(...$params);

        if (Settings::getStaff('email_logo')) {
            $this->addLogo(Settings::getStaff('email_logo'), 'full');
        }
    }

    public function addRoundedSquare($lines, $separator = 'body-border radius')
    {
        $this->addBlock('roundedSquare', $lines, $separator);
    }

    public function addButton($title, $action)
    {
        $this->messageBlocks[] = [
            'type' => 'button',
            'content' => $title,
            'action' => $action
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
