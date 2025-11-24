<?php
return [
    'colors' => [
        'primary' => [
            'label' => __('Action Color', 'wappointment'),
            'fields' => [
                'text' => ['label' => __('Text', 'wappointment')],
                'bg' => ['label' => __('Background', 'wappointment')],
            ],
            'main' => true
        ],
        'header' => [
            'label' => __('Header Color', 'wappointment'),
            'fields' => [
                'text' => ['label' => __('Text', 'wappointment')],
                'bg' => ['label' => __('Background', 'wappointment')],
            ],
            'main' => true
        ],
        'body' => [
            'label' => __('Body Colors', 'wappointment'),
            'fields' => [
                'text' => ['label' => __('Text', 'wappointment')],
                'bg' => ['label' => __('Background', 'wappointment')],
                'disabled_links' => ['label' => __('Disabled day', 'wappointment')],
            ],
            'main' => true
        ],
        'selected_day' => [
            'label' => __('Selected day', 'wappointment'),
            'fields' => [
                'text' => ['label' => __('Text', 'wappointment')],
                'bg' => ['label' => __('Background', 'wappointment')],
            ]
        ],
        'secondary' => [
            'label' => __('Secondary Button', 'wappointment'),
            'fields' => [
                'text' => ['label' => __('Text', 'wappointment')],
                'bg' => ['label' => __('Background', 'wappointment')],
                'text_selected' => ['label' => __('Text (selected)', 'wappointment')],
                'bg_selected' => ['label' => __('Background (selected)', 'wappointment')],
            ]
        ],
        'form' => [
            'label' => 'Form',
            'fields' => [
                'success' => ['label' => __('Success', 'wappointment')],
                'error' => ['label' => __('Error', 'wappointment')],
                'payment' => ['label' => __('Payment Border', 'wappointment')],
            ]
        ],
        'address' => [
            'label' => 'Address',
            'fields' => [
                'text' => ['label' => __('Text', 'wappointment')],
                'bg' => ['label' => __('Background', 'wappointment')],
            ]
        ],
        'confirmation' => [
            'label' => __('Confirmation Header', 'wappointment'),
            'fields' => [
                'text' => ['label' => __('Text', 'wappointment')],
                'bg' => ['label' => __('Background', 'wappointment')],
            ]
        ]
    ],
    'general' => [
        'fields' => [
            'check_header_compact_mode' => [
                'label' => __('Header Compact mode', 'wappointment'),
                'tip' => __('Appointment details is compacted in the header', 'wappointment')
            ],
            'check_hide_staff_name' => [
                'label' => __('Hide staff name', 'wappointment'),
                'tip' => __('Ideal if you are not having multiple staff', 'wappointment')
            ],
            'location' => [
                'conditions' => [
                    ['key' => 'general.check_header_compact_mode', 'val' => false]
                ],
                'tip' => __('Appears in standard summary', 'wappointment')
            ],
            'when' => [
                'conditions' => [
                    ['key' => 'general.check_header_compact_mode', 'val' => false]
                ],
                'tip' => __('Appears in standard summary', 'wappointment')
            ],
            'service' => [
                'conditions' => [
                    ['key' => 'general.check_header_compact_mode', 'val' => false]
                ],
                'tip' => __('Appears in standard summary', 'wappointment')
            ],
            'min' => [
                'tip' => 'minutes'
            ],
            'noappointments' => [
                'tip' => __('Show when no appointments are available for that staff', 'wappointment')
            ]
        ]
    ],

    'button' => [
        'fields' => [
            'check_bold' => ['label' => __('Bold', 'wappointment')],
            'slide_size' => [
                'label' => __('Text Size', 'wappointment'),
                'options' => ['min' => .6, 'max' => 2.6, 'step' => .1, 'unit' => 'em'],
            ],
        ]

    ],
    'selection' => [

        'fields' => [
            'check_viewweek' => ['label' => __('Week View', 'wappointment')],
        ]

    ],

    'service_selection' => [
        'fields' => [
            'check_price_right' => ['label' => __('Price right aligned', 'wappointment')],
        ],
    ],
    'service_location' => [
        'fields' => [
            'select_location' => false,
        ],
        'sub' => 'You can edit modalities names in [url wurl="wappointment_settings#/modalities"]Wappointment > Settings > Services > Modalities[/url]',
    ],
    'form' => [
        'categories' => [
            [
                'label' => __('Appointment Modalities', 'wappointment'),
                'fields' => [
                    'byzoom' => false,
                    'inperson' => false,
                    'byskype' => false,
                ]
            ],
            [
                'label' => __('Booking Form', 'wappointment'),
                'fields' => [
                    'fullname' => false,
                    'email' => false,
                    'phone' => false,
                    'skype' => false,
                    'address' => false,
                    'back' => false,
                    'confirm' => false,
                    'check_terms' => ['label' => __('Add data proccessing notice', 'wappointment')],
                    'terms' => ['conditions' => [['key' => 'form.check_terms', 'val' => true]]],
                    'terms_link' => ['conditions' => [['key' => 'form.check_terms', 'val' => true]]],
                ]
            ],
        ]

    ],
    'confirmation' => [
        'categories' => [
            [
                'label' => __('Appointment Confirmed', 'wappointment'),
                'fields' => [
                    'confirmation' => false,
                    'when' => false,
                    'service' => false,
                    'duration' => false,
                    'savetocal' => false
                ]
            ],
            [
                'label' => __('Conditional confirmation', 'wappointment'),
                'fields' => [
                    'pending' => ['tip' => __('When admin confirmation is required', 'wappointment')],
                    'skype' => ['tip' => __('Skype appointments only', 'wappointment')],
                    'phone' => ['tip' => __('Phone appointments only', 'wappointment')],
                    'physical' => ['tip' => __('Appointments at a location only', 'wappointment')],
                    'zoom' => ['tip' => __('Video appointments only', 'wappointment')],
                ]
            ],
        ]
    ],

    'swift_payment' => [
        'categories' => [
            [
                'label' => __('On Site Payment', 'wappointment'),
                'key' => 'onsite',
                'fields' => [
                    'onsite_tab' => false,
                    'onsite_desc' => false,
                    'onsite_confirm' => false,
                ]
            ],
            [
                'label' => __('Advanced', 'wappointment'),
                'key' => 'avanced',
                'fields' => [
                    'check_tos' => ['label' => __('Add TOS and privacy links', 'wappointment'), 'tip' => __('Privacy link is in previous step', 'wappointment')],
                    'tos_text' => ['label' => 'https://', 'conditions' => [['key' => 'swift_payment.check_tos', 'val' => true]]],
                    'tos_link' => ['conditions' => [['key' => 'swift_payment.check_tos', 'val' => true]]],
                ],
                'last' => true,
                'nodrag' => true
            ],
        ],
        'categories_draggable' => true

    ],

];
