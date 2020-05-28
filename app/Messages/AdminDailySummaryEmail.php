<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;
use Wappointment\Services\Sections;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Service;

class AdminDailySummaryEmail extends AbstractAdminEmail
{
    use AttachesIcs;

    protected $sections = null;
    protected $date_string = '';
    protected $tz = '';

    public function loadContent()
    {
        $this->loadTomorrowData();
        $this->date_string = $this->tomorrowCarbon()->format(Settings::get('date_format'));
        $this->tz = Settings::getStaff('timezone');

        $this->subject = 'Daily summary for ' . $this->date_string;
        $this->addLogo();
        $this->addBr();

        $serviceDurationInSeconds = Service::get()['duration'] * 60;
        $coverage = $this->sections->getCoverage($serviceDurationInSeconds);

        $staff = new \Wappointment\WP\Staff;
        $lines = [
            'Hi ' . $staff->getFirstName() . ', ',
            'Here is a summary of your appointments for ' . $this->date_string
        ];

        if (!empty($coverage)) {
            $newlines = [
                'New Appointments: ' . count($this->sections->appointments),
                'Available Slots: ' . $this->sections->getFreeSlots($serviceDurationInSeconds) . ' (duration ' . Service::get()['duration'] . 'min)',
                'Coverage: ' . $coverage
            ];
        } else {
            $newlines = [
                'No availabilities for ' . $this->date_string
            ];
        }

        $this->addLines(array_merge($lines, $newlines));

        if ($this->sections->getFreeSlots($serviceDurationInSeconds) == 0) {
            $this->addButton(
                'Open new slots',
                WPHelpers::adminUrl('wappointment_calendar'),
                false
            );
        }

        $this->getAppointmentsList();

        $this->addLines([
            'Have a great day!',
            '',
            'Ps: An .ics file with all your appointments is attached'
        ]);

        $this->attachIcs($this->sections->appointments, 'daily_appointments', true);
    }

    public function getAppointmentsList()
    {
        $appointmentSumarry = [];

        foreach ($this->sections->appointments as $appointment) {
            $appointmentSumarry[] = '<hr/>' .
                $appointment->start_at->setTimezone($this->tz)->format(Settings::get('time_format')) .
                ' ' . $appointment->client->name . ' / '
                . $appointment->getDuration() . '<br>' . $appointment->client->email;
        }

        if (!empty($appointmentSumarry)) {
            array_unshift($appointmentSumarry, '<strong>' . $this->date_string . '</strong>');
            $this->addRoundedSquare($appointmentSumarry);
        }
    }

    protected function tomorrowCarbon()
    {
        return \Wappointment\ClassConnect\Carbon::tomorrow(Settings::getStaff('timezone'));
    }

    private function loadTomorrowData()
    {
        $this->sections = new Sections($this->tomorrowCarbon()->timestamp, $this->tomorrowCarbon()->timestamp + 86400);
    }
}
