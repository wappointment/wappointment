<?php

namespace Wappointment\Installation\Steps;

use Wappointment\Services\Reminder;
use Wappointment\Models\Reminder as MReminder;
use Wappointment\Helpers\TipTap;

class SeedData extends \Wappointment\Installation\MethodsRunner
{
    public function remindersToSeed()
    {
        $email_type = MReminder::getType('email');
        return [
            [
                'type' => $email_type,
                'event' => MReminder::APPOINTMENT_PENDING,
                'subject' => __('Your appointment is pending', 'wappointment'),
                'published' => 1,
                'options' => [
                    'body' => TipTap::simpleArrayToTipTap(
                        [   /* translators: %s - client's first name. */
                            ['p' => sprintf(__('Dear %s,', 'wappointment'), '[client:name]')],
                            ['p' => ''],
                            /* translators: %1$s is the service name, %2$s is the appointment duration, %3$s is appointment start date */
                            ['p' => vsprintf(__('You have booked a %1$s of %2$s long on %3$s', 'wappointment'), ['[service:name]', '[appointment:duration]', '[appointment:starts]'])],
                            ['p' => __('It will be confirmed shortly, you will receive the confirmation by email.', 'wappointment')],
                            ['p' => ''],
                            ['p' => __('Best,', 'wappointment')],
                            ['p' => \WappointmentLv::blogname()],
                        ]
                    )
                ],
            ],
            [
                'type' => $email_type,
                'event' => MReminder::APPOINTMENT_RESCHEDULED,
                'subject' => __('Your appointment has been rescheduled', 'wappointment'),
                'published' => 1,
                'options' => [
                    'body' => TipTap::simpleArrayToTipTap(
                        [
                            /* translators: %s - client's first name. */
                            ['p' => sprintf(__('Dear %s,', 'wappointment'), '[client:name]')],
                            ['p' => ''],
                            ['p' => __('Your appointment has been rescheduled.', 'wappointment')],
                            /* translators: %s - appointment starts date. */
                            ['p' => sprintf(__('Your new appointment will start on %s', 'wappointment'), '[appointment:starts]')],
                            ['p' => ''],
                            ['p' => __('We look forward to seeing you!', 'wappointment')],
                            ['p' => \WappointmentLv::blogname()],
                        ]
                    ),
                ],
            ],
            [
                'type' => $email_type,
                'event' => MReminder::APPOINTMENT_CANCELLED,
                'subject' => __('Your appointment has been cancelled', 'wappointment'),
                'published' => 1,
                'options' => [
                    'body' => TipTap::simpleArrayToTipTap(
                        [
                            /* translators: %s - client's first name. */
                            ['p' => sprintf(__('Dear %s,', 'wappointment'), '[client:name]')],
                            ['p' => ''],
                            /* translators: %s - appointment starts date. */
                            ['p' => sprintf(__('Your appointment taking place the %s has been cancelled.', 'wappointment'), '[appointment:starts]')],
                            /* translators: %s - a "click here" link will be added. */
                            ['p' => sprintf(__('If you want to book a new appointment with us, %s.', 'wappointment'), '[ label="' . __('click here', 'wappointment') . '" link="linkNew"]')],
                            ['p' => ''],
                            ['p' => __('We hope to see you soon!', 'wappointment')],
                            ['p' => \WappointmentLv::blogname()],
                        ]
                    ),
                ],
            ],
        ];
    }

    protected function canRun()
    {
        // 1 - insert default reminders
        foreach ($this->remindersToSeed() as $reminder) {
            if (!isset($reminder['locked'])) {
                $reminder['locked'] = 1;
            }
            Reminder::save($reminder);
        }
    }
}
