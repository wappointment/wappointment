<?php

namespace Wappointment\WP;

use Wappointment\Helpers\Get;
use Wappointment\Models\Client;
use Wappointment\Services\WidgetSettings;

class AppointmentHistory
{
    public $atts = [];
    public $appointments = [];
    public $timezone = 'UTC';

    public function __construct($atts)
    {
        $this->atts = $atts;
        $this->loadData();
    }

    public function loadData()
    {
        $email = wp_get_current_user()->user_email;

        if (!empty($email)) {
            $client = Client::where('email', $email)->first();
            if (!is_null($client)) {
                $this->timezone = $client->getTimezone();
                $this->appointments = apply_filters('wappointment_history', $client->appointments)->sortByDesc('start_at');
            }
        }
    }

    public function render()
    {
        if (empty($this->appointments)) {
            return __('No appointments found.', 'wappointment');
        }

        return $this->renderAppointmentListing();
    }

    public function dateLabel()
    {
        return empty($this->atts['date_label']) ? __('Date and time', 'wappointment') : $this->atts['date_label'];
    }

    public function serviceLabel()
    {
        return empty($this->atts['service_label']) ? __('Service', 'wappointment') : $this->atts['service_label'];
    }

    public function durationLabel()
    {
        return empty($this->atts['duration_label']) ? __('Duration', 'wappointment') : $this->atts['duration_label'];
    }

    public function staffLabel()
    {
        return empty($this->atts['staff_label']) ? __('Staff', 'wappointment') : $this->atts['staff_label'];
    }

    public function renderAppointmentListing()
    {
        $history = Get::style('table_history') . '<div id="wappointment-history"><table>';
        $history .= $this->renderHeader();
        foreach ($this->appointments as $appointment) {
            $history .= $this->renderRow($appointment);
        }
        return $history . '</table></div>';
    }

    public function renderHeader()
    {
        return '<tr>'
            . '<th>' . $this->dateLabel() . '</th>'
            . '<th>' . $this->serviceLabel() . '</th>'
            . '<th>' . $this->durationLabel() . '</th>'
            . '<th>' . $this->staffLabel() . '</th>'
            . '</tr>';
    }

    public function renderRow($appointment)
    {
        $row = '<tr>';
        $row .= '<td>'
            . '<div>' . $appointment->getStartsDayAndTime($this->timezone) . '</div>'
            . '<div>' . $this->renderCancelRescheduleLink($appointment) . '</div>'
            . '</td>';
        $row .= '<td>' . $appointment->getServiceName() . '</td>';
        $row .= '<td>' . $appointment->getDuration() . '</td>';
        $row .= '<td>' . $appointment->getStaffName() . '</td>';
        $row .= '</tr>';
        return $row;
    }

    public function renderCancelRescheduleLink($appointment)
    {
        $links = '<div class="wlinksWaphistory" >';
        $videoLocation = $appointment->getLocationVideo();
        $widget = new WidgetSettings;
        if ($videoLocation && $appointment->canShowLink()) {
            $links .= '<span><a href="' .  $appointment->getLinkViewEvent() . '">' . $widget->getSetting('view.join') . '</a></span>';
        }
        if ($appointment->canStillCancel()) {
            $links .= '<span><a href="' .  $appointment->getLinkCancelEvent() . '">' . $widget->getSetting('cancel.button') . '</a></span>';
        }
        if ($appointment->canStillReschedule()) {
            $links .= '<span><a href="' .  $appointment->getLinkRescheduleEvent() . '">' . $widget->getSetting('reschedule.button') . '</a></span>';
        }
        return $links . '</div>';
    }
}
