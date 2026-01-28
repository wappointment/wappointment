<?php
declare(strict_types=1);

namespace Wappointment\System;

/**
 * Shortcodes registration
 */
class Shortcodes
{
    /**
     * Register all shortcodes
     */
    public function register(): void
    {
        add_shortcode('wap_widget', [$this, 'renderBookingWidget']);
    }

    /**
     * Render the booking widget shortcode
     */
    public function renderBookingWidget(array $atts): string
    {
        $atts = shortcode_atts([
            'title' => 'Book now'
        ], $atts);

        // Enqueue widget assets
        wp_enqueue_script(
            'wappointment-widget',
            plugins_url('dist/widget.js', WAPPOINTMENT_FILE),
            ['wp-element'],
            WAPPOINTMENT_VERSION,
            true
        );

        wp_enqueue_style(
            'wappointment-widget',
            plugins_url('resources/css/widget.css', WAPPOINTMENT_FILE),
            [],
            WAPPOINTMENT_VERSION
        );

        // Pass data to widget
        wp_localize_script('wappointment-widget', 'wappointmentWidget', [
            'title' => esc_html($atts['title']),
            'apiUrl' => rest_url('wappointment/v1'),
            'nonce' => wp_create_nonce('wp_rest')
        ]);

        // Return the container
        return '<div class="wappointment-widget-root" data-title="' . esc_attr($atts['title']) . '"></div>';
    }
}
