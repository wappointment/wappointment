<?php

namespace Wappointment\Services;

use Wappointment\Models\Client as MClient;
use Wappointment\Validators\HttpRequest\BookingAdmin;

class AdminLegacy
{
    public static function book(BookingAdmin $booking)
    {
        $client_id = (int) $booking->get('clientid');

        if ($client_id > 0) {
            $client = MClient::find($client_id);
        } else {
            if (is_array($booking->get('email'))) {
                throw new \WappointmentException(__('Malformed parameter', 'wappointment'), 1);
            }
            $client = MClient::where('email', $booking->get('email'))->first();
        }

        $dataClient = [
            'name' => $booking->get('name'),
            'options' => [
                'tz' => $booking->get('timezone') ?: Settings::getStaff('timezone'),
            ]
        ];

        if (!empty($booking->get('phone'))) {
            $dataClient['options']['phone'] = $booking->get('phone');
        }
        if (!empty($booking->get('skype'))) {
            $dataClient['options']['skype'] = $booking->get('skype');
        }

        if (empty($client)) {
            $dataClient['email'] = $booking->get('email');
            $client = MClient::create($dataClient);
        } else {
            $options = $client->options;
            if (!empty($booking->get('phone'))) {
                $options['phone'] = $booking->get('phone');
            }
            if (!empty($booking->get('skype'))) {
                $options['skype'] = $booking->get('skype');
            }
            $client->options = $options;
            $client->save();
        }

        //book with that client
        return self::createAppointment($client, $booking->get('start'), $booking->get('end'), $booking->get('type'));
    }

    private static function createAppointment(MClient $client, $start, $end, $type, $service = false)
    {
        if ($service === false) {
            $service = Service::get();
        }

        //test type is allowed
        if (!in_array($type, $service['type'])) {
            throw new \WappointmentException('Error booking type not allowed', 1);
        }

        $type = (int) call_user_func('Wappointment\Models\Appointment::getType' . ucfirst($type));
        //test that this is bookable
        $hasBeenBooked = Appointment::adminBook($client, $start, $end, $type, $service);
        if (!$hasBeenBooked) {
            throw new \WappointmentException(__('Error while booking', 'wappointment') . '(5)', 1);
        }
        return $hasBeenBooked;
    }

    public function search($email, $size = 30)
    {
        $clients = MClient::where('email', 'like', $email . '%')->get()->toArray();
        foreach ($clients as &$client) {
            $client['avatar'] = get_avatar_url($client['email'], ['size' => $size]);
        }

        return $clients;
    }
}
