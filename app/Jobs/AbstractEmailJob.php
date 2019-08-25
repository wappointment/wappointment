<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Mail;

abstract class AbstractEmailJob implements JobInterface
{
    protected $mailer = null;

    protected $params = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->mailer = new Mail();
        $this->parseParams($params);
    }

    protected function parseParams($params)
    {
        $this->params = $params;
    }

    protected function generateEmail()
    {
        $emailClass = static::EMAIL;

        return new $emailClass($this->params);
    }
}
