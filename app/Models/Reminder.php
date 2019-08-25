<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\Messages\AppointmentEmailFiller;

class Reminder extends Model
{
    protected $table = 'wappo_reminders';

    protected $fillable = [
        'subject', 'type', 'event', 'locked', 'options', 'published'
    ];
    protected $casts = [
        'options' => 'array',
        'locked' => 'boolean',
        'published' => 'boolean'
    ];
    const TYPE_EMAIL = 1;
    const TYPE_SMS = 2;
    const APPOINTMENT_STARTS = 1; // appointment start
    const APPOINTMENT_CONFIRMED = 2;
    const APPOINTMENT_RESCHEDULED = 3;
    const APPOINTMENT_CANCELLED = 4;
    const APPOINTMENT_PENDING = 5;

    const WHEN_UNIT_MINUTES = 1;
    const WHEN_UNIT_HOURS = 2;
    const WHEN_UNIT_DAYS = 3;

    public static $types = [
        self::TYPE_EMAIL => 'email',
        self::TYPE_SMS => 'sms'
    ];
    public static $events = [
        self::APPOINTMENT_STARTS => 'appointment_starts',
        self::APPOINTMENT_CONFIRMED => 'appointment_confirmed',
        self::APPOINTMENT_RESCHEDULED => 'appointment_rescheduled',
        self::APPOINTMENT_CANCELLED => 'appointment_cancelled',
        self::APPOINTMENT_PENDING => 'appointment_pending'
    ];

    public static function getTypeCode($key)
    {
        return self::getPropCode($key, self::$types);
    }

    public static function getEventCode($key)
    {
        return self::getPropCode($key, self::$events);
    }

    public static function getPropCode($key, $array)
    {
        return array_search($key, $array);
    }

    public function getDelay()
    {
        //only certain reminder have an event allowing delay
        if ($this->event == self::APPOINTMENT_STARTS) {
            return -$this->options['when_number'] * $this->getUnitInSeconds();
        }
        return false;
    }

    private function getUnitInSeconds()
    {
        switch ($this->options['when_unit']) {
            case self::WHEN_UNIT_MINUTES:
                return 60;
            case self::WHEN_UNIT_HOURS:
                return 3600;
            case self::WHEN_UNIT_DAYS:
                return 86400;
        }
    }

    public function toMailable(Appointment $appointment)
    {
        return new AppointmentEmailFiller($this->subject, $this->getHtmlBody($appointment));
    }

    public function getHtmlBody(Appointment $appointment)
    {
        if ($this->isTipTap()) {
            return \Wappointment\Helpers\TipTap::toHTML($this->filterBody($appointment));
        }
    }

    private function filterBody(Appointment $appointment)
    {
        $bodyEmail = $this->options['body'];
        $newBodyEmailContent = [];

        foreach ($bodyEmail['content'] as $key => $rowContent) {
            if (\Illuminate\Support\Str::startsWith($rowContent['type'], 'cblock')) {
                if ($appointment->isPhysical() && $rowContent['type'] == 'cblockphysical') {
                    $newBodyEmailContent[] = $rowContent;
                } else {
                    if ($appointment->isPhone() && $rowContent['type'] == 'cblockphone') {
                        $newBodyEmailContent[] = $rowContent;
                    } elseif ($appointment->isSkype() && $rowContent['type'] == 'cblockskype') {
                        $newBodyEmailContent[] = $rowContent;
                    }
                }
            } else {
                $newBodyEmailContent[] = $rowContent;
            }
        }

        $bodyEmail['content'] = $newBodyEmailContent;

        return $bodyEmail;
    }

    private function isTipTap()
    {
        return isset($this->options['body']['type']) && $this->options['body']['type'] == 'doc';
    }
}
