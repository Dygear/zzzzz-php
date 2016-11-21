<?php
namespace namespaceNameHere;

/**
 * PHP version of defaultnamehere/zzzzz
 */

/**
 * The status of a user.
 */
class Status
{
    const STATUS_LEVEL_OFFLINE = 0;
    const STATUS_LEVEL_INVISIBLE = 1;
    const STATUS_LEVEL_IDLE = 2;
    const STATUS_LEVEL_ACTIVE = 3;

    public $value_map = [
        "offline" => STATUS_LEVEL_OFFLINE,
        "invisible" => STATUS_LEVEL_INVISIBLE,
        "idle" => STATUS_LEVEL_IDLE,
        "active" => STATUS_LEVEL_ACTIVE
    ];

    const STATUSES = "status webStatus messengerStatus fbAppStatus otherStatus";

    const STATUS_TYPE_MAP = [
        "status" => 4,
        "webStatus" => 3,
        "messengerStatus" => 2,
        "fbAppStatus" => 1,
        "otherStatus" => 0
    ];


    function __construct($time, $status_json)
    {
        $this->time = $time;
        $this->_status = [];
        $this->lat = false;

        $fields = json_decode($status_json);

        foreach (explode(' ', $this->STATUSES) as $status) {
            # Map status_name -> status value enum
            $this->_status[$status] = $this->value_map[$fields[$status]];
        }

        # Is this an entry for a last active time?
        if (isset($fields['lat'])) {
            $this->lat = true;
        }
    }

    public function is_online()
    {
        return $this->_status["status"] == STATUS_LEVEL_ACTIVE;
    }

    /**
     * Returns all status types which are currently active. If types other than "status" are active, "status" is necessarily also active.
     */
    public function all_active_status_types()
    {
        return array_filter(
            array_keys($this->_status),
            function($status_type) {
                return $this->_status[$status_type] >= STATUS_LEVEL_ACTIVE;
            }
        );
    }

    public function highest_active_status_type()
    {
        $active_status_types = $this->all_active_status_types();

        if (!$active_status_types) {
            return 0;
        }

        return max($this->STATUS_TYPE_MAP[$status]);
    }
}