<?php

namespace Wappointment\Services;

class Regenerate
{
    public static function all()
    {
        foreach (Staff::get() as $staff) {
            (new Availability($staff['id']))->regenerate();
        }
    }
}
