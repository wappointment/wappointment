<?php

namespace Wappointment\Services;

use Wappointment\Helpers\Get;
use Wappointment\WP\Helpers as WPHelpers;

class WidgetSettings
{

    private $settings = [];
    private $fields = [];
    private $steps = [];

    private $db_settings = [];
    private $merged_settings = [];
    private $key_option = 'widget_settings';

    public function __construct()
    {
        $this->settings = Get::list('widget_settings');
        $this->fields = Get::list('widget_fields');
        $this->steps = Get::list('widget_steps');
        $ppolicy = get_privacy_policy_url();

        $this->settings['form']['terms_link'] = empty($ppolicy) ? 'http://' : $ppolicy;
        $this->db_settings = WPHelpers::getOption($this->key_option, []);
        $this->merged_settings = empty($this->db_settings) ?
            $this->defaultSettings() : $this->merge($this->defaultSettings(), $this->db_settings);
    }

    public function steps()
    {
        $steps = $this->steps;
        if (!Payment::active()) {
            $steps = $this->removeStep('swift_payment', $steps);
        }
        return $steps;
    }

    protected function removeStep($stepKey, $steps)
    {
        $newSteps = [];
        foreach ($steps as $step) {
            if ($step['key'] != $stepKey) {
                $newSteps[] = $step;
            }
        }
        return $newSteps;
    }

    public function defaultSettings()
    {
        if (static::servicesAreSold()) {
            $this->settings['service_selection']['check_price_right'] = true;
        }
        return apply_filters('wappointment_widget_settings_default', $this->settings);
    }

    public function defaultFields()
    {
        return apply_filters('wappointment_widget_fields_default', $this->getFields());
    }

    protected function getFields()
    {
        if (VersionDB::canServices()) {
            unset($this->fields['form']['categories'][0]);
            $this->fields['form']['categories'] = array_values($this->fields['form']['categories']);
        }

        return $this->fields;
    }

    public function get()
    {
        return $this->mergeTranslations($this->merged_settings);
    }

    protected function mergeTranslations($settings)
    {
        $settings['i18n'] = Get::list('widget_translations');
        return $settings;
    }

    public function getSetting($settingName)
    {
        $find = explode('.', $settingName);
        if (empty($this->merged_settings[$find[0]])) {
            return 'undefined';
        }
        if (empty($this->merged_settings[$find[0]][$find[1]])) {
            return 'undefined';
        }
        return $this->merged_settings[$find[0]][$find[1]];
    }

    public function adminFieldsInfo()
    {
        return $this->setHiddenFields($this->defaultFields());
    }

    private function setHiddenFields($fields)
    {
        $fields['swift_payment']['categories'] = Payment::orderMethods($fields['swift_payment']['categories']);

        if ((int) Settings::get('approval_mode') === 1) {
            $fields['confirmation']['pending']['hidden'] = true;
        }

        return $fields;
    }

    public function save($settings)
    {
        return WPHelpers::setOption($this->key_option, $this->filterSettings($settings), true);
    }

    public function filterSettings($settings)
    {
        $accepted = array_keys($this->defaultSettings());

        return (\WappointmentLv::collect($settings))->reject(function ($value, $key) use ($accepted) {
            return !in_array($key, $accepted);
        })->toArray();
    }

    private function merge($array1, $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->merge($merged[$key], $value);
            } elseif (is_numeric($key)) {
                if (!in_array($value, $merged)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    private static function servicesAreSold()
    {
        return !empty(Settings::get('services_sold')) || static::wooInstalled();
    }

    private static function wooInstalled()
    {
        return Addons::isActive('wappointment_woocommerce');
    }
}
