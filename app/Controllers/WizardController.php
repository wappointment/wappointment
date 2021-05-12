<?php

namespace Wappointment\Controllers;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\ClassConnect\Request;
use Wappointment\Services\Settings;
use Wappointment\Services\Reset;

class WizardController extends RestController
{
    private $last_step = 4;

    public function later(Request $request)
    {
        WPHelpers::setOption('wizard_step', -1);
        return ['message' => 'Allright, you can come back here whenever you want.'];
    }

    public function setStep(Request $request)
    {

        if ($request->input('step') == 1) {
            new \Wappointment\Installation\Process();
        }
        if (in_array($request->input('step'), [2, 3])) {
            Reset::refreshCache();
        }

        WPHelpers::setOption('wizard_step', $request->input('step'));

        if ($this->last_step == $request->input('step')) {
            if (!empty($request->input('booking_page_id'))) {
                Settings::save('booking_page', (int) $request->input('booking_page_id'));
            }
            return ['message' => 'Done with the setup. Let\'s get booked!'];
        }

        return true;
    }
}
