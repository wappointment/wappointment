<?php

namespace Wappointment\Managers;

class Service
{
    public static function all()
    {
        return Central::get('Service')::all();
    }

    public static function save($data)
    {
        return Central::get('Service')::saveService($data);
    }

    public static function patch($service_id, $data)
    {
        return Central::get('Service')::patch($service_id, $data);
    }

    public static function hasZoom($service)
    {
        if (!method_exists(Central::get('Service'), 'hasZoom')) {
            return false;
        }
        return Central::get('Service')::hasZoom($service);
    }

    public static function extractDurations($services)
    {
        //'durations' => [Service::get()['duration']],
        if (count($services) == 1 && !empty($services[0]['duration'])) {
            return [$services[0]['duration']];
        }
        $durations = $services->map(function ($item, $key) {
            $innerdur = [];
            foreach ($item['options']['durations'] as $key => $array) {
                $innerdur[] = $array['duration'];
            }
            return $innerdur;
        });

        $durations_filtered = array_filter($durations->flatten()->unique()->toArray());
        sort($durations_filtered);
        return $durations_filtered;
    }
}
