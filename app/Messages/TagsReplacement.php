<?php

namespace Wappointment\Messages;

use Wappointment\Helpers\Get;
use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;

/**
 * Todo Redo improve ...
 */
class TagsReplacement
{
    public $params = [];
    public $finds = [];
    public $replaces = [];

    public function __construct($params)
    {
        $this->params = $params;
        $this->params['email_helper'] = new EmailHelper;
        $this->prepareTags();
    }

    public static function emailsTags()
    {
        return apply_filters('wappointment_emails_tags', static::appendStaffCF(Get::list('email_tags')));
    }

    public static function appendStaffCF($email_tags_core)
    {
        if (Settings::get('allow_staff_cf')) {
            $custom_fields = WPHelpers::getOption('staff_custom_fields', []);
            foreach ($custom_fields as $custom_field) {
                $email_tags_core[] =  [
                    'model' => 'staff',
                    'key' => $custom_field['key'],
                    /* translators: %s - field name. */
                    'label' => sprintf(__('Staff Custom Field - %s', 'wappointment'), $custom_field['name']),
                    'getMethod' => 'getStaffCustomField',
                    'sanitize' => true,
                    'modelCall' => 'appointment'
                ];
            }
        }
        return $email_tags_core;
    }

    public static function emailsLinks()
    {
        return apply_filters('wappointment_emails_links', Get::list('email_links'));
    }


    public function replace($subject)
    {
        return str_replace($this->finds, $this->replaces, $subject);
    }

    /**
     * Todo generate the value of only the found tags
     *
     * @return void
     */
    private function prepareTags()
    {
        foreach (array_merge(static::emailsTags(), static::emailsLinks()) as $tag) {
            $tag_find = '[' . $tag['model'] . ':' . $tag['key'] . ']';
            $replace = $this->getValue($tag);
            if (!empty($tag['sanitize'])) {
                $replace = sanitize_text_field($replace);
            }
            $this->addFindReplace($tag_find, $replace);
        }
    }

    private function getValue($tag)
    {
        $model_key = empty($tag['modelCall']) ? $tag['model'] : $tag['modelCall'];

        if (empty($this->params[$model_key])) {
            return '';
        }

        if (isset($tag['getResult'])) {
            return $tag['getResult'];
        } else {
            if (isset($tag['getMethod'])) {
                if (method_exists($this->params[$model_key], $tag['getMethod'])) {
                    if ($tag['getMethod'] == 'getStartsDayAndTime') {
                        return $this->params['appointment']->getStartsDayAndTime($this->params['client']->getTimezone());
                    } else {
                        return call_user_func([
                            $this->params[$model_key],
                            $tag['getMethod']
                        ], empty($tag['requiresParams']) ? $tag : $this->params);
                    }
                }
            } else {
                $key = $tag['key'];
                if (is_object($this->params[$model_key])) {
                    //return $this->params[$model_key]->$key;
                    return !empty($this->params[$model_key]->$key) ? $this->params[$model_key]->$key : '';
                }

                if (is_array($this->params[$model_key])) {
                    return !empty($this->params[$model_key][$key]) ? $this->params[$model_key][$key] : '';
                }
            }
        }
    }

    private function addFindReplace($find, $replace)
    {
        $this->finds[] = $find;
        $this->replaces[] = $replace;
    }
}
