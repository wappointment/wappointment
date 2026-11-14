<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Auth;

use Wappointment\Actions\Auth\RegisterRolesAction;

class RegisterRolesUseCase
{
    public function __construct(private RegisterRolesAction $action) {}

    public function execute(): void
    {
        $this->action->handle();
    }
}
