<?php

namespace Wappointment\Services\Wappointment;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\ClassConnect\Carbon;
use Wappointment\WP\Plugins;
// @codingStandardsIgnoreFile
class Addons extends API
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        $response = $this->client->get($this->call('/api/addons'));
        $solutions = static::licensedSolutions();
        $data = $this->processResponse($response);

        if (!empty($solutions)) {
            foreach ($data->addons as &$package) {
                foreach ($solutions as $solution) {
                    if ($solution->package_key == $package->key || ($this->isAPlugin($package) && $this->getPluginDetails($package)->id == $solution->id)) {
                        $expires_at_carbon = (new Carbon($solution->expires_at));
                        $package->expires_at = $expires_at_carbon->format('d/m/Y');
                        $carbonnow = Carbon::now();
                        $package->expires_in = $expires_at_carbon->diffInDays($carbonnow);
                        if ($carbonnow->gt($expires_at_carbon)) {
                            $package->expires_in = -$package->expires_in;
                        }
                    }
                    $package->plugin = false;
                    if ($this->isAPlugin($package)) {
                        $package->plugin = $package->solutions[0]->type === 1;
                        $package->installed = Plugins::wp()->installed($this->pluginFileName($package));
                        $package->activated = Plugins::wp()->active($this->pluginFileName($package));
                    }
                    if ($this->pluginNamekey($package)) {
                        $package = apply_filters(
                            'wappointment_addon_wrapper_' . str_replace('-', '_', $this->pluginNamekey($package)),
                            $package
                        );
                    }
                }
            }
        }

        return $data;
    }
    public function pluginNamekey($package)
    {
        $details = $this->getPluginDetails($package);

        return $details->namekey;
    }
    private function pluginFileName($package)
    {
        return $this->pluginNamekey($package) . '/index.php';
    }

    private function getPluginDetails($package)
    {
        if (empty($package->solutions[0])) {
            throw new \WappointmentException("Cannot find addon", 1);
        }
        return (object) $package->solutions[0];
    }
    private function isAPlugin($package)
    {
        return count($package->solutions) === 1;
    }
    public function install($package)
    {
        //download zip file through wordpress installer?
        return $this->wpInstall($this->canInstall($package));
    }
    public function activate($package)
    {
        $result = Plugins::wp()->activate($this->pluginFileName($package));
        if (is_wp_error($result)) {
            // Process Error
        } else {
            return ['message' => 'Addon activated'];
        }
    }
    public function deactivate($package)
    {
        Plugins::wp()->deactivate([$this->pluginFileName($package)]);
        return ['message' => 'Addon deactivated'];
    }
    private function wpInstall($solutionToInstall)
    {
        if (!current_user_can('install_plugins')) {
            throw new \WappointmentException('Sorry, you are not allowed to install plugins on this site.');
        }

        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';


        $status['pluginName'] = $solutionToInstall->options->name;

        $skin = new \WP_Ajax_Upgrader_Skin();

        $upgrader = new \Plugin_Upgrader($skin);
        $result = $upgrader->install(
            $this->call(
                '/api/addons/package/' . $solutionToInstall->namekey . '/' . $this->getSiteKey()
            )
        );

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $status['debug'] = $skin->get_upgrade_messages();
        }

        if (is_wp_error($result)) {
            $status['errorCode'] = $result->get_error_code();
            $status['errorMessage'] = $result->get_error_message();
            //wp_send_json_error($status);
        } elseif (is_wp_error($skin->result)) {
            $status['errorCode'] = $skin->result->get_error_code();
            $status['errorMessage'] = $skin->result->get_error_message();
            //wp_send_json_error($status);
        } elseif ($skin->get_errors()->has_errors()) {
            $status['errorMessage'] = $skin->get_error_messages();
            //wp_send_json_error($status);
        } elseif (is_null($result)) {
            global $wp_filesystem;

            $status['errorCode'] = 'unable_to_connect_to_filesystem';
            $status['errorMessage'] = 'Unable to connect to the filesystem. Please confirm your credentials.';

            // Pass through the error from WP_Filesystem if one was raised.
            if ($wp_filesystem instanceof \WP_Filesystem_Base && is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->has_errors()) {
                $status['errorMessage'] = esc_html($wp_filesystem->errors->get_error_message());
            }
        }
        if (!empty($status['errorMessage'])) {
            throw new \Exception($status['errorMessage']);
        }
        return [
            'message' => __('Success installing addon', 'wappointment')
        ];
    }

    private function canInstall($package)
    {
        if (count($package->solutions) > 1) {
            throw new \Exception("Package is not installable");
        }
        $solutions = static::licensedSolutions();
        foreach ($solutions as $solution) {
            if ($package->solutions[0]['id'] === $solution->id) {
                return $solution;
            }
        }
        throw new \Exception("Cannot install package");
    }

    public static function licensedSolutions()
    {
        return !empty(WPHelpers::getOption('site_details')) ? json_decode(WPHelpers::getOption('site_details')) : [];
    }
}
