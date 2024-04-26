<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\Carbon;
use Wappointment\Achse\Math\Interval\Integer\IntegerInterval;
// @codingStandardsIgnoreFile
class Segment
{
    public function convertModel($collection)
    {
        $segments = [];
        foreach ($collection as $item) {
            $segments[] = [
                $item['start_at']->timestamp,
                $item['end_at']->timestamp
            ];
        }
        return $segments;
    }

    public function convertToIntervals($segments)
    {
        $intervals = [];
        foreach ($segments as $i => $segment) {
            try {
                $intervals[$i] = \Wappointment\Achse\Math\Interval\Integer\IntegerIntervalFactory::create(
                    (int) $segment[0],
                    false,
                    (int) $segment[1],
                    false
                );
            } catch (\Throwable $th) {
                // \Wappointment\Models\Log::data([
                //     'info' => "Error with the segment ",
                //     'start' => $this->debugDate($segment[0]),
                //     'end' => $this->debugDate($segment[1])
                // ]);
                // throw new \WappointmentException("Error with the segment " . $segment[0] . ' - ' . $segment[1], 1);
            }
        }
        return $intervals;
    }

    public function convertToArray($intervals)
    {
        $segments = [];
        foreach ($intervals as $i => $interval) {
            $segments[$i] = [
                $interval->getLeft()->getValue()->toInt(),
                $interval->getRight()->getValue()->toInt()
            ];
        }
        return array_values($segments);
    }

    public function convertToArrayDebugged($intervals)
    {
        $segments = [];
        foreach ($intervals as $i => $interval) {
            $segments[$i] = [
                $this->debugDate($interval->getLeft()->getValue()->toInt()),
                $this->debugDate($interval->getRight()->getValue()->toInt())
            ];
        }
        return $segments;
    }

    public function substract($mainSegments, $substractSegments, $debug = false)
    {

        $mainSegments = $this->convertToIntervals($mainSegments);

        $substractSegments = $this->convertToIntervals($substractSegments);

        foreach ($substractSegments as $substract) {
            if ($debug) {
                echo 'NEW SUBSTRACT' . "\n" . $this->debugSegment($substract) . "\n";
            }

            foreach ($mainSegments as $i => $main) {
                $mainSegments = $this->processMainSegment($mainSegments, $main, $substract, $i, $debug);
            }
        }

        if ($debug) {
            //dd($mainSegments);
            exit;
        }
        return $this->convertToArray(array_filter($mainSegments));
    }

    public function processMainSegment($mainSegments, $main, $substract, $i, $debug = false)
    {
        if (is_null($main)) {
            return $mainSegments;
        }
        if ($debug) {
            echo '--main segment [' . $i . '] total ' . count($mainSegments) . "\n";
        }
        if ($this->intersects($main, $substract)) {
            if ($debug) {
                echo  "----[substract] COLLIDES WITH \n" . '[main]' . $this->debugSegment($main) . "\n";
            }
            $mainSegments = $this->containsOrTouches($mainSegments, $main, $substract, $i, $debug);
            if ($debug) {
                dd($mainSegments);
                print_r($this->convertToArrayDebugged($mainSegments));
            }
        }
        return array_values($mainSegments);
    }

    public function containsOrTouches($mainSegments, $main, $substract, $i, $debug = false)
    {
        if ($substract->isContaining($main)) { // the whole section is unavailable
            if ($debug) {
                echo  "---- [substract] CONTAINS \n" . '[main]' . $this->debugSegment($main) . "\n";
            }
            $mainSegments[$i] = null;
        } else { // part of the section is must be available
            $diff = $main->difference($substract);
            $start_array = array_slice($mainSegments, 0, $i); // we just remove the section needed
            $end_array = array_slice($mainSegments, $i + 1);

            $mainSegments = array_merge($start_array, $diff, $end_array);

            if ($debug && !empty($diff)) {
                $this->debugSegment($diff);
            }
        }
        return $mainSegments;
    }

