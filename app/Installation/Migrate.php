<?php

namespace Wappointment\Installation;

use Wappointment\Config\Database;

class Migrate extends \Illuminate\Database\Migrations\Migration
{
    public $capsule;
    public $migrationRepo;

    public function __construct()
    {
        $this->capsule = Database::capsule();

        $this->migrationRepo = new \Illuminate\Database\Migrations\DatabaseMigrationRepository($this->capsule->getDatabaseManager(), Database::$prefix_self . '_migrations');

        if (!$this->migrationRepo->repositoryExists()) {
            $this->migrationRepo->createRepository();
        }
    }

    public function migrate()
    {
        $filesys = new \Illuminate\Filesystem\Filesystem();
        $migrator = new \Illuminate\Database\Migrations\Migrator($this->migrationRepo, $this->capsule->getDatabaseManager(), $filesys);
        $migrator->run([WAPPOINTMENT_PATH . 'database' . DIRECTORY_SEPARATOR . 'migrations']);
        $migrationCreator = new \Illuminate\Database\Migrations\MigrationCreator($filesys);
    }
}
