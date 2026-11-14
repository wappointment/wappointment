<?php
declare(strict_types=1);

namespace Wappointment\System;

class LegacyAddons
{
    public static function block(): void
    {
        $blocked = [];
        foreach ([
            'WappointmentServicesAddon' => 'Services',
            'WappointmentGroupAddon' => 'Group',
            'WappointmentPackagesAddon' => 'Packages',
            'WappointmentPaypalAddon' => 'PayPal',
            'WappointmentStaffAddon' => 'Staff',
            'WappointmentStripeAddon' => 'Stripe',
            'WappointmentSyncalAddon' => 'Syncal',
            'WappointmentWoocommerce' => 'WooCommerce',
            'WappointmentMailChimpAddon' => 'Mailchimp',
        ] as $class => $name) {
            if (class_exists($class, false)) {
                remove_action('init', [$class, 'init'], 12);
                $blocked[] = $name;
            }
        }
        if (class_exists('WappointmentTwilioAddon', false)) {
            remove_action('wappointments_autoload_init', ['WappointmentTwilioAddon', 'init'], 12);
            $blocked[] = 'Twilio';
        }
        if ($blocked) {
            add_action('admin_notices', static function () use ($blocked): void {
                if (!current_user_can('manage_options')) {
                    return;
                }
                echo '<div class="notice notice-error"><p>' . esc_html(sprintf(
                    __('Wappointment v3 does not load legacy add-ons: %s. Their data has not been removed. Deactivate these plugins; their features must be migrated to Wappointment Premium before use.', 'wappointment'),
                    implode(', ', $blocked)
                )) . '</p></div>';
            });
        }
    }
}
