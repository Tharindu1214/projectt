<?php

class Start_CaptureTest extends \PHPUnit_Framework_TestCase
{
    protected $cardForSuccess = array(
        "number" => "4242424242424242",
        "exp_month" => 11,
        "exp_year" => 2020,
        "cvc" => "123"
    );

    private $charge;

    public static function setUpBeforeClass()
    {
        Start::$fallback = false;
        Start::setApiKey('test_sec_k_2b99b969196bece8fa7fd');

        if (getenv("CURL") == "1") {
            Start::$useCurl = true;
        }
    }

    function setUp()
    {
        $data = array(
            "amount" => 1050,
            "currency" => "usd",
            "email" => "ahmed@example.com",
            "card" => $this->cardForSuccess,
            "description" => "Charge for test@example.com",
            "capture" => false
        );

        $this->charge = Start_Charge::create($data);

        $this->assertEquals($this->charge["state"], "authorized");
    }

    function testCaptureWithoutParams()
    {
        $capture = Start_Capture::create(array(
            "charge_id" => $this->charge["id"]
        ));

        $this->assertEquals($capture["state"], "captured");
        $this->assertEquals($capture["captured_amount"], 1050);
    }

    function testCaptureWithParams()
    {
        $capture = Start_Capture::create(array(
            "charge_id" => $this->charge["id"],
            "amount"    => 1000
        ));

        $this->assertEquals($capture["state"], "captured");
        $this->assertEquals($capture["captured_amount"], 1000);
    }
}
