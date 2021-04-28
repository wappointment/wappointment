<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Managers\Central;
use Wappointment\Services\Settings;
use Wappointment\Services\DateTime;
use Wappointment\Services\Status;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Models\Status as Mstatus;
use Wappointment\Services\AppointmentNew as Appointment;
use Wappointment\Services\Preferences;
use Wappointment\Services\Wappointment\DotCom;
use Wappointment\Services\VersionDB;
use Wappointment\Services\Calendars;
use Wappointment\Services\CurrentUser;

class EventsController extends RestController
{
    private $timezone = '';
    private $isLegacy = false;

    private function TESTprocessAvail($avails)
    {
        foreach ($avails as &$avail) {
            $avail[0] = Carbon::createFromTimestamp($avail[0]);
            $avail[1] = Carbon::createFromTimestamp($avail[1]);
        }
        return $avails;
    }

    public function get(Request $request)
    {
        $this->isLegacy = VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);
        $this->timezone = $request->input('timezone');

        if ((bool) $request->input('viewingFreeSlot')) {
            return $this->debugAvailability($request);
        } else {
            $pref_save = [];
            $prob_pref = ['cal-duration', 'cal-minH', 'cal-maxH', 'cal-avail-col', 'cal-appoint-col'];
            foreach ($prob_pref as $pref_key) {
                if (!empty($request->header($pref_key)) && $request->header($pref_key) !== 'null') {
                    $pref_save[str_replace('-', '_', $pref_key)] = $request->header($pref_key);
                }
            }

            if (!empty($pref_save)) {
                //we save the duration preference
                (new Preferences)->saveMany($pref_save);
            }


            $data = [];
            $staff_id = null;
            if (!$this->isLegacy) {
                $staff_id = $request->input('staff_id');
                $staffs = Calendars::all();
                $activeStaff = $staffs->firstWhere('id', $staff_id);
                $data['now'] = (new Carbon())->setTimezone($this->timezone)->format('Y-m-d\TH:i:00');
                $data['availability'] = $activeStaff->availability;
                $regav = $activeStaff->getRegav();
                $regavTimezone = $activeStaff->getTimezone();
            } else {
                $data['availability'] = WPHelpers::getStaffOption('availability');
                $data['now'] = (new Carbon())->setTimezone($this->timezone)->format('Y-m-d\TH:i:00');
                $regav = Settings::getStaff('regav');
                $regavTimezone = Settings::getStaff('timezone');
            }

            $data['events'] = array_merge(
                $this->events($request, $staff_id),
                $this->regavToBgEvent($request, $regav, $regavTimezone)
            );

            return $data;
        }
    }

    private function debugAvailability(Request $request)
    {
        if (VersionDB::canServices()) {
            $staffs = Calendars::all();
            $availability = $staffs->firstWhere('id', $request->input('staff_id'))->availability;
        } else {
            $availability = WPHelpers::getStaffOption('availability');
        }

        $times = $this->TESTprocessAvail($availability);
        $bg_events = [];
        foreach ($times as $key => $timeslot) {
            $bg_events[] = $this->setBgEvent($timeslot[0], $timeslot[1], 'debugging');
        }

        return [
            'availability' => $availability,
            'events' => $bg_events, //$this->TESTprocessAvail(Settings::getStaff('availability')),
            'now' => (new Carbon())->setTimezone($this->timezone)->format('Y-m-d\TH:i:00')
        ];
    }

    public function delete(Request $request)
    {
        $appointment = $this->canEditAppointment($request->input('id'));

        if (Appointment::cancel($appointment)) {
            return ['message' => 'Appointment cancelled'];
        }
        throw new \WappointmentException('Error deleting appointment', 1);
    }

    public function recordDotcom(Request $request)
    {
        $appointment = $this->getAppointmentModel()::with('client')->where('id', (int)$request->input('id'))->first();
        $staff_id = empty($appointment->staff_id) ? Settings::get('activeStaffId') : (int)$appointment->staff_id;
        $dotcomapi = new DotCom;
        $dotcomapi->setStaff($staff_id);
        if (!empty($appointment->client) && empty($appointment->options['providers']) && $dotcomapi->isConnected()) {
            $dotcomapi->create($appointment);

            $options = $appointment->options;
            $options['providers'] = [];
            $appointment->options = $options;
            $appointment->save();
            return ['message' => 'Appointment has been sent'];
        }
        throw new \WappointmentException('Appointment cannot be sent', 1);
    }
    protected function canEditAppointment($id)
    {
        $appointment = $this->getAppointmentModel()::find((int)$id);
        if (!CurrentUser::isAdmin() && CurrentUser::calendarId() !== (int) $appointment->staff_id) {
            throw new \WappointmentException("Cannot modify an appointment which doesnt belong to you", 1);
        }
        return $appointment;
    }
    public function put(Request $request)
    {
        $this->canEditAppointment($request->input('id'));

        if (Appointment::confirm($request->input('id'))) {
            return ['message' => 'Appointment confirmed'];
        } else {
            throw new \WappointmentException('Appointment couldn\'t be confirmed', 1);
        }
    }

    public function patch(Request $request)
    {
        $this->timezone = $request->input('timezone');
        $this->canEditAppointment($request->input('id'));
        if (Appointment::patch(
            (int)$request->input('id'),
            [
                'start_at' => DateTime::convertUnixTS($request->input('start')),
                'end_at' => DateTime::convertUnixTS($request->input('end'))
            ]
        )) {
            return ['message' => 'Appointment updated'];
        } else {
            throw new \WappointmentException('Appointment couldn\'t be updated', 1);
        }
    }

    private function convertTime($dateString)
    {
        return (new Carbon($dateString))->setTimezone($this->timezone)->toDateTimeString();
    }

    private function prepareClient($client)
    {
        if (isset($client->email)) {
            $client->avatar = get_avatar_url($client->email, ['size' => 30]);
        }
        return $client;
    }

    protected function getAppointmentModel()
    {
        return Central::get('AppointmentModel');
    }

    private function getAppointments($start_at_string, $end_at_string, $staff_id = null)
    {
        $appointmentsQuery = $this->getAppointmentModel()::with(['client' => function ($q) {
            $q->withTrashed();
        }])
            ->where('status', '>=', $this->getAppointmentModel()::STATUS_AWAITING_CONFIRMATION)
            ->where('start_at', '>=', $start_at_string)
            ->where('end_at', '<=', $end_at_string);

        if (!$this->isLegacy) {
            $appointmentsQuery->where('staff_id', (int)$staff_id);
        }

        return  $appointmentsQuery->get();
    }

    private function events(Request $request, $staff_id = null)
    {

        $ends_at_carbon = DateTime::timeZToUtc($request->input('end'))->setTimezone('UTC');
        $start_at_string = DateTime::timeZToUtc($request->input('start'))->setTimezone('UTC')->format(WAPPOINTMENT_DB_FORMAT);
        $end_at_string = $ends_at_carbon->format(WAPPOINTMENT_DB_FORMAT);
        $events = [];
        $appointments = Appointment::adminCalendarGetAppointments([
            'start_at' => $start_at_string,
            'end_at' => $end_at_string,
            'staff_id' => $staff_id
        ], $this->isLegacy);
        // $appointments = apply_filters(
        //     'wappointment_calendar_events_query',
        //     [],
        //     ['start_at' => $start_at_string, 'end_at' => $end_at_string, 'staff_id' => $staff_id]
        // );
        if (empty($appointments)) {
            $appointments = $this->getAppointments($start_at_string, $end_at_string, $staff_id);
        }

        foreach ($appointments as $event) {
            $addedEvent = [
                'start' => $event->start_at->setTimezone($this->timezone)->format('Y-m-d\TH:i:00'),
                'end' => $event->end_at->setTimezone($this->timezone)->format('Y-m-d\TH:i:00'),
                'id' => $event->id,
                'delId' => $event->id,
                'location' => $event->getLocationSlug(),
                'status' => $event->status,
                'options' => $event->options,
                'client' => $this->prepareClient($event->client),
                'type' => 'appointment',
                'onlyDelete' => true,
                'rendering' => (bool) $event->status ? 'appointment-confirmed' : 'appointment-pending',
                'className' => (bool) $event->status ? 'appointment-confirmed' : 'appointment-pending'
            ];
            if ($this->isLegacy) {
                $events[] = $addedEvent;
            } else {
                $events[] = Appointment::adminCalendarUpdateAppointmentArray($addedEvent, $event);
            }
        }

        $statusEventsQuery = Mstatus::where('start_at', '<', $end_at_string)
            ->where('end_at', '>', $start_at_string)
            ->where('muted', '<', 1);

        if (!$this->isLegacy) {
            $statusEventsQuery->where('staff_id', (int)$staff_id);
        }

        $statusEvents = $statusEventsQuery->get();

        $recurringBusyQuery = Mstatus::where('recur', '>', Mstatus::RECUR_NOT)
            ->where('muted', '<', 1);
        if (!$this->isLegacy) {
            $recurringBusyQuery->where('staff_id', (int)$staff_id);
        }

        $recurringBusy = $recurringBusyQuery->get();

        $maxts = (new \Wappointment\Services\Availability($staff_id))->getMaxTs();
        $punctualEvent = Status::expand($recurringBusy, $maxts < $ends_at_carbon->timestamp ? $maxts : $ends_at_carbon->timestamp);

        $statusEvents = $statusEvents->concat($punctualEvent);
        foreach ($statusEvents as $event) {
            $addedEvent = [
                'start' => $event->start_at->setTimezone($this->timezone)->format('Y-m-d\TH:i:00'),
                'end' => $event->end_at->setTimezone($this->timezone)->format('Y-m-d\TH:i:00'),
                'id' => $event->recur > MStatus::RECUR_NOT ? time() : $event->id,
                'delId' => $event->id,
                'recur' => $event->recur,
                'source' => empty($event->source) ? '' : $event->source,
                'onlyDelete' => true,
                'rendering' => 'background',
                'options' =>  $event->options
            ];

            if ($event->type == Mstatus::TYPE_FREE) {
                $addedEvent['className'] = 'opening extra';
                $addedEvent['type'] = 'free';
            } else {
                if (empty($event->source)) {
                    $addedEvent['className'] = 'busy';
                    $addedEvent['type'] = 'busy';
                    if ($request->input('view') == 'month') {
                        $addedEvent['allDay'] = true;
                    }
                } else {
                    if ($event->recur > 0) {
                        $addedEvent['className'] = 'calendar recurrent';
                        $addedEvent['type'] = 'calendar';
                    } else {
                        $addedEvent['className'] = 'calendar';
                        $addedEvent['type'] = 'calendar';
                    }
                }
            }
            $events[] = $addedEvent;
        }

        return $events;
    }

    private function regavToBgEvent(Request $request, $regav, $regavTimezone)
    {
        $bg_events = [];
        $startDate = new Carbon($request->input('start'), $this->timezone);

        $daysOfTheWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        while (!empty($daysOfTheWeek)) {
            $dayName = $daysOfTheWeek[$startDate->dayOfWeek];

            foreach ($regav[$dayName] as $dayTimeblock) {
                $start = (new Carbon($startDate->format(WAPPOINTMENT_DB_FORMAT . ':00'), $regavTimezone));
                $end = (new Carbon($startDate->format(WAPPOINTMENT_DB_FORMAT . ':00'), $regavTimezone));

                $unit_added = !empty($regav['precise']) ? 'addMinutes' : 'addHours'; //detect precision mode
                $start->$unit_added($dayTimeblock[0]);
                $end->$unit_added($dayTimeblock[1]);

                $bg_events[] = $this->setBgEvent($start, $end);
            }

            unset($daysOfTheWeek[$startDate->dayOfWeek]);
            $startDate->addDay(1);
        }
        return $bg_events;
    }

    private function setBgEvent($start, $end, $className = 'opening')
    {
        $format = 'Y-m-d\TH:i:00';
        return [
            'start' => $start->setTimezone($this->timezone)->format($format),
            'end' => $end->setTimezone($this->timezone)->format($format),
            'rendering' => 'background',
            'className' => $className,
            'type' => 'ra'
        ];
    }
}
