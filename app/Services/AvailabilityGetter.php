<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;

class AvailabilityGetter
{
    private $staff = [];
    public $start_at = false;
    public $end_at = false;

    /**
     * start_at & end_at utc timestamps
     */
    public function __construct($start_at = false, $end_at = false)
    {
        $this->start_at = $start_at;
        $this->end_at = $end_at;

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

        //Unused
        $new_availability = [];
        foreach ($availabilities as $availability) {
            if (($availability[0] >= $this->start_at && $availability[0] < $this->end_at)
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

    public function isAvailable($start_at, $end_at, $staff_id)
    {
        foreach ($this->staff[$staff_id]['availability'] as $segment) {
            if ($segment[0] <= $start_at && $segment[1] >= $end_at) {
                return true;
            }
        }
        return false;
    }
}
