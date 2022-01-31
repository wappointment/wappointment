<?php

namespace Wappointment\Remote\Guzzle;

use Wappointment\Remote\AbstractClient;
use Wappointment\Remote\InterfaceClient;

class Client extends AbstractClient implements InterfaceClient
{
    public $client = null;
    public $response = null;
    public $params = [];

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client;
    }

    public function post($url)
    {
        $existingParams = $this->getParams();
        if (!empty($existingParams)) {
            $this->response = new Response($this->client->request('POST', $url, $existingParams));
        } else {
            $this->response = new Response($this->client->request('POST', $url));
        }
        return $this->response;
    }

    public function get($url)
    {
        $this->response = new Response($this->client->request('GET', $url));
        return $this->response;
    }

    public function getCalendar($url)
    {
        $this->setCurlParams($url);
        $this->response = new Response($this->client->request('GET', $url, $this->getParams()));
        return $this->response;
    }

    public function setForm($formParams)
    {
        //Used to send an application/x-www-form-urlencoded POST request.
        return $this->setParam('form_params', $formParams);
    }

    public function setTimeout($timeout = 5)
    {
        return $this->setParam('connect_timeout', $timeout);
    }

    public function getContent()
    {
        return $this->response->getBody()->getContent();
    }

    protected function setCurlParams($url)
    {
        $this->setParam('curl', [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);
    }
}
