<?php

namespace Wappointment\WP;

use Wappointment\Models\Client;
use Wappointment\Services\WidgetSettings;

class AppointmentHistory
{
    public static function render($atts)
    {
        $email =  wp_get_current_user()->user_email;
        $empty = __('No past appointments found ...', 'wappointment');
        if (empty($email)) {
            return $empty;
        }
        $client = Client::where('email', $email)->first();
        if (empty($client)) {
            return $empty;
        } else {
            return static::renderAppointmentListing($client, $atts);
        }
    }

    public static function dateLabel($atts)
    {
        return empty($atts['date_label']) ? __('Date and time', 'wappointment') : $atts['date_label'];
    }

    public static function serviceLabel($atts)
    {
        return empty($atts['service_label']) ? __('Service', 'wappointment') : $atts['service_label'];
    }

    public static function durationLabel($atts)
    {
        return empty($atts['duration_label']) ? __('Duration', 'wappointment') : $atts['duration_label'];
    }

    public static function staffLabel($atts)
    {
        return empty($atts['staff_label']) ? __('Staff', 'wappointment') : $atts['staff_label'];
    }

    public static function renderAppointmentListing($client, $atts)
    {
        $history = static::getStyle() . '<div id="wappointment-history"><table>';
        $history .= '<tr>'
            . '<th>' . static::dateLabel($atts) . '</th>'
            . '<th>' . static::serviceLabel($atts) . '</th>'
            . '<th>' . static::durationLabel($atts) . '</th>'
            . '<th>' . static::staffLabel($atts) . '</th>'
            . '</tr>';
        foreach ($client->appointments->sortByDesc('id') as $appointment) {
            $history .= '<tr>';
            $history .= '<td>'
                . '<div>' . $appointment->getStartsDayAndTime($client->getTimezone()) . '</div>'
                . '<div>' . static::renderCancelRescheduleLink($appointment) . '</div>'
                . '</td>';
            $history .= '<td>' . $appointment->getServiceName() . '</td>';
            $history .= '<td>' . $appointment->getDuration() . '</td>';
            $history .= '<td>' . $appointment->getStaffName() . '</td>';
            $history .= '</tr>';
        }
        return $history . '</table></div>';
    }

    public static function renderCancelRescheduleLink($appointment)
    {
        $links = '<div class="wlinksWaphistory" >';
        $videoLocation = $appointment->getLocationVideo();
        $widget = new WidgetSettings();
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

    public static function getStyle()
    {
        return '<style>
        table {
          font-family: arial, sans-serif;
          border-collapse: collapse;
          width: 100%;
        }
        
        td, th {
          border: 1px solid #dddddd;
          text-align: left;
          padding: 8px;
        }
        
        tr:nth-child(even) {
          background-color: #dddddd;
        }
        .wlinksWaphistory span:before{
            content: " - ";
        }

        .wlinksWaphistory span:first-child:before{
            content: "";
        }

        
        </style>';
    }
}
