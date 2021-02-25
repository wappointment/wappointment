<?php

namespace Wappointment\Controllers;

use Wappointment\Controllers\RestController;
use Wappointment\Managers\Central;

class CustomFieldsController extends RestController
{
    public function get()
    {
        return Central::get('CustomFields')::get();
    }
}
