<?php

namespace Wappointment\Controllers;

class AdminDefaultController
{
    public static function defaultContent()
    {
        _safe('<div id="' . WAPPOINTMENT_SLUG . '_app"></div>');
    }
}
