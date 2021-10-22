<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\RakitValidator;
use Wappointment\Models\Reminder as MReminder;
use Wappointment\Models\Appointment;
use Wappointment\Helpers\TipTap;
use Wappointment\Helpers\Translations;

class Reminder
{
    public static function save($reminderData)
    {

        $validator = new RakitValidator;
        $reminderData['subject'] = strip_tags($reminderData['subject']);
        $validationRules = [
            'subject' => 'required|max:100',
            'type' => 'required',
            'event' => 'required',
            'options' => 'required|array',
            'options.body' => 'required'
        ];

        $validationMessages = [
            'type:required' => __('Select a type of reminder', 'wappointment'),
        ];

        $validation = $validator->make($reminderData, $validationRules);
        $validation->setMessages($validationMessages);
        $validation->validate();

        if ($validation->fails()) {
            throw new \WappointmentValidationException(
                Translations::get('error_saving'),
                1,
                null,
                $validation->errors()->toArray()
            );
        }

        if (isset($reminderData['id']) && $reminderData['id'] > 0) {
            $reminderData['options'] = json_encode($reminderData['options']);
            return (bool) MReminder::where('id', (int)$reminderData['id'])
                ->update($reminderData);
        } else {
            return (bool) MReminder::create($reminderData);
        }
    }

    public static function delete($id)
    {
        return MReminder::destroy($id);
    }

    public static function preview($id, $recipient)
    {
        $reminder = MReminder::find($id);
        $transport = apply_filters('wappointment_reminder_transport', ['email' => [__CLASS__, 'sendPreview']]);

        return call_user_func_array($transport[$reminder->getTypeLabel()], [$reminder, $recipient]);
    }

    public static function sendPreview($reminder, $recipient)
    {
        return (new Mail())
            ->to($recipient)
            ->send($reminder->toMailable(new Appointment));
    }

    public static function getSeedReminder()
    {
        return self::getSeeds()[1];
    }

    public static function getSeeds($service_type = false)
    {
        if ($service_type === false) {
            $service = Service::get();
            $service_type = $service['type'];
        }
        return self::baseSeeds(self::remindersToSeed($service_type));
    }

    public static function baseSeeds($arraySeeds)
    {
        $email_type = MReminder::getType('email');
        $arraySeeds[] = [
            'type' => $email_type,
            'event' => MReminder::APPOINTMENT_PENDING,
            'subject' => __('Your appointment is pending', 'wappointment'),
            'published' => 1,
            'locked' => 1,
            'options' => [
                'body' => TipTap::simpleArrayToTipTap(
                    [
                        /* translators: %s - client's first name. */
                        ['p' => sprintf(__('Dear %s,', 'wappointment'), '[client:name]')],
                        ['p' => ''],
                        ['p' =>
                        /* translators: %1$s is the service name, %2$s is the appointment duration, %3$s is appointment start date */
                        vsprintf(__('You have booked a %1$s of %2$s long on %3$s', 'wappointment'), ['[service:name]', '[appointment:duration]', '[appointment:starts]'])],
                        ['p' => __('It will be confirmed shortly, you will receive the confirmation by email.', 'wappointment')],
                        ['p' => ''],
                        ['p' => __('Best,', 'wappointment')],
                        ['p' => \WappointmentLv::blogname()],
                    ]
                )
            ],
        ];
        $arraySeeds[] = [
            'type' => $email_type,
            'event' => MReminder::APPOINTMENT_RESCHEDULED,
            'subject' => __('Your appointment has been rescheduled', 'wappointment'),
            'published' => 1,
            'locked' => 1,
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
        ];
        $arraySeeds[] = [
            'type' => $email_type,
            'event' => MReminder::APPOINTMENT_CANCELLED,
            'subject' => __('Your appointment has been cancelled', 'wappointment'),
            'published' => 1,
            'locked' => 1,
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
        ];
        return $arraySeeds;
    }

    public static function remindersToSeed($types)
    {
        $footer = [
            ['p' => ''],
            ['p' => 'Best,'],
            ['p' => \WappointmentLv::blogname()]
        ];
        $email_confirmed = [
            /* translators: %s - client's first name. */
            ['p' => sprintf(__('Dear %s,', 'wappointment'), '[client:name]')],
            ['p' => ''],
            /* translators: %1$s is the service name, %2$s is the appointment duration, %3$s is appointment start date */
            ['p' => vsprintf(__('You have booked a %1$s of %2$s long on %3$s', 'wappointment'), ['[service:name]', '[appointment:duration]', '[appointment:starts]'])],
        ];
        $email_reminder = [
            /* translators: %s - client's first name. */
            ['p' => sprintf(__('Dear %s,', 'wappointment'), '[client:name]')],
            ['p' => ''],
            /* translators: %s - appointment starts date */
            ['p' => sprintf(__('We remind you that you have an appointment on %s', 'wappointment'), '[appointment:starts]')],
        ];
        $messageService = [
            'physical' => [
                /* translators: %s - service address */
                ['p' => sprintf(__('It will take place at this address: %s', 'wappointment'), '[service:address]')]
            ],
            'phone' => [
                /* translators: %s - client's phone number */
                ['p' => sprintf(__('It will take place over the phone, we will call you on this number: %s', 'wappointment'), '[client:phone]')]
            ],
            'skype' => [
                /* translators: %s - client's skype username */
                ['p' => sprintf(__('It will take place on Skype, we will call you on this account: %s', 'wappointment'), '[client:skype]')]
            ],
            'zoom' => [
                ['p' => __('It will take place by video online.', 'wappointment')],
                /* translators: %s - a "here" link is added. */
                ['h3' => sprintf(__('Click %s to begin the meeting', 'wappointment'), '[ label="' . __('here', 'wappointment') . '" link="linkNew"]')]
            ]
        ];
        if (!is_array($types)) {
            $types = ['physical', 'phone', 'skype', 'zoom'];
        }
        foreach ($types as $type) {
            $email_confirmed[] = [$type => $messageService[$type]];
            $email_reminder[] = [$type => $messageService[$type]];
        }


        $email_confirmed[] = ['p' => '[order:summary],'];

        foreach ($footer as $footerRow) {
            $email_confirmed[] = $footerRow;
            $email_reminder[] = $footerRow;
        }
        $email_type = MReminder::getType('email');
        return [
            [
                'type' => $email_type,
                'event' => MReminder::APPOINTMENT_CONFIRMED,
                'subject' => __('Your appointment has been confirmed', 'wappointment'),
                'locked' => 1,
                'published' => 1,
                'options' => [
                    'body' => TipTap::simpleArrayToTipTap($email_confirmed)
                ],
            ],
            [
                'type' => $email_type,
                'event' => MReminder::APPOINTMENT_STARTS,
                'locked' => 0,
                'published' => 1,
                'subject' => __('Don\'t forget your appointment', 'wappointment'),
                'options' => [
                    'body' => TipTap::simpleArrayToTipTap($email_reminder),
                    'when_number' => '1',
                    'when_unit' => '3',
                ],
            ],
        ];
    }
}
