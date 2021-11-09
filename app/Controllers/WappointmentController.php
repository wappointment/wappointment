<?php

namespace Wappointment\Controllers;

use Wappointment\Helpers\Translations;
use Wappointment\Services\Wappointment\EmailList;
use Wappointment\Services\Wappointment\Contact;
use Wappointment\Services\Wappointment\BookingTest as BookingTestAPI;
use Wappointment\Services\BookingTest;
use Wappointment\Validators\HttpRequest\SubscribeAdmin;
use Wappointment\Validators\HttpRequest\ContactAdmin;
use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;

class WappointmentController extends RestController
{

    public function subscribe(SubscribeAdmin $request)
    {

        $result = (new EmailList)->subscribe($request->get('email'), $request->get('list'));

        if ($result) {
            return [
                'result' => $result,
                'message' => $result === true ?
                    'Already subscribed' : (isset($result['response']->status) && $result['response']->status > 0 ?
                        "Great, we'll keep you posted as soon as this is out!" :
                        'Great we\'ve sent you an email, just quickly check your inbox and confirm!')
            ];
        }
        throw new \WappointmentException("Couldn't subscribe you.", 1);
    }

    public function contact(ContactAdmin $request)
    {

        $result = (new Contact)->sendMessage($request);

        if ($result) {
            return [
                'result' => $result,
                'message' => __('Great your message has been sent, we\'ll get back to you soon', 'wappointment')
            ];
        }
        throw new \WappointmentException(Translations::get('error_sending'), 1);
    }

    /**
     * Legacy TODO remove
     */
    public function sendTestBooking($request)
    {
        (new BookingTestAPI)->record($request);
        $result = (new BookingTest)->send();
        // quickly trigger the queue task
        WPHelpers::cronTrigger();
        if ($result) {
            Settings::save('show_welcome', false);
            return [
                'result' => true,
                'message' => "Alright we just created a test appointment"
            ];
        }
        return [
            'result' => false,
            'message' => "We couldn't send test email"
        ];
    }

    /**
     * Legacy TODO remove
     */
    public function sendIgnoreBooking()
    {

        Settings::save('show_welcome', false);

        return [
            'result' => true,
            'message' => "Have fun!"
        ];
    }
}
