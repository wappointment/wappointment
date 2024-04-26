<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Helpers\Translations;
use Wappointment\Services\Status;
// phpcs:ignoreFile
class StatusController extends RestController
{
    public function save(Request $request)
    {

        if ($request->input('type') == 'free') {
            if (Status::free($request->input('start'), $request->input('end'), $request->input('timezone'), $request, $request->input('staff_id'))) {
                return ['message' => __('Extra free time added', 'wappointment')];
            }
        } elseif ($request->input('type') == 'busy') {
            if (Status::busy($request->input('start'), $request->input('end'), $request->input('timezone'), $request->input('staff_id'))) {
                return ['message' => __('Busy time added', 'wappointment')];
            }
        }
        throw new \WappointmentException(Translations::get('error_creating'), 1);
    }

    public function delete(Request $request)
    {
        $event = Status::delete($request->input('id'));

        if ($event) {
            return ['message' => !empty($event->source) ? __('Muted successfully', 'wappointment') : Translations::get('element_deleted')];
        }
        throw new \WappointmentException(Translations::get('error_deleting'), 1);
    }
}
