<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Helpers\Site;
use Wappointment\Helpers\Translations;
use Wappointment\Services\Reminder;
use Wappointment\Models\Reminder as MReminder;
use Wappointment\Services\Settings;
use Wappointment\Services\VersionDB;

class ReminderController extends RestController
{
    public function isLegacy()
    {
        return !VersionDB::canServices();
    }
    private function getRequested(Request $request)
    {
        return $request->except(['rest_route', 'locked', 'email_logo', 'label', 'canTranslate', 'children']);
    }
    public function save(Request $request)
    {
        $requested = $this->getRequested($request);

        $requested['published'] = true;
        $this->saveImage($request);
        if ($this->isTrueOrFail(Reminder::save($requested))) {
            return ['message' => Translations::get('element_saved')];
        }
        throw new \WappointmentException(Translations::get('error_saving'), 1);
    }

    protected function saveImage(Request $request)
    {
        if ($request->has('email_logo')) {
            if ($this->isLegacy()) {
                Settings::saveStaff('email_logo', $request->input('email_logo'));
            } else {
                Settings::save('email_logo', $request->input('email_logo'));
            }
        }
    }

    public function patch(Request $request)
    {
        $this->saveImage($request);
        if ($this->isTrueOrFail(Reminder::save($this->getRequested($request)))) {
            return ['message' => Translations::get('element_updated')];
        }
        throw new \WappointmentException(Translations::get('error_updating'), 1);
    }

    public function preview(Request $request)
    {
        if ($this->isTrueOrFail(Reminder::preview($request->input('reminder'), $request->input('recipient')))) {
            return ['message' => __('Reminder preview sent', 'wappointment')];
        }
        throw new \WappointmentException(__('Error sending', 'wappointment'), 1);
    }

    public function delete(Request $request)
    {
        if (Reminder::delete($request->input('id'))) {
            return ['message' => Translations::get('element_deleted')];
        }
        throw new \WappointmentException(Translations::get('error_deleting'), 1);
    }

    public function get()
    {
        $queryReminders = MReminder::query();

        $queryReminders->activeReminders();
        $queryReminders->whereIn('type', MReminder::getTypes('code'));

        $data = [
            'mail_status' => (bool) Settings::get('mail_status'),
            'languages' => Site::languages(),
            'allow_cancellation' => (bool) Settings::get('allow_cancellation'),
            'email_footer' => Settings::get('email_footer'),
            'link_color' => Settings::get('email_link_color'),
            'allow_rescheduling' => (bool) Settings::get('allow_rescheduling'),
            'reschedule_link' => Settings::get('reschedule_link'),
            'cancellation_link' => Settings::get('cancellation_link'),
            'save_appointment_text_link' => Settings::get('save_appointment_text_link'),
            'multiple_service_type' => \Wappointment\Helpers\Service::hasMultipleTypes($this->isLegacy()),
            'recipient' => wp_get_current_user()->user_email,
            'defaultReminders' => [
                'email' => Reminder::getSeedReminder()
            ],
            'labels' => [
                'types' => MReminder::getTypes(),
                'events' => MReminder::getEvents()
            ]
        ];

        $data['email_logo'] = $this->isLegacy() ? Settings::getStaff('email_logo') : Settings::get('email_logo');

        return array_merge(
            apply_filters('wappointment_settings_reminders_get', $data),
            ['reminders' => $queryReminders->getParentSorting()]
        ) ;
    }
}
