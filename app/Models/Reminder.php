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


    const APPOINTMENT_STARTS = 1;
    const APPOINTMENT_CONFIRMED = 2;
    const APPOINTMENT_RESCHEDULED = 3;
    const APPOINTMENT_CANCELLED = 4;
    const APPOINTMENT_PENDING = 5;

    const WHEN_UNIT_MINUTES = 1;
    const WHEN_UNIT_HOURS = 2;
    const WHEN_UNIT_DAYS = 3;

    public static function getType($type)
    {
        $types = self::getTypes();
        foreach ($types as $type_infos) {
            if ($type_infos['name'] == $type) {
                return $type_infos['code'];
            }
        }
        return 1;
    }

    public static function getTypes($col = false)
    {
        $types = apply_filters('wappointment_reminder_types', [
            [
                'code' => 1,
                'name' => 'email',
                'icon' => 'dashicons-email-alt'
            ]
        ]);
        return $col === false ? $types : self::getCol($types, $col);
    }

    protected static function getCol($types, $col)
    {
        $types_codes = [];
        foreach ($types as $type) {
            $types_codes[] = $type[$col];
        }
        return $types_codes;
    }

    public function getTypeLabel()
    {
        $types = self::getTypes();
        foreach ($types as $type_infos) {
            if ($type_infos['code'] == $this->type) {
                return $type_infos['name'];
            }
        }
    }

    public static function getEvents()
    {
        return [
            self::APPOINTMENT_STARTS => 'appointment_starts',
            self::APPOINTMENT_CONFIRMED => 'appointment_confirmed',
            self::APPOINTMENT_RESCHEDULED => 'appointment_rescheduled',
            self::APPOINTMENT_CANCELLED => 'appointment_cancelled',
            self::APPOINTMENT_PENDING => 'appointment_pending'
        ];
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
            if (\WappointmentLv::starts_with($rowContent['type'], 'cblock')) {
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
