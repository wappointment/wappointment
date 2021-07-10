<?php

/**
 * This file has been created for documentation purpose only
 * simply copy and modify the code for your own needs
 * in your active theme's functions.php for instance
 */


/**
 * Edit logo in emails sent to the staff notifications of new cancel and reschedule bookings, daily emails etc ..
 */
add_filter('wappointment_admin_email_head_image', 'my_func_wappointment_admin_email_head_image');

function my_func_wappointment_admin_email_head_image($imageLink)
{
    $imageLink = 'https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png';
    return $imageLink;
}


/**
 * Edit link on that logo
 */
add_filter('wappointment_admin_email_head_link', 'my_func_wappointment_admin_email_head_link');

function my_func_wappointment_admin_email_head_link($linkValue)
{
    $linkValue = 'https://google.com/';
    return $linkValue;
}
