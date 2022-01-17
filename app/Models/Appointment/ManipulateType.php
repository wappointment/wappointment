<?php

namespace Wappointment\Models\Appointment;

trait ManipulateType
{
    public function isPhone()
    {
        return self::TYPE_PHONE == $this->type;
    }

    public function isPhysical()
    {
        return self::TYPE_PHYSICAL == $this->type;
    }

    public function isSkype()
    {
        return self::TYPE_SKYPE == $this->type;
    }

    public function isZoom()
    {
        return self::TYPE_ZOOM == $this->type;
    }

    public static function getTypePhysical()
    {
        return self::TYPE_PHYSICAL;
    }

    public static function getTypePhone()
    {
        return self::TYPE_PHONE;
    }

    public static function getTypeSkype()
    {
        return self::TYPE_SKYPE;
    }

    public static function getTypeZoom()
    {
        return self::TYPE_ZOOM;
    }
}
