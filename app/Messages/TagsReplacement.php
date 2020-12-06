<?php

namespace Wappointment\Messages;

class TagsReplacement
{
    public $params = [];
    public $finds = [];
    public $replaces = [];

    public function __construct($params)
    {
        $this->params = $params;

        $this->prepareTags();
    }

    public static function emailsTags()
    {
        $email_tags_core = [
            [
                'model' => 'client',
                'key' => 'name',
                'label' => 'Client\'s name',
                'sanitize' => true
            ],
            [
                'model' => 'client',
                'key' => 'email',
                'label' => 'Client\'s email',
                'sanitize' => true
            ],
            [
                'model' => 'client',
                'key' => 'phone',
                'label' => 'Client\'s phone',
                'getMethod' => 'getPhone',
                'sanitize' => true
            ],
            [
                'model' => 'client',
                'key' => 'skype',
                'label' => 'Client\'s skype',
                'getMethod' => 'getSkype',
                'sanitize' => true
            ],
            [
                'model' => 'service',
                'key' => 'name',
                'label' => 'Service name',
                'getMethod' => 'getServiceName',
                'sanitize' => true,
                'modelCall' => 'appointment'
            ],
            [
                'model' => 'service',
                'key' => 'address',
                'label' => 'Service address',
                'getMethod' => 'getServiceAddress',
                'sanitize' => true,
                'modelCall' => 'appointment'
            ],
            [
                'model' => 'appointment',
                'key' => 'duration',
                'label' => 'Appointment\'s duration',
                'getMethod' => 'getDuration'
            ],
            [
                'model' => 'appointment',
                'key' => 'location',
                'label' => 'Appointment\'s location',
                'getMethod' => 'getLocation'
            ],
            [
                'model' => 'appointment',
                'key' => 'starts',
                'label' => 'Appointment\'s date and time',
                'getMethod' => 'getStartsDayAndTime'
            ],


        ];

        return apply_filters('wappointment_emails_tags', $email_tags_core);
    }

    public static function emailsLinks()
    {
        $email_links_core = [
            [
                'model' => 'appointment',
                'key' => 'linkAddEventToCalendar',
                'label' => 'Link to save appointment to calendar',
                'getMethod' => 'getLinkAddEventToCalendar'
            ],
            [
                'model' => 'appointment',
                'key' => 'linkRescheduleEvent',
                'label' => 'Link to reschedule appointment',
                'getMethod' => 'getLinkRescheduleEvent'
            ],
            [
                'model' => 'appointment',
                'key' => 'linkCancelEvent',
                'label' => 'Link to cancel appointment',
                'getMethod' => 'getLinkCancelEvent'
            ],
            [
                'model' => 'appointment',
                'key' => 'linkNew',
                'label' => 'Link to book a new appointment',
                'getMethod' => 'getLinkNewEvent'
            ],
            [
                'model' => 'appointment',
                'key' => 'linkView',
                'label' => 'Link to view the appointment details (Meeting room url etc ...)',
                'getMethod' => 'getLinkViewEvent'
            ],


        ];

        return apply_filters('wappointment_emails_links', $email_links_core);
    }


    public function replace($subject)
    {
        return str_replace($this->finds, $this->replaces, $subject);
    }

    private function prepareTags()
    {
        foreach (array_merge(static::emailsTags(), static::emailsLinks()) as $key => $tag) {
            $tag_find = '[' . $tag['model'] . ':' . $tag['key'] . ']';
            $replace = $this->getValue($tag);
            if (!empty($tag['sanitize'])) {
                $replace = sanitize_text_field($replace);
            }
            $this->addFindReplace($tag_find, $replace);
        }

        // foreach ($this->hiddenEmailTags() as $tag => $replace) {
        //     if (!empty($tag['sanitize'])) {
        //         $replace = sanitize_text_field($replace);
        //     }
        //     $this->addFindReplace($tag, $replace);
        // }
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
                        ]);
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
