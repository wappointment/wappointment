<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;
use Wappointment\Models\Status;
use Wappointment\System\Status as SystemStatus;
use Wappointment\WP\Helpers as WPHelpers;


class AlterStatusesTable2 extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!SystemStatus::dbVersionAlterRequired()) {
            return;
        }
        $this->clearCalendarStatus();

        try {
            Capsule::schema()->table(Database::$prefix_self . '_statuses', function ($table) {
                $table->unique(['source', 'eventkey'], 'unique_source_eventkey');
            });
        } catch (\Throwable $th) {
            $this->refetch();
            throw $th;
        }

        $this->refetch();
    }

    private function refetch()
    {
        foreach (\Wappointment\Services\Staff::getIds() as $staff_id) {
            $calendar_urls = WPHelpers::getStaffOption('cal_urls', $staff_id);
            $hasChanged = false;
            if (!empty($calendar_urls) && is_array($calendar_urls)) {
                foreach ($calendar_urls as $calurl) {
                    if ((new \Wappointment\Services\Calendar($calurl, $staff_id))->refetch()) {
                        $hasChanged = true;
                    }
                }
            }

            //regenerate availability only when we get new events
            if ($hasChanged) {
                (new \Wappointment\Services\Availability($staff_id))->regenerate();
            }
        }
    }


    private function clearCalendarStatus()
    {
        $calendar_urls = WPHelpers::getStaffOption('cal_urls');
        if (!empty($calendar_urls) && is_array($calendar_urls)) {
            $calkeys = [];
            foreach ($calendar_urls as $calkey => $calurl) {
                $calkeys[] = $calkey;
            }
            Status::whereIn('source', $calkeys)->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
