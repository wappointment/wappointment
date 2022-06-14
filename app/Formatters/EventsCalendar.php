<?php

namespace Wappointment\Formatters;

use Wappointment\Managers\Central;
use Wappointment\Services\Status;
use Wappointment\Models\Status as Mstatus;
use Wappointment\Services\Settings;
use Wappointment\Formatters\CustomFields;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Services\DateTime;
use Wappointment\Services\VersionDB;
use Wappointment\Services\Calendars;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\AppointmentNew;
use Wappointment\Services\Payment;

class EventsCalendar
{
    private $isLegacy = false;
    private $timezone = '';
    private $customFieldsKeyLabel = [];
    private $customFieldsKeyValues = [];
    private $timeFormat = '';
    private $ends_at_carbon = null;
    private $start_at_string = '';
    private $end_at_string = '';
    private $request = null;

    public function __construct($request)
    {
        $this->isLegacy = VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);
        $this->request = $request;
        $this->timezone = $this->request->input('timezone');
        $this->customFieldsKeyLabel = CustomFields::keyLabels();
        $this->customFieldsKeyValues = CustomFields::keyValues();
        $this->timeFormat = Settings::get('time_format');
        $this->ends_at_carbon = DateTime::timeZToUtc($this->request->input('end'))->setTimezone('UTC');
        $this->start_at_string = DateTime::timeZToUtc($this->request->input('start'))->setTimezone('UTC')->format(WAPPOINTMENT_DB_FORMAT);
        $this->end_at_string = $this->ends_at_carbon->format(WAPPOINTMENT_DB_FORMAT);
    }

    public function get()
    {
        if ((bool) $this->request->input('viewingFreeSlot')) {
            return $this->debugAvailability();
        } else {
            $data = [];
            $staff_id = null;
            if (!$this->isLegacy) {
                $staff_id = $this->request->input('staff_id');
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
                $this->events($staff_id),
                $this->statusBusyFree($staff_id),
                $this->regavToBgEvent($regav, $regavTimezone)
            );

            return $data;
        }
    }


    private function prepareClient($client)
    {
        if (isset($client->email)) {
            $client->avatar = get_avatar_url($client->email, ['size' => 30]);
        }
        return $client;
    }


    private function getAppointments($staff_id = null)
    {
        $appointmentModel = Central::get('AppointmentModel');
        $appointmentsQuery = $appointmentModel::with(['client' => function ($q) {
            $q->withTrashed();
        }])
            ->where('status', '>=', $appointmentModel::STATUS_AWAITING_CONFIRMATION)
            ->where('start_at', '>=', $this->start_at_string)
            ->where('end_at', '<=', $this->end_at_string);

        if (!$this->isLegacy) {
            $appointmentsQuery->where('staff_id', (int)$staff_id);
        }

        return  $appointmentsQuery->get();
    }


    protected function formatAppointmentTime($carbonTime, $format = 'Y-m-d\TH:i:00')
    {
        return $carbonTime->setTimezone($this->timezone)->format($format);
    }

    protected function formatEvent($event)
    {
        $owes = !empty($event->client->options['owes']) ? $event->client->options['owes'] : 0;
        $preparedClient = $this->prepareClient($event->client);
        $nameService = isset($event->service->name) ? $event->service->name : 'Undefined service';
        return [
            'start' => $this->formatAppointmentTime($event->start_at),
            'end' => $this->formatAppointmentTime($event->end_at),
            'id' => $event->id,
            'delId' => $event->id,
            'location' => $event->getLocationSlug(),
            'status' => $event->status,
            'options' => $this->formatEventOptions($event),
            'client' => $preparedClient,
            'type' => 'appointment',
            'recurrent' => isset($event->recurrent) && $event->recurrent > 0,
            'onlyDelete' => true,
            'rendering' => (bool) $event->status ? 'appointment-confirmed' : 'appointment-pending',
            'className' => apply_filters('wappointment_calendar_appointment_class', $this->baseClassAppointment($owes, $event), $event),
            'owes' => $owes,
            'display' => [
                'short' => [
                    'title' => !empty($preparedClient) ? $preparedClient->name : __('Unknown client', 'wappointment'),
                    /* translators: %1$s is service name, %2$s is the duration  */
                    'service' => sprintf(__('%1$s - %2$smin', 'wappointment'), $nameService, $event->getDurationInSec() / 60),
                    'time' => $this->formatAppointmentTime($event->start_at, $this->timeFormat)
                        . ' - ' .
                        $this->formatAppointmentTime($event->getEndTimeWithoutBuffer(), $this->timeFormat),
                    'location' => $event->location->name
                ],
                'long' => $this->getClientOptions($preparedClient)
            ]
        ];
    }

    private function formatEventOptions($event)
    {
        $options = $event->options;
        if ($event->isJitsi()) {
            $options['jitsi_url'] = $event->getMeetingLink();
        }
        return $options;
    }

    public function baseClassAppointment($owes, $event)
    {
        return ($owes > 0 ? 'appointment-owes ' : '') . ((bool) $event->status ? 'appointment-confirmed' : 'appointment-pending');
    }

    protected function getClientOptions($preparedClient)
    {
        $data = [];
        if (!empty($preparedClient)) {
            /* translators: %s - email address. */
            $data[] = sprintf(__('Email: %s', 'wappointment'), $preparedClient->email);
            foreach ($preparedClient->options as $keyOption => $optionValue) {

                if ($keyOption == 'appointment_key' || ($keyOption == 'rtl' && $optionValue === false) || !isset($this->customFieldsKeyLabel[$keyOption])) {
                    continue;
                }
                /* translators: %1$s is label %2$s is value */
                $data[] = sprintf(__('%1$s: %2$s', 'wappointment'), $this->getLabelFromKey($keyOption), $this->getLabelFromValues($keyOption, $optionValue));
            }
        }

        return $data;
    }

    public function getLabelFromKey($keyOption)
    {
        switch ($keyOption) {
            case 'tz':
                return __('Timezone', 'wappointment');
            case 'owes':
                return __('Owes', 'wappointment');
            case 'rtl':
                return 'Right to left';
            default:
                $label = isset($this->customFieldsKeyLabel[$keyOption]) ? $this->customFieldsKeyLabel[$keyOption] : $keyOption;
                return strpos($label, ':') !== false ? str_replace(':', '', $label) : $label;
        }
    }

    public function getLabelFromValues($keyOption, $optionValues)
    {
        if ($keyOption == 'owes' && is_numeric($optionValues)) {
            return Payment::formatPrice($optionValues / 100);
        }

        $valuesLabelsDefinition = isset($this->customFieldsKeyValues[$keyOption]) ? $this->customFieldsKeyValues[$keyOption] : $optionValues;
        if (is_array($valuesLabelsDefinition)) {
            $valuesForHumans = [];
            $optionValues = !is_array($optionValues) ? [$optionValues] : $optionValues;
            foreach ($optionValues as $valueKey) {
                foreach ($valuesLabelsDefinition as $valueLabelDefined) {
                    if (isset($valueLabelDefined['value']) && $valueLabelDefined['value'] == $valueKey) {
                        $valuesForHumans[] = $valueLabelDefined['label'];
                    }
                }
            }
            return implode(',', $valuesForHumans);
        } else {
            return $optionValues;
        }
    }

    private function events($staff_id = null)
    {
        $events = [];
        $appointments = AppointmentNew::adminCalendarGetAppointments([
            'start_at' => $this->start_at_string,
            'end_at' => $this->end_at_string,
            'staff_id' => $staff_id
        ], $this->isLegacy);
        if (empty($appointments)) {
            $appointments = $this->getAppointments($staff_id);
        }

        foreach ($appointments as $event) {
            $addedEvent = $this->formatEvent($event);

            if ($this->isLegacy) {
                $events[] = $addedEvent;
            } else {
                $events[] = AppointmentNew::adminCalendarUpdateAppointmentArray($addedEvent, $event);
            }
        }

        $events = apply_filters('wappointment_admin_calendar_event', $events, $appointments);

        return $events;
    }

    protected function statusBusyFree($staff_id = null)
    {
        $events = [];
        $statusEventsQuery = Mstatus::where('start_at', '<', $this->end_at_string)
            ->where('end_at', '>', $this->start_at_string)
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

        $punctualEvent = Status::expand($recurringBusy, $maxts < $this->ends_at_carbon->timestamp ? $maxts : $this->ends_at_carbon->timestamp);

        $statusEvents = $statusEvents->concat($punctualEvent);
        foreach ($statusEvents as $event) {
            $events[] = $this->generateStatusEvent($event);
        }
        return $events;
    }

    protected function generateStatusEvent($event)
    {
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
                if ($this->request->input('view') == 'month') {
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
        return $addedEvent;
    }

    private function regavToBgEvent($regav, $regavTimezone)
    {
        $bg_events = [];
        $startDate = new Carbon($this->request->input('start'), $this->timezone);

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

    private function TESTprocessAvail($avails)
    {
        foreach ($avails as &$avail) {
            $avail[0] = Carbon::createFromTimestamp($avail[0]);
            $avail[1] = Carbon::createFromTimestamp($avail[1]);
        }
        return $avails;
    }

    private function debugAvailability()
    {
        if (VersionDB::canServices()) {
            $staffs = Calendars::all();
            $availability = $staffs->firstWhere('id', $this->request->input('staff_id'))->availability;
        } else {
            $availability = WPHelpers::getStaffOption('availability');
        }

        $times = $this->TESTprocessAvail($availability);
        $bg_events = [];
        foreach ($times as $timeslot) {
            $bg_events[] = $this->setBgEvent($timeslot[0], $timeslot[1], 'debugging');
        }

        return [
            'availability' => $availability,
            'events' => $bg_events,
            'now' => (new Carbon())->setTimezone($this->timezone)->format('Y-m-d\TH:i:00')
        ];
    }
}
