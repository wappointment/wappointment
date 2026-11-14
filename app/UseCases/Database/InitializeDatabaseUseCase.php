<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Database;

use Wappointment\Actions\Database\InitializeDatabaseAction;

class InitializeDatabaseUseCase
{
    public function __construct(private InitializeDatabaseAction $action) {}

    public function execute(): void
    {
        $this->action->handle();
    }
}
