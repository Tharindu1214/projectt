<?php
require_once 'TestHelper.php';

class Start_ChargeTest extends \PHPUnit_Framework_TestCase
{
    protected $cardForSuccess = array(
        "number" => "4242424242424242",
        "exp_month" => 11,
        "exp_year" => 2020,
        "cvc" => "123"
    );

    protected $cardForFailure = array(
        "number" => "4000000000000002",
        "exp_month" => 11,
        "exp_year" => 2020,
        "cvc" => "123"
    );

    public static function setUpBeforeClass()
    {
        Start::$fallback = false;
        Start::setApiKey('test_sec_k_2b99b969196bece8fa7fd');

        if (getenv("CURL") == "1") {
            Start::$useCurl = true;
        }
    }

    function testCreateSuccess()
    {
        /* token should be created via javascipt library beautiful.js */
        /* more info here: https://docs.start.payfort.com/guides/beautiful.js/ */
        $token = TestHelper::createtoken($this->cardForSuccess);

        $data = array(
            "amount" => 1050,
            "currency" => "usd",
            "email" => "ahmed@example.com",
            "card" => $token["id"],
            "description" => "Charge for test@example.com"
        );

        $charge = Start_Charge::create($data);

        $this->assertEquals($charge["state"], "captured");

        return $charge;
    }

    /**
     * @depends testCreateSuccess
     */
    function testLoadCharge($existing_charge)
    {
        $charge = Start_Charge::get($existing_charge["id"]);

        $this->assertEquals($charge["state"], "captured");
    }

    function testInvalidData()
    {
        $data = array(
            "amount" => 1050,
            "currency" => "usd",
            "email" => "ahmed@example.com",
        );

        try {
            $result = Start_Charge::create($data);
        } catch (Start_Error_Request $e) {
            $this->assertSame('unprocessable_entity', $e->getErrorCode());
            $this->assertSame('Request params are invalid.', $e->getMessage());
        }
    }

    function testCreateFailure()
    {
        /* token should be created via javascipt library beautiful.js */
        /* more info here: https://docs.start.payfort.com/guides/beautiful.js/ */
        $token = TestHelper::createtoken($this->cardForFailure);

        $data = array(
            "amount" => 1050,
            "currency" => "usd",
            "email" => "ahmed@example.com",
            "card" => $token["id"],
            "description" => "Charge for test@example.com"
        );
        try {
            $result = Start_Charge::create($data);
        } catch (Start_Error_Banking $e) {
            $this->assertSame('card_declined', $e->getErrorCode());
            $this->assertSame('Charge was declined.', $e->getMessage());
        }
    }

    function testMetadata()
    {
        /* token should be created via javascipt library beautiful.js */
        /* more info here: https://docs.start.payfort.com/guides/beautiful.js/ */
        $token = TestHelper::createtoken($this->cardForSuccess);

        $data = array(
            "amount" => 1050,
            "currency" => "usd",
            "email" => "ahmed@example.com",
            "card" => $token["id"],
            "description" => "Charge for test@example.com",
            "metadata" => array(
                "reference_id" => "1234567890",
                "tag" => "new"
            )
        );

        $result = Start_Charge::create($data);

        $this->assertEquals($result["metadata"], array(
            "reference_id" => "1234567890",
            "tag" => "new"
        ));
    }

    function testList()
    {
        $all = Start_Charge::all();

        $this->assertNotEmpty($all["charges"]);
    }

}
