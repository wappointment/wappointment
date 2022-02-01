<?php

namespace Wappointment\Remote;

use Wappointment\Remote\Guzzle\Client as GuzzleClient;
use Wappointment\Remote\WP\Client as WPClient;
use Wappointment\Services\Settings;

class Request
{
    public function __construct()
    {
        $this->client = Settings::get('wp_remote') ? new WPClient : new GuzzleClient;
    }

    public function post($url)
    {
        return $this->client->post($url);
    }

    public function get($url)
    {
        return $this->client->get($url);
    }

    public function getCalendar($url)
    {
        return $this->client->getCalendar($url);
    }

    public function setForm($formsParams)
    {
        $this->client->setForm($formsParams);
        return $this;
    }

    public function setTimeout($timeout = 5)
    {
        $this->client->setTimeout($timeout);
        return $this;
    }

    public function failed()
    {
        return $this->client->failed();
    }

    public function getContent()
    {
        return $this->client->getContent();
    }

    public function headerIsEqual($headerName, $valueTest)
    {
        return $this->client->headerIsEqual($headerName, $valueTest);
    }

    public function hasHeader($headerName)
    {
        return $this->client->hasHeader($headerName);
    }
}
