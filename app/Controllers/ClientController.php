<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Services\Client;

class ClientController extends RestController
{
    public function search(Request $request)
    {
        return Client::search($request->input('email'));
    }
}
