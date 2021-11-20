<?php

namespace Wappointment\Services;

use Wappointment\Models\Client;

class Filters
{
    public static function prepareEmailData($data, Client $client, $status = 0)
    {
        return apply_filters('wappointment_appointment_prepare_email_data', $data, $client, $status);
    }
}
