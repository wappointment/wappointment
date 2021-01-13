<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\VCalendar;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Models\Client;
use Wappointment\Models\Appointment;

class IcsGenerator
{

    public $ics_date = 'Ymd\THis\Z';
    protected $vcalendar = null;
    protected $admin = false;

    public function __construct($admin = false)
    {
        $this->admin = $admin;
        $this->vcalendar = new VCalendar([
            'PRODID' => '-//' . $this->getProdId() . '/Appointments 1.0//EN',
            'METHOD' => 'PUBLISH'
        ]);
    }

    protected function getProdId()
    {
        return str_replace(['http://', 'https://'], '', get_site_url());
    }

    public function generate()
    {
        return $this->vcalendar->serialize();
    }

    public function event(Appointment $appointment, Client $client, $mergeparams = [])
    {
        $staff = $appointment->getStaff();
        $addparams = [
            'ORGANIZER' => ['name' => $staff->getUserDisplayName(), 'email' =>  $staff->emailAddress()],
            'ATTENDEE' => ['name' => $client->name, 'email' =>  $client->email],
        ];

        $this->generateEvent($appointment, $client, $staff, $addparams, $mergeparams);
    }

    public function cancelled(Appointment $appointment, Client $client)
    {
        $this->event($appointment, $client, ['STATUS' => 'CANCELLED']);
    }

    public function summary($appointments, $cancelled = false)
    {
        foreach ($appointments as $appointment) {
            $appointment = $this->fillClient($appointment);

            if ($appointment instanceof Appointment && $appointment->client instanceof Client) { //ignore mssing data
                if ($cancelled) {
                    $this->cancelled($appointment, $appointment->client);
                } else {
                    $this->event($appointment, $appointment->client);
                }
            }
        }
    }

    public function fillClient($appointment)
    {
        if (is_array($appointment->client)) {
            $clientObject = new Client;
            $clientObject->fill($appointment->client);
            $appointment->client = $clientObject;
        }
        return $appointment;
    }

    protected function generateEvent(Appointment $appointment, Client $client, $staff, $addparams = [], $mergeparams = [])
    {
        $title = $this->getTitle($appointment, $staff);

        $category = 'APPOINTMENT';
        $event_data = [
            'UID' => 'wappointment-' . md5($this->getProdId() . $appointment->id) . '@' . $this->getProdId(),
            'CATEGORIES' => $category,
            'LOCATION' => $this->getLocation($appointment),
            'SUMMARY' => $title,
            'DTSTART' => $this->getFormattedDate($appointment->start_at),
            'DTEND' =>  $this->getFormattedDate($appointment->end_at),
            'DESCRIPTION' => $this->getDescription($appointment),
            'STATUS' => $appointment->isConfirmed() ? 'CONFIRMED' : 'TENTATIVE',
            'DTSTAMP' => $this->getFormattedDate($appointment->created_at),
            'CREATED' => $this->getFormattedDate($appointment->created_at),
            'LAST-MODIFIED' => $this->getFormattedDate($appointment->updated_at),
            'TRANSP' => 'OPAQUE',
            'SEQUENCE' => $appointment->getSequence()
        ];

        $vevent = $this->vcalendar->add('VEVENT', array_merge($event_data, $mergeparams));

        $vevent = $this->addExtra($vevent, $addparams);

        $vevent->add('VALARM', [
            'ACTION' => 'DISPLAY',
            'DESCRIPTION' => $title,
            'TRIGGER' => '-PT1H',
        ]);
    }

    protected function getFormattedDate($date)
    {
        return $this->getCarbonDate($date)->format($this->ics_date);
    }

    protected function getCarbonDate($date)
    {
        return $date instanceof \Wappointment\ClassConnect\Carbon ? $date : new Carbon($date);
    }

    protected function addExtra($vevent, $extras = [])
    {
        foreach ($extras as $key => $value) {
            if (in_array($key, ['ORGANIZER', 'ATTENDEE'])) {
                $vevent->add(
                    $key,
                    'mailto:' . $value['email'],
                    [
                        'CN' => $value['name'],
                    ]
                );
            } else {
                $vevent->add($key, $value);
            }
        }
        return $vevent;
    }

    protected function getLocation(Appointment $appointment)
    {
        return $appointment->getLocation();
    }

    protected function getTitle(Appointment $appointment, $staff)
    {
        $title = $appointment->getServiceName() . ' ';
        $title .= $this->admin ? $appointment->client->name . '(' . $appointment->client->email . ')' :  $staff->getUserDisplayName();

        return $title;
    }

    protected function getDescription(Appointment $appointment)
    {
        $description = '';
        foreach (['name', 'tz', 'email', 'phone', 'skype'] as $key) {
            if (!empty($appointment->client->options[$key])) {
                $description .= "\n" . $key . ' : ';
                $description .= $appointment->client->options[$key];
            }
        }

        if ($appointment->isZoom()) {
            $description .= "\n\nAppointment is a Video meeting";
            $description .= "\nMeeting will be accessible from the link below " .
                "\n " . $appointment->getLinkViewEvent();
        }

        $description = apply_filters('wappointment_ics_description', $description, $appointment);

        $canCanCelOrRescheduleOrBoth = Settings::get('allow_rescheduling') ? true : (Settings::get('allow_cancellation') ? true : false);

        if ($canCanCelOrRescheduleOrBoth) {
            $description .= "\n\nNeed to modify this event?\n\n";
            $description .= Settings::get('allow_cancellation') ? "Cancel (until " . $appointment->cancelLimit() . ") : \n" . $appointment->getLinkCancelEvent() . "\n\n" : '';
            $description .= Settings::get('allow_rescheduling') ? "Reschedule (until " . $appointment->rescheduleLimit() . ") : \n" . $appointment->getLinkRescheduleEvent() . "\n\n" : '';
        }
        $description .= "\n-----------------------------------";

        return $description . "\nBooked with https://wappointment.com";
    }

    protected function appointments($staff_id, $start = false, $end = false)
    {
        $multiple_staff = false;
        $query = (new Appointment)
            ->where('start_at', '>=', Carbon::today()->subWeeks(2)->toDateString())
            ->orderBy('start_at')
            ->with(['client']);

        if ($multiple_staff) {
            $query->where('staff_id', $staff_id);
        }
        return $query->get();
    }
}
