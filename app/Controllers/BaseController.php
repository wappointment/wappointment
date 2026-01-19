<?php
declare(strict_types=1);

namespace Wappointment\Controllers;

/**
 * Base controller with view rendering capability
 */
abstract class BaseController
{
    /**
     * Render a view file
     */
    protected function render(string $view, array $data = []): void
    {
        $viewPath = WAPPOINTMENT_PATH . 'app/Views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            wp_die("View file not found: {$view}");
        }

        // Extract data array to variables
        extract($data);
        
        // Include the view file
        include $viewPath;
    }
}
