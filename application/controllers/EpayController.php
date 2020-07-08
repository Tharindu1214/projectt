<?php

require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/epayco/autoload.php';
class EpayController extends PaymentController
{
    private $keyName="epayco";

    private $error = false;

    private $paymentSettings = false;

    private $currencyCode = 'usd';

    public function charge($orderId='')
    {
        /*$epayco = new Epayco\Epayco(array(
        "apiKey" => "005fc56edd19addea4f4a86e03c50376",
        "privateKey" => "84a1d3e0db7f5d73682288aba273e86f",
        "lenguage" => "ES",
        "test" => true
        ));

        $token = $epayco->token->create(array(
        "card[number]" => "4575623182290326",
        "card[exp_year]" => "2017",
        "card[exp_month]" => "07",
        "card[cvc]" => "123"
        ));


        $client = $epayco->customer->create(array(
            "token_card" => $token->id,
            "name" => "Joe Doe",
            "email" => "joe" . rand() . "@payco.co",
            "phone" => "3005234321",
            "default" => true
        ));




        $pay = $epayco->charge->create(array(
        "token_card" => $token->id,
        "customer_id" => $client->data->customerId,
        "doc_type" => "CC",
        "doc_number" => "1035851980",
        "name" => "John",
        "last_name" => "Doe",
        "email" => "example@email.com",
        "bill" => "OR-1234",
        "description" => "Test Payment",
        "value" => "116000",
        "tax" => "16000",
        "tax_base" => "100000",
        "currency" => "COP",
        "dues" => "12"
        )); */

        //CommonHelper::printArray($pay);  die;

        $this->_template->render();
    }


    public function response()
    {
        mail("pooja.rani@ablysoft.com", "Test Response", serialize($_POST));
        $this->set('data', $_POST);
        var_dump($_POST);
        die;
    }


    public function confirm()
    {
        mail("pooja.rani@ablysoft.com", "Test Response", serialize($_POST));
        $this->set('data', $_POST);
        $this->_template->render();
    }


    public function checkCardType()
    {
        $post = FatApp::getPostedData();
        $res=ValidateElement::ccNumber($post['cc']);
        echo json_encode($res);
        exit;
    }

    private function formatPayableAmount($amount = null)
    {
        if ($amount == null) {
            return false;
        }
        $amount = number_format($amount, 2, '.', '');
        return $amount*100;
    }

    private function getPaymentSettings()
    {
        $pmObj=new PaymentSettings($this->keyName);
        return $pmObj->getPaymentSettings();
    }

