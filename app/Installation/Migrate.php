<?php

namespace Wappointment\Installation;

use Wappointment\Config\Database;
use Wappointment\ClassConnect\Capsule;

class Migrate extends \Illuminate\Database\Migrations\Migration
{
    public $capsule;
    public $migrationRepo;
    protected $migrations_folders = false;

    public function __construct($migrations_folder = false)
    {
        $this->capsule = Database::capsule();

        $this->migrationRepo = new \Illuminate\Database\Migrations\DatabaseMigrationRepository(
            $this->capsule->getDatabaseManager(),
            Database::$prefix_self . '_migrations'
        );

        if (!$this->migrationRepo->repositoryExists()) {
            $this->migrationRepo->createRepository();
        }
        $this->setMigrationFolders($migrations_folder);
    }

    protected function getForeignName($name)
    {
        return is_multisite() ? Capsule::connection()->getTablePrefix() . $name : $name;
    }

    protected function setMigrationFolders($migrations_folder = false)
    {
        $migrations_folder = $migrations_folder === false ?
            WAPPOINTMENT_PATH . 'database' . DIRECTORY_SEPARATOR . 'migrations' : $migrations_folder;
        $this->addFolder($migrations_folder);
    }

    protected function addFolder($folder = false)
    {
        if ($folder) {
            $this->migrations_folders[] = $folder;
        }
    }

    public function migrate()
    {
        $filesys = new \Illuminate\Filesystem\Filesystem();
        $migrator = new \Illuminate\Database\Migrations\Migrator(
            $this->migrationRepo,
            $this->capsule->getDatabaseManager(),
            $filesys
        );
        return $migrator->run($this->migrations_folders);
    }

    public function rollback()
    {
        $filesys = new \Illuminate\Filesystem\Filesystem();
        $migrator = new \Illuminate\Database\Migrations\Migrator(
            $this->migrationRepo,
            $this->capsule->getDatabaseManager(),
            $filesys
        );
        return $migrator->rollback($this->migrations_folders);
    }
}
