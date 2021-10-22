<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;
use Wappointment\Services\Sections;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Service;
use Wappointment\Services\VersionDB;

class AdminDailySummaryEmail extends AbstractAdminEmail
{
    use AttachesIcs;

    protected $sections = null;
    protected $date_string = '';
    protected $tz = '';
    protected $staff = null;

    protected function isLegacy()
    {
        return VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);
    }

    protected function getStaff()
    {
        return $this->isLegacy() ?  new \Wappointment\WP\StaffLegacy : new \Wappointment\WP\Staff($this->params['staff_id']);
    }

    private function loadTomorrowData()
    {
        $this->staff = $this->getStaff();
        $this->tz = $this->staff->timezone;
        $this->date_string = $this->tomorrowCarbon()->format(Settings::get('date_format'));

        /* translators: %s - today's date. */
        $this->subject = sprintf(__('Daily summary for %s', 'wappointment'), $this->date_string);
        $this->sections = new Sections($this->tomorrowCarbon()->timestamp, $this->tomorrowCarbon()->timestamp + 86400, $this->staff, $this->isLegacy());
    }

    public function loadContent()
    {

        $this->loadTomorrowData();

        $this->addLogo();
        $this->addBr();

        $serviceDurationInSeconds = Service::get()['duration'] * 60;
        $coverage = $this->sections->getCoverage($serviceDurationInSeconds);


        $lines = [
            /* translators: %s - client's first name. */
            sprintf(__('Hi %s,', 'wappointment'), $this->staff->getFirstName()),
            /* translators: %s - today's date. */
            sprintf(__('Here is a summary of your appointments for %s', 'wappointment'), $this->date_string)
        ];

        if (!empty($coverage)) {
            $newlines = [
                /* translators: %s - number of appointments. */
                sprintf(__('New Appointments: %s', 'wappointment'), count($this->sections->appointments)),
                /* translators: %1$s - numbers of slots, %2$s slots duration. */
                sprintf(__('Available slots: %1$s (duration %2%s min', 'wappointment'), $this->sections->getFreeSlots($serviceDurationInSeconds), Service::get()['duration']),
                /* translators: %s - percentage. */
                sprintf(__('Coverage: %s', 'wappointment'), $coverage),
            ];
        } else {
            $newlines = [
                /* translators: %s - todays date. */
                sprintf(__('No availabilities for %s', 'wappointment'), $this->date_string),
            ];
        }

        $this->addLines(array_merge($lines, $newlines));

        if ($this->sections->getFreeSlots($serviceDurationInSeconds) == 0) {
            $this->addButton(
                __('Open new slots', 'wappointment'),
                WPHelpers::adminUrl('wappointment_calendar'),
                false
            );
        }

        $this->getAppointmentsList();

        $this->addLines([
            __('Have a great day!', 'wappointment'),
            '',
            __('Ps: An .ics file with all your appointments is attached', 'wappointment')
        ]);

        $this->attachIcs($this->sections->appointments, 'daily_appointments', true);
    }

    public function getAppointmentsList()
    {
        $appointmentSumarry = [];

        foreach ($this->sections->appointments as $appointment) {
            $appointmentSumarry[] = $this->getAppointmentFormatted($appointment);
        }

        if (!empty($appointmentSumarry)) {
            array_unshift($appointmentSumarry, '<strong>' . $this->date_string . '</strong>');
            $this->addRoundedSquare($appointmentSumarry);
        }
    }

    public function getAppointmentFormatted($appointment)
    {
        return '<hr/>' .
            $appointment->start_at->setTimezone($this->tz)->format(Settings::get('time_format')) .
            ' ' . $appointment->client->name . ' / '
            . $appointment->getDuration() . ' - ' . $appointment->getLocation() . '<br>' . $appointment->client->email;
    }

    protected function tomorrowCarbon()
    {
        return \Wappointment\ClassConnect\Carbon::tomorrow($this->tz);
    }
}
