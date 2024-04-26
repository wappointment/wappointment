<?php

namespace Wappointment\Services\Wappointment;

use Wappointment\Remote\Request as RequestRemote;
use Wappointment\WP\Helpers as WPHelpers;
// @codingStandardsIgnoreFile
abstract class API
{
    protected $client = null;
    protected $site_key = '';
    private $domain = WAPPOINTMENT_SITE;

    public function __construct()
    {
        $this->client = new RequestRemote;
        $this->site_key = WPHelpers::getOption('site_key');
    }

    public function call($endpoint = '/api/licence')
    {
        return $this->domain . $endpoint;
    }

    protected function getParams($params = [])
    {
        if ($this->getSiteKey()) {
            $params['Site-Key'] = $this->getSiteKey();
        }

        return array_merge([
            'Site-Name' => WPHelpers::getWPOption('blogname'),
            'Site-Url' => rest_url(),
            'Site-Wp-Version' => get_bloginfo('version'),
            'Site-Php-Version' => PHP_VERSION,
        ], $params);
    }

    protected function processResponse($response)
    {

        //WPREMOTE
        if ($response->getStatusCode() < 200  || $response->getStatusCode() > 204) {
            throw new \WappointmentException('Error requesting Wappointment.com');
        }
        if ($response->getStatusCode() === 204) {
            if (method_exists($this, 'handle204Errors')) {
                return $this->handle204Errors($response);
            }

            throw new \WappointmentException(
                !empty($response->getHeaderLine('reason-reject')) ? $response->getHeaderLine('reason-reject') : 'Cannot connect to Wappointment.com'
            );
        }
        return json_decode($response->getContent());
    }

    protected function getSiteKey()
    {
        return $this->site_key;
    }
}
