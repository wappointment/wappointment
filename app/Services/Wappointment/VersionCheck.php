<?php

namespace Wappointment\Services\Wappointment;

class VersionCheck extends API
{

    public function __construct()
    {
        parent::__construct();
        //testing version check set_site_transient('update_plugins', null);
        add_filter('pre_set_site_transient_update_plugins', [$this, 'sitePluginsVersionCheckTriggerred'], 10, 2);
    }

    public function sitePluginsVersionCheckTriggerred($transient, $deux)
    {
        if ($transient === false || !isset($transient->response)) {
            return $transient;
        }

        foreach ($this->getWappointmentActiveSlugs() as $plugin) {
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

    protected function getWappointmentActiveSlugs()
    {
        $plugins_slugs = [];
        foreach (array_keys(\Wappointment\Services\Addons::getActive()) as $plugin_key) {
            $plugins_slugs[] = str_replace('_', '-', $plugin_key);
        }

        return $plugins_slugs;
    }

    public function getActivePluginVersion($plugin)
    {
        $version_constant = strtoupper(str_replace('-', '_', $plugin)) . '_VERSION';
        return defined($version_constant) ? constant($version_constant) : 0;
    }

    public function pluginData($plugin_slug = null, $new_version = false)
    {
        $id_url = $this->call('/' . $plugin_slug);

        $data = new \stdClass;
        if ($new_version !== false) {
            $data->new_version = (string) $new_version;
        }
        $data->id      = $id_url;
        $data->slug    = $plugin_slug;
        $data->plugin    = $plugin_slug . '/index.php';
        $data->package = $this->call('/api/addons/package/' . $plugin_slug . '/' . $this->getSiteKey());
        $data->url = $id_url;

        return $data;
    }

    public function latestVersion($plugin_slug = null)
    {
        static $versions_checked = [];
        if (isset($versions_checked[$plugin_slug])) {
            return $versions_checked[$plugin_slug];
        }

        $response = $this->client->get($this->call('/api/addon/' . $plugin_slug . '/check'));

        $data = $this->processResponse($response);

        $versions_checked[$plugin_slug] = empty($data->version) ? false : $data->version;

        return $versions_checked[$plugin_slug];
    }

    public static function retriggerVersionCheck()
    {
        delete_site_transient('update_plugins');
    }
}
