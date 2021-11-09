<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Helpers\Get;
use Wappointment\Helpers\Translations;
use Wappointment\Services\Settings;

class CurrencyController extends RestController
{
    public function get(Request $request)
    {
        return Get::list('currencies');
    }

    public function save(Request $request)
    {
        if (strlen($request->input('currency')) > 3) {
            throw new \WappointmentException('Currency is not correct', 1);
        }
        Settings::save('currency', $request->input('currency'));
        return ['message' => Translations::get('element_saved')];
    }
}
