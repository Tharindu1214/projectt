<?php
class Start_Request {
    public static function make_request($path, $data = array(), $method = '') {

        $url = Start::getBaseURL() . $path;

        try {
            return Start::$useCurl ? Start_Net_Curl::make_request($url, $data, $method) : Start_Net_Stream::make_request($url, $data, $method);
        } catch (Start_Error_SSLError $e) {
            // fallback to opposite method
            if (Start::$fallback) {
                return Start::$useCurl ? Start_Net_Stream::make_request($url, $data, $method) : Start_Net_Curl::make_request($url, $data, $method);
            } else {
                throw $e;
            }
        }
    }
}
