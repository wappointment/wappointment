<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;

trait HasAppointmentFooterLinks
{
    protected $separator = ' | ';

    private function calendarLink()
    {
        return '<a href="[appointment:linkAddEventToCalendar]">' . Settings::get('save_appointment_text_link') . '</a>';
    }

    private function rescheduleAndCancelLinks()
    {
        $links = '';
        if (Settings::get('allow_rescheduling')) {
            $links .= '<a href="[appointment:linkRescheduleEvent]">' . Settings::get('reschedule_link') . '</a>';
        }

        if (Settings::get('allow_cancellation')) {
            if (!empty($links)) {
                $links .= $this->separator;
            }
            $links .= '<a href="[appointment:linkCancelEvent]">' . Settings::get('cancellation_link') . '</a></p>';
        }
        if (empty($links)) {
            return '';
        }
        return  $links;
    }

    protected function footerLinks()
    {
        $rescheduleAndCancelLinks = $this->rescheduleAndCancelLinks();
        if (!empty($rescheduleAndCancelLinks)) {
            $rescheduleAndCancelLinks = $this->separator . $rescheduleAndCancelLinks;
        }

        return '<p>' . $this->calendarLink() . $rescheduleAndCancelLinks . '</p>';
    }
}
