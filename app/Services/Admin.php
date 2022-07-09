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
            $client = MClient::create(
                static::addDefaultEmail(
                    $booking->preparedData(),
                    $booking
                )
            );
        }
        //book with that client
        return $client->bookAsAdmin($booking);
    }

    /**
     * fail saafe for recurrent booking creating error
     *
     * @param [type] $dataClient
     * @param [type] $booking
     * @return void
     */
    protected static function addDefaultEmail($dataClient, $booking)
    {
        if (empty($dataClient['email']) && !empty($booking->get('staff_id'))) {
            $sraff = new \Wappointment\WP\Staff((int)$booking->get('staff_id'));
            $dataClient['email'] = $sraff->emailAddress();
        }
        return $dataClient;
    }
}
