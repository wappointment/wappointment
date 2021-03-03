<?php

namespace Wappointment\Services;

use Wappointment\Managers\Central;
use Wappointment\ClassConnect\RakitValidator;
use Wappointment\Validators\HasValues;
use Wappointment\Validators\RequiredIfHas;
use Wappointment\Validators\RequiredIfFields;

class Calendars
{
    public static function getModel()
    {
        return Central::get('CalendarModel');
    }

    public static function all()
    {
        $services = static::getModel()::orderBy('sorting')->fetch();
        return $services->filter(function ($service, $key) {
            return count($service->locations) > 0;
        })->all();
    }

    public static function save($calendarData)
    {
        $validator = new RakitValidator;
        // $validator->addValidator('hasvalues', new HasValues());
        // $validator->addValidator('required_if_has', new RequiredIfHas());
        // $validator->addValidator('required_if_fields', new RequiredIfFields());

        $validationRules = [
            'name' => 'required',
            'avatar' => '',
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
                $query->where('id', '!=', $calendarData['id']);
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
        $calendarData['availability'] = (new \Wappointment\Services\Availability($calendarDB))->regenerate(false);
        if (!empty($calendarDB)) {
            $calendarDB->update($calendarData);
        } else {
            $calendarDB = static::getModel()::create($calendarData);
        }

        do_action('wappointment_calendar_saved', $calendarData);
        return $calendarDB;
    }

    public static function dataToOptions($calendarData, $calendarDB)
    {
        $optiondb = !empty($calendarDB->options) ? $calendarDB->options : [];
        return array_merge($optiondb, [
            'avatar' => $calendarData['avatar'],
            'gravatar' => $calendarData['gravatar'],
            'timezone' => $calendarData['timezone'],
            'regav' => $calendarData['regav'],
            'avb' => $calendarData['avb'],
        ]);
    }

    public static function delete($service_id = false)
    {
        $serviceModel = static::getModel();
        $old_service = $serviceModel::find($service_id);
        $serviceModel::where('sorting', '>', $old_service->sorting)->decrement('sorting');
        return $serviceModel::where('id', $service_id)->delete();
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
}
