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
        $dataClient = $booking->preparedData();
        if (empty($dataClient['name'])) {
            $dataClient['name'] = '';
        }
        if (empty($client)) {
            $client = MClient::create($dataClient);
        } else {
            unset($dataClient['email']);
            $options = $client->options;

            foreach ($dataClient as $key => $value) {
                if ($key !== 'options') {
                    $client->$key = $value;
                }
            }
            foreach ($dataClient['options'] as $key => $optionvalue) {
                $options[$key] = $optionvalue;
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