    private function getPaymentForm($orderId)
    {
        $frm = new Form('frmPaymentForm', array('id'=>'frmPaymentForm','action'=>CommonHelper::generateUrl('StripePay', 'charge', array($orderId)), 'class' =>"form form--normal"));
        $frm->addRequiredField(Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER', $this->siteLangId), 'cc_number');
        $frm->addRequiredField(Labels::getLabel('LBL_CARD_HOLDER_NAME', $this->siteLangId), 'cc_owner');
        $data['months'] = applicationConstants::getMonthsArr($this->siteLangId);
        $today = getdate();
        $data['year_expire'] = array();
        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $data['year_expire'][strftime('%Y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
        }
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_MONTH', $this->siteLangId), 'cc_expire_date_month', $data['months'], '', array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_YEAR', $this->siteLangId), 'cc_expire_date_year', $data['year_expire'], '', array(), '');
        $frm->addPasswordField(Labels::getLabel('LBL_CVV_SECURITY_CODE', $this->siteLangId), 'cc_cvv')->requirements()->setRequired();
        /* $frm->addCheckBox(Labels::getLabel('LBL_SAVE_THIS_CARD_FOR_FASTER_CHECKOUT',$this->siteLangId), 'cc_save_card','1'); */
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId));

        return $frm;
    }

    private function doPayment($payment_amount = null, $orderInfo = null)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $this->paymentSettings=$this->getPaymentSettings();
        if ($payment_amount == null || !$this->paymentSettings || $orderInfo['id'] == null) {
            return false;
        }
        $checkPayment = false;
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            try {
                if (!isset($_POST['stripeToken'])) {
                    throw new Exception("The Stripe Token was not generated correctly");
                } else {
                    $stripe = array(
                    'secret_key'      => $this->paymentSettings['privateKey'],
                    'publishable_key' => $this->paymentSettings['publishableKey']
                    );
                    if (!empty(trim($this->paymentSettings['privateKey'])) && !empty(trim($this->paymentSettings['publishableKey']))) {
                        \Stripe\Stripe::setApiKey($stripe['secret_key']);
                    }

                    $customer = \Stripe\Customer::create(
                        array(
                          "email" => $orderInfo['customer_email'],
                          "source" => $_POST['stripeToken'],
                        )
                    );
                    $charge = \Stripe\Charge::create(
                        array(
                        /* 'source'     => $_POST['stripeToken'], */
                        "customer" => $customer->id,
                        'amount'   => $payment_amount,
                        'currency' => $this->currencyCode,
                        )
                    );
                    $charge = $charge->__toArray();

                    if (isset($charge['status'])) {
                        if (strtolower($charge['status']) == 'succeeded') {
                            $message = '';
                            $message .= 'Id: '.(string)$charge['id']. "&";
                            $message .= 'Object: '.(string)$charge['object']. "&";
                            $message .= 'Amount: '.(string)$charge['amount']. "&";
                            $message .= 'Amount Refunded: '.(string)$charge['amount_refunded']. "&";
                            $message .= 'Application Fee: '.(string)$charge['application_fee']. "&";
                            $message .= 'Balance Transaction: '.(string)$charge['balance_transaction']. "&";
                            $message .= 'Captured: '.(string)$charge['captured']. "&";
                            $message .= 'Created: '.(string)$charge['created']. "&";
                            $message .= 'Currency: '.(string)$charge['currency']. "&";
                            $message .= 'Customer: '.(string)$charge['customer']. "&";
                            $message .= 'Description: '.(string)$charge['description']. "&";
                            $message .= 'Destination: '.(string)$charge['destination']. "&";
                            $message .= 'Dispute: '.(string)$charge['dispute']. "&";
                            $message .= 'Failure Code: '.(string)$charge['failure_code']. "&";
                            $message .= 'Failure Message: '.(string)$charge['failure_message']. "&";
                            $message .= 'Invoice: '.(string)$charge['invoice']. "&";
                            $message .= 'Livemode: '.(string)$charge['livemode']. "&";
                            $message .= 'Paid: '.(string)$charge['paid']. "&";
                            $message .= 'Receipt Email: '.(string)$charge['receipt_email']. "&";
                            $message .= 'Receipt Number: '.(string)$charge['receipt_number']. "&";
                            $message .= 'Refunded: '.(string)$charge['refunded']. "&";
                            $message .= 'Shipping: '.(string)$charge['shipping']. "&";
                            $message .= 'Statement Descriptor: '.(string)$charge['statement_descriptor']. "&";
                            $message .= 'Status: '.(string)$charge['status']. "&";
                            /* Recording Payment in DB */
                            $orderPaymentObj=new OrderPayment($orderInfo['id']);
                            $orderPaymentObj->addOrderPayment($this->paymentSettings["pmethod_name"], $charge['id'], ($payment_amount/100), Labels::getLabel("MSG_Received_Payment", $this->siteLangId), $message);
                            /* End Recording Payment in DB */
                            $checkPayment = true;
                            FatApp::redirectUser(CommonHelper::generateUrl('custom', 'paymentSuccess', array($orderInfo['id'])));
                        } else {
                            $orderPaymentObj->addOrderPaymentComments($message);
                            FatApp::redirectUser(CommonHelper::generateUrl('custom', 'paymentFailed'));
                        }
                    }
                }
            } catch (Exception $e) {
                $this->error = $e->getMessage();
            }
        }
        return $checkPayment;
    }
}
