<?php

namespace Wappointment\Controllers;


use Wappointment\ClassConnect\Request;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Services\Settings;
use Wappointment\Services\DateTime;
use Wappointment\Services\Status;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Models\Status as Mstatus;
use Wappointment\Models\Appointment as AppointmentModel;
use Wappointment\Services\Appointment;



class EventsController extends RestController
{
    private $timezone = '';

    private function TESTprocessAvail($avails)
    {
        foreach ($avails as &$avail) {
            $avail[0] = Carbon::createFromTimestamp($avail[0])->setTimezone($this->timezone)->format('Y-m-d\TH:i:00 T');
            $avail[1] = Carbon::createFromTimestamp($avail[1])->setTimezone($this->timezone)->format('Y-m-d\TH:i:00 T');
        }
        return $avails;
    }

    public function get(Request $request)
    {
        $this->timezone = $request->input('timezone');

        return [
            'events' => array_merge($this->events($request), $this->regavToBgEvent($request)),
            'availability' => WPHelpers::getStaffOption('availability'), //$this->TESTprocessAvail(Settings::getStaff('availability')),
            'now' => (new Carbon())->setTimezone($this->timezone)->format('Y-m-d\TH:i:00')
        ];
    }

    public function delete(Request $request)
    {
        $appointment = AppointmentModel::where('id', $request->input('id'))->first();
        if (Appointment::cancel($appointment)) {
            return ['message' => 'Appointment cancelled'];
        }
        throw new \WappointmentException('Error deleting appointment', 1);
    }


    public function put(Request $request)
    {
        if (Appointment::confirm($request->input('id'))) {
            return ['message' => 'Appointment confirmed'];
        } else {
            throw new \WappointmentException('Appointment couldn\'t be confirmed', 1);
        }
    }

    public function patch(Request $request)
    {
        $this->timezone = $request->input('timezone');

        if (Appointment::patch(
            $request->input('id'),
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

    private function getAppointments($start_at_string, $end_at_string)
    {
        return AppointmentModel::with('client')
            ->where('status', '>=', AppointmentModel::STATUS_AWAITING_CONFIRMATION)
            ->where('start_at', '>=', $start_at_string)
            ->where('end_at', '<=', $end_at_string)
            ->get();
    }
    private function events(Request $request)
    {
        $ends_at_carbon = DateTime::TimeZToUtc($request->input('end'));
        $start_at_string = DateTime::TimeZToUtc($request->input('start'))->format(WAPPOINTMENT_DB_FORMAT);
        $end_at_string = $ends_at_carbon->format(WAPPOINTMENT_DB_FORMAT);
        $events = [];

        $appointments = apply_filters('wappointment_calendar_events_query', [], ['start_at' => $start_at_string, 'end_at' => $end_at_string]);

        if (empty($appointments)) {
            $appointments = $this->getAppointments($start_at_string, $end_at_string);
        }

        foreach ($appointments as $event) {

            $addedEvent = [
                'start' => $event->start_at->setTimezone($this->timezone)->format('Y-m-d\TH:i:00'),
                'end' => $event->end_at->setTimezone($this->timezone)->format('Y-m-d\TH:i:00'),
                'id' => $event->id,
                'delId' => $event->id,
                'location' => $event->getLocationSlug(),
                'status' => $event->status,
                'client' => $this->prepareClient($event->client),
                'type' => 'appointment',
                'onlyDelete' => true,
                'rendering' => (bool) $event->status ? 'appointment-confirmed' : 'appointment-pending',
                'className' => (bool) $event->status ? 'appointment-confirmed' : 'appointment-pending'
            ];

            $events[] = apply_filters('wappointment_appointment_get_filter', $addedEvent, $event);
        }

        $statusEvents = Mstatus::where('start_at', '<', $end_at_string)
            ->where('end_at', '>', $start_at_string)
            ->where('muted', '<', 1)
            ->get();

        $recurringBusy = Mstatus::where('recur', '>', Mstatus::RECUR_NOT)
            ->where('muted', '<', 1)
            ->get();
        /* dd($recurringBusy->toArray()); */
        $sixtydaysinsecs = 60 * 24 * 3600;
        $punctualEvent = Status::expand($recurringBusy, $ends_at_carbon->timestamp + $sixtydaysinsecs);

        $statusEvents = $statusEvents->concat($punctualEvent);
        //dd($statusEvents);
        foreach ($statusEvents as $event) {
            $addedEvent = [
                'start' => $event->start_at->setTimezone($this->timezone)->format('Y-m-d\TH:i:00'),
                'end' => $event->end_at->setTimezone($this->timezone)->format('Y-m-d\TH:i:00'),
                'id' => $event->recur > MStatus::RECUR_NOT ? time() : $event->id,
                'delId' => $event->id,
                'recur' => $event->recur,
                'source' => empty($event->source) ? '' : $event->source,
                'onlyDelete' => true,
                'rendering' => 'background'
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
                        $addedEvent['options'] = $event->options;
                    } else {
                        $addedEvent['className'] = 'calendar';
                        $addedEvent['type'] = 'calendar';
                        $addedEvent['options'] = $event->options;
                    }
                }
            }
            $events[] = $addedEvent;
        }

        return $events;
    }

    private function regavToBgEvent(Request $request)
    {
        $bg_events = [];
        $this->regav = Settings::getStaff('regav');
        $regavTimezone = Settings::getStaff('timezone');
        $startDate = new Carbon($request->input('start'), $this->timezone);

        $daysOfTheWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        while (count($daysOfTheWeek) > 0) {
            $dayName = $daysOfTheWeek[$startDate->dayOfWeek];

            foreach ($this->regav[$dayName] as $dayTimeblock) {
                $start = (new Carbon($startDate->format(WAPPOINTMENT_DB_FORMAT . ':00'), $regavTimezone))->hour($dayTimeblock[0]);
                $end = (new Carbon($startDate->format(WAPPOINTMENT_DB_FORMAT . ':00'), $regavTimezone))->hour($dayTimeblock[1]);

                $bg_events[] = [
                    'start' => $start->setTimezone($this->timezone)->format('Y-m-d\TH:i:00'),
                    'end' => $end->setTimezone($this->timezone)->format('Y-m-d\TH:i:00'),
                    'rendering' => 'background',
                    'className' => 'opening',
                    'type' => 'ra'
                ];
            }

            unset($daysOfTheWeek[$startDate->dayOfWeek]);
            $startDate->addDay(1);
        }
        return $bg_events;
    }
}
