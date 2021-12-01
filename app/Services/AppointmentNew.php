<?php

namespace Wappointment\Services;

use Wappointment\Models\Client;
use Wappointment\Models\Location;
use Wappointment\Models\Appointment as AppointmentModel;
use Wappointment\Managers\Central;
use Wappointment\Models\OrderPrice;
use Wappointment\Models\TicketAbstract;
use Wappointment\WP\Helpers as WPHelpers;

class AppointmentNew
{
    public static function tryBook(Client $client, $start_at, $end_at, $type, $service, $staff_id = null)
    {
        if (static::canBook($start_at, $end_at, false, $staff_id)) {
            return static::processBook($client, $start_at, $end_at, $type, $service, false, $staff_id);
        }
    }

    public static function adminBook(Client $client, $start_at, $end_at, $type, $service, $staff_id = null)
    {
        if (static::canBook($start_at, $end_at, true, $staff_id)) {
            return static::processBook($client, $start_at, $end_at, $type, $service, true, $staff_id);
        }
    }

    protected static function processBook(Client $client, $start_at, $end_at, $type, $service, $forceConfirmed = false, $staff_id = null)
    {
        $start_at = static::unixToDb($start_at);
        $end_at = static::unixToDb($end_at);
        $location = Location::where('id', $client->bookingRequest->get('location'))->first();
        $status = $forceConfirmed ? AppointmentModel::STATUS_CONFIRMED : static::getDefaultStatus($service);
        $staff_id_value = empty($staff_id) ? 0 : static::getStaffId($staff_id);
        $appointmentData = apply_filters('wappointment_process_book', [
            'start_at' => $start_at,
            'end_at' => $end_at,
            'type' => $location->type > 3 ? $location->type : $location->type - 1,
            'client_id' => $client->id,
            'edit_key' => $client->generateEditKey($start_at . $staff_id_value),
            'status' => $status,
            'service_id' => $client->bookingRequest->get('service'),
            'location_id' => $location->id,
            'duration' => $client->bookingRequest->get('duration'),
            'staff_id' => $staff_id_value,
            'package_id' => $client->bookingRequest->get('package_id'),
            'package_price_id' => $client->bookingRequest->get('package_price_id'),
        ], $client, $start_at, $service);

        return static::bookCreate($appointmentData, $client, $forceConfirmed, $status);
    }

    protected static function getStaffId($staff_id)
    {
        return isset($staff_id->id) ? $staff_id->id : $staff_id;
    }

    public static function adminCalendarGetAppointments($params, $legacy = false)
    {
        $with =  $legacy ? ['client'] : ['client', 'service'];
        $appointmentsQuery  = AppointmentModel::with($with)
            ->where('status', '>=', AppointmentModel::STATUS_AWAITING_CONFIRMATION)
            ->where('start_at', '>=', $params['start_at'])
            ->where('end_at', '<=', $params['end_at']);

        if (!$legacy && !empty($params['staff_id'])) {
            $appointmentsQuery->where('staff_id', (int) $params['staff_id']);
        }

        return $appointmentsQuery->get();
    }

    public static function adminCalendarUpdateAppointmentArray($addedEvent, $appointment)
    {

        if (!empty($appointment->service)) {
            $addedEvent['service'] = $appointment->service->toArray();
            foreach ($appointment->service->locations as $location) {
                if ($location->id == $appointment->location_id) {
                    $addedEvent['location'] = $location->toArray();
                }
            }
        }

        return $addedEvent;
    }

    public static function getLocation($locationLegacy, $appointment)
    {
        $location = static::getAppointmentLocation($appointment);

        if (!empty($location)) {
            return $location->name;
        }
        return $location;
    }

    public static function getAddress($address, $appointment)
    {
        $location = static::getAppointmentLocation($appointment);

        if (!empty($location) && !empty($location->options['address'])) {
            return $location->options['address'];
        }
        return $address;
    }

    protected static function getAppointmentLocation($appointment)
    {
        if (!empty($appointment->location_id) && $appointment->location_id > 0) {
            return Location::where('id', $appointment->location_id)->first();
        }
    }


    public static function delete($id)
    {
        $appointment = static::getAppointmentModel()::find($id);

        $status = $appointment->destroy($id);
        if ($status) {
            (new Availability($appointment->staff_id))->regenerate();
        }
        return $status;
    }

