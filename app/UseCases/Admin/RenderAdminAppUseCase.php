<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Admin;

use Wappointment\Actions\Admin\RenderAdminAppAction;

class RenderAdminAppUseCase
{
    public function __construct(private RenderAdminAppAction $action) {}

    public function execute(string $page): void
    {
        $this->action->handle($page);
    }
}
