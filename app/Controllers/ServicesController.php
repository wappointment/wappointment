<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Helpers\Get;
use Wappointment\Helpers\Translations;
use Wappointment\Services\Services;
use Wappointment\Services\VersionDB;
use Wappointment\Managers\Service;
use Wappointment\Repositories\Services as RepositoriesServices;
use Wappointment\Services\Payment;
use Wappointment\Services\Settings;

class ServicesController extends RestController
{

    public function get(Request $request)
    {
        $serviceModel = Service::model();
        $db_update_required = VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);
        $services = $db_update_required ? $this->getlegacy() : (new RepositoriesServices)->get();
        $data = [
            'db_required' => $db_update_required,
            'services' => $services,
            'currency' => Payment::currencyCode(),
            'tax' => Settings::get('tax'),
        ];

        if (!$db_update_required) {
            $data['limit_reached'] = $serviceModel::canCreate() ? false : Translations::get('add_calendars_addon', [Get::list('addons')['wappointment_services']['name']]);
        }
        return $data;
    }

    public function getlegacy()
    {
        return Service::all();
    }

    public function save(Request $request)
    {
        $data = $request->only(['id', 'name', 'options', 'locations_id']);
        $new = false;
        if (empty($data['id'])) {
            $data['sorting'] = Services::total();
            $new = true;
        }
        $result = Services::save($data);
        if ($new) {
            Services::reorder($result->id, 0);
        }

        $this->refreshRepository();
        return ['message' => Translations::get('element_saved') . __('Next, assign it to your staff in Wappointment > Settings > Calendars & Staff', 'wappointment'), 'result' => $result];
    }

    protected function refreshRepository()
    {
        (new RepositoriesServices)->refresh();
    }

    public function reorder(Request $request)
    {
        $data = $request->only(['id', 'new_sorting']);

        $result = Services::reorder($data['id'], $data['new_sorting']);
        $this->refreshRepository();
        return ['message' => Translations::get('element_reordered'), 'result' => $result];
    }


    public function delete(Request $request)
    {
        Services::delete($request->input('id'));
        $this->refreshRepository();
        // clean order
        return ['message' => Translations::get('element_deleted'), 'result' => true];
    }
}