    protected static function getAppointmentModel()
    {
        return Central::get('AppointmentModel');
    }

    /**
     * we go through here each time we CREATE a new appointment
     * whether it's free or paid
     */
    public static function create($data, Client $client, $status)
    {
        $appointment = static::createAppointment($data, $client->bookingRequest->get('slots'));
        $ticket = apply_filters('wappointment_create_appointment_ticket', $appointment, $client, $status);
        return static::generateOrder($client, $ticket, $client->bookingRequest->get('slots'));
    }

    public static function generateOrder($client, TicketAbstract $ticket, $slots = false, $service = false)
    {
        $order = null;
        $service = $service ? $service : $client->bookingRequest->getService();
        if ($service->isSold() && !Payment::isWooActive()) {
            $ticket->hydrateService($service);
            $order = $client->generateOrder($ticket, $slots);
        }
        $appointment = $ticket->getAppointment();
        $appointment->hydrateService($service);
        return ['appointment' => $appointment, 'client' => $client, 'order' => $order, 'ticket' => $ticket];
    }

    protected static function createAppointment($data, $slots = false)
    {
        if (empty($data['options']) || !is_array($data['options'])) {
            $data['options'] = [];
        }

        if ($slots === false) {
            $data = apply_filters('wappointment_set_ticket_options', $data, $slots);
        }

        $data['options']['buffer_time'] = (int) Settings::get('buffer_time');
        return static::getAppointmentModel()::create($data);
    }

    public static function confirm($id, $soft = false, $client = null, $order = null)
    {

        //dd('confirm trye', $id);
        $oldAppointment = $appointment = static::getAppointmentModel()::where('id', (int)$id)
            ->where('status', static::getAppointmentModel()::STATUS_AWAITING_CONFIRMATION)->first();
        if (empty($appointment)) {
            if ($soft === true) {
                return false;
            }
            throw new \WappointmentException("Can't find appointment", 1);
        } else {
            //dd('confirmed appointment');
            $result = $appointment->update(['status' => static::getAppointmentModel()::STATUS_CONFIRMED]);
            if ($result) {
                //send confirm email to client and admin
                $clientModel = empty($client) ? Client::find($appointment->client_id) : $client;

                static::sendConfirmationEvent($appointment, $clientModel, $oldAppointment, $order);
            }
            return $result;
        }
    }

    public static function sendConfirmationEvent($appointment, $client, $oldAppointment, $order = null)
    {
        JobHelper::dispatch('AppointmentConfirmedEvent', [
            'appointment' => $appointment,
            'client' => $client,
            'oldAppointment' => $oldAppointment,
            'order' => $order
        ], $client, static::getAppointmentModel()::STATUS_CONFIRMED);
    }

    public static function patch($id, $data)
    {
        $appointment = static::getAppointmentModel()::where('id', (int)$id)->first();
        $oldAppointment = $appointment->replicate();

        $result = $appointment->update($data);

        if ($result) {
            static::appointmentModified($appointment, $oldAppointment);
        }
        return $result;
    }

    public static function reschedule($edit_key, $start_at)
    {
        $allowrescheduling = (bool) Settings::get('allow_rescheduling');
        if (!$allowrescheduling) {
            throw new \WappointmentException('Appointment rescheduling is not allowed', 1);
        }
        if (is_array($edit_key)) {
            throw new \WappointmentException(__("Malformed parameter", 'wappointment'), 1);
        }
        $appointment = static::getAppointmentModel()::where('edit_key', $edit_key)->first();
        if (!apply_filters('wappointment_reschedule_allowed', $allowrescheduling, ['appointment' => $appointment])) {
            throw new \WappointmentException('Appointment rescheduling is not allowed', 1);
        }
        $oldAppointment = $appointment->replicate();
        if (empty($appointment)) {
            throw new \WappointmentException(__("Can't find appointment", 'wappointment'), 1);
        }
        if (!$appointment->canStillReschedule()) {
            throw new \WappointmentException(__("Can't reschedule appointment anymore", 'wappointment'), 1);
        }

        $result = $appointment->update(
            [
                'start_at' => static::unixToDb($start_at),
                'end_at' => static::unixToDb($start_at + $appointment->getFullDurationInSec()),
                'options' => $appointment->getIncrementedSequenceOptions()
            ]
        );
        if ($result) {
            static::appointmentModified($appointment, $oldAppointment);
            return $appointment->toArraySpecial();
        }
        return false;
    }

