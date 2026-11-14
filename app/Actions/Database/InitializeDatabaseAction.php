<?php
declare(strict_types=1);

namespace Wappointment\Actions\Database;

use Wappointment\Repositories\DatabaseSchemaRepository;
use Wappointment\Database\BookingSchema;

class InitializeDatabaseAction
{
    public function __construct(
        private DatabaseSchemaRepository $repository,
        private InspectDatabaseSchemaAction $inspect,
    ) {}

    public function handle(): void
    {
        $this->repository->withInstallationLock(function (): void {
            $report = $this->inspect->handle();
            if ($report->state === 'empty') {
                foreach ([
                    'wappointment_installation_completed', 'wappointment_db_version',
                    'wappointment_booking_schema', 'wappointment_settings',
                ] as $option) {
                    if (get_option($option, false) !== false) {
                        throw new \RuntimeException('Wappointment installation records exist but its tables are missing. Restore or inspect the database before continuing.');
                    }
                }
                $this->repository->createFresh();
                $report = $this->inspect->handle();
            }
            if ($report->state !== 'compatible') {
                $first = $report->issues[0] ?? null;
                throw new \RuntimeException(
                    'Wappointment database does not match master. No existing tables were changed. '
                    . ($first ? "{$first->table}.{$first->member}: expected {$first->expected}; found {$first->actual}." : '')
                );
            }
            update_option('wappointment_booking_schema', BookingSchema::VERSION, false);
            if (get_option('wappointment_booking_schema') !== BookingSchema::VERSION) {
                throw new \RuntimeException('Wappointment tables were verified but its schema readiness marker could not be saved.');
            }
        });
    }
}
