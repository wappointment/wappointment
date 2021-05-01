<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Services\Services;
use Wappointment\Services\VersionDB;
use Wappointment\Managers\Service;
use Wappointment\Repositories\Services as RepositoriesServices;

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
            'page' => $request->input('page'),
        ];

        if (!$db_update_required) {
            $data['limit_reached'] = $serviceModel::canCreate() ? false : 'To add more services, get the "Services Suite" addon';
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
        if (empty($data['id'])) {
            $data['sorting'] = Services::total();
        }
        $result = Services::save($data);
        $this->refreshRepository();
        return ['message' => 'Service saved! Now to use it, assign it to your staff', 'result' => $result];
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
        return ['message' => 'Service has been reordered', 'result' => $result];
    }


    public function delete(Request $request)
    {
        Services::delete($request->input('id'));
        $this->refreshRepository();
        // clean order
        return ['message' => 'Service deleted', 'result' => true];
    }
}
