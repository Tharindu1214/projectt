<?php
require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/ccavenue/Crypto.php';
class CcavenuePayController extends PaymentController
{
    private $keyName="Ccavenue";

    public function charge($orderId = '')
    {
        if (empty($orderId)) {
            FatUtility::exitWIthErrorCode(404);
        }
        $pmObj = new PaymentSettings($this->keyName);

        if (!$paymentSettings = $pmObj->getPaymentSettings()) {
            Message::addErrorMessage($pmObj->getError());
            CommonHelper::redirectUserReferer();
        }
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!$orderInfo['id']) {
            FatUtility::exitWIthErrorCode(404);
        } elseif ($orderInfo && $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $frm = $this->getPaymentForm($orderId);
            $this->set('frm', $frm);
        } else {
            $this->set('error', Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }

        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);
        $this->set('exculdeMainHeaderDiv', true);
        $this->_template->addCss('css/payment.css');
        $this->_template->render(true, false);
    }

    public function iframe($orderId)
    {
        $orderPaymentObj=new OrderPayment($orderId, $this->siteLangId);
        $payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!$orderInfo['id']) {
            FatUtility::exitWIthErrorCode(404);
        }

        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        $working_key = $paymentSettings['working_key'];
        $access_code = $paymentSettings['access_code'];
        $merchant_data = '';
        $post = FatApp::getPostedData();

        foreach ($post as $key => $value) {
            $merchant_data.=$key.'='.$value.'&';
        }
        //$merchant_data= str_replace("#~#","&",$merchant_data);
        $merchant_data.="currency=INR";
        $encrypted_data=encrypt($merchant_data, $working_key); // Method for encrypting the data.
        if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) {
            $iframe_url = 'https://secure.ccavenue.com';
        } else {
            $iframe_url = 'https://test.ccavenue.com';
        }
        $iframe_url.='/transaction/transaction.do?command=initiateTransaction&encRequest='.$encrypted_data.'&access_code='.$access_code;
        $this->set('url', $iframe_url);
        if (CommonHelper::isAppUser()) {
            $this->set('exculdeMainHeaderDiv', true);
        }

        $this->_template->render(true, false);
    }
    public function callback()
    {
        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        $post = FatApp::getPostedData();
        $workingKey = $paymentSettings['working_key'];
        $encResponse = $post["encResp"];            //This is the response sent by the CCAvenue Server
        $rcvdString = decrypt($encResponse, $workingKey);        //Crypto Decryption used as per the specified working key.
        $request = $rcvdString;
        $order_status = "";
        $decryptValues = explode('&', $rcvdString);
        $dataSize = sizeof($decryptValues);
        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);
            if ($i == 3) {
                $order_status = $information[1];
            }
            if ($i == 26) {
                $orderId = $information[1];
            }
            if ($i == 10) {
                $paid_amount = $information[1];
            }
            if ($i == 1) {
                $tracking_id = $information[1];
            }
        }
        $orderPaymentObj = new OrderPayment($orderId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        if ($paymentGatewayCharge>0) {
            $total_paid_match = ((float)$paid_amount == $paymentGatewayCharge);
            if (!$total_paid_match) {
                $request .= "\n\n CCAvenue :: TOTAL PAID MISMATCH! " . strtolower($paid_amount) . "\n\n";
            }
            if ($order_status=="Success" && $total_paid_match) {
                $orderPaymentObj->addOrderPayment($paymentSettings["pmethod_name"], $tracking_id, $paymentGatewayCharge, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), $request);
                FatApp::redirectUser(CommonHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
            } else {
                $orderPaymentObj->addOrderPaymentComments($request);
                FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
            }
        }
    }

    private function getPaymentForm($orderId)
    {
        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        $frm = new Form('frm-ccavenue', array('id'=>'frm-ccavenue','action'=>CommonHelper::generateFullUrl('CcavenuePay', 'iframe', array($orderId)), 'class' =>"form form--normal"));

        $frm->addHiddenField('', 'tid', "", array("id"=>"tid"));
        $frm->addHiddenField('', 'merchant_id', $paymentSettings["merchant_id"]);
        $frm->addHiddenField('', 'order_id', $orderInfo['invoice']);
        $frm->addHiddenField('', 'amount', $paymentGatewayCharge);
        $frm->addHiddenField('', 'merchant_param1', $orderId);
        //$frm->addHiddenField('', 'currency', $orderInfo["order_currency_code"]);
        $frm->addHiddenField('', 'language', "EN");
        $frm->addHiddenField('', 'redirect_url', CommonHelper::generateFullUrl('CcavenuePay', 'callback'));
        $frm->addHiddenField('', 'cancel_url', CommonHelper::getPaymentCancelPageUrl());
        //$frm->addHiddenField('', 'item_name_1', $order_payment_gateway_description);
        $frm->addHiddenField('', 'billing_name', $orderInfo["customer_billing_name"]);
        $frm->addHiddenField('', 'billing_address', $orderInfo["customer_billing_address_1"].', '.$orderInfo["customer_billing_address_2"]);
        $frm->addHiddenField('', 'billing_city', $orderInfo["customer_billing_city"]);
        $frm->addHiddenField('', 'billing_state', $orderInfo["customer_billing_state"]);
        $frm->addHiddenField('', 'billing_zip', $orderInfo["customer_billing_postcode"]);
        $frm->addHiddenField('', 'billing_country', $orderInfo['customer_billing_country']);
        $frm->addHiddenField('', 'billing_tel', $orderInfo['customer_billing_phone']);
        $frm->addHiddenField('', 'billing_email', $orderInfo['customer_email']);
        $frm->addHiddenField('', 'delivery_name', $orderInfo["customer_shipping_name"]);
        $frm->addHiddenField('', 'delivery_address', $orderInfo["customer_shipping_address_1"].', '.$orderInfo["customer_shipping_address_2"]);
        $frm->addHiddenField('', 'delivery_city', $orderInfo["customer_shipping_city"]);
        $frm->addHiddenField('', 'delivery_state', $orderInfo["customer_shipping_state"]);
        $frm->addHiddenField('', 'delivery_zip', $orderInfo["customer_shipping_postcode"]);
        $frm->addHiddenField('', 'delivery_country', $orderInfo['customer_shipping_country']);
        $frm->addHiddenField('', 'delivery_tel', $orderInfo['customer_shipping_phone']);
        $frm->addHiddenField('', 'integration_type', 'iframe_normal');
        return $frm;
    }
}
