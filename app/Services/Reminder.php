<?php

namespace Wappointment\Services;

use Wappointment\Models\Reminder as MReminder;
use Wappointment\Models\Appointment;
use Wappointment\Helpers\TipTap;

class Reminder
{
    public static function save($reminderData)
    {
        $validator = new \Rakit\Validation\Validator;

        $validationRules = [
            'type' => 'required',
            'event' => 'required',
            'options' => 'required|array',
            'options.body' => 'required'
        ];

        $validationMessages = [
            'type:required' => 'Select a type of reminder',
        ];

        $validation = $validator->make($reminderData, $validationRules);
        $validation->setMessages($validationMessages);
        $validation->validate();

        if ($validation->fails()) {
            throw new \WappointmentValidationException(
                "Cannot save Reminder",
                1,
                null,
                $validation->errors()->toArray()
            );
        }

        if (isset($reminderData['id']) && $reminderData['id'] > 0) {
            $reminderData['options'] = json_encode($reminderData['options']);
            return (bool) MReminder::where('id', $reminderData['id'])->update($reminderData);
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
            'subject' => 'Your appointment is pending',
            'published' => 1,
            'locked' => 1,
            'options' => [
                'body' => TipTap::simpleArrayToTipTap(
                    [
                        ['p' => 'Dear [client:name],'],
                        ['p' => ''],
                        ['p' =>
                        'You have booked a [service:name] of [appointment:duration] long on [appointment:starts]'],
                        ['p' => 'It will be confirmed shortly, you will receive the confirmation by email.'],
                        ['p' => ''],
                        ['p' => 'Best,'],
                        ['p' => \WappointmentLv::blogname()],
                    ]
                )
            ],
        ];
        $arraySeeds[] = [
            'type' => $email_type,
            'event' => MReminder::APPOINTMENT_RESCHEDULED,
            'subject' => 'Your appointment has been rescheduled',
            'published' => 1,
            'locked' => 1,
            'options' => [
                'body' => TipTap::simpleArrayToTipTap(
                    [
                        ['p' => 'Dear [client:name],'],
                        ['p' => ''],
                        ['p' => 'Your appointment has been rescheduled.'],
                        ['p' => 'Your new appointment will start on [appointment:starts]'],
                        ['p' => ''],
                        ['p' => 'We look forward to seeing you !'],
                        ['p' => \WappointmentLv::blogname()],
                    ]
                ),
            ],
        ];
        $arraySeeds[] = [
            'type' => $email_type,
            'event' => MReminder::APPOINTMENT_CANCELLED,
            'subject' => 'Your appointment has been cancelled',
            'published' => 1,
            'locked' => 1,
            'options' => [
                'body' => TipTap::simpleArrayToTipTap(
                    [
                        ['p' => 'Dear [client:name],'],
                        ['p' => ''],
                        ['p' => 'Your appointment taking place the [appointment:starts] has been cancelled.'],
                        ['p' => 'If you want to book a new appointment with us, [ label="click here" link="linkNew"].'],
                        ['p' => ''],
                        ['p' => 'We hope to see you soon!'],
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
            ['p' => 'Dear [client:name],'],
            ['p' => ''],
            ['p' => 'You have booked a [service:name] of [appointment:duration] long on [appointment:starts]'],
        ];
        $email_reminder = [
            ['p' => 'Dear [client:name],'],
            ['p' => ''],
            ['p' => 'We remind you that you have an appointment on [appointment:starts]'],
        ];
        $messageService = [
            'physical' => 'It will take place at this address : [service:address]',
            'phone' => 'It will take place over the phone, we will call you on this number : [client:phone]',
            'skype' => 'It will take place on Skype, we will call you on this account : [client:skype]',
            'zoom' => 'It will take place by video online, the meeting room link will be accessible [ label="here" link="linkView"]. '
        ];
        if (count($types) > 1) {
            foreach ($types as $type) {
                $email_confirmed[] = [$type => $messageService[$type]];
                $email_reminder[] = [$type => $messageService[$type]];
            }
        } else {
            foreach ($types as $type) {
                $email_confirmed[] = ['p' => $messageService[$type]];
                $email_reminder[] = ['p' => $messageService[$type]];
            }
        }

        foreach ($footer as $footerRow) {
            $email_confirmed[] = $footerRow;
            $email_reminder[] = $footerRow;
        }
        $email_type = MReminder::getType('email');
        return [
            [
                'type' => $email_type,
                'event' => MReminder::APPOINTMENT_CONFIRMED,
                'subject' => 'Your appointment has been confirmed',
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
                'subject' => 'Don\'t forget your appointment',
                'options' => [
                    'body' => TipTap::simpleArrayToTipTap($email_reminder),
                    'when_number' => '1',
                    'when_unit' => '3',
                ],
            ],
        ];
    }
}
