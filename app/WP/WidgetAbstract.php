<?php

namespace Wappointment\WP;

abstract class WidgetAbstract extends \WP_Widget
{
    protected static function getDefaultInstance()
    {
        return array_map(
            function ($field) {
                return $field['default'];
            },
            static::formDefinition()
        );
    }

    public function update($new_instance, $old_instance)
    {
        return $this->updateInstance($old_instance, $new_instance);
    }

    protected function updateInstance($instance, $new_instance)
    {
        foreach (static::formDefinition() as $key => $field) {
            switch ($field['type']) {
                case 'checkbox':
                    $instance[$key] = (!empty($new_instance[$key])) ? true : false;
                    break;

                default:
                    $instance[$key] = (!empty($new_instance[$key])) ? strip_tags($new_instance[$key]) : '';
            }
        }
        return $instance;
    }



    protected function getValueField($key, $data, $instance)
    {
        $default = !empty($data['default']) ? $data['default'] : '';
        switch ($data['type']) {
            case 'text':
                return !empty($instance[$key]) ? $instance[$key] : $default;
            case 'checkbox':
                $default = empty($default) ? false : $default;
                return !empty($instance[$key]) ? (bool) $instance[$key] : $default;
            default:
                return !empty($instance[$key]) ? $instance[$key] : $default;
        }
    }
    protected function fillFormDefinition($form_definition, $instance)
    {
        foreach ($form_definition as $key => $data) {
            $data['id'] = $this->get_field_id($key);
            $data['name'] = $this->get_field_name($key);
            $data['value'] = $this->getValueField($key, $data, $instance);
            $form_definition[$key] = $data;
        }
        return $form_definition;
    }
}