    protected static function appointmentModified($appointment, $oldAppointment)
    {
        (new Availability($appointment->staff_id))->regenerate();
        //send rescheduled email to client and admin
        $clientModel = $appointment->getClientModel();

        JobHelper::dispatch('AppointmentRescheduledEvent', [
            'appointment' => $appointment,
            'client' => $clientModel,
            'oldAppointment' => $oldAppointment
        ], $clientModel);
    }

    public static function unixToDb($unixTS)
    {
        return \Wappointment\ClassConnect\Carbon::createFromTimestamp($unixTS)->toDateTimeString();
    }



    public static function isLegacy()
    {
        return VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);
    }

    protected static function canBook($start_at, $end_at, $is_admin = false, $staff_id = null)
    {
        if ($is_admin === true) {
            return true;
        }

        //test that this is not a double booking scenario
        $start_at_str = static::unixToDb($start_at);
        $end_at_str = static::unixToDb($end_at);

        $queryAppointment = static::getAppointmentModel()::where('status', '>=', static::getAppointmentModel()::STATUS_AWAITING_CONFIRMATION);
        if (!static::isLegacy()) {
            $queryAppointment->where('staff_id', static::getStaffId($staff_id));
        }
        if ($queryAppointment
            ->where(function ($query) use ($start_at_str, $end_at_str) {
                $query->where(function ($query) use ($start_at_str, $end_at_str) {
                    $query->where('start_at', $start_at_str);
                    $query->where('end_at', $end_at_str);
                });

                /**
                 *     DB:  □□□□□□□□□□□■■■■■■■■■■■■■■■■■■■■■■■■□□□□□□□□□□
                 *     this: □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
                 *     Db contains this
                 */
                $query->orWhere(function ($query) use ($start_at_str, $end_at_str) {
                    $query->where('start_at', '<=', $start_at_str);
                    $query->where('end_at', '>=', $end_at_str);
                });
                /*
                 *     DB:   □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
                 *     This: □□□□□□□□□□□■■■■■■■■■■■■■■■■■■■■■■■■□□□□□□□□□□
                 *      this contains DB
                 */
                $query->orWhere(function ($query) use ($start_at_str, $end_at_str) {
                    $query->where('start_at', '>=', $start_at_str);
                    $query->where('end_at', '<=', $end_at_str);
                });
                /*
                 *     DB:   □□□□□□□□□□□■■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□
                 *     This: □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
                 *     DB intersects this
                 */
                $query->orWhere(function ($query) use ($start_at_str, $end_at_str) {
                    $query->where('start_at', '<', $start_at_str);
                    $query->where('end_at', '>', $start_at_str);
                });
                /*
                 *     DB:   □□□□□□□□□□□□□□□□□□■■■■■■■■■■■□□□□□□□□□□□□□□□□
                 *     This: □□□□□□□□□□□■■■■■■■■■■■■■□□□□□□□□□□□□□□□□□□□□□
                 */
                $query->orWhere(function ($query) use ($start_at_str, $end_at_str) {
                    $query->where('start_at', '<', $end_at_str);
                    $query->where('end_at', '>', $end_at_str);
                });
            })
            ->exists()
        ) {
            throw new \WappointmentException(__('Slot already booked', 'wappointment'), 1);
        }

        if ((new AvailabilityGetter(static::isLegacy() ? null : $staff_id, $start_at, $end_at))->isAvailable()) {
            return true;
        }

        throw new \WappointmentException(__('Slot not available', 'wappointment'), 1);
    }

    protected static function bookCreate($data, Client $client, $is_admin = false, $status = 0)
    {

        $dataReturned = static::create($data, $client, $status);
        static::afterBookEvents($dataReturned, $client, $data['staff_id'], $is_admin, $status);

        return $dataReturned;
    }

    public static function afterBookEvents($dataReturned, $client, $staff_id, $is_admin = false, $status = 0)
    {
        $dispatching = $status == static::getAppointmentModel()::STATUS_AWAITING_CONFIRMATION ? 'AppointmentBookedEvent' : 'AppointmentConfirmedEvent';
        //send pending email to client and admin
        JobHelper::dispatch($dispatching, $dataReturned, $client, $status);

        static::availabilityRefreshTrigger($staff_id, $is_admin);
    }

    public static function availabilityRefreshTrigger($staff_id, $is_admin = false)
    {
        //if availability has not been refreshed for a while we refresh it now otherwise we queue a job for it
        if (!defined('DISABLE_WP_CRON') || $is_admin === true) {
            //when web cron is disabled we need an immediate refresh of availability
            (new Availability($staff_id))->regenerate();
        } else {
            $availability = new Availability($staff_id);
            if ($availability->getLastRefresh() > 2) {
                $availability->regenerate();
            } else {
                $availability->incrementLastRefresh();

                \Wappointment\Services\Queue::tryPush(
                    'Wappointment\Jobs\AvailabilityRegenerate',
                    ['staff_id' => $staff_id],
                    'availability'
                );
            }
            //we immediately spawn a process to trigger availability regenerate in the back
            WPHelpers::cronTrigger();
        }
    }

    public static function tryCancel($request)
    {
        if (!(bool) Settings::get('allow_cancellation')) {
            throw new \WappointmentException('Appointment cancellation is not allowed', 1);
        }

        $edit_key = $request->input('appointmentkey');
        if (is_array($edit_key)) {
            throw new \WappointmentException(__("Malformed parameter", 'wappointment'), 1);
        }
        $appointment = static::getAppointmentModel()::where('edit_key', $edit_key)
            ->where('status', '>=', static::getAppointmentModel()::STATUS_AWAITING_CONFIRMATION)->first();

        if (empty($appointment)) {
            throw new \WappointmentException(__("Can't find appointment", 'wappointment'), 1);
        }
        if (!$appointment->canStillCancel()) {
            throw new \WappointmentException(__("Can't cancel appointment anymore", 'wappointment'), 1);
        }

        $already_cancelled = apply_filters('wappointment_external_cancel', false, $appointment, $request);

        return $already_cancelled !== false ? $already_cancelled : static::cancel($appointment);
    }

    public static function cancel(AppointmentModel $appointment, $client = null)
    {
        //used for credit return in addons
        apply_filters('wappointment_cancelled_appointment', $appointment);

        \Wappointment\Models\Log::canceledAppointment($appointment);

        $client = is_null($client) ? $appointment->getClientModel() : $client;

        //clearing charges for that appointment clearing order prices
        static::clearCharges([$appointment->id]);

        static::destroy($appointment);

        //trigger cancelled email to user and cancelled notification to admin
        JobHelper::dispatch('AppointmentCanceledEvent', [
            'appointment' => $appointment,
            'client' => $client
        ], $client);

        return true;
    }

    public static function destroy($appointment)
    {
        // not all appointments need to be destroyed
        if (!apply_filters('wappointment_appointment_is_group', false, $appointment)) {
            $appointment->incrementSequence();
            $appointment->destroy($appointment->id);
            (new Availability($appointment->getStaffId()))->regenerate();
        }
    }

    public static function clearCharges($appointment_ids = [], $charge_ids = [], $delete = false)
    {
        if (empty($charge_ids)) {
            $charge_ids = OrderPrice::select('id')->whereIn('appointment_id', $appointment_ids)->get()->map(function ($e) {
                return $e->id;
            })->toArray();
        }

        if (!empty($charge_ids)) {
            $query = OrderPrice::whereIn('id', $charge_ids);
            if ($delete) {
                $query->delete(); //when silent cancelling we delete the previous recorded charge because we just want one appointment per order
            } else {
                $query->update(['appointment_id' => null]); // otherwise when we cancel an appointment we keep the old charge, we just set the appointment to null
            }
        }
    }


    public static function silentCancel($appointment_ids = [], $charge_ids = [])
    {
        static::clearCharges($appointment_ids, $charge_ids, true);
        $appointments = AppointmentModel::whereIn('id', $appointment_ids)->get();
        foreach ($appointments as $appointment) {
            static::destroy($appointment);
        }
    }

    public static function getDefaultStatus($service)
    {
        if ($service->isSold()) {
            return static::getAppointmentModel()::STATUS_AWAITING_CONFIRMATION;
        }

        $default_status = ((int) Settings::get('approval_mode') === 1) ?
            static::getAppointmentModel()::STATUS_CONFIRMED : static::getAppointmentModel()::STATUS_AWAITING_CONFIRMATION;

        return apply_filters('wappointment_appointment_default_status', $default_status, $service);
    }
}
