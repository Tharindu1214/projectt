<?php

class Start_Customer {
    /* Create a new customer for given $data */
    public static function create(array $data) {
        $return_data = Start_Request::make_request("/customers", $data);
        return $return_data;
    }

    /* List all created customers */
    public static function all() {
        $return_data = Start_Request::make_request("/customers");
        return $return_data;
    }

    /* Retrieve an existing Customer */
    public static function get($customer_id) {
        $return_data = Start_Request::make_request("/customers/" . $customer_id);
        return $return_data;
    }

    /* Create a new customer for given $data */
    public static function update($customer_id, array $data) {
        $return_data = Start_Request::make_request("/customers/" . $customer_id, $data, 'PUT');
        return $return_data;
    }
}
