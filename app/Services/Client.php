<?php

namespace Wappointment\Services;

use Wappointment\Models\Client as MClient;
use Wappointment\Validators\HttpRequest\Booking;

class Client
{
    public static function book(Booking $booking)
    {
        //create or load client account
        $client = MClient::where('email', $booking->get('email'))->first();
        $dataClient = [
            'name' => $booking->get('name'),
            'options' => [
                'tz' => $booking->get('ctz'),
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
        return $client->book($booking);
    }

    public static function search($email, $size = 30)
    {
        $clients = MClient::where('email', 'like', $email . '%')->get()->toArray();
        foreach ($clients as &$client) {
            $client['avatar'] = get_avatar_url($client['email'], ['size' => $size]);
        }

        return $clients;
    }
}
