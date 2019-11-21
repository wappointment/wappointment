<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\Carbon;
use Wappointment\Models\Status as MStatus;
use Wappointment\Models\Appointment;
use Wappointment\WP\Helpers as WPHelpers;

class Availability
{
    private $staff = [];
    public $daysOfTheWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    public $availabilities = [];
    public $start_at = false;
    public $end_at = false;

    /**
     * start_at & end_at utc timestamps
     */
    public function __construct($start_at = false, $end_at = false)
    {
        $this->start_at = $start_at;
        $this->end_at = $end_at;
        $this->load();
    }

    private function load()
    {
        foreach (Staff::getIds() as $staff_id) {
            $this->staff[$staff_id] = [
                'availability' => $this->getSection($staff_id),
                'timezone' => Settings::getStaff('timezone', $staff_id),
                'ra' => Settings::getStaff('regav', $staff_id),
            ];
        }
    }

    public function getStaff($staff_id)
    {
        return $this->staff[$staff_id]['availability'];
    }

    private function getSection($staff_id)
    {
        $availabilities = WPHelpers::getStaffOption('availability', $staff_id);
        if (!$this->start_at || !$this->end_at) {
            return $availabilities;
        }

        $new_availability = [];
        foreach ($availabilities as $availability) {
            if (
                ($availability[0] >= $this->start_at && $availability[0] < $this->end_at)
                || ($availability[1] > $this->start_at && $availability[1] <= $this->end_at)
            ) {
                $new_availability[] = $availability;
                if ($availability[0] >= $this->start_at && $availability[1] <= $this->end_at) {
                    $new_availability[] = $availability;
                } elseif ($availability[0] >= $this->start_at && $availability[1] >= $this->end_at) {
                    $new_availability[] = [$availability[0], $this->end_at];
                } elseif ($availability[0] <= $this->start_at && $availability[1] <= $this->end_at) {
                    $new_availability[] = [$this->start_at, $availability[1]];
                } elseif ($availability[0] <= $this->start_at && $availability[1] >= $this->end_at) {
                    $new_availability[] = [$this->start_at, $this->end_at];
                }
            }
        }

        return $new_availability;
    }

    public function isAvailable(Int $start_at, Int $end_at, $staff_id)
    {
        foreach ($this->staff[$staff_id]['availability'] as $segment) {
            if ($segment[0] <= $start_at && $segment[1] >= $end_at) {
                return true;
            }
        }
        return false;
    }

    /**
     * Regenerate all  by default ?
     *
     * @param boolean $staff_id
     * @param integer $days
     * @return void
     */
    public function regenerate($staff_id = false, $days = 60)
    {
        if ($staff_id === false) $staff_id = Settings::get('activeStaffId');

        // get regular avail and apply for x time from now
        $this->availabilities = $this->generateAvailabilityWithRA($staff_id, $days);

        // get busy and free time
        $today = Carbon::today();
        $end = $today->copy()->addDays($days);
        $start_at_string = $today->format(WAPPOINTMENT_DB_FORMAT);
        $end_at_string = $end->format(WAPPOINTMENT_DB_FORMAT);
        // dd($start_at_string);

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

        //dd($statusEvents->toArray());
        $statusBusy = $statusEvents->where('type', MStatus::TYPE_BUSY);

        $notrecurringBusy = $statusBusy->where('recur', MStatus::RECUR_NOT);
        $recurringBusy = $statusBusy->where('recur', '>', MStatus::RECUR_NOT)->all();

        $generatedPunctualEvents = $this->expandRecurring($recurringBusy, $end->timestamp);

        $recurringBusy = null;

        $newBusyStatuses = $notrecurringBusy->concat($generatedPunctualEvents);

        $segmentService = new Segment();

        $segmentCollection = $segmentService->convertModel($statusEvents->where('type', MStatus::TYPE_FREE)->all());
        // get clean free time to increase availability
        $frees = $segmentService->flatten($segmentCollection);

        //merge ra and extra free
        $test = array_merge($this->availabilities, $frees);
        $collection = \WappointmentLv::collect($test)->sortBy(0)->values()->all();


        $this->availabilities = $segmentService->flatten($collection);

        // substract busy times to availability
        $busy_status = $segmentService->convertModel($newBusyStatuses->all());
        $this->availabilities = $segmentService->substract($this->availabilities, $busy_status);

        // get appointments
        $appointments = Appointment::where('start_at', '>=', $start_at_string)
            ->where('status', '>=', Appointment::STATUS_AWAITING_CONFIRMATION)
            ->where('end_at', '<=', $end_at_string)
            ->get();

        $appointments = $segmentService->convertModel($appointments);

        // substract appointments to availability
        $this->availabilities = $segmentService->substract($this->availabilities, $appointments);

        $this->reOrder();

        WPHelpers::setStaffOption('since_last_refresh', 0, $staff_id);
        //save it to the db
        return WPHelpers::setStaffOption('availability', $this->availabilities, $staff_id);
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
        /* echo '<pre>';
        print_r($this->availabilities); */

        $this->availabilities = $newAvail;
        /* echo '<h1>newOrder</h1>';
        print_r($this->availabilities);
        dd('end'); */
    }

    private function generateAvailabilityWithRA($staff_id, $duration)
    {
        $staff_timezone = $this->staff[$staff_id]['timezone'];

        $dayNumber = 1;
        $now = Carbon::today($staff_timezone);
        $min_time = Carbon::now($staff_timezone)->addHours(Settings::get('hours_before_booking_allowed'));
        $availability = [];
        while ($dayNumber <= $duration) {
            $dayName = $this->daysOfTheWeek[$now->dayOfWeek];

            $dailyAvailability = $this->staff[$staff_id]['ra'][$dayName];
            //dd($this->staff[$staff_id]['ra']);
            foreach ($dailyAvailability as $dayTimeblock) {
                $start = (new Carbon($now->format(WAPPOINTMENT_DB_FORMAT . ':00'), $staff_timezone))->hour($dayTimeblock[0]);
                $end = (new Carbon($now->format(WAPPOINTMENT_DB_FORMAT . ':00'), $staff_timezone))->hour($dayTimeblock[1]);
                if ($min_time->gte($end)) continue;
                while ($min_time->gt($start)) {
                    $start = $start->addMinutes(Service::getObject()->duration);
                    //echo $min_time->format(WAPPOINTMENT_DB_FORMAT.':00') . ' > ' . $start->format(WAPPOINTMENT_DB_FORMAT.':00') . "\n";
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

    private function patch($staff_id, $start, $end, $busy = true)
    {
        // remove availability
        if ($busy) { } else { //add availability
        }
    }
}
