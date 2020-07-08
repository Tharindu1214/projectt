<?php
class ValidateElement extends FatUtility
{
    // const PHONE_REGEX = '^(\+\d{1,2}\s)?\(?\d{3}\)?[\s#-]\d{3}[\s#-]\d{4}$';
    const PHONE_NO_FORMAT = '';
    const PHONE_NO_LENGTH = 14;
    const PHONE_REGEX = '^(?!0+$)[0-9]{1,14}$';
    const ZIP_REGEX = '^[a-zA-Z0-9]+$';
    const CITY_NAME_REGEX = '^([^0-9]*)$';
    const PASSWORD_REGEX = '^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%-_]{8,15}$';
    const USERNAME_REGEX = '^[a-zA-Z0-9]{3,30}$';
    const VISA_REGEX = '^4';
    const MASTER_REGEX = '^5[1-5]';
    const AMEX_REGEX = '^3[47]';
    const DINERS_CLUB_REGEX = '^3(?:0[0-5]|[68])';
    const DISCOVER_REGEX = '^6(?:011|5)';
    const JCB_REGEX = '^(?:2131|1800|35\d{3})';
    const TIME_REGEX = '^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$';
    /*const PHONE_FORMATS = [
        '123-456-7890',
        '(123) 456-7890',
        '123 456 7890',
        '123#456#7890',
        '+91 123 456 7890',
    ];*/


    public static function phone($string = '')
    {
        if (strlen($string) < 10) {
            return false;
        }

        if (!preg_match('/'.static::PHONE_REGEX.'/', $string)) {
            return false;
        }
        return true;
    }

    public static function convertPhone($string)
    {
        /*// Allow only Digits, remove all other characters.
        $number = preg_replace("/[^\d]/", "", $string);

        // get number length.
        $length = strlen($number);

        // if number = 10
        if ($length == 10) {
            $number = preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "($1) $2-$3", $number);
        }

        return $number;*/
        return $string;
    }

    public static function password($string = '')
    {
        if (strlen($string) < 1) {
            return false;
        }

        if (!preg_match('/'.static::PASSWORD_REGEX.'/', $string)) {
            return false;
        }
        return true;
    }

    public static function username($string = '')
    {
        if (strlen($string) < 3) {
            return false;
        }
        if (!preg_match('/'.static::USERNAME_REGEX.'/', $string)) {
            return false;
        }
        return true;
    }

    public static function ccNumber($cardNumber)
    {
        $cardNumber = preg_replace('/\D/', '', ($cardNumber));
        $len = strlen($cardNumber);
        $result=array();
        if ($len > 16) {
            $result['card_type']='Invalid';
            return $result;
        }
        switch ($cardNumber) {
            case 0:
                $result['card_type']='';
                break;
            case (preg_match('/'.static::VISA_REGEX.'/', $cardNumber) >= 1):
                $result['card_type']='VISA';
                break;
            case (preg_match('/'.static::MASTER_REGEX.'/', $cardNumber) >= 1):
                $result['card_type']='MASTER';
                break;
            case (preg_match('/'.static::AMEX_REGEX.'/', $cardNumber) >= 1):
                $result['card_type']='AMEX';
                break;
            case (preg_match('/'.static::DINERS_CLUB_REGEX.'/', $cardNumber) >= 1):
                $result['card_type']='DINERS_CLUB';
                break;
            case (preg_match('/'.static::DISCOVER_REGEX.'/', $cardNumber) >= 1):
                $result['card_type']='DISCOVER';
                break;
            case (preg_match('/'.static::JCB_REGEX.'/', $cardNumber) >= 1):
                $result['card_type']='JCB';
                break;
            default:
                $result['card_type']='';
                break;
        }
        return $result;
    }
}
