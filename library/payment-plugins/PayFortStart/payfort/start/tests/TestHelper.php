<?php
class TestHelper
{
    protected static $open_api_key = "test_open_k_fcd2be7651c659bbdfc2";

    public static function createToken($card)
    {
        $api_key_to_restore = Start::getApiKey();

        Start::setApiKey(self::$open_api_key);

        $token = Start_Token::create($card);

        Start::setApiKey($api_key_to_restore);

        return $token;
    }
}
