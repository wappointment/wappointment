<?php

namespace Wappointment\Services;

use Wappointment\Models\Client as MClient;
use Wappointment\Validators\HttpRequest\LegacyBooking;

class ClientLegacy
{

    public static function book(LegacyBooking $booking)
    {
        if (is_array($booking->get('email'))) {
            throw new \WappointmentException("Malformed parameter", 1);
        }
        //create or load client account
        $client = MClient::withTrashed()->where('email', $booking->get('email'))->first();
        if (!empty($client) && !empty($client->deleted_at)) {
            $client->restore();
        }
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
}
