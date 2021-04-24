<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Models\Appointment;
use Wappointment\Services\Client;
use Wappointment\Validators\HttpRequest\BookingAdmin;
use Wappointment\Services\Admin;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Models\Client as ClientModel;
use Wappointment\Services\CurrentUser;
use Wappointment\Services\DateTime;
use Wappointment\Services\Settings;

class ClientController extends RestController
{
    public function search(Request $request)
    {
        return Client::search($request->input('email'));
    }

    public function book(BookingAdmin $booking)
    {
        if ($booking->hasErrors()) {
            return WPHelpers::restError('Review your fields', 500, $booking->getErrors());
        }

        $result = Admin::book($booking);
        if (isset($result['errors'])) {
            return WPHelpers::restError('Impossible to proceed with the booking', 500, $result['errors']);
        }

        return ['message' => 'Appointment recorded'];
    }

    public function index(Request $request)
    {
        if (!empty($request->input('per_page'))) {
            Settings::saveStaff('per_page', $request->input('per_page'));
        }

        return [
            'page' => $request->input('page'),
            'viewData' => [
                'per_page' => Settings::getStaff('per_page'),
                'timezones_list' => DateTime::tz()
            ],
            'clients' => $this->getClients()
        ];
    }

    protected function getClients()
    {
        $query = ClientModel::orderBy('id', 'DESC');
        if (!CurrentUser::isAdmin()) {
            $raw = str_replace(
                '?',
                CurrentUser::calendarId(),
                Appointment::select('client_id')
                    ->where('staff_id', CurrentUser::calendarId())
                    ->distinct()
                    ->toSql()
            );
            $query->whereRaw('id IN (' . $raw . ')');
        }

        return $query->paginate(Settings::getStaff('per_page'));
    }

    public function save(Request $request)
    {
        $this->testIsClientOwned($request);
        Client::save($request->all());

        return [
            'message' => 'Client save',
        ];
    }

    public function delete(Request $request)
    {
        $this->testIsClientOwned($request);
        ClientModel::where('id', (int)$request->input('id'))->delete();

        return [
            'elementDeleted' => $request->input('id'),
            'message' => 'Client deleted',
        ];
    }

    protected function testIsClientOwned(Request $request)
    {
        if (!CurrentUser::isAdmin()) {
            $appointment = Appointment::where('client_id', (int)$request->input('id'))->where('staff_id', CurrentUser::calendarId())->first();
            if (empty($appointment)) {
                throw new \WappointmentException("Cannot modify clients that are not yours", 1);
            }
        }
    }
}
