<?php

class Start_Capture {
    /* capture charge */
    public static function create(array $data) {
        $return_data = Start_Request::make_request("/charges/" . $data["charge_id"] . "/capture", $data);
        return $return_data;
    }
}
