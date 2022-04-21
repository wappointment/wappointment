<?php

namespace Wappointment\Services;

use Wappointment\Models\Client as MClient;
use Wappointment\Validators\HttpRequest\BookingAdmin;

class Admin
{
    public static function book(BookingAdmin $booking)
    {
        $client_id = $booking->get('clientid');

        if ($client_id > 0) {
            $client = MClient::find((int)$client_id);
        } else {
            $client = MClient::where('email', $booking->get('email'))->withTrashed()->first();
            if (!empty($client) && $client->trashed()) {
                $client->restore();
            }
        }

        if (empty($client)) {
            $dataClient = $booking->preparedData();
            $client = MClient::create($dataClient);
        }
        //book with that client
        return $client->bookAsAdmin($booking);
    }
}
