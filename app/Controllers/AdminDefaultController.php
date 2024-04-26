<?php

namespace Wappointment\Controllers;
// phpcs:ignoreFile
class AdminDefaultController
{
    public static function defaultContent()
    {
        echo '<div id="' . WAPPOINTMENT_SLUG . '_app"></div>';
    }
}
