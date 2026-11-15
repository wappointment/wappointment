<?php
declare(strict_types=1);

namespace Wappointment\System;

class WordPressBookingWidget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'wappointment_booking_widget',
            __('Wappointment Booking', 'wappointment'),
            ['description' => __('Add a Wappointment booking widget.', 'wappointment')]
        );
    }

    public function widget($args, $instance): void
    {
        echo $args['before_widget'];
        echo (new Shortcodes())->renderBookingWidget([
            'title' => sanitize_text_field($instance['title'] ?? __('Book now', 'wappointment')),
            'button_title' => sanitize_text_field($instance['button_title'] ?? ''),
            'brc_floats' => !empty($instance['brc_floats']) ? '1' : '',
        ]);
        echo $args['after_widget'];
    }

    public function form($instance): void
    {
        $title = sanitize_text_field($instance['title'] ?? __('Book now', 'wappointment'));
        $buttonTitle = sanitize_text_field($instance['button_title'] ?? '');
        $floating = !empty($instance['brc_floats']);
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title', 'wappointment'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('button_title')); ?>"><?php esc_html_e('Button text', 'wappointment'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('button_title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('button_title')); ?>" type="text"
                value="<?php echo esc_attr($buttonTitle); ?>">
        </p>
        <p>
            <input id="<?php echo esc_attr($this->get_field_id('brc_floats')); ?>"
                name="<?php echo esc_attr($this->get_field_name('brc_floats')); ?>" type="checkbox" value="1"
                <?php checked($floating); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('brc_floats')); ?>"><?php esc_html_e('Float at the bottom right of the screen', 'wappointment'); ?></label>
        </p>
        <?php
    }

    public function update($newInstance, $oldInstance): array
    {
        return [
            'title' => sanitize_text_field($newInstance['title'] ?? ''),
            'button_title' => sanitize_text_field($newInstance['button_title'] ?? ''),
            'brc_floats' => !empty($newInstance['brc_floats']) ? 1 : 0,
        ];
    }
}
