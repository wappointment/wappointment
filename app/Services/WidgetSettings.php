<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;

class WidgetSettings
{

    private $settings = [
        'colors' => [
            'primary' => [
                'bg' => '#855785',
                'text' => '#ffffff',
            ],

            'header' => [
                'bg' => '#F5F4F4',
                'text' => '#676767',
                'durationbg' => '#eeeeee',
            ],
            'body' => [
                'bg' => '#ffffff', //calendar_bg
                'text' => '#505050', //calendar_cotext
                'disabled_links' => '#cccccc' //calendar_codisabled
            ],
            'selected_day' => [
                'bg' => '#a0a0a0', //back_bg
                'text' => '#ffffff', //back_color
            ],
            'secondary' => [
                'bg' => '#f7f7f7', //back_bg
                'bg_selected' => '#949494', //back_sel_bg
                'text' => '#606060', //back_color
                'text_selected' => '#ffffff', //back_sel_co
            ],
            'form' => [
                'success' => '#66c677', //success_co
                'error' => '#ed7575', //error_co
            ],
            'address' => [
                'bg' => '#e6e6e6',
                'text' => '#606060',
            ],
            'confirmation' => [
                'bg' => '#82ca9c', //header_bg
                'text' => '#ffffff', //header_co
            ]
        ],
        'general' => [
            'check_header_compact_mode' => false,
            'when' => 'When',
            'service' => 'Service',
            'location' => 'Where',
            'min' => 'min'
        ],
        'button' => [
            'title' => 'Book now!',
            'check_full' => false,
            'check_bold' => false,
            'slide_size' => 1.3
        ],
        'selection' => [
            'title' => '[total_slots] free slots',
            'timezone' => 'Timezone: [timezone]',
            'morning' => 'Morning',
            'afternoon' => 'Afternoon',
            'evening' => 'Evening',
            'check_viewweek' => false,
        ],
        'form' => [
            'header' => 'Confirm Appointment',
            'byskype' => 'By Skype',
            'byphone' => 'By Phone',
            'inperson' => 'At a Location',
            'fullname' => 'Full Name:',
            'email' => 'E-mail:',
            'phone' => 'Phone:',
            'skype' => 'Skype username:',
            'address' => 'Address:',
            'back' => 'Back',
            'confirm' => 'Confirm',
            'check_terms' => false,
            'terms' => 'See how [link]we process your data[/link]',
            'terms_link' => '',
        ],
        'confirmation' => [
            'confirmation' => 'Appointment Booked',
            'when' => 'When:',
            'duration' => 'Duration:',
            'service' => 'Service:',
            'pending' => 'The appointment is pending and should be quickly confirmed',
            'skype' => 'The appointment will take place on Skype, we will call you on this account:',
            'phone' => 'The appointment will take place over the phone, we will call you on this number:',
            'physical' => 'The appointment will take place at this address:',
            'savetocal' => 'Save it to your calendar'
        ],
        'cancel' => [
            'page_title' => 'Cancel Appointment',
            'title' => 'Appointment details',
            'confirmed' => 'Appointment has been cancelled!',
            'confirmation' => 'Are you sure you want to cancel your appointment?',
            'toolate' => "Cannot cancel. This is too close to appointment's start",
            'button' => 'Cancel',
            'confirm' => 'Confirm',
        ],
        'reschedule' => [
            'page_title' => 'Reschedule Appointment',
            'title' => 'Appointment details',
            'toolate' => "Cannot reschedule. This is too close to appointment's start",
            'button' => 'Reschedule',
            'confirm' => 'Confirm',
        ],
    ];

