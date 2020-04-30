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
                'subject' => 'Your appointment is pending',
                'published' => 1,
                'options' => [
                    'body' => TipTap::simpleArrayToTipTap(
                        [
                            ['h3' => 'Dear [client:name],'],
                            ['p' => ''],
                            ['p' => 'You have booked a [service:name] of [appointment:duration] long on [appointment:starts]'],
                            ['p' => 'It will be confirmed shortly, you will receive the confirmation by email.'],
                            ['p' => ''],
                            ['p' => 'Best,'],
                            ['p' => \WappointmentLv::blogname()],
                        ]
                    )
                ],
            ],
            [
                'type' => $email_type,
                'event' => MReminder::APPOINTMENT_RESCHEDULED,
                'subject' => 'Your appointment has been rescheduled',
                'published' => 1,
                'options' => [
                    'body' => TipTap::simpleArrayToTipTap(
                        [
                            ['h3' => 'Dear [client:name],'],
                            ['p' => ''],
                            ['p' => 'Your appointment has been rescheduled.'],
                            ['p' => 'Your new appointment will start on [appointment:starts]'],
                            ['p' => ''],
                            ['p' => 'We look forward to seeing you !'],
                            ['p' => \WappointmentLv::blogname()],
                        ]
                    ),
                ],
            ],
            [
                'type' => $email_type,
                'event' => MReminder::APPOINTMENT_CANCELLED,
                'subject' => 'Your appointment has been cancelled',
                'published' => 1,
                'options' => [
                    'body' => TipTap::simpleArrayToTipTap(
                        [
                            ['h3' => 'Dear [client:name],'],
                            ['p' => ''],
                            ['p' => 'Your appointment taking place the [appointment:starts] has been cancelled.'],
                            ['p' => 'If you want to book a new appointment with us, [ label="click here" link="linkNew"].'],
                            ['p' => ''],
                            ['p' => 'We hope to see you soon!'],
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
