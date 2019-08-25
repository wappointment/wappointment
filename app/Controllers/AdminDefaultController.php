<?php

namespace Wappointment\Controllers;

class AdminDefaultController
{
    public static function defaultContent()
    {
        echo '<div id="' . WAPPOINTMENT_SLUG . '_app"></div>';
    }
}
