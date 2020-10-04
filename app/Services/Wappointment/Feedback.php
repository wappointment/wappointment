<?php

namespace Wappointment\Services\Wappointment;

class Feedback extends API
{


    public function sendFeedback($request)
    {

        $response = $this->client->request('POST', $this->call('/api/feedback'), [
            'form_params' => [
                'origin' => get_option('siteurl'),
                'reason' => $request->input('reason'),
                'email' => $request->input('email'),
                'details' => $request->input('details'),
            ]
        ]);


        $result = $this->processResponse($response);

        return ['response' => $result];
    }
}
