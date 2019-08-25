<?php

namespace Wappointment\Controllers;

use Wappointment\Services\ViewsData;
use Wappointment\ClassConnect\Request;

class ViewsDataController extends RestController
{
    public function get(Request $request)
    {
        return (new ViewsData())->load($request->input('key'));
    }
}
