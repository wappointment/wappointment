<?php
declare(strict_types=1);

namespace Wappointment\Controllers;

/**
 * Admin controller handling backend pages
 */
class AdminController extends BaseController
{
    /**
     * Render the React app for all pages
     */
    public function renderApp(): void
    {
        // Enqueue React app assets
        wp_enqueue_script(
            'wappointment-react-app',
            plugins_url('dist/app.js', WAPPOINTMENT_FILE),
            ['wp-element'],
            WAPPOINTMENT_VERSION,
            true
        );

        // Pass data to React app
        wp_localize_script('wappointment-react-app', 'wappointmentData', [
            'apiUrl' => rest_url('wappointment/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'currentPage' => $_GET['page'] ?? 'wappointment-jobs'
        ]);

        // Hook to allow addons to enqueue their own scripts/overrides
        do_action('wappointment_admin_enqueue_scripts', $_GET['page'] ?? 'wappointment-jobs');

        // Render the root view
        $this->render('admin/app');
    }

    public function jobs(): void
    {
        $this->renderApp();
    }

    public function clients(): void
    {
        $this->renderApp();
    }

    public function settings(): void
    {
        $this->renderApp();
    }
}
