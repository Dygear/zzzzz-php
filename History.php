<?php
namespace namespaceNameHere;

/**
 * PHP version of defaultnamehere/zzzzz
 */

/**
 * Object representing the history for a particular user. History stored sparse.
 */
class Histroy
{
    const EPOCH_TIME = 1452556800;

    # Save the start time so computation time doesn't offset the measured time.
    public $START_TIME;

    public $activity;

    public function __construct($uid)
    {
        $this->START_TIME = time();

        foreach (file("log/{$uid}.txt") as $line) {
            $this->activity = $this->parse_status(trim($line));
        }
    }

    public function create_time_map($status_list)
    {
        $status_map = [];

        foreach ($status_list as $item) {
            $status_map[int($item["time"])] = $item["status"];
        }

        return $status_map;
    }

    public function parse_status($lines)
    {
        # A list of status objects.
        $this->activity = [];

        # Keep a list of seen times so we can avoid duplicates in the history
        $seen_times = [];

        foreach ($lines as $line) {
            list($time, $fields) = explode('|', $line);
            # Only add new times, preferring earlier records in the file. This is probably not optimal since later records seem to be more likely to be LATs, but oh well gotta break a few algorithmic contraints to make a BILLION dollars.
            if (!in_array($time, $seen_times)) {
                $seen_times[] = $time;
                $this->activity[] = Status::Status(int($time), $fields);
            }
        }

        return $activity;
    }

    /**
     * Get the HAST (highest active status type) for the user at a particular time by querying the sparse data.
     */
    public function get_status($time)
    {
        # Since we treat status data points as valid until the next data point, the "current" status is the one on the left of where the inserted time would go.
        return max(array_keys($this->activity));
    }

    /**
     * Turns a sparse time series into a dense one, with number of seconds per bucket specified by resolution.
     * If a status_type (status, webStatus, messengerStatus etc.) is given, returns a generator of the status level (online, offline, idle) for that status type.
     */
    public function normalised(int $max_time_back_seconds = null, int $resolution = 60, $status_type = null)
    {
        if ($max_time_back_seconds === null) {
            $start_time = $this::EPOCH_TIME;
        } else {
            $start_time = $this->START_TIME - $max_time_back_seconds;
        }

        foreach (range($start_time, $this->START_TIME, $resolution) as $tick)
        {
            $status_obj = $this->get_status($tick);

            if ($status_type === null) {
                yield $this->status_obj->highest_active_status_type();
            } else {
                yield $this->status_obj->_status[$status_type];
            }
        }
    }
}