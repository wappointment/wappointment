<?php
declare(strict_types=1);

namespace Wappointment\Actions\Admin;

use Wappointment\Auth\AccessPolicy;

class RenderAdminAppAction
{
    public function __construct(private AccessPolicy $access) {}

    public function handle(string $page): void
    {
        if (!in_array($page, $this->access->allowedPages(), true)) {
            wp_die(esc_html__('You are not allowed to view this Wappointment page.', 'wappointment'), '', ['response' => 403]);
        }
        wp_enqueue_script(
            'wappointment-react-app',
            plugins_url('dist/app.js', WAPPOINTMENT_FILE),
            ['wp-element', 'wp-i18n'],
            WAPPOINTMENT_VERSION,
            true
        );
        wp_set_script_translations('wappointment-react-app', 'wappointment');
        wp_localize_script('wappointment-react-app', 'wappointmentData', [
            'apiUrl' => rest_url('wappointment/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'currentPage' => $page,
            'allowedPages' => $this->access->allowedPages(),
            'permissions' => [
                'appointmentsConfirm' => $this->access->can('appointments.confirm'),
                'appointmentsCancel' => $this->access->can('appointments.cancel'),
                'clientsCreate' => $this->access->can('clients.create'),
                'clientsEdit' => $this->access->can('clients.edit'),
                'clientsDelete' => $this->access->can('clients.delete'),
                'selfWeekly' => $this->access->can('self.weekly'),
            ],
        ]);
        do_action('wappointment_admin_enqueue_scripts', $page);
        $viewPath = WAPPOINTMENT_PATH . 'app/Views/admin/app.php';
        if (!file_exists($viewPath)) {
            wp_die('View file not found: admin/app');
        }
        include $viewPath;
    }
}
