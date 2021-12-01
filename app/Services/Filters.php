<?php

namespace Wappointment\Services;

class Filters
{
    public static function prepareEmailData($data, $client, $status = 0)
    {
        return apply_filters('wappointment_appointment_prepare_email_data', $data, $client, $status);
    }
}
