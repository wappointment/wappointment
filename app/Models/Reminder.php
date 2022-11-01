<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\Messages\AppointmentEmailFiller;
use Wappointment\Services\Payment;
use Wappointment\Services\Settings;

class Reminder extends Model
{
    protected $table = 'wappo_reminders';

    protected $fillable = [
        'subject', 'type', 'event', 'locked', 'options', 'published', 'parent', 'lang'
    ];

    protected $casts = [
        'options' => 'array',
        'locked' => 'boolean',
        'published' => 'boolean'
    ];
    protected $appends = ['label'];

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

    public function scopeActiveReminders($query)
    {
        $is_in = [static::APPOINTMENT_STARTS, static::APPOINTMENT_CONFIRMED];
        if ((int) Settings::get('approval_mode') != 1 || Payment::active()) {
            $is_in[] = static::APPOINTMENT_PENDING;
        }
        if (Settings::get('allow_rescheduling')) {
            $is_in[] = static::APPOINTMENT_RESCHEDULED;
        }
        if (Settings::get('allow_cancellation')) {
            $is_in[] = static::APPOINTMENT_CANCELLED;
        }

        $query->whereIn('event', apply_filters('wappointment_reminders_listed', $is_in));
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

    public function getLabelAttribute()
    {
        $labels = [
            self::APPOINTMENT_STARTS => $this->getReminderLabel(),
            self::APPOINTMENT_CONFIRMED => __('Sent after appointment has been confirmed.', 'wappointment'),
            self::APPOINTMENT_RESCHEDULED => __('Sent after appointment has been rescheduled.', 'wappointment'),
            self::APPOINTMENT_CANCELLED => __('Sent after appointment has been cancelled.', 'wappointment'),
            self::APPOINTMENT_PENDING => __('Sent after appointment has been booked (when admin approval is required or when payment method is active)', 'wappointment'),
        ];
        $labels = apply_filters('wappointment_reminders_labels', $labels);
        return empty($labels[$this->event]) ? 'undefined' : $labels[$this->event];
    }

    public function getReminderLabel()
    {
        /* translators: %1$s is replaced with a number of %2$s which are either, minutes, hours or days */
        return empty($this->options['when_number']) ? '' : sprintf(__('Sent before appointment takes place.(sent %1$s %2$s before)', 'wappointment'), $this->options['when_number'], $this->convertUnit($this->options['when_unit']));
    }

    public function convertUnit($unit)
    {
        return $unit == 1 ? 'minute(s)' : $this->isHoursOrDays($unit);
    }

    public function isHoursOrDays($unit)
    {
        return $unit == 2 ? 'hours' : 'days';
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

    public function toMailable($appointment = null)
    {
        return new AppointmentEmailFiller([
            'subject' => $this->subject,
            'body' => $this->getHtmlBody($appointment)
        ]);
    }

    public function getHtmlBody($appointment = null)
    {
        if ($this->isTipTap()) {
            return \Wappointment\Helpers\TipTap::toHTML($this->filterBody($appointment));
        }
    }

    private function filterBody($appointment = null)
    {
        $bodyEmail = $this->options['body'];
        $newBodyEmailContent = [];

        foreach ($bodyEmail['content'] as $key => $rowContent) {
            if ($appointment !== null && \WappointmentLv::starts_with($rowContent['type'], 'cblock')) {
                if ($appointment->isPhysical() && $rowContent['type'] == 'cblockphysical') {
                    $newBodyEmailContent[] = $rowContent;
                } else {
                    if ($appointment->isPhone() && $rowContent['type'] == 'cblockphone') {
                        $newBodyEmailContent[] = $rowContent;
                    } elseif ($appointment->isSkype() && $rowContent['type'] == 'cblockskype') {
                        $newBodyEmailContent[] = $rowContent;
                    } elseif ($appointment->isZoom() && $rowContent['type'] == 'cblockzoom') {
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
