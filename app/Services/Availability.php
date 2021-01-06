<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\Carbon;
use Wappointment\Models\Status as MStatus;
use Wappointment\Models\Appointment;
use Wappointment\WP\Helpers as WPHelpers;

class Availability
{
    public $daysOfTheWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    public $availabilities = [];
    private $staff_id = false;
    private $days = 0;
    private $timezone = '';
    private $regav = [];
    private $segmentService = null;

    public function __construct($staff_id = false)
    {
        $this->segmentService = new Segment();

        if ($staff_id === false) {
            $staff_id = Settings::get('activeStaffId');
        }
        $this->staff_id = $staff_id;
        $this->timezone = Settings::getStaff('timezone', $this->staff_id);
        $this->regav = Settings::getStaff('regav', $this->staff_id);
        $this->days = (int) Settings::getStaff('availaible_booking_days', $this->staff_id);
    }
    /**
     * Regenerate all  by default ?
     *
     * @param boolean $staff_id
     * @return void
     */
    public function regenerate()
    {

        // get regular avail and apply for x time from now
        $this->availabilities = $this->generateAvailabilityWithRA();

        // get busy and free time
        $today = Carbon::today();

        $end = $today->copy()->addDays($this->days);
        $start_at_string = $today->format(WAPPOINTMENT_DB_FORMAT);
        $end_at_string = $end->format(WAPPOINTMENT_DB_FORMAT);

        $statusEvents = MStatus::where('muted', '<', 1)
            ->where(function ($query) use ($end_at_string, $start_at_string) {
                $query->where(function ($qry) use ($end_at_string, $start_at_string) {
                    $qry->where('start_at', '<', $end_at_string)
                        ->where('end_at', '>', $start_at_string);
                })
                    ->orWhere('recur', '>', MStatus::RECUR_NOT);
            })
            ->orderBy('start_at')
            ->get();

        $statusBusy = $statusEvents->where('type', MStatus::TYPE_BUSY);

        $notrecurringBusy = $statusBusy->where('recur', MStatus::RECUR_NOT);
        $recurringBusy = $statusBusy->where('recur', '>', MStatus::RECUR_NOT)->all();

        $generatedPunctualEvents = $this->expandRecurring($recurringBusy, $end->timestamp);

        $recurringBusy = null;

        $newBusyStatuses = $notrecurringBusy->concat($generatedPunctualEvents);

        $segmentCollection = $this->segmentService->convertModel($statusEvents->where('type', MStatus::TYPE_FREE)->all());
        // get clean free time to increase availability
        $frees = $this->segmentService->flatten($segmentCollection);

        //merge ra and extra free
        $test = array_merge($this->availabilities, $frees);
        $collection = \WappointmentLv::collect($test)->sortBy(0)->values()->all();

        $this->availabilities = $this->segmentService->flatten($collection);

        // get appointments
        $appointments = Appointment::where('start_at', '>=', $start_at_string)
            ->where('status', '>=', Appointment::STATUS_AWAITING_CONFIRMATION)
            ->where('end_at', '<=', $end_at_string)
            ->get();

        $appointments = $this->segmentService->convertModel($appointments);

        // substract appointments to availability
        $this->availabilities = $this->segmentService->substract($this->availabilities, $appointments);

        // substract busy times to availability
        $busy_status = $this->segmentService->convertModel($newBusyStatuses->all());
        $this->availabilities = $this->segmentService->substract($this->availabilities, $busy_status);

        //$this->availabilities = $segmentService->flatten($this->availabilities);
        $this->reOrder();

        WPHelpers::setStaffOption('since_last_refresh', 0, $this->staff_id);
        //save it to the db
        return WPHelpers::setStaffOption('availability', $this->availabilities, $this->staff_id);
    }

    private function generateAvailabilityWithRA()
    {

        $dayNumber = 1;
        $now = Carbon::today($this->timezone);
        $min_time = Carbon::now($this->timezone)->addHours(Settings::get('hours_before_booking_allowed'));
        $availability = [];
        while ($dayNumber <= $this->days) {
            $dayName = $this->daysOfTheWeek[$now->dayOfWeek];

            $dailyAvailability = $this->regav[$dayName];

            foreach ($dailyAvailability as $dayTimeblock) {
                $start = (new Carbon($now->format(WAPPOINTMENT_DB_FORMAT . ':00'), $this->timezone));
                $end = (new Carbon($now->format(WAPPOINTMENT_DB_FORMAT . ':00'), $this->timezone));

                $unit_added = !empty($this->regav['precise']) ? 'addMinutes' : 'addHours'; //detect precision mode
                $start->$unit_added($dayTimeblock[0]);
                $end->$unit_added($dayTimeblock[1]);

                if ($min_time->gte($end)) {
                    continue;
                }
                while ($min_time->gt($start)) {
                    $start = $start->addMinutes(Service::getObject()->duration);
                    continue;
                }

                $availability[] = [
                    $start->timestamp,
                    $end->timestamp
                ];
            }
            $now->addDay();
            $dayNumber++;
        }
        return $availability;
    }

    private function expandRecurring($recurringBusy, $end)
    {
        return Status::expand($recurringBusy, $end);
    }

    private function reOrder()
    {
        $newOrder = [];
        foreach ($this->availabilities as $key => $avail) {
            $newOrder[$avail[0]] = $key;
        }
        $newAvail = [];
        foreach ($newOrder as $index) {
            $newAvail[] = $this->availabilities[$index];
        }

        $this->availabilities = $newAvail;
    }

    private function patch($staff_id, $start, $end, $busy = true)
    {
        // remove availability
        if ($busy) {
        } else { //add availability
        }
    }
}
