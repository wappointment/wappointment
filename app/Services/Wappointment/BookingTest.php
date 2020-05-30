<?php

namespace Wappointment\Services\Wappointment;

class BookingTest extends API
{


    public function record($request)
    {

        $response = $this->client->request('POST', $this->call('/api/bookingtest'), [
            'form_params' => $this->prepareData($request->all())
        ]);


        $result = $this->processResponse($response);


        return ['response' => $result];
    }

    private function prepareData($data)
    {
        $data['site'] = get_site_url();
        return $data;
    }
}
