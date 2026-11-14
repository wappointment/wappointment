<?php
declare(strict_types=1);

namespace Wappointment\Actions\Admin;

class RenderAdminAppAction
{
    public function handle(string $page): void
    {
        wp_enqueue_script(
            'wappointment-react-app',
            plugins_url('dist/app.js', WAPPOINTMENT_FILE),
            ['wp-element'],
            WAPPOINTMENT_VERSION,
            true
        );
        wp_localize_script('wappointment-react-app', 'wappointmentData', [
            'apiUrl' => rest_url('wappointment/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'currentPage' => $page,
        ]);
        do_action('wappointment_admin_enqueue_scripts', $page);
        $viewPath = WAPPOINTMENT_PATH . 'app/Views/admin/app.php';
        if (!file_exists($viewPath)) {
            wp_die('View file not found: admin/app');
        }
        include $viewPath;
    }
}
