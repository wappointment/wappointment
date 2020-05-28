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

    public function event(Appointment $appointment, Client $client, $extra = [])
    {
        $staff = $appointment->getStaff();
        $calparams = [
            'ORGANIZER' => ['name' => $staff->getUserDisplayName(), 'email' =>  $staff->emailAddress()],
            'ATTENDEE' => ['name' => $client->name, 'email' =>  $client->email],
        ];

        $this->generateEvent($appointment, $client, $staff, array_merge($calparams, $extra));
    }

    public function cancelled(Appointment $appointment, Client $client)
    {
        $this->event($appointment, $client, ['STATUS' => 'CANCELLED']);
    }

    public function summary($appointments, $cancelled = false)
    {
        foreach ($appointments as $appointment) {
            if ($appointment instanceof Appointment) {
                if ($cancelled) {
                    $this->cancelled($appointment, $appointment->client);
                } else {
                    $this->event($appointment, $appointment->client);
                }
            }
        }
    }

    protected function generateEvent(Appointment $appointment, Client $client, $staff, $extra = [])
    {
        $title = $this->getTitle($appointment, $staff);

        $category = 'APPOINTMENT';
        $event_data = [
            'UID' => 'wappointment-' . md5($this->getProdId() . $appointment->id) . '@' . $this->getProdId(),
            'CATEGORIES' => $category,
            'LOCATION' => $this->getLocation($appointment),
            'SUMMARY' => $title,
            'DTSTART' => $appointment->start_at,
            'DTEND' =>  $appointment->end_at->format($this->ics_date),
            'DESCRIPTION' => $this->getDescription($appointment),
            'STATUS' => $appointment->isConfirmed() ? 'CONFIRMED' : 'TENTATIVE',
            'DTSTAMP' => $appointment->created_at->format($this->ics_date),
            'CREATED' => $appointment->created_at->format($this->ics_date),
            'LAST-MODIFIED' => $appointment->updated_at->format($this->ics_date),
            'TRANSP' => 'OPAQUE',
            'SEQUENCE' => $appointment->getSequence()
        ];

        $vevent = $this->vcalendar->add('VEVENT', $event_data);

        $vevent = $this->addExtra($vevent, $extra);

        $vevent->add('VALARM', [
            'ACTION' => 'DISPLAY',
            'DESCRIPTION' => $title,
            'TRIGGER' => '-PT1H',
        ]);
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

        $canCanCelOrRescheduleOrBoth = Settings::get('allow_rescheduling') ? true : (Settings::get('allow_cancellation') ? true : false);

        if ($canCanCelOrRescheduleOrBoth) {
            $description .= "\n\nNeed to modify this event?\n\n";
            $description .= Settings::get('allow_cancellation') ? "Cancel (until " . $appointment->cancelLimit() . ") : \n" . $appointment->getLinkCancelEvent() . "\n\n" : '';
            $description .= Settings::get('allow_rescheduling') ? "Reschedule (until " . $appointment->rescheduleLimit() . ") : \n" . $appointment->getLinkRescheduleEvent() . "\n\n" : '';
        }
        $description .= "\n-----------------------------------";
        $description .= "\nPowered by https://wappointment.com";

        return $description;
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
