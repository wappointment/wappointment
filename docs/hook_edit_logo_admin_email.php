<?php

/**
 * This file has been created for documentation purpose only
 * simply copy and modify the code for your own needs
 * in your active theme's functions.php for instance
 */

/**
 * This code should be placed in your active themes functions.php file
 */

/**
 * Edit logo in emails sent to the staff notifications of new cancel and reschedule bookings, daily emails etc ..
 */
add_filter('wappointment_admin_email_head_image', 'my_func_wappointment_admin_email_head_image');

function my_func_wappointment_admin_email_head_image()
{
    return 'https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png';
}


/**
 * Edit link on that logo
 */
add_filter('wappointment_admin_email_head_link', 'my_func_wappointment_admin_email_head_link');

function my_func_wappointment_admin_email_head_link()
{
    return 'https://google.com/';
}


/**
 * Edit ics attachments signatures only for premium
 */
add_filter('wappointment_ics_signature', 'my_wappointment_ics_signature');

function my_wappointment_ics_signature()
{
    return "\nBooked through https://google.com/";
}


/**
 * edit colors of admin email
 */
add_filter('wappointment_admin_email_color', 'my_wappointment_admin_email_color', 10, 2);

function my_wappointment_admin_email_color($colorValue, $colorName)
{

    switch ($colorName) {
        case 'primary':
            return '#6664CB';
        case 'primaryLight':
            return '#7A78D5';
        case 'separator':
            return '#d5d5d5';
        default:
            return $colorValue;
    }
}
