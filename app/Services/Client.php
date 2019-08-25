<?php

namespace Wappointment\Services;


use Wappointment\Models\Client as MClient;
use Wappointment\Validators\HttpRequest\Booking;

class Client
{
    public static function book(Booking $booking)
    {
        //create or load client account
        $client = MClient::firstOrCreate(
            ['email' => $booking->get('email')],
            [
                'name' => $booking->get('name'),
                'options' => [
                    'phone' => $booking->get('phone'),
                    'skype' => $booking->get('skype'),
                    'tz' => $booking->get('ctz'),
                ]
            ]
        );

        //book with that client
        return $client->book($booking->get('time'), $booking->get('type'));
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
