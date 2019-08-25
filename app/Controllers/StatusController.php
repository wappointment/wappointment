<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Services\Status;

class StatusController extends RestController
{
    public function save(Request $request)
    {
        if ($request->input('type') == 'free') {
            if (Status::free($request->input('start'), $request->input('end'), $request->input('timezone'))) {
                return ['message' => 'Extra free time added'];
            }
        } elseif ($request->input('type') == 'busy') {
            if (Status::busy($request->input('start'), $request->input('end'), $request->input('timezone'))) {
                return ['message' => 'Busy time added'];
            }
        }
    }

    public function delete(Request $request)
    {
        $event = Status::delete($request->input('id'));

        if ($event) {
            return ['message' => !empty($event->source) ? 'Event muted' : 'Event deleted'];
        }
        throw new WappointmentException('Error while deleting element', 1);
    }
}
