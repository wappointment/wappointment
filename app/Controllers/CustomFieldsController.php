<?php

namespace Wappointment\Controllers;

use Wappointment\Controllers\RestController;
use Wappointment\Services\CustomFields;

class CustomFieldsController extends RestController
{
    public function get()
    {
        return CustomFields::get();
    }
}
