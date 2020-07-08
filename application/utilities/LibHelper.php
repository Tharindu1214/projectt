<?php
class LibHelper extends FatUtility
{
    public static function dieJsonError($message)
    {
        if (true ===  MOBILE_APP_API_CALL) {
            $message = strip_tags($message);
        }
        FatUtility::dieJsonError($message);
    }

    public static function dieWithError($message)
    {
        FatUtility::dieWithError($message);
    }
}
