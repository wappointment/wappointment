<?php

use Wappointment\Models\Calendar;
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

        $staff = new StaffLegacy();
        $dotcom = $staff->getDotcom();
        $data = [
            'wp_uid' => $staff->id,
            'name' => $staff->name,
            'options' => [
                'regav' => $staff->getRegav(),
                'timezone' => $staff->timezone,
                'email_logo' => Settings::getStaff('email_logo'),
                'avb' => $staff->getAvb(),
                'avatar_id' => Settings::getStaff('avatarId'),
                'calurl' => $staff->getCalendarUrls(),
                'dotcom' => $dotcom
            ],
            'availability' => $staff->getAvailability()
        ];

        if (!empty($dotcom['account_key'])) {
            $data['account_key'] = $dotcom['account_key'];
        }
        Calendar::create($data);
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
