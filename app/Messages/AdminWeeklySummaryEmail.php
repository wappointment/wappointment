<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;
use Wappointment\Services\Sections;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Service;

class AdminWeeklySummaryEmail extends AdminDailySummaryEmail
{

    protected $sections = null;
    protected $date_start_string = '';
    protected $date_end_string = '';

    public function startWeek()
    {
        return $this->tomorrowCarbon();
    }

    public function endWeek()
    {
        return $this->tomorrowCarbon()->addDays(6);
    }

    private function loadNextWeekData()
    {
        $this->staff = $this->getStaff();
        $this->tz = $this->staff->timezone;
        $this->date_start_string = $this->startWeek()->toDateString();
        $this->date_end_string = $this->endWeek()->toDateString();
        $this->subject = 'Weekly summary ' . $this->date_start_string . ' - ' . $this->date_end_string;

        $this->sections = new Sections($this->startWeek()->timestamp, $this->endWeek()->timestamp, $this->staff, $this->isLegacy());
    }

    public function loadContent()
    {
        $this->loadNextWeekData();


        $this->addLogo();
        $this->addBr();
        $serviceDurationInSeconds = Service::get()['duration'] * 60;
        $coverage = $this->sections->getCoverage($serviceDurationInSeconds);

        $lines = [
            'Hi ' .  $this->staff->getFirstName() . ', ',
            'Here is a summary of your appointments for this week: ' . $this->date_start_string . ' - ' . $this->date_end_string
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

        $this->getAppointmentsListWeek($this->startWeek(), $this->endWeek());

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
            if (isset($appointmentGroupedByDay[$startingDay->toDateString()])) {
                foreach ($appointmentGroupedByDay[$startingDay->toDateString()] as $appointment) {
                    $appointmentSumarry[] = $this->getAppointmentFormatted($appointment);
                }
            } else {
                $appointmentSumarry[] = '<small>No appointments for that day</small>';
            }
            $appointmentSumarry[] = ' ';
            $startingDay->addDay();
        }

        $this->addRoundedSquare($appointmentSumarry);
    }
}
