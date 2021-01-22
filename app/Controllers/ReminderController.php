<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Services\Reminder;
use Wappointment\Models\Reminder as MReminder;
use Wappointment\Services\Settings;
use Wappointment\Services\Status;

class ReminderController extends RestController
{
    private $columns = [
        'id', 'subject', 'type', 'event', 'locked', 'published', 'options'
    ];

    public function save(Request $request)
    {
        $requested = $request->except(['rest_route', 'locked', 'email_logo', 'label']);
        $requested['published'] = true;
        $this->saveImage($request);
        if ($this->isTrueOrFail(Reminder::save($requested))) {
            return ['message' => 'Reminder saved'];
        }
        throw new \WappointmentException('Couldn\'t save preview', 1);
    }
    protected function saveImage(Request $request)
    {
        if ($request->has('email_logo')) {
            Settings::saveStaff('email_logo', $request->input('email_logo'));
        }
    }
    public function patch(Request $request)
    {
        $this->saveImage($request);
        if ($this->isTrueOrFail(Reminder::save($request->except(['rest_route', 'locked', 'email_logo', 'label'])))) {
            return ['message' => 'Reminder updated'];
        }
        throw new \WappointmentException('Couldn\'t update reminder', 1);
    }

    public function preview(Request $request)
    {
        if ($this->isTrueOrFail(Reminder::preview($request->input('reminder'), $request->input('recipient')))) {
            return ['message' => 'Reminder preview sent'];
        }
        throw new \WappointmentException('Couldn\'t send preview', 1);
    }

    public function delete(Request $request)
    {
        if (Reminder::delete($request->input('id'))) {
            return ['message' => 'Reminder deleted'];
        }
        throw new \WappointmentException('Impossible to delete reminder', 1);
    }

    public function get()
    {
        $queryReminders = MReminder::select($this->columns);
        if ((int) Settings::get('approval_mode') == 1) {
            $queryReminders->whereNotIn('event', [MReminder::APPOINTMENT_PENDING]);
        }
        if ((int) Settings::get('allow_rescheduling') != 1) {
            $queryReminders->whereNotIn('event', [MReminder::APPOINTMENT_RESCHEDULED]);
        }
        if ((int) Settings::get('allow_cancellation') != 1) {
            $queryReminders->whereNotIn('event', [MReminder::APPOINTMENT_CANCELLED]);
        }
        $queryReminders->whereIn('type', MReminder::getTypes('code'));
        return apply_filters(
            'wappointment_settings_reminders_get',
            [
                'mail_status' => (bool) Settings::get('mail_status'),
                'allow_cancellation' => (bool) Settings::get('allow_cancellation'),
                'allow_rescheduling' => (bool) Settings::get('allow_rescheduling'),
                'reschedule_link' => Settings::get('reschedule_link'),
                'cancellation_link' => Settings::get('cancellation_link'),
                'save_appointment_text_link' => Settings::get('save_appointment_text_link'),
                'recipient' => (new \Wappointment\WP\Staff())->emailAddress(),
                'multiple_service_type' => \Wappointment\Helpers\Service::hasMultipleTypes(),
                'reminders' => $queryReminders->get(),
                'defaultReminders' => [
                    'email' => Reminder::getSeedReminder()
                ],
                'email_logo' => Settings::getStaff('email_logo'),
                'labels' => [
                    'types' => MReminder::getTypes(),
                    'events' => MReminder::getEvents()
                ]
            ]
        );
    }
}
