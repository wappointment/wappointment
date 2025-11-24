<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Helpers\Translations;
use Wappointment\Models\Appointment;
use Wappointment\Services\Client;
use Wappointment\Validators\HttpRequest\BookingAdmin;
use Wappointment\Services\Admin;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Models\Client as ClientModel;
use Wappointment\Services\CurrentUser;
use Wappointment\Services\DateTime;
use Wappointment\Services\Settings;
// @codingStandardsIgnoreFile
class ClientController extends RestController
{

    public function search(Request $request)
    {
        return Client::search($request->input('email'));
    }

    public function book(BookingAdmin $booking)
    {
        if ($booking->hasErrors()) {
            return WPHelpers::restError(Translations::get('review_fields'), 500, $booking->getErrors());
        }

        $result = Admin::book($booking);
        if (isset($result['errors'])) {
            return WPHelpers::restError(Translations::get('booking_failed'), 500, $result['errors']);
        }

        return ['message' => __('Appointment recorded', 'wappointment')];
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
            'clients' => $this->getClients($request)
        ];
    }

    protected function getClients(Request $request)
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

        if (!empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('email', 'LIKE', '%' . $searchTerm . '%');
                $query->orWhere('name', 'LIKE', '%' . $searchTerm . '%');
                $query->orWhere('options', 'LIKE', '%' . $searchTerm . '%');
            });
           
        }

        return $query->paginate(Settings::getStaff('per_page'));
    }

    public function save(Request $request)
    {
        $this->testIsClientOwned($request);
        Client::save($request->all());

        return [
            'message' => Translations::get('element_saved'),
        ];
    }

    public function delete(Request $request)
    {
        $this->testIsClientOwned($request);
        ClientModel::where('id', (int)$request->input('id'))->delete();

        return [
            'elementDeleted' => $request->input('id'),
            'message' => Translations::get('element_deleted'),
        ];
    }

    protected function testIsClientOwned(Request $request)
    {
        if (!CurrentUser::isAdmin()) {
            $appointment = Appointment::where('client_id', (int)$request->input('id'))->where('staff_id', CurrentUser::calendarId())->first();
            if (empty($appointment)) {
                throw new \WappointmentException(__('Cannot modify clients that are not yours', 'wappointment'), 1);
            }
        }
    }
}
