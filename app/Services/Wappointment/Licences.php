<?php

namespace Wappointment\Services\Wappointment;

use Wappointment\WP\Helpers as WPHelpers;
// @codingStandardsIgnoreFile
class Licences extends API
{
    public function hasLicenceInstalled()
    {
        return !empty($this->getSiteKey()) && !empty(WPHelpers::getOption('site_details'));
    }

    public function register($product_key)
    {

        $response = $this->client
            ->setForm($this->getParams(['Product-Key' => $product_key]))
            ->post($this->call('/api/licence/register'));

        $data = $this->processResponse($response);
        $this->recordSiteKey($data);
        \Wappointment\Services\Wappointment\VersionCheck::retriggerVersionCheck();
        return $data;
    }

    public function check()
    {
        if (!$this->hasLicenceInstalled()) {
            return false;
        }
        $response = $this->client
            ->setForm($this->getParams())
            ->post($this->call('/api/site/check'));

        $data = $this->processResponse($response);

        $this->recordDetails($data);

        return $data;
    }

    public function clear()
    {
        return WPHelpers::deleteOption('site_details');
    }

    public function canUseAddon($addon_name)
    {
        $site_options = json_decode(WPHelpers::getOption('site_details'));

        if (!empty($site_options) && count($site_options) > 0) {
            foreach ($site_options as $option) {
                if ($option->namekey == str_replace('_', '-', $addon_name)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function recordDetails($data)
    {
        return WPHelpers::setOption('site_details', wp_json_encode($data));
    }

    protected function recordSiteKey($data)
    {
        if (!is_object($data) || empty($data->sitekey)) {
            throw new \WappointmentException("Cannot record site's key" . print_r($data));
        }
        if (!empty($data->details)) {
            WPHelpers::setOption('site_details', wp_json_encode($data->details));
        }
        return WPHelpers::setOption('site_key', $data->sitekey);
    }

    protected function handle204Errors($response)
    {
        if (!empty($response->getHeaderLine('reason-reject')) && !empty($response->getHeaderLine('licence-clear'))) {
            $this->clear();

            throw new \WappointmentException('You have no valid licence for your site' . $response->getHeaderLine('reason-reject'));
        }

        throw new \WappointmentException(
            !empty($response->getHeaderLine('reason-reject')) ? $response->getHeaderLine('reason-reject') : 'Cannot connect to Wappointment.com'
        );
    }
}
