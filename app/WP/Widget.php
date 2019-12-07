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
        return empty($_REQUEST['wappo_module_off']);
    }
    public static function baseHtml($button_title, $brfixed = false)
    {
        if (!self::canShow()) return;
        \Wappointment\WP\Helpers::enqueueFrontScripts();
        $brFixedString = !empty($brfixed) ? 'data-brfixed="true"' : '';
        return '<div class="wappointment_widget" data-button-title="' . esc_attr($button_title) . '" ' . $brFixedString . '></div>';
    }


    protected static function formDefinition()
    {
        $definition = [
            'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Book an appointment'],
            'button_title' => ['type' => 'text', 'label' => 'Button text', 'default' => (new \Wappointment\Services\WidgetSettings)->get()['button']['title']],
            'brc_floats' => ['type' => 'checkbox', 'label' => 'Floats in the bottom right corner', 'default' => false],
        ];
        return apply_filters('wappointment_booking_widget_form_fields', $definition);
    }
    public function widget($args, $instance)
    {
        if (!self::canShow()) return;
        if (empty($instance)) {
            $instance = static::getDefaultInstance();
        }
        $brfixed = (!empty($instance['brc_floats'])) ? true : false;
        $widget_html = '';
        $widget_html .= $args['before_widget'];
        if (!empty($instance['title']) && !$brfixed) {
            $widget_html .= $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        $widget_html .= static::baseHtml($instance['button_title'], $brfixed);
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
