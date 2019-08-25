<?php

namespace Wappointment\Services\Wappointment;

class VersionCheck extends API
{

    public function __construct()
    {
        parent::__construct();
        add_filter('pre_set_site_transient_update_plugins', [$this, 'site_plugins_version_check_triggerred'], 10, 2);
    }

    public function site_plugins_version_check_triggerred($transient, $deux)
    {

        if ($transient === false || !isset($transient->response)) return $transient;

        $plugins_slugs = ['wappointment-woocommerce']; //TODO cannot be hardcoded

        foreach ($plugins_slugs as $plugin) {
            $plugin_file = $plugin . '/index.php';
            if (is_plugin_active($plugin_file) && !isset($transient->response[$plugin_file])) {
                $latestVersion = $this->latestVersion($plugin);
                if ($latestVersion !== false && version_compare($latestVersion, $this->getActivePluginVersion($plugin), '>')) {
                    //then there needs to be an update on that plugin
                    $transient->response[$plugin_file] = $this->pluginData($plugin, $latestVersion);
                }
            }
        }

        return $transient;
    }

    public function getActivePluginVersion($plugin)
    {
        $version_constant = strtoupper(str_replace('-', '_', $plugin)) . '_VERSION';
        return defined($version_constant) ? constant($version_constant) : 0;
    }
    public function pluginData($plugin_slug = null, $new_version = false)
    {
        $data = new \stdClass;
        $data->id      = $this->call($plugin_slug);
        $data->slug    = $plugin_slug;
        $data->package = $this->call('/api/addons/package/' . $plugin_slug . '/' . $this->getSiteKey());
        $data->url = $this->call($plugin_slug);

        if ($new_version !== false) {
            $data->new_version = (string) $new_version;
        }

        return $data;
    }

    public function latestVersion($plugin_slug = null)
    {
        static $versions_checked = [];
        if (isset($versions_checked[$plugin_slug])) return $versions_checked[$plugin_slug];

        $response = $this->client->request('GET', $this->call('/api/addon/' . $plugin_slug . '/check'));

        $data = $this->processResponse($response);

        $versions_checked[$plugin_slug] = empty($data->version) ? false : $data->version;

        return $versions_checked[$plugin_slug];
    }

    public static function retriggerVersionCheck()
    {
        delete_site_transient('update_plugins');
    }
}
