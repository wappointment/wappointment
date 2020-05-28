<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;
use Wappointment\Services\Sections;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Service;

class AdminWeeklySummaryEmail extends AdminDailySummaryEmail
{

    protected $sections = null;

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
        $this->tz = Settings::getStaff('timezone');
        $serviceDurationInSeconds = Service::get()['duration'] * 60;
        $coverage = $this->sections->getCoverage($serviceDurationInSeconds);

        $staff = new \Wappointment\WP\Staff;
        $lines = [
            'Hi ' . $staff->getFirstName() . ', ',
            'Here is a summary of your appointments for this week: ' . $date_start_string . ' - ' . $date_end_string
        ];

        if (!empty($coverage)) {
            $newlines = [
                'New Appointments: ' . count($this->sections->appointments),
                'Available Slots: ' . $this->sections->getFreeSlots($serviceDurationInSeconds) . ' (duration ' . Service::get()['duration'] . 'min)',
                'Coverage: ' . $coverage
            ];
        } else {
            $newlines = [
                'No availabilities for this week'
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

        $this->getAppointmentsListWeek($startingDay, $endDay);

        $this->addLines([
            'Have a great week!',
            '',
            'Ps: An .ics file with all your appointments is attached'
        ]);
        $this->attachIcs($this->sections->appointments, 'weekly_appointments', true);
    }

    public function getAppointmentsListWeek($startingDay, $endDay)
    {
        $appointmentSumarry = [];
        $tz = $this->tz;
        $appointmentGroupedByDay = $this->sections->appointments->mapToGroups(function ($item, $key) use ($tz) {
            return [$item->start_at->setTimezone($tz)->toDateString() => $item];
        });
        while ($startingDay->lessThanOrEqualTo($endDay)) {
            $appointmentSumarry[] = '<strong>' . $startingDay->format('D Y-m-d') . '</strong>';
            $appointmentSumarry[] = '<hr/>';
            if (isset($appointmentGroupedByDay[$startingDay->toDateString()])) {
                foreach ($appointmentGroupedByDay[$startingDay->toDateString()] as $appointment) {
                    $appointmentSumarry[] =
                        $appointment->start_at->setTimezone($this->tz)->format(Settings::get('time_format')) .
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
