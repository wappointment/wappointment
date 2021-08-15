<?php

namespace Wappointment\Services;

use Wappointment\Managers\Central;
use Wappointment\ClassConnect\RakitValidator;

class Calendars
{
    public static function getModel()
    {
        return Central::get('CalendarModel');
    }

    public static function all($onlyAvailable = false, $cron = false)
    {
        $query = static::getModel()::active()->orderBy('sorting');
        if ($onlyAvailable) {
            $query->whereNotNull('availability');
        } else {
            //this is admin we add permissions
            if ($cron === false && !CurrentUser::isAdmin()) {
                $query->where('wp_uid', CurrentUser::id());
            }
        }
        $results = $query->fetch();
        if ($onlyAvailable) {
            return $results->filter(function ($e) {
                return count($e->services) > 0;
            });
        } else {
            return $results;
        }
    }

    public static function save($calendarData)
    {
        $validator = new RakitValidator;

        $validationRules = [
            'name' => 'required|is_adv_string|max:100',
            'avatar_id' => '',
            'gravatar' => '',
            'timezone' => 'required',
            'id' => '',
            'wp_uid' => '',
            'regav' => 'required|array',
            'avb' => 'required|numeric',
        ];

        if ($calendarData['wp_uid'] > 0) {
            $query = static::getModel()::where('wp_uid', $calendarData['wp_uid']);
            if ($calendarData['id'] > 0) {
                $query->where('id', '!=', (int) $calendarData['id']);
            }
            if ($query->first()) {
                throw new \WappointmentException("Select another WordPress account this one is already used for another calendar", 1);
            }
        }

        $validationRules = apply_filters('wappointment_calendar_validation_rules', $validationRules);
        $validation = $validator->make($calendarData, $validationRules);

        $validation->validate();

        if ($validation->fails()) {
            throw new \WappointmentValidationException("Cannot save Calendar", 1, null, $validation->errors()->toArray());
            return $validation->errors()->toArray();
        }

        return static::saveCalendar($calendarData);
    }

    public static function saveCalendar($calendarData)
    {

        $calendarDB = null;
        if (!empty($calendarData['id'])) {
            $calendarDB = static::getModel()::findOrFail($calendarData['id']);
        } else {
            if (!Central::get('CalendarModel')::canCreate()) {
                throw new \WappointmentValidationException("Cannot save Calendar .");
            }
        }

        $calendarData = apply_filters('wappointment_calendar_before_saved', $calendarData, $calendarDB);

        $calendarData['options'] = static::dataToOptions($calendarData, $calendarDB);

        if (!empty($calendarDB)) {
            $calendarDB->update($calendarData);
        } else {
            $calendarDB = static::getModel()::create($calendarData);
            $calendarDB->addAllServices();
        }

        if (!empty($calendarDB)) {
            (new \Wappointment\Services\Availability($calendarDB))->regenerate();
        }

        static::autoRecordNotification();
        do_action('wappointment_calendar_saved', $calendarData);
        return $calendarDB;
    }

    public static function autoRecordNotification()
    {
        if (Settings::get('weekly_summary')) {
            Queue::queueWeeklyJob();
        }

        if (Settings::get('daily_summary')) {
            Queue::queueDailyJob();
        }
    }

    public static function dataToOptions($calendarData, $calendarDB)
    {
        $optiondb = !empty($calendarDB->options) ? $calendarDB->options : [];
        return array_merge($optiondb, [
            'avatar_id' => empty($calendarData['avatar_id']) ? '' : $calendarData['avatar_id'],
            'gravatar' => $calendarData['gravatar'],
            'timezone' => $calendarData['timezone'],
            'regav' => static::regavClean($calendarData['regav']),
            'avb' => $calendarData['avb'],
        ]);
    }

    public static function regavClean($regav)
    {
        foreach ($regav as $day => $blocks) {
            if ($day === 'precise') {
                $newblocks = $blocks;
            } else {
                $newblocks = [];

                if (is_array($blocks) && !empty($blocks)) {
                    foreach ($blocks as $key => $block) {
                        if ($block[1] - $block[0] > 0) {
                            $newblocks[] = $block;
                        }
                    }
                }
            }

            $regav[$day] = $newblocks;
        }

        return $regav;
    }

    public static function delete($calendar_id = false)
    {
        $calendarModel = static::getModel();
        $old_calendar = $calendarModel::find($calendar_id);
        $calendarModel::where('sorting', '>', $old_calendar->sorting)->decrement('sorting');
        $old_calendar->account_key = null;
        $old_calendar->save();
        return $calendarModel::where('id', $calendar_id)->delete();
    }

    public static function get($service_id = false)
    {
        return static::getModel()::findOrFail($service_id);
    }

    public static function total()
    {
        return static::getModel()::where('id', '>', 0)->count();
    }

    public static function reorder($id, $neworder)
    {
        $serviceModel = static::getModel();
        $old_service = $serviceModel::find($id);
        if ($old_service->sorting > $neworder) {
            $serviceModel::where('sorting', '>=', $neworder)->where('sorting', '<', $old_service->sorting)->increment('sorting');
        } else {
            $serviceModel::where('sorting', '>', $old_service->sorting)->where('sorting', '<=', $neworder)->decrement('sorting');
        }

        return $serviceModel::where('id', $id)->update(['sorting' => $neworder]);
    }

    public static function toggle($id)
    {
        $serviceModel = static::getModel();
        $old_service = $serviceModel::find($id);

        return $serviceModel::where('id', $id)->update(['status' => $old_service->status == 1 ? 0 : 1]);
    }
}
