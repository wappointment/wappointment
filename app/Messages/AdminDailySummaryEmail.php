<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;
use Wappointment\Services\Sections;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Service;

class AdminDailySummaryEmail extends AbstractAdminEmail
{
    private $sections = null;

    public function loadEmail()
    {
        $this->loadTomorrowData();
        $date_string = $this->tomorrowCarbon()->format(Settings::get('date_format'));
        $this->subject = 'Daily summary for ' . $date_string;
        $this->addLogo();
        $this->addBr();
        $tz = Settings::getStaff('timezone');
        $serviceDurationInSeconds = Service::get()['duration'] * 60;
        $coverage = $this->sections->getCoverage($serviceDurationInSeconds);

        if (!empty($coverage)) {
            $contentBlock = [
                '<center>' . 'Appointments: ' . count($this->sections->appointments) . '</center>',
                '<center>' . 'Free slots: ' . $this->sections->getFreeSlots($serviceDurationInSeconds) . ' (duration ' . Service::get()['duration'] . 'min)</center>',
                '<center>' . 'Coverage: ' . $coverage . '</center>'
            ];

            $this->addRoundedSquare($contentBlock, false);
            if ($this->sections->getFreeSlots($serviceDurationInSeconds) == 0) {
                $this->addButton(
                    'Set new availabilities for tomorrow',
                    WPHelpers::adminUrl('wappointment_calendar')
                );
            }
        } else {
            $this->addRoundedSquare([
                '<center>No availabilities for ' . $date_string . '</center>'
            ], false);
            $this->addButton(
                'Set availabilities for tomorrow',
                WPHelpers::adminUrl('wappointment_calendar')
            );
        }

        $appointmentSumarry = [];

        foreach ($this->sections->appointments as $appointment) {
            $appointmentSumarry[] = '<hr/>' . $appointment->start_at->setTimezone($tz)->format(Settings::get('time_format')) .
                ' ' . $appointment->client->name . ' / ' . $appointment->getDuration() . '<br>' . $appointment->client->email;
        }
        if (!empty($appointmentSumarry)) {
            array_unshift($appointmentSumarry, '<strong>' . $date_string . '</strong>');
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
