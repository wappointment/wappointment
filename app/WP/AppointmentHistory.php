<?php

namespace Wappointment\WP;

use Wappointment\Models\Client;

class AppointmentHistory
{
    public static function render($atts)
    {
        $client = Client::where('email', wp_get_current_user()->user_email)->first();
        if (empty($client)) {
            return __('No past appointments found ...', 'wappointment');
        } else {
            return static::renderAppointmentListing($client, $atts);
        }
    }

    public static function dateLabel($atts)
    {
        return empty($atts['dateLabel']) ? __('Date and time', 'wappointment') : $atts['dateLabel'];
    }

    public static function serviceLabel($atts)
    {
        return empty($atts['serviceLabel']) ? __('Service', 'wappointment') : $atts['serviceLabel'];
    }

    public static function durationLabel($atts)
    {
        return empty($atts['durationLabel']) ? __('Duration', 'wappointment') : $atts['durationLabel'];
    }

    public static function staffLabel($atts)
    {
        return empty($atts['staffLabel']) ? __('Staff', 'wappointment') : $atts['staffLabel'];
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
            $history .= '<td>' . $appointment->getStartsDayAndTime($client->getTimezone()) . '</td>';
            $history .= '<td>' . $appointment->getServiceName() . '</td>';
            $history .= '<td>' . $appointment->getDuration() . '</td>';
            $history .= '<td>' . $appointment->getStaffName() . '</td>';
            $history .= '</tr>';
        }
        return $history . '</table></div>';
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
        </style>';
    }
}
