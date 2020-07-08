<?php

class Start_Token {

    /*
     * Create card token.
     *
     * !!! This method should not be used in production !!!
     * It's here only for testing purposes!
     * In production you should use our JavaScript
     * library Beautiful.js: https://docs.start.payfort.com/guides/beautiful.js/
     * */

    public static function create(array $data) {
        return Start_Request::make_request("/tokens", $data);
    }
}
