<?php
declare(strict_types=1);

namespace Wappointment\Database;

use Wappointment\System\Container;
use Wappointment\UseCases\Database\InitializeDatabaseUseCase;

class BookingSchema
{
    public const VERSION = MasterSchema::REFERENCE;

    public static function install(): void
    {
        Container::getInstance()->make(InitializeDatabaseUseCase::class)->execute();
    }
}
