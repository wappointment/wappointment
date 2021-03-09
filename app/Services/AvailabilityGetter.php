<?php

namespace Wappointment\Services;

use Wappointment\WP\StaffLegacy;

class AvailabilityGetter
{
    public $start_at = false;
    public $end_at = false;
    private $isLegacy = true;
    private $availabilityProcessed = [];

    /**
     * start_at & end_at utc timestamps
     */
    public function __construct($staff = null, $start_at = false, $end_at = false)
    {
        $this->start_at = $start_at;
        $this->end_at = $end_at;
        if (!empty($staff)) {
            $this->isLegacy = false;
            $this->selectedStaff = $staff;
            $this->availabilityProcessed = $this->getSection();
        } else {
            $this->selectedStaff = new StaffLegacy;
            $this->availabilityProcessed = $this->getSection();
        }
    }


    protected function getAvail()
    {
        return $this->isLegacy ? $this->selectedStaff->getAvailability() : $this->selectedStaff->availability;
    }

    public function getSection()
    {
        $availabilities = $this->getAvail();
        if (!$this->start_at) { //no section specified, we take it all
            return $availabilities;
        }

        //Unused
        $new_availability = [];
        foreach ($availabilities as $availability) {
            if ($this->isIntersecting($availability)) {
                if ($this->isIncluded($availability)) {
                    $new_availability[] = $availability;
                } elseif ($this->isEndingLater($availability)) {
                    $new_availability[] = [$availability[0], $this->end_at];
                } elseif ($this->isStartingEarlier($availabilities)) {
                    $new_availability[] = [$this->start_at, $availability[1]];
                } elseif ($this->isStartingEarlierAndEndingLater($availability)) {
                    $new_availability[] = [$this->start_at, $this->end_at];
                }
            }
        }

        return $new_availability;
    }

    protected function isStartingEarlierAndEndingLater($availability)
    {
        return $availability[0] <= $this->start_at && $availability[1] >= $this->end_at;
    }

    protected function isStartingEarlier($availability)
    {
        return $availability[0] <= $this->start_at && $availability[1] <= $this->end_at;
    }

    protected function isEndingLater($availability)
    {
        return $availability[0] >= $this->start_at && $availability[1] >= $this->end_at;
    }
    /**
     * Start section >= start limit && start section < end limit
     * OR
     * End section > start limit && end section <= end limit
     */
    protected function isIntersecting($availability)
    {
        return ($availability[0] >= $this->start_at && $availability[0] < $this->end_at)
            || ($availability[1] > $this->start_at && $availability[1] <= $this->end_at);
    }

    /**
     * Start section >= start limit && end section <= end limit
     */
    protected function isIncluded($availability)
    {
        return $availability[0] >= $this->start_at && $availability[1] <= $this->end_at;
    }

    public function isAvailable()
    {
        return !empty($this->availabilityProcessed);
    }
}
