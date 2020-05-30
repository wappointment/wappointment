<?php

namespace Wappointment\Services;

use Wappointment\Models\Client as MClient;
use Wappointment\ClassConnect\Carbon;

class BookingTest
{
    public function send()
    {

        $client = MClient::firstOrCreate(
            ['email' => 'ben@wappointment.com'],
            [
                'name' => 'Ben From Wappointment',
                'options' => [
                    'tz' => 'Europe/Brussels',
                    'test_appointment' => true
                ]
            ]
        );

        $availability_data = (new ViewsData())->load('front_availability');

        $service = Service::get();
        $durationinSec = $service['duration'] * 60;

        $staff = $availability_data['staffs'][0];

        $availability = $availability_data['availability'][$staff['id']][0];
        $type = (int) call_user_func('Wappointment\Models\Appointment::getType' . ucfirst($service['type'][0]));

        $hasBeenBooked = Appointment::adminBook($client, $availability[0], $availability[0] + $durationinSec, $type, $service);

        if (!$hasBeenBooked) {
            throw new \WappointmentException('Error cannot generate test appointment', 1);
        }

        return ['response' => $hasBeenBooked];
    }

    public function minTime($timezone)
    {
        return Carbon::now($timezone)->addHours(Settings::get('hours_before_booking_allowed'))->timestamp;
    }
}
