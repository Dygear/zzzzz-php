<?php
namespace namespaceNameHere;

/**
 * PHP version of defaultnamehere/zzzzz
 */

class Graph
{
    const LOG_DATA_DIR = 'log';
    const CSV_OUTPUT_DIR = 'generated_graphs/csv';

    # LOL TIMEZONES TOO HARD
    const UTC_OFFSET = 11;
    const ONE_DAY_SECONDS = 60 * 60 * 24;

    public function __construct() {
        if (!file_exists(self::CSV_OUTPUT_DIR)) {
            mkdir(self::CSV_OUTPUT_DIR);
        }
    }

    public function to_csv($uid, $start_time, $end_time)
    {
        # The user's history.
        $status_history = new History($uid);

        # Generate a CSV from the multiple linear timeseries
        $data = 'time,' . implode(',', explode(' ', Status::STATUSES) . PHP_EOL);

        # TODO preprocess sort and splice this instead of linear search.
        # UPDATE nahhhh I think I'll just commit it to github ;>_>
        foreach ($status_history->activity as $data_point) {
            if ($start_time < $data_point['time'] AND $data_point['time'] < $end_time) {
                # Sample line: <time>,3,1,1,1,1
                foreach (explode(' ', Status::STATUSES) as $status_type) {
                    $join = $data_point->_status[$status_type];
                }
                $data .= $data_point['time'] . ',' . implode(',', $join) . PHP_EOL;
            }
        }

        file_put_contents("generated_graphs/csv/{$uid}.csv", $data);
    }


    public function generate_all_csvs($start_time, $end_time)
    {
        foreach (scandir($this->LOG_DATA_DIR) as $filename) {
            echo $filename . PHP_EOL;
            $uid = explode('.', $filename)[0];
            $this->to_csv($uid, $start_time, $end_time);
        }
    }


    public function Grapher()
    {
        $now = time();
        # Graph the last three days by default, but you can do ~whatever you believe you cannnnn~
        $this->generate_all_csvs($now - (3 * $this::ONE_DAY_SECONDS), $now);
    }
}