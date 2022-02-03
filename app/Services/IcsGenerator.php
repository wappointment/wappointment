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
            'ORGANIZER' => ['name' => $staff->name, 'email' =>  $staff->emailAddress()],
            'ATTENDEE' => ['name' => $client->name, 'email' =>  $client->email],
        ];

        if ($this->admin) {
            $addparams = apply_filters('wappointment_ics_organizer', $addparams);
        }

        $this->generateEvent($appointment, $client, $staff, $addparams, $mergeparams);
    }

    public function cancelled(Appointment $appointment, Client $client)
    {
        $this->event($appointment, $client, ['STATUS' => 'CANCELLED']);
    }

    public function summary($appointments, $cancelled = false, $client = null)
    {
        foreach ($appointments as $appointment) {
            $appointment = $this->fillClient($appointment, $client);
            if ($appointment instanceof Appointment && $appointment->getClientModel() instanceof Client) { //ignore mssing data
                if ($cancelled) {
                    $this->cancelled($appointment, $appointment->getClientModel());
                } else {
                    $this->event($appointment, $appointment->getClientModel());
                }
            }
        }
    }

    public function fillClient($appointment, $client)
    {
        if (!is_null($client)) { // valid for group events
            $appointment->client = $client;
        } else {
            if (is_array($appointment->client)) {
                $clientObject = new Client;
                $clientObject->fill($appointment->client);
                $appointment->client = $clientObject;
            }
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
            'DTEND' =>  $this->getFormattedDate($this->getDateEndMinusBuffer($appointment)),
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

    protected function getDateEndMinusBuffer($appointment)
    {
        return $appointment->end_at->subSeconds($appointment->getBufferInSec());
        //return Carbon::createFromTimestamp($appointment->end_at->timestamp - $appointment->getBufferInSec());
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
        $title .= $this->admin ? $appointment->getClientModel()->name . '(' . $appointment->getClientModel()->email . ')' :  $staff->name;

        return esc_html($title);
    }

    protected function getDescription(Appointment $appointment)
    {
        $description = '';
        foreach (['name', 'tz', 'email', 'phone', 'skype'] as $key) {
            if (!empty($appointment->getClientModel()->options[$key])) {
                $description .= "\n" . $key . ' : ';
                $description .= esc_html($appointment->getClientModel()->options[$key]);
            }
        }


        $description = apply_filters('wappointment_ics_description', $description, $appointment);

        if ($appointment->isZoom()) {
            $description .= "\n\n" . __('Appointment is a Video meeting', 'wappointment');
            $description .= "\n" . __('Meeting will be accessible from the link below:', 'wappointment') .
                "\n " . $appointment->getLinkViewEvent();
        }

        $canCanCelOrRescheduleOrBoth = Settings::get('allow_rescheduling') ? true : (Settings::get('allow_cancellation') ? true : false);

        if ($canCanCelOrRescheduleOrBoth) {
            $description .= "\n\n" . __('Need to modify this event?', 'wappointment') . "\n\n";
            /* translators: %1$s - date %2$s rescheule link. */
            $description .= Settings::get('allow_rescheduling') ? sprintf(__('Reschedule (until %1$s): &#10; %2$s', 'wappointment') . "\n\n", $appointment->rescheduleLimit(), $appointment->getLinkRescheduleEvent())  : '';
            /* translators: %1$s - date %2$s cancel link. */
            $description .= Settings::get('allow_cancellation') ? sprintf(__('Cancel (until %1$s): &#10; %2$s', 'wappointment') . "\n\n", $appointment->cancelLimit(), $appointment->getLinkCancelEvent()) : '';
        }
        $description .= "\n-----------------------------------";

        return $description . static::getIcsSignature();
    }

    public static function getIcsSignature()
    {
        /* translators: %s is replaced with https://wappointment.com */
        $ics_signature = "\n" . sprintf(__('Booked with %s', 'wappointment'), 'https://wappointment.com');

        return !empty(\Wappointment\WP\Helpers::getOption('site_details')) ? apply_filters('wappointment_ics_signature', $ics_signature) : $ics_signature;
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
