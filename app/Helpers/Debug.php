<?php

namespace Wappointment\Helpers;

class Debug
{
    public static function convertExceptionToArray($e)
    {
        return [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => \WappointmentLv::collect($e->getTrace())->map(function ($trace) {
                return \Illuminate\Support\Arr::except($trace, ['args']);
            })->all(),
        ];
    }
}
