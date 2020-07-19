<?php

namespace Wappointment\Services\Wappointment;

use Wappointment\Validators\HttpRequest\ContactAdmin;

class Contact extends API
{


    public function sendMessage(ContactAdmin $request)
    {

        $response = $this->client->request('POST', $this->call('/api/ticket/create'), [
            'form_params' => $this->prepareData($request->getData())
        ]);


        $result = $this->processResponse($response);
        if ($result) {
            //getQuestionIdAndTrack
        }

        return ['response' => $result];
    }

    private function prepareData($data)
    {
        $data['message'] = nl2br($data['message']);
        //$data['options.sitedetails'] = $this->getSiteDetails();
        return $data;
    }

    private function getSiteDetails()
    {
        return [];
    }
}
