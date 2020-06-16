<?php

namespace Wappointment\WP;

class Widget extends WidgetAbstract
{
    public function __construct()
    {
        parent::__construct('wappointment', 'Wappointment Booking');
    }

    public static function canShow()
    {
        return empty($_REQUEST['wappo_module_off']) && empty($_REQUEST['appointmentkey']);
    }
    public static function baseHtml($instance = [])
    {
        if (!self::canShow()) {
            return;
        }
        \Wappointment\WP\Helpers::enqueueFrontScripts();
        $htmlAttributes = !empty($brfixed) ? 'data-brfixed="true"' : '';
        foreach ($instance as $attr => $val) {
            if (!in_array($attr, ['title']) && (!is_bool($val) || (is_bool($val) && $val === true))) {
                $htmlAttributes .= ' data-' . str_replace('_', '-', strtolower($attr)) . '="' . esc_attr($val) . '"';
            }
        }
        return '<button class="wappointment_widget" ' . $htmlAttributes . '>' . esc_html($instance['button_title']) . '</button>';
    }


    protected static function formDefinition()
    {
        $definition = [
            'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Book an appointment'],
            'button_title' => [
                'type' => 'text', 'label' => 'Button text',
                'default' => (new \Wappointment\Services\WidgetSettings)->get()['button']['title']
            ],
            'brc_floats' => [
                'type' => 'checkbox',
                'label' => 'Floats in the bottom right corner', 'default' => false
            ],
        ];

        return apply_filters('wappointment_booking_widget_form_fields', static::contextualize($definition));
    }


    protected static function contextualize($definition)
    {
        if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'elementor_ajax') {
            $definition['notice_elementor'] = [
                'label' => 'The style of the button can be changed from <a href="admin.php?page=wappointment_settings#/general" target="_blank">Wappointment > General >
                Booking Widget setup</a> ',
                'class' => 'elementor-control-field-description'
            ];
        }
        //dd($definition);
        return $definition;
    }

    public function widget($args, $instance)
    {
        if (!self::canShow()) {
            return;
        }
        if (empty($instance)) {
            $instance = static::getDefaultInstance();
        }
        $brfixed = (!empty($instance['brc_floats'])) ? true : false;
        $widget_html = '';
        $widget_html .= $args['before_widget'];
        if (!empty($instance['title']) && !$brfixed) {
            $widget_html .=
                $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        $widget_html .= static::baseHtml($instance);
        $widget_html .= $args['after_widget'];


        echo $widget_html;
    }

    public function form($instance)
    {

        if (empty($instance)) {
            $instance = static::getDefaultInstance();
        }

        $form = new \Wappointment\Services\Forms($this->fillFormDefinition(static::formDefinition(), $instance));

        echo $form->getHtml();
    }
}
