<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;

class Log extends Model
{
    protected $table = 'wappo_logs';
    protected $fillable = [
        'client_id', 'type', 'options'
    ];

    public const TYPE_DEFAULT = 0;
    public const TYPE_APPOINTMENT_CANCELLED = 1;

    public static function canceledAppointment($appointment)
    {
        return self::create(
            [
                'type' => self::TYPE_APPOINTMENT_CANCELLED,
                'options' => $appointment->toArray(),
                'client_id' => $appointment->client_id,
                'created_at' => \Carbon\Carbon::now()->format(WAPPOINTMENT_DB_FORMAT)
            ]
        );
    }
    public static function data($data)
    {
        return self::create(
            [
                'type' => self::TYPE_APPOINTMENT_CANCELLED,
                'options' => $data,
                'created_at' => \Carbon\Carbon::now()->format(WAPPOINTMENT_DB_FORMAT)
            ]
        );
    }
}
