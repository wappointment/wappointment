<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\Carbon;

class DateTime
{
    public static function tz()
    {
        $ordered_tz = [];

        $t_zones = timezone_identifiers_list();

        foreach ($t_zones as $keyTz => $full_name) {
            $continent_city = explode('/', $full_name);
            /*             if ($continent_city[0] == 'UTC') {
                            continue;
                        } */
            try {
                //this throws exception for 'US/Pacific-New'
                $zone = new \DateTimeZone($full_name);

                $seconds = $zone->getOffset(new \DateTime('now', $zone));
                $hours = sprintf('%+02d', intval($seconds / 3600));
                $minutes = sprintf('%02d', ($seconds % 3600) / 60);

                if (!isset($ordered_tz[$continent_city[0]])) {
                    $ordered_tz[$continent_city[0]] = [];
                }
                $sub_tz = empty($continent_city[1]) ? $continent_city[0] : $continent_city[1];
                $ordered_tz[$continent_city[0]][$sub_tz] = [
                    'name' => $full_name,
                    'hours' => $hours,
                    'minutes' => $minutes,
                    'key' => $keyTz
                ];
            } catch (\WappointmentException $e) {
            }
        }

        ksort($ordered_tz);

        return $ordered_tz;
    }
    public static function timeZToUtc($time)
    {
        $time = \str_replace('Z', '', $time);

        return (new Carbon($time, 'UTC'));
    }
    public static function converTotUtc($time, $timezone)
    {
        $time = str_replace('Z', '', $time);
        return (new Carbon($time, $timezone))->setTimezone('UTC')->format(WAPPOINTMENT_DB_FORMAT . ':00');
    }

    public static function convertUnixTS($timestamp, $format = WAPPOINTMENT_DB_FORMAT . ':00', $timezone = 'UTC')
    {
        return Carbon::createFromTimestamp($timestamp)->setTimezone($timezone)->format($format);
    }
}
