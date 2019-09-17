<?php

namespace Wappointment\WP;

class Widget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct('wappointment', 'Wappointment Booking');
    }

    protected static function baseHtml($button_title)
    {
        \Wappointment\WP\Helpers::enqueueFrontScripts();
        return '<div class="wappointment_widget" data-button-title="' . esc_attr($button_title) . '"></div>';
    }
    protected static function getDefaultInstance()
    {
        return [
            'title' => 'Book an appointment',
            'button_title' => 'Book now!',
        ];
    }
    public function widget($args, $instance)
    {
        if (empty($instance)) {
            $instance = self::getDefaultInstance();
        }
        $widget_html = '';
        $widget_html .= $args['before_widget'];
        if (!empty($instance['title'])) {
            $widget_html .= $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $widget_html .= self::baseHtml($instance['button_title']);
        $widget_html .= $args['after_widget'];

        echo $widget_html;
    }

    public function form($instance)
    {
        if (empty($instance)) {
            $instance = self::getDefaultInstance();
        }

        $title = !empty($instance['title']) ? $instance['title'] : '';

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
        </p><?php

                }

                public function update($new_instance, $old_instance)
                {
                    $instance = $old_instance;
                    $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
                    $instance['button_title'] = (!empty($new_instance['button_title'])) ? strip_tags($new_instance['button_title']) : '';
                    return $instance;
                }
            }
