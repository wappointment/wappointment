<?php

namespace Wappointment\Services;

use Wappointment\Models\Client as MClient;
use Wappointment\Validators\HttpRequest\Booking;
use Wappointment\System\Status;

class Client
{

    public static function save($data)
    {
        if (is_array($data['email'])) {
            throw new \WappointmentException("Malformed parameter", 1);
        }
        //create or load client account
        $client = MClient::withTrashed()->where('email', $data['email'])->first();

        if (!empty($client) && !empty($client->deleted_at)) {
            $client->restore();
        } else {
            $client = MClient::create(
                [
                    'email' =>  $data['email'],
                    'name' => $data['name'],
                    'options' => [
                        'tz' => $data['options']['tz'],
                        'skype' => $data['options']['skype'],
                        'phone' => $data['options']['phone'],
                    ]
                ]
            );
        }

        $options = $client->options;
        foreach ($data['options'] as $key => $value) {
            $options[$key] = $value;
        }
        $client->options = $options;
        $client->name = $data['name'];
        $client->save();

        //book with that client
        return $client;
    }

    public static function book(Booking $booking)
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

    public static function search($email, $size = 30)
    {
        $clients = MClient::where('email', 'like', $email . '%')->get()->toArray();
        foreach ($clients as &$client) {
            $client['avatar'] = get_avatar_url($client['email'], ['size' => $size]);
        }

        return $clients;
    }

    public static function delete($clientId)
    {
        if (version_compare(Status::dbVersion(), '1.9.3') >= 0) {
            $client = MClient::find($clientId);
            $client->update([
                'email' => md5($client->email . time()),
                'name' => 'Client erased',
                'options' => [],
            ]);
            $client->delete();

            return $client;
        }
        throw new \WappointmentException("Run the pending database update first", 1);
    }
}
