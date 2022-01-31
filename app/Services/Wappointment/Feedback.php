<?php

namespace Wappointment\Services\Wappointment;

class Feedback extends API
{


    public function sendFeedback($request)
    {

        $response = $this->client
            ->setForm([
                'origin' => get_option('siteurl'),
                'reason' => $request->input('reason'),
                'email' => $request->input('email'),
                'details' => $request->input('details'),
                'version' => WAPPOINTMENT_VERSION,
            ])
            ->post($this->call('/api/feedback'));


        $result = $this->processResponse($response);

        return ['response' => $result];
    }
}
