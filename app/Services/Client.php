<?php

namespace Wappointment\Services;

use Wappointment\Models\Client as MClient;
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
