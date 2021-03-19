<?php

use Wappointment\Models\Calendar;
use Wappointment\Models\Appointment;
use Wappointment\Models\Status;
use Wappointment\Services\Settings;
use Wappointment\WP\StaffLegacy;

class ImportCalendars extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $activeStaffId = Settings::get('activeStaffId');
        if (empty($activeStaffId)) {
            Settings::save('activeStaffId', wp_get_current_user()->ID);
        }
        $staff = new StaffLegacy;
        $dotcom = $staff->getDotcom();
        Settings::save('email_logo', Settings::getStaff('email_logo'));
        $data = [
            'wp_uid' => $staff->id,
            'name' => $staff->name,
            'status' => 1,
            'options' => [
                'regav' => $staff->getRegav(),
                'timezone' => $staff->timezone,
                'avb' => $staff->getAvb(),
                'avatar_id' => Settings::getStaff('avatarId'),
                'cal_urls' => $staff->getCalendarUrls(),
                'dotcom' => $dotcom,
                'gravatar' => $staff->gravatar
            ],
            'availability' => $staff->getAvailability()
        ];

        if (!empty($dotcom['account_key'])) {
            $data['account_key'] = $dotcom['account_key'];
        }
        $calendar = Calendar::firstOrCreate(['wp_uid' => $staff->id], $data);
        $calendar->addAllServices();
        Appointment::query()->update(['staff_id' => $calendar->id]);
        Status::query()->update(['staff_id' => $calendar->id]);
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
