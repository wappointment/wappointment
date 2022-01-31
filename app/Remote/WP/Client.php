<?php

namespace Wappointment\Remote\WP;

use Wappointment\Remote\AbstractClient;
use Wappointment\Remote\InterfaceClient;

class Client extends AbstractClient implements InterfaceClient
{

    public $response = null;
    public $params = [];

    public function post($url)
    {
        $this->setParam('method', 'POST');
        $this->response = new Response(wp_remote_post($url, $this->getParams()));
        return $this->response;
    }

    public function getParams()
    {
        $paramsData = $this->params;
        if ($this->hasHeaders()) {
            $paramsData['headers'] = $this->headers;
        }
        return $paramsData;
    }

    public function get($url)
    {
        $this->setParam('method', 'GET');
        $this->response = new Response(wp_remote_get($url, $this->getParams()));
        return $this->response;
    }

    public function getCalendar($url)
    {
        return $this->get($url);
    }

    public function setForm($formParams)
    {
        //Used to send an application/x-www-form-urlencoded POST request.
        $this->setHeader('Content-type', 'application/x-www-form-urlencoded');
        return $this->setParam('body', $formParams);
    }

    public function setTimeout($timeout = 5)
    {
        return $this->setParam('timeout', $timeout);
    }

    public function getContent()
    {
        return $this->reponse->getBody()->getContent();
    }
}
