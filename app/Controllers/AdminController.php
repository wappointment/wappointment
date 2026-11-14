<?php
declare(strict_types=1);

namespace Wappointment\Controllers;

use Wappointment\UseCases\Admin\RenderAdminAppUseCase;

class AdminController
{
    public function __construct(private RenderAdminAppUseCase $useCase) {}

    public function __invoke(): void
    {
        $page = isset($_GET['page']) && is_string($_GET['page'])
            ? sanitize_key(wp_unslash($_GET['page']))
            : 'wappointment-jobs';
        $this->useCase->execute($page);
    }
}
