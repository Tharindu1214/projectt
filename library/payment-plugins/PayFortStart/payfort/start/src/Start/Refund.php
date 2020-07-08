<?php

class Start_Refund {
    /* refund charge */
    public static function create(array $data) {
        $return_data = Start_Request::make_request("/charges/" . $data["charge_id"] . "/refunds", $data);
        return $return_data;
    }

    /* get all refunds for charge */
    public static function all($charge_id) {
        $return_data = Start_Request::make_request("/charges/" . $charge_id . "/refunds");
        return $return_data;
    }
}