    private $fields = [
        'colors' => [
            'primary' => [
                'label' => 'Action Color',
                'fields' => [
                    'text' => ['label' => 'Text'],
                    'bg' => ['label' => 'Background'],
                ],
                'main' => true
            ],
            'header' => [
                'label' => 'Header Color',
                'fields' => [
                    'text' => ['label' => 'Text'],
                    'bg' => ['label' => 'Background'],
                ],
                'main' => true
            ],
            'body' => [
                'label' => 'Body Colors',
                'fields' => [
                    'text' => ['label' => 'Text'],
                    'bg' => ['label' => 'Background'],
                    'disabled_links' => ['label' => 'Disabled day'],
                ],
                'main' => true
            ],
            'selected_day' => [
                'label' => 'Selected day',
                'fields' => [
                    'text' => ['label' => 'Text'],
                    'bg' => ['label' => 'Background'],
                ]
            ],
            'secondary' => [
                'label' => 'Secondary Button',
                'fields' => [
                    'text' => ['label' => 'Text'],
                    'bg' => ['label' => 'Background'],
                    'text_selected' => ['label' => 'Text (selected)'],
                    'bg_selected' => ['label' => 'Background (selected)'],
                ]
            ],
            'form' => [
                'label' => 'Form',
                'fields' => [
                    'success' => ['label' => 'Success'],
                    'error' => ['label' => 'Error'],
                ]
            ],
            'address' => [
                'label' => 'Address',
                'fields' => [
                    'text' => ['label' => 'Text'],
                    'bg' => ['label' => 'Background'],
                ]
            ],
            'confirmation' => [
                'label' => 'Confirmation Header',
                'fields' => [
                    'text' => ['label' => 'Text'],
                    'bg' => ['label' => 'Background'],
                ]
            ]
        ],
        'general' => [
            'check_header_compact_mode' => ['label' => 'Header Compact mode']
        ],
        'button' => [
            'backgroundColor' => ['label' => 'Primary Button Background'],
            'color' => ['label' => 'Primary Button Text'],
            'check_full' => ['label' => 'Full Width'],
            'check_bold' => ['label' => 'Bold'],
            'slide_size' => [
                'label' => 'Text Size',
                'options' => ['min' => .6, 'max' => 2.6, 'step' => .1, 'unit' => 'em']
            ],
        ],
        'selection' => [
            'header_co' => ['label' => 'Header text'],
            'header_bg' => ['label' => 'Header background'],
            'calendar_bg' => ['label' => 'Body background'],
            'calendar_cotext' => ['label' => 'Body text'],
            'calendar_codisabled' => ['label' => 'Disabled day'],
            'check_viewweek' => ['label' => 'Week View'],
        ],
        'form' => [
            'back_bg' => ['label' => 'Secondary Button background'],
            'back_color' => ['label' => 'Secondary Button text'],
            'back_sel_bg' => ['label' => 'Secondary Button background(selected)'],
            'back_sel_co' => ['label' => 'Secondary Button text(selected)'],
            'success_co' => ['label' => 'Success color'],
            'error_co' => ['label' => 'Error color'],
            'check_terms' => ['label' => 'Add data proccessing notice'],
        ],
        'confirmation' => [
            'header_co' => ['label' => 'Header Confirmatiom Text'],
            'header_bg' => ['label' => 'Header Confirmatiom background'],
        ]
    ];
    private $db_settings = [];
    private $merged_settings = [];
    private $key_option = 'widget_settings';

    public function __construct()
    {
        $ppolicy = get_privacy_policy_url();

        $this->settings['form']['terms_link'] = empty($ppolicy) ? 'http://' : $ppolicy;
        $this->db_settings = WPHelpers::getOption($this->key_option, []);
        $this->merged_settings = empty($this->db_settings) ?
            $this->defaultSettings() : $this->merge($this->defaultSettings(), $this->db_settings);
        //$this->merged_settings = $this->merge($this->settings, $this->db_settings);
    }
    public function defaultSettings()
    {
        return apply_filters('wappointment_widget_settings_default', $this->settings);
    }
    public function defaultFields()
    {
        return apply_filters('wappointment_widget_fields_default', $this->fields);
    }

    public function get()
    {
        return $this->merged_settings;
    }

    public function adminFieldsInfo()
    {
        return $this->setHiddenFields($this->defaultFields());
    }

    private function hidePhone($fields)
    {
        $fields['form']['byphone']['hidden'] = true;
        $fields['form']['phone']['hidden'] = true;
        $fields['confirmation']['phone']['hidden'] = true;
        return $fields;
    }
    private function hideSkype($fields)
    {
        $fields['form']['byskype']['hidden'] = true;
        $fields['form']['skype']['hidden'] = true;
        $fields['confirmation']['skype']['hidden'] = true;
        return $fields;
    }
    private function hideInPerson($fields)
    {
        $fields['form']['inperson']['hidden'] = true;
        $fields['form']['address']['hidden'] = true;
        $fields['confirmation']['physical']['hidden'] = true;
        return $fields;
    }
    private function hideButtonService($fields)
    {
        $fields['form']['byphone']['hidden'] = true;
        $fields['form']['byskype']['hidden'] = true;
        $fields['form']['inperson']['hidden'] = true;
        $fields['colors']['secondary']['fields']['text_selected']['hidden'] = true;
        $fields['colors']['secondary']['fields']['bg_selected']['hidden'] = true;
        return $fields;
    }
    private function setHiddenFields($fields)
    {
        $service = Service::getObject();
        if (!$service->hasManyTypes()) {
            $fields = $this->hideButtonService($fields);
        }
        if (!$service->hasPhone()) {
            //$fields = $this->hidePhone($fields);
        }
        if (!$service->hasSkype()) {
            $fields = $this->hideSkype($fields);
        }
        if (!$service->hasPhysical()) {
            $fields = $this->hideInPerson($fields);
        }
        if ((int) Settings::get('approval_mode') === 1) {
            $fields['confirmation']['pending']['hidden'] = true;
        }

        return $fields;
    }

    public function save($settings)
    {
        return WPHelpers::setOption($this->key_option, $this->filterSettings($settings), true);
    }

    public function filterSettings($settings)
    {
        $accepted = array_keys($this->defaultSettings());

        return (\WappointmentLv::collect($settings))->reject(function ($value, $key) use ($accepted) {
            return !in_array($key, $accepted);
        })->toArray();
    }

    private function merge($array1, $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->merge($merged[$key], $value);
            } elseif (is_numeric($key)) {
                if (!in_array($value, $merged)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
