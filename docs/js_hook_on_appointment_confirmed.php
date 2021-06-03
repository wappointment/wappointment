<?php

/**
 * This file has been created for documentation purpose only
 * simply copy and modify the code for your own needs
 * in your active theme's functions.php for instance
 */

/**
 * you can add a similar function to your theme's functions.php file in order to
 * - redirect a user to a custom thank you page for instance
 * - send an event on your own analytics system
 * - or anything else ..
 *
 * @return void
 */

/**
 * when an appointment is confirmed
 *
 * @return void
 */
function my_wappo_analytics_appointment_confirmed()
{

  echo '<script>';
  echo "document.addEventListener('wappo_confirmed', function (e) {
              // window.location.replace('https://mysite.com/myurl');
              console.log('appointment Data', e.wdata )
            }, false);";
  echo '</script>';
}
add_action('wp_print_footer_scripts', 'my_wappo_analytics_appointment_confirmed');


/**
 * when an appointment is rescheduled
 *
 * @return void
 */
function my_wappo_analytics_appointment_rescheuled()
{

  echo '<script>';
  echo "document.addEventListener('wappo_rescheduled', function (e) {
              // window.location.replace('https://mysite.com/myurl');
              console.log('appointment Data', e.wdata )
            }, false);";
  echo '</script>';
}
add_action('wp_print_footer_scripts', 'my_wappo_analytics_appointment_rescheuled');
