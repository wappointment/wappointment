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
                'sanitize' => true
            ],
            [
                'model' => 'service',
                'key' => 'address',
                'label' => 'Service address',
                'getMethod' => 'getServiceAddress',
                'sanitize' => true
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

    public function replace($subject)
    {
        return str_replace($this->finds, $this->replaces, $subject);
    }

    private function prepareTags()
    {
        foreach (static::emailsTags() as $key => $tag) {
            $tag_find = '[' . $tag['model'] . ':' . $tag['key'] . ']';
            $replace = $this->getValue($tag);
            if (!empty($tag['sanitize'])) {
                $replace = sanitize_text_field($replace);
            }
            $this->addFindReplace($tag_find, $replace);
        }

        foreach ($this->hiddenEmailTags() as $tag => $replace) {
            if (!empty($tag['sanitize'])) {
                $replace = sanitize_text_field($replace);
            }
            $this->addFindReplace($tag, $replace);
        }
    }

    private function hiddenEmailTags()
    {
        $email_hidden_tags_core = [
            '[appointment:linkAddEventToCalendar]' => $this->getValue(['model' => 'appointment', 'getMethod' => 'getLinkAddEventToCalendar']),
            '[appointment:linkRescheduleEvent]' => $this->getValue(['model' => 'appointment', 'getMethod' => 'getLinkRescheduleEvent']),
            '[appointment:linkCancelEvent]' => $this->getValue(['model' => 'appointment', 'getMethod' => 'getLinkCancelEvent']),
            '[appointment:linkNew]' => $this->getValue(['model' => 'appointment', 'getMethod' => 'getLinkNewEvent']),
        ];

        return apply_filters('wappointment_emails_hidden_tags', $email_hidden_tags_core);
    }

    private function getValue($tag)
    {

        if (empty($this->params[$tag['model']])) {
            return '';
        }

        if (isset($tag['getMethod'])) {
            if (method_exists($this->params[$tag['model']], $tag['getMethod'])) {
                if ($tag['getMethod'] == 'getStartsDayAndTime') {
                    return $this->params['appointment']->getStartsDayAndTime($this->params['client']->getTimezone());
                } else {
                    return call_user_func([
                        $this->params[$tag['model']],
                        $tag['getMethod']
                    ]);
                }
            }
        } else {
            $key = $tag['key'];
            if (is_object($this->params[$tag['model']])) {
                //return $this->params[$tag['model']]->$key;
                return !empty($this->params[$tag['model']]->$key) ? $this->params[$tag['model']]->$key : '';
            }

            if (is_array($this->params[$tag['model']])) {
                return !empty($this->params[$tag['model']][$key]) ? $this->params[$tag['model']][$key] : '';
            }
        }
    }

    private function addFindReplace($find, $replace)
    {
        $this->finds[] = $find;
        $this->replaces[] = $replace;
    }
}
