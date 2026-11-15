<?php
declare(strict_types=1);

namespace Wappointment\System;

class LegacyWordPressBookingWidget extends WordPressBookingWidget
{
    public function __construct()
    {
        parent::__construct('wappointment');
    }

    public function widget($args, $instance): void
    {
        if (!empty($_REQUEST['wappo_module_off']) || !empty($_REQUEST['appointmentkey'])) {
            return;
        }

        $instance = is_array($instance) ? $instance : [];
        $title = sanitize_text_field($instance['title'] ?? __('Book an appointment', 'wappointment'));
        $buttonTitle = sanitize_text_field($instance['button_title'] ?? '');
        if ($buttonTitle === '') {
            $settings = get_option('wappointment_widget_settings', []);
            $savedTitle = is_array($settings) && isset($settings['button']) && is_array($settings['button'])
                ? ($settings['button']['title'] ?? '') : '';
            $buttonTitle = is_string($savedTitle) && $savedTitle !== ''
                ? sanitize_text_field($savedTitle) : __('Book now!', 'wappointment');
        }
        $floating = !empty($instance['brc_floats']);

        echo $args['before_widget'];
        if ($title !== '' && !$floating) {
            echo $args['before_title'] . wp_kses_post(apply_filters('widget_title', $title, $instance, $this->id_base)) . $args['after_title'];
        }
        echo (new Shortcodes())->renderBookingWidget([
            'title' => $title !== '' ? $title : __('Book now', 'wappointment'),
            'button_title' => $buttonTitle,
            'brc_floats' => $floating ? '1' : '',
        ]);
        echo $args['after_widget'];
    }
}
