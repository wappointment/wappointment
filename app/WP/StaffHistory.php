<?php

namespace Wappointment\WP;

use Wappointment\Managers\Central;
use Wappointment\Models\Appointment;
use Wappointment\Repositories\CalendarsBack;

class StaffHistory extends AppointmentHistory
{
    public function loadData()
    {
        $id =  wp_get_current_user()->ID;
        if (!empty($id)) {
            $calendar = $this->findCalendar($id);
            $this->timezone = $calendar['timezone'];

            $this->appointments = Appointment::where('staff_id', $calendar['id'])->orderBy('id', 'DESC')->get();
        }
    }

    protected function findCalendar($id)
    {
        $calendars = (new CalendarsBack)->get();
        foreach ($calendars as $calendar) {
            if (isset($calendar['wp_uid']) && (int)$calendar['wp_uid'] === $id) {
                return $calendar;
            }
        }
    }

    public function clientLabel()
    {
        return empty($this->atts['client_label']) ? esc_html__('Client', 'wappointment') : esc_html($this->atts['client_label']);
    }

    public function locationLabel()
    {
        return empty($this->atts['location_label']) ? esc_html__('Location', 'wappointment') : esc_html($this->atts['location_label']);
    }

    public function renderHeader()
    {
        return '<tr>'
            . '<th>' . $this->dateLabel() . '</th>'
            . '<th>' . $this->serviceLabel() . '</th>'
            . '<th>' . $this->clientLabel() . '</th>'
            . '</tr>';
    }

    public function renderRow($appointment)
    {
        $row = '<tr>';
        $row .= '<td>'
            . '<div>' . esc_html($appointment->getStartsDayAndTime($this->timezone)) . '</div>'
            . '<div>' . $this->renderCancelRescheduleLink($appointment) . '</div>'
            . '</td>';
        $row .= '<td>' . esc_html($appointment->getServiceName()) . '<br/>' . esc_html($appointment->getLocation()) . '</td>';
        $row .= '<td>' . $this->getClientData($appointment) . '</td>';
        $row .= '</tr>';
        return $row;
    }

    public function getClientData($appointment)
    {
        $html = '';
        foreach ($this->getDataClient($appointment->getClientModel()) as $row) {
            $html .= '<div>' . esc_html($row['label']) . ':' . esc_html($row['value']) . '</div>';
        }
        return $html;
    }

    public function getDataClient($client)
    {
        if (empty($client)) {
            return [];
        }
        $data = [
            'name' => [
                'label' => esc_html__('Name', 'wappointment'),
                'value' => $client->name
            ],
            'email' => [
                'label' => esc_html__('Email', 'wappointment'),
                'value' => $client->email
            ]
        ];
        if (!empty($client->getPhone())) {
            $data['phone'] = [
                'label' => esc_html__('Phone', 'wappointment'),
                'value' => $client->getPhone()
            ];
        }

        $cfs = Central::get('CustomFields')::get();
        foreach ($cfs as $cf) {
            if (empty($cf['core'])) {
                $cfValue = $this->getCfValue($cf, $client);
                if (!empty($cfValue)) {
                    $data[$cf['namekey']] = [
                        'label' => $cf['name'],
                        'value' => $cfValue
                    ];
                }
            }
        }

        return $data;
    }

    public function getCfValue($cf, $client)
    {
        $value = !empty($client->options[$cf['namekey']]) ? $client->options[$cf['namekey']] : false;
        if ($value !== false && strpos($value, 'inp-') !== false) {
            foreach ($cf['values'] as $cfvalue) {
                if ($cfvalue['value'] == $value) {
                    return $cfvalue['label'];
                }
            }
        }
    }
}
