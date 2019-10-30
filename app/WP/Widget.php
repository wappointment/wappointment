<?php

namespace Wappointment\WP;

class Widget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct('wappointment', 'Wappointment Booking');
    }

    public static function baseHtml($button_title, $brfixed = false)
    {
        \Wappointment\WP\Helpers::enqueueFrontScripts();
        $brFixedString = !empty($brfixed) ? 'data-brfixed="true"' : '';
        return '<div class="wappointment_widget" data-button-title="' . esc_attr($button_title) . '" ' . $brFixedString . '></div>';
    }
    protected static function getDefaultInstance()
    {
        return [
            'title' => 'Book an appointment',
            'button_title' => 'Book now!',
            'brc_floats' => false,
        ];
    }
    public function widget($args, $instance)
    {
        if (empty($instance)) {
            $instance = self::getDefaultInstance();
        }
        $brfixed = (!empty($instance['brc_floats'])) ? true : false;
        $widget_html = '';
        $widget_html .= $args['before_widget'];
        if (!empty($instance['title']) && !$brfixed) {
            $widget_html .= $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        $widget_html .= self::baseHtml($instance['button_title'], $brfixed);
        $widget_html .= $args['after_widget'];

        echo $widget_html;
    }

    public function form($instance)
    {
        if (empty($instance)) {
            $instance = self::getDefaultInstance();
        }

        $title = !empty($instance['title']) ? $instance['title'] : '';
        $brc_floats = !empty($instance['brc_floats']) ? (bool) $instance['brc_floats'] : false;

        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo 'Title'; ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>

        <?php
                $title = !empty($instance['button_title']) ? $instance['button_title'] : (new \Wappointment\Services\WidgetSettings)->get()['button']['title'];

                ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('button_title')); ?>"><?php echo 'Button text'; ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('button_title')); ?>" name="<?php echo esc_attr($this->get_field_name('button_title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php

                ?>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($brc_floats); ?> id="<?php echo $this->get_field_id('brc_floats'); ?>" name="<?php echo $this->get_field_name('brc_floats'); ?>" />
            <label for="<?php echo $this->get_field_id('brc_floats'); ?>"><?php echo 'Floats in the bottom right corner' ?></label>
        </p>
<?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['button_title'] = (!empty($new_instance['button_title'])) ? strip_tags($new_instance['button_title']) : '';
        $instance['brc_floats'] = (!empty($new_instance['brc_floats'])) ? true : false;
        return $instance;
    }
}
