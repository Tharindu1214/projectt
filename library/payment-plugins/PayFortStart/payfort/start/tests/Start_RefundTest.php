<?php

class Start_RefundTest extends \PHPUnit_Framework_TestCase
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
            "description" => "Charge for test@example.com"
        );

        $this->charge = Start_Charge::create($data);

        $this->assertEquals($this->charge["state"], "captured");
    }

    function testRefundWithoutParams()
    {
        $refund = Start_Refund::create(array(
            "charge_id" => $this->charge["id"]
        ));

        $this->assertEquals($refund["state"], "succeeded");
        $this->assertEquals($refund["amount"], 1050);
        $this->assertEquals($refund["reason"], "");
        $this->assertEquals($refund["charge"]["state"], "refunded");
        $this->assertEquals($refund["charge"]["refunded_amount"], 1050);
    }

    function testRefundWithParams()
    {
        $refund = Start_Refund::create(array(
            "charge_id" => $this->charge["id"],
            "amount"    => 1000,
            "reason"    => "requested by customer"
        ));

        $this->assertEquals($refund["state"], "succeeded");
        $this->assertEquals($refund["amount"], 1000);
        $this->assertEquals($refund["reason"], "requested by customer");

        $this->assertEquals($refund["charge"]["state"], "partially_refunded");
        $this->assertEquals($refund["charge"]["refunded_amount"], 1000);
        $this->assertEquals($refund["charge"]["captured_amount"], 50);
    }

    function testRefundList()
    {
        $refund_1 = Start_Refund::create(array(
            "charge_id" => $this->charge["id"],
            "amount" => 100
        ));

        $refund_2 = Start_Refund::create(array(
            "charge_id" => $this->charge["id"],
            "amount" => 100
        ));

        $list = Start_Refund::all($this->charge["id"]);

        $this->assertCount(2, $list["refunds"]);
    }
}
