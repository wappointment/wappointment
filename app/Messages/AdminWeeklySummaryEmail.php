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
        /* translators: %1$s - start of the week's date %2$s end of the week's date. */
        $this->subject = sprintf(__('Weekly summary  %1$s - %2$s', 'wappointment'), $this->date_start_string, $this->date_end_string);

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
            /* translators: %s - client's first name. */
            sprintf(__('Hi %s,', 'wappointment'), $this->staff->getFirstName()),
            /* translators: %1$s - start of the week's date %2$s end of the week's date. */
            sprintf(__('Here is a summary of your appointments for this week: %1$s - %2$s', 'wappointment'), $this->date_start_string, $this->date_end_string),
        ];

        if (!empty($coverage)) {
            $newlines = [
                /* translators: %s - number of appointments. */
                sprintf(__('New Appointments: %s', 'wappointment'), count($this->sections->appointments)),
                /* translators: %1$s - numbers of slots, %2$s slots duration. */
                sprintf(__('Available slots: %1$s (duration %2$s min', 'wappointment'), $this->sections->getFreeSlots($serviceDurationInSeconds), Service::get()['duration']),
                /* translators: %s - percentage. */
                sprintf(__('Coverage: %s', 'wappointment'), $coverage),
            ];
        } else {
            $newlines = [
                __('No availabilities for this week', 'wappointment'),
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

        $this->getAppointmentsListWeek($this->startWeek(), $this->endWeek());

        $this->addLines([
            __('Have a great week!', 'wappointment'),
            '',
            __('Ps: An .ics file with all your appointments is attached', 'wappointment')
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
                $appointmentSumarry[] = '<small>' . __('No appointments for that day', 'wappointment') . '</small>';
            }
            $appointmentSumarry[] = ' ';
            $startingDay->addDay();
        }

        $this->addRoundedSquare($appointmentSumarry);
    }
}
