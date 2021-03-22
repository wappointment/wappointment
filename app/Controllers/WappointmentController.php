<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Wappointment\DotCom;
use Wappointment\Services\Wappointment\EmailList;
use Wappointment\Services\Wappointment\Contact;
use Wappointment\Services\Wappointment\BookingTest as BookingTestAPI;
use Wappointment\Services\BookingTest;
use Wappointment\Validators\HttpRequest\SubscribeAdmin;
use Wappointment\Validators\HttpRequest\ContactAdmin;
use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\ClassConnect\Request;

class WappointmentController extends RestController
{
    public function connect(Request $request)
    {
        $staff_id = !empty($request->input('id')) ? $request->input('id') : Settings::get('activeStaffId');
        $dotcomapi = new DotCom;
        $dotcomapi->setStaff($staff_id);
        $result = $dotcomapi->connect($request->get('account_key'));

        if ($result) {
            return [
                'data' => $result['dotcom'],
                'message' => 'Account has been connected'
            ];
        }
        throw new \WappointmentException("Couldn't connect with this key.", 1);
    }

    public function disconnect(Request $request)
    {
        $staff_id = !empty($request->input('id')) ? $request->input('id') : Settings::get('activeStaffId');
        $dotcom = new DotCom;
        $dotcom->setStaff($staff_id);
        $result = $dotcom->disconnect($staff_id);

        if ($result) {
            return [
                'data' => $result,
                'message' => 'Account has been disconnected'
            ];
        }
        throw new \WappointmentException("Couldn't disconnect account.", 1);
    }

    public function refresh(Request $request)
    {
        $staff_id = !empty($request->input('id')) ? $request->input('id') : Settings::get('activeStaffId');
        $dotcom = new DotCom;
        $dotcom->setStaff($staff_id);
        $result = $dotcom->refresh();

        if ($result) {
            return [
                'data' => $result,
                'message' => 'Account has been refreshed'
            ];
        }
        throw new \WappointmentException("Couldn't refresh account.", 1);
    }

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
                'message' => 'Great your message has been sent, we\'ll get back to you soon'
            ];
        }
        throw new \WappointmentException("Couldn't send your message.", 1);
    }

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

    public function sendIgnoreBooking()
    {

        Settings::save('show_welcome', false);

        return [
            'result' => true,
            'message' => "Have fun!"
        ];
    }
}
