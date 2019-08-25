<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Services\Wappointment\Licences;
use Wappointment\Services\Wappointment\Addons;
use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;

class AddonsController extends RestController
{
    public function get(Request $request)
    {
        if ($request->input('remember') == 'true') {
            Settings::save('wappointment_allowed', true);
        }
        $data = (new Addons)->get();
        $data->admin_email = Settings::get('email_notifications');
        $statuses = WPHelpers::getOption('subscribed_status');
        $data->statuses = $statuses === false ? []:$statuses;
        return $data;
    }

    public function save(Request $request)
    {
        $result = (new Licences)->register($request->input('pkey'));
        
        return [
            'message' => $result->message,
            'addons' => (new Addons)->get()->addons
        ];
    }

    public function install(Request $request)
    {
        return (new Addons)->install((object) $request->input('addon'));
    }

    public function activate(Request $request)
    {
        return (new Addons)->activate((object) $request->input('addon'));
    }

    public function deactivate(Request $request)
    {
        return (new Addons)->deactivate((object) $request->input('addon'));
    }

    public function check()
    {
        $resultCheck = (new Licences)->check();
        if($resultCheck) {
            return ['message' => 'Success checking licence'];
        }
    }
}