    public function debug($mainSegments)
    {
        foreach ($mainSegments as $main) {
            echo  '[new]' . $this->debugSegment($main) . "\n";
        }
        echo 'bye';
        exit;
    }

    private function intersects(IntegerInterval $interval, IntegerInterval $interval_2)
    {
        //return $interval->isColliding($interval_2);
        return ($interval->isColliding($interval_2)
            || $interval->isContaining($interval_2)
            || $interval_2->isContaining($interval));
    }

    /**
     * heavy function
     *
     * @param IntegerInterval $interval
     * @param IntegerInterval $interval_2
     * @return void
     */
    private function inContact(IntegerInterval $interval, IntegerInterval $interval_2)
    {

        return ($interval->isColliding($interval_2) || $interval->isFollowedBy($interval_2)
            || $interval->isContaining($interval_2) || $interval_2->isContaining($interval));
    }

    private function notInContact(IntegerInterval $interval, IntegerInterval $interval_2)
    {
        return ($interval->getLeft() < $interval_2->getRight() && $interval->getRight() < $interval_2->getLeft())
            ||
            ($interval->getLeft() > $interval_2->getRight() && $interval->getRight() > $interval_2->getLeft());
    }


    public function flatten($dirtySegments, $debug = false)
    {
        $dirtySegments = $this->convertToIntervals($dirtySegments);

        $ignorekey = [];

        for ($ki = 0; $ki < count($dirtySegments) + 1; $ki++) {
            $free = empty($dirtySegments[$ki]) ? '' : $dirtySegments[$ki]; //Todo error to trackdown
            if (isset($ignorekey[$ki]) || empty($free)) {
                continue;
            }

            for ($ki2 = 0; $ki2 < count($dirtySegments) + 1; $ki2++) {
                if ($ki == $ki2) {
                    continue;
                }
                $free2 = !empty($dirtySegments[$ki2]) ? $dirtySegments[$ki2] : ''; //Todo error to trackdown
                if (empty($free2)) {
                    continue;
                }

                if ($debug) {
                    echo '[' . $ki . ']free ' . $this->debugSegment($free) . "\n";
                    echo '[' . $ki2 . ']free2 ' . $this->debugSegment($free2) . "\n";
                    echo 'Union ' . $ki . ' ' . $ki2 . "?\n";
                }

                if (!$this->notInContact($free, $free2)) {
                    if ($this->inContact($free, $free2)) {
                        if ($free->isFollowedBy($free2)) {
                            $free2->getLeft()->asOpened();
                        }

                        $free = $dirtySegments[$ki] = $free->union($free2)[0];

                        if ($free2->isContaining($free)) {
                            $dirtySegments[$ki] = null;
                            $ignorekey[$ki] = true;
                        } else {
                            $dirtySegments[$ki2] = null;
                            $ignorekey[$ki2] = true;
                        }

                        if ($debug) {
                            echo "OK \n";
                        }
                    }
                } else {
                    if ($debug) {
                        echo "FAIL \n";
                    }
                }
                if ($debug) {
                    echo '--------------' . "\n";
                }
            }

            if ($debug) {
                echo 'Single ' . $ki . "\n";
                echo '+++++++++++++++' . "\n ";
            }
        }
        $cleanSegments = [];
        foreach ($dirtySegments as $key => $union) {
            if (is_null($union)) {
                continue;
            }
            //echo  '[' . $key . ']' . $this->debugSegment($union) . "\n";
            $cleanSegments[] = [$union->getLeft()->getValue()->toInt(), $union->getRight()->getValue()->toInt()];
        }
        return $cleanSegments;
    }

    public function debugSegment($segment)
    {
        return '[' .
            $this->debugDate($segment->getLeft()->getValue()->toInt()) . ',' .
            $this->debugDate($segment->getRight()->getValue()->toInt()) . ']';
    }

    public function debugDate($timestamp)
    {
        return Carbon::createFromTimestamp($timestamp)->format('Y-m-d\TH:i:00 T');
    }
}
