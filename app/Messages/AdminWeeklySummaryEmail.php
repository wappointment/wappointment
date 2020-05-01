<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;
use Wappointment\Services\Sections;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Service;

class AdminWeeklySummaryEmail extends AdminDailySummaryEmail
{
    private $sections = null;

    public function startWeek()
    {
        return $this->tomorrowCarbon();
    }

    public function endWeek()
    {
        return $this->tomorrowCarbon()->addDays(6);
    }

    public function loadContent()
    {
        $this->loadNextWeekData();
        $startingDay = $this->startWeek();
        $endDay = $this->endWeek();
        $date_start_string = $startingDay->toDateString();
        $date_end_string = $endDay->toDateString();
        $this->subject = 'Weekly summary ' . $date_start_string . ' - ' . $date_end_string;
        $this->addLogo();
        $this->addBr();
        $tz = Settings::getStaff('timezone');
        $serviceDurationInSeconds = Service::get()['duration'] * 60;
        $coverage = $this->sections->getCoverage($serviceDurationInSeconds);

        if (!empty($coverage)) {
            $contentBlock = [
                '<center>' . 'Appointments: ' . count($this->sections->appointments) . '</center>',
                '<center>' . 'Free slots: ' .
                    $this->sections->getFreeSlots($serviceDurationInSeconds) .
                    ' (duration ' . Service::get()['duration'] . 'min)</center>',
                '<center>' . 'Coverage: ' . $coverage . '</center>'
            ];

            $this->addRoundedSquare($contentBlock, false);
            if ($this->sections->getFreeSlots($serviceDurationInSeconds) == 0) {
                $this->addButton(
                    'Set new availabilities for next week',
                    WPHelpers::adminUrl('wappointment_calendar')
                );
            }
        } else {
            $this->addRoundedSquare([
                '<center>No availabilities for the week of '
                    . $date_start_string . ' - ' . $date_end_string . '</center>'
            ], false);
            $this->addButton(
                'Set availabilities for next week',
                WPHelpers::adminUrl('wappointment_calendar')
            );
        }

        $appointmentSumarry = [];

        $appointmentGroupedByDay = $this->sections->appointments->mapToGroups(function ($item, $key) use ($tz) {
            return [$item->start_at->setTimezone($tz)->toDateString() => $item];
        });
        while ($startingDay->lessThanOrEqualTo($endDay)) {
            $appointmentSumarry[] = '<strong>' . $startingDay->format('D Y-m-d') . '</strong>';
            $appointmentSumarry[] = '<hr/>';
            if (isset($appointmentGroupedByDay[$startingDay->toDateString()])) {
                foreach ($appointmentGroupedByDay[$startingDay->toDateString()] as $appointment) {
                    $appointmentSumarry[] =
                        $appointment->start_at->setTimezone($tz)->format(Settings::get('time_format')) .
                        ' ' . $appointment->client->name .
                        ' / ' . $appointment->getDuration() . '<br>' . $appointment->client->email;
                }
            } else {
                $appointmentSumarry[] = '<small>No appointments for that day</small>';
            }
            $appointmentSumarry[] = ' ';
            $startingDay->addDay();
        }

        $this->addRoundedSquare($appointmentSumarry);
    }

    private function loadNextWeekData()
    {
        $this->sections = new Sections($this->startWeek()->timestamp, $this->endWeek()->timestamp);
    }
}
