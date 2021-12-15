<?php
return [
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
            'payment' => '#f7f7f7',
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
        'check_hide_staff_name' => false,
        'when' => __('When', 'wappointment'),
        'service' => __('Service', 'wappointment'),
        'location' => __('Where', 'wappointment'),
        'package' => __('Package', 'wappointment'),
        'min' => __('min', 'wappointment'),
        'noappointments' => __('No appointments available', 'wappointment')
    ],
    'button' => [
        'title' => __('Book now!', 'wappointment'),
        'check_full' => false,
        'check_bold' => false,
        'slide_size' => 1.3
    ],
    'staff_selection' => [
        'pickstaff' => __('Select staff', 'wappointment'),
        'availabilityfor' => __('Availability for', 'wappointment')
    ],
    'selection' => [
        'check_viewweek' => false,
        /* translators: %s - total slot. */
        'title' => sprintf(__('%s free slots', 'wappointment'), '[total_slots]'),
        /* translators: %s - timezone. */
        'timezone' => sprintf(__('Timezone: %s', 'wappointment'), '[timezone]'),
        'morning' => __('Morning', 'wappointment'),
        'afternoon' => __('Afternoon', 'wappointment'),
        'evening' => __('Evening', 'wappointment'),
    ],
    'form' => [
        'byskype' => __('By Skype', 'wappointment'),
        'byphone' => __('By Phone', 'wappointment'),
        'byzoom' => __('Video meeting', 'wappointment'),
        'inperson' => __('At a Location', 'wappointment'),
        'fullname' => __('Full Name:', 'wappointment'),
        'email' => __('Email:', 'wappointment'),
        'phone' => __('Phone:', 'wappointment'),
        'skype' => __('Skype username:', 'wappointment'),
        'back' => __('Back', 'wappointment'),
        'confirm' => __('Confirm', 'wappointment'),
        'check_terms' => false,
        /* translators: %s - a "we process your data" link is added. */
        'terms' => sprintf(__('See how %s', 'wappointment'), '[link]' . __('we process your data', 'wappointment') . '[/link]'),
        'terms_link' => '',
    ],
    'confirmation' => [
        'confirmation' => __('Appointment Booked', 'wappointment'),
        'when' => __('When:', 'wappointment'),
        'service' => __('Service:', 'wappointment'),
        'duration' => __('Duration:', 'wappointment'),
        'pending' => __('The appointment is pending and should be quickly confirmed', 'wappointment'),
        'skype' => __('The appointment will take place on Skype, we will call you on this account:', 'wappointment'),
        /* translators: %s - a "here" link is added. */
        'zoom' => sprintf(__('The appointment will take place by Video meeting online, the link will show %s.', 'wappointment'), '[meeting_link]' . __('here', 'wappointment') . '[/meeting_link]'),
        'phone' => __('The appointment will take place over the phone, we will call you on this number:', 'wappointment'),
        'physical' => __('The appointment will take place at this address:', 'wappointment'),
        'savetocal' => __('Save it to your calendar', 'wappointment')
    ],
    'view' => [
        'join' => __('Join Meeting', 'wappointment'),
        'missing_url' => __('The meeting room link will appear once it is time to start.', 'wappointment'),
        /* translators: %1$s - number of days, %2$s - number of hours, %3$s - number of minutes, %4$s - number of seconds */
        'timeleft' => sprintf(__('(%1$sd %2$sh %3$sm %4$ss)', 'wappointment'), '[days_left]', '[hours_left]', '[minutes_left]', '[seconds_left]'),
    ],
    'cancel' => [
        'page_title' => __('Cancel Appointment', 'wappointment'),
        'title' => __('Appointment details', 'wappointment'),
        'confirmed' => __('Appointment has been cancelled!', 'wappointment'),
        'confirmation' => __('Are you sure you want to cancel your appointment?', 'wappointment'),
        'toolate' => __('Too late to cancel', 'wappointment'),
        'button' => __('Cancel', 'wappointment'),
        'confirm' => __('Confirm', 'wappointment'),
    ],
    'reschedule' => [
        'page_title' => __('Reschedule Appointment', 'wappointment'),
        'title' => __('Appointment details', 'wappointment'),
        'toolate' => __('Too late to reschedule', 'wappointment'),
        'button' => __('Reschedule', 'wappointment'),
        'confirm' => __('Confirm', 'wappointment'),
    ],
    'service_selection' => [
        'select_service' => __('Pick a service', 'wappointment'),
        'check_full_width' => false
    ],
    'service_duration' => [
        'select_duration' => __('How long will be the session?', 'wappointment'),
    ],
    'service_location' => [
        'select_location' => __('How should we meet?', 'wappointment'),
    ],

    'swift_payment' => [
        'onsite_tab' => __('Pay later', 'wappointment'),
        'onsite_desc' => __('You will pay on the day of your appointment', 'wappointment'),
        'onsite_confirm' => __('Confirm', 'wappointment'),
    ],
];
