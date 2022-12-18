<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Formatters\EventsCalendar;
use Wappointment\Services\DateTime;
use Wappointment\Helpers\Translations;
use Wappointment\Services\Settings;
use Wappointment\Services\AppointmentNew;
use Wappointment\Services\Preferences;
use Wappointment\Services\Wappointment\DotCom;
use Wappointment\Services\CurrentUser;
use Wappointment\Managers\Central;
use Wappointment\Models\Appointment;
use Wappointment\Repositories\CalendarsBack;

class EventsController extends RestController
{
    protected function getAppointmentModel()
    {
        return Central::get('AppointmentModel');
    }

    protected function canEditAppointment($id)
    {
        $appointment = $this->getAppointmentModel()::find((int)$id);
        if (!CurrentUser::isAdmin() && CurrentUser::calendarId() !== (int) $appointment->staff_id) {
            throw new \WappointmentException(__('Cannot modify an appointment which doesn\'t belong to you', 'wappointment'), 1);
        }
        return $appointment;
    }

    public function delete(Request $request)
    {
        $appointment = $this->canEditAppointment($request->input('id'));
        $failures = [];
        if ($request->input('sibblings')) {
            $failures = $this->deleteSiblings($appointment);
        } else {
            //cancel just the one here
            if ($this->processCancel($appointment)) {
                return ['message' => __('Appointment cancelled', 'wappointment'), 'failures' => $failures];
            } else {
                throw new \WappointmentException(__('Error deleting appointment', 'wappointment'), 1);
            }
        }

        return ['message' => __('Appointment cancelled', 'wappointment'), 'failures' => $failures];
    }

    public function forceDelete(Request $request)
    {
        $appointment = $this->canEditAppointment($request->input('id'));
        if ($this->processCancel($appointment)) {
            return ['message' => __('Appointment cancelled', 'wappointment')];
        } else {
            throw new \WappointmentException(__('Error deleting appointment', 'wappointment'), 1);
        }

        return ['message' => __('Appointment cancelled', 'wappointment'), 'failures' => $failures];
    }

    public function deleteSiblings(Appointment $appointment)
    {
        //cancel all related
        $appointments = Appointment::where('parent', $appointment->parent > 0 ? $appointment->parent : $appointment->id)
            ->get();
        $failures = [];
        if ($appointment->parent > 0) {
            $parent = Appointment::find($appointment->parent);
        } else {
            $parent = $appointment;
        }
        foreach ($appointments as $childAppointment) {
            $result = $this->processCancel($childAppointment);
            if ($result !== true) {
                $failures[] = $result;
            }
        }

        $result = $this->processCancel($parent);
        if ($result !== true) {
            $failures[] = $result;
        }
        return $failures;
    }

    protected function processCancel($appointment)
    {
        try {
            AppointmentNew::cancel($appointment, null, true);
        } catch (\Throwable $th) {
            $staff = CalendarsBack::findById($appointment->staff_id);
            return 'Couldn\'t delete the session starting at ' . $appointment->start_at->setTimezone($staff['timezone'])->format('Y-m-d\TH:i:00');
        }
        return true;
    }

    public function recordDotcom(Request $request)
    {
        $appointment = $this->getAppointmentModel()::with('client')->where('id', (int)$request->input('id'))->first();
        $staff_id = empty($appointment->staff_id) ? Settings::get('activeStaffId') : (int)$appointment->staff_id;
        $dotcomapi = new DotCom();
        $dotcomapi->setStaff($staff_id);
        if (empty($appointment->options['providers']) && $dotcomapi->isConnected()) {
            $dotcomapi->create($appointment);

            $options = $appointment->options;
            $options['providers'] = [];
            $appointment->options = $options;
            $appointment->save();
            return ['message' => 'Appointment has been sent'];
        }
        throw new \WappointmentException('Appointment cannot be sent', 1);
    }

    public function put(Request $request)
    {
        $this->canEditAppointment($request->input('id'));

        if (AppointmentNew::confirm($request->input('id'))) {
            return ['message' => __('Appointment confirmed', 'wappointment')];
        } else {
            throw new \WappointmentException(__('Error confirming appointment', 'wappointment'), 1);
        }
    }

    public function patch(Request $request)
    {
        $this->canEditAppointment($request->input('id'));
        if (AppointmentNew::patch(
            (int)$request->input('id'),
            [
                'start_at' => DateTime::convertUnixTS($request->input('start')),
                'end_at' => DateTime::convertUnixTS($request->input('end'))
            ]
        )) {
            return ['message' => Translations::get('element_updated')];
        } else {
            throw new \WappointmentException(Translations::get('error_updating'), 1);
        }
    }

    public function get(Request $request)
    {
        $this->parsePreferences($request);
        $eventsCalendar = new EventsCalendar($request);
        return $eventsCalendar->get();
    }

    public function parsePreferences(Request $request)
    {
        $pref_save = [];
        $prob_pref = ['cal-duration', 'cal-minH', 'cal-maxH', 'cal-avail-col', 'cal-appoint-col'];
        foreach ($prob_pref as $pref_key) {
            if (!empty($request->header($pref_key)) && $request->header($pref_key) !== 'null') {
                $pref_save[str_replace('-', '_', $pref_key)] = $request->header($pref_key);
            }
        }

        if (!empty($pref_save)) {
            //we save the duration preference
            (new Preferences())->saveMany($pref_save);
        }
    }
}
