<?php
class CitrusPayController extends PaymentController
{
    private $keyName="Citrus";

    public function charge($orderId)
    {
        $pmObj=new PaymentSettings($this->keyName);
        if (!$paymentSettings=$pmObj->getPaymentSettings()) {
            Message::addErrorMessage($pmObj->getError());
            CommonHelper::redirectUserReferer();
        }
        $orderPaymentObj=new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount=$orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo=$orderPaymentObj->getOrderPrimaryinfo();
        if (!$orderInfo['id']) {
            FatUtility::exitWIthErrorCode(404);
        } elseif ($orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $frm = $this->getPaymentForm($orderId);
            $this->set('frm', $frm);
            $this->set('paymentAmount', $paymentAmount);
        } else {
            $this->set('error', Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }
        $this->set('orderInfo', $orderInfo);
        $this->set('exculdeMainHeaderDiv', true);
        $this->_template->addCss('css/payment.css');
        $this->_template->render(true, false);
    }

    public function callback()
    {
        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        $post = FatApp::getPostedData();
        $orderId = (isset($post['TxId']))?$post['TxId']:0;
        $orderPaymentObj = new OrderPayment($orderId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        foreach ($post as $key => $value) {
            $request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
        }
        if ($paymentGatewayCharge>0) {
            if (strtoupper($post['TxStatus']) == 'SUCCESS') {
                //resp signature validation
                $str=$post['TxId'].$post['TxStatus'].$post['amount'].$post['pgTxnNo'].$post['issuerRefNo'].$post['authIdCode'].$post['firstName'].$post['lastName'].$post['pgRespCode'].$post['addressZip'];
                $respSig=$post['signature'];
                if (hash_hmac('sha1', $str, $paymentSettings['merchant_secret_key']) == $respSig) {
                    $orderPaymentObj->addOrderPayment($paymentSettings["pmethod_name"], $post['pgTxnNo'], $paymentGatewayCharge, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), $request);
                    FatApp::redirectUser(CommonHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
                } else {
                    $request .= "\n\n Citrus :: Invalid or forged transactiond.  \n\n";
                    $orderPaymentObj->addOrderPaymentComments($request);
                    FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
                }
            } else {
                $orderPaymentObj->addOrderPaymentComments($request);
                if ($post['pgRespCode']==3) {
                    FatApp::redirectUser(CommonHelper::getPaymentCancelPageUrl());
                }

                FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
            }
        } else {
            FatUtility::exitWithErrorCode(404);
        }
    }


    private function getPaymentForm($orderId)
    {
        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        $vanityUrl = $paymentSettings['merchant_vanity_url'];
        $currency ='INR';
        $merchantTxnId = $orderId;
        $orderAmount = $paymentGatewayCharge;
        $tmpdata = "$vanityUrl$orderAmount$merchantTxnId$currency";
        $secSignature = hash_hmac('sha1', $tmpdata, $paymentSettings['merchant_secret_key']);
        if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) {
            $actionUrl = 'https://production.citruspay.com/sslperf/';
        } elseif (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == false) {
            $actionUrl = 'https://sandbox.citruspay.com/sslperf/';
        }
        $actionUrl = $actionUrl."$vanityUrl";

        $frm = new Form('frm-citrus-payment', array('id'=>'frm-citrus-payment','action'=>$actionUrl, 'class' =>"form form--normal"));

        $frm->addHiddenField('', 'merchantTxnId', $orderId);
        $frm->addHiddenField('', 'orderAmount', $paymentGatewayCharge);
        $frm->addHiddenField('', 'currency', "INR");
        $frm->addHiddenField('', 'secSignature', $secSignature);
        $frm->addHiddenField('', 'returnUrl', CommonHelper::generateFullUrl('CitrusPay', 'callback'));
        $frm->addHiddenField('', 'email', $orderInfo["customer_email"]);
        $frm->addHiddenField('', 'phoneNumber', $orderInfo["customer_phone"]);
        $frm->addHiddenField('', 'addressState', $orderInfo["customer_billing_state"]);
        $frm->addHiddenField('', 'addressCity', $orderInfo["customer_billing_city"]);
        $frm->addHiddenField('', 'addressStreet1', $orderInfo["customer_billing_address_1"]);
        $frm->addHiddenField('', 'addressStreet2', $orderInfo["customer_billing_address_2"]);
        $frm->addHiddenField('', 'addressCountry', $orderInfo["customer_billing_country"]);
        $frm->addHiddenField('', 'addressZip', $orderInfo["customer_billing_postcode"]);
        $custName = explode(" ", $orderInfo["customer_name"]);
        $firstName = $lastName = !empty($custName[0])?$custName[0]:'';
        $lastName = !empty($custName[1])?$custName[1]:'';
        $frm->addHiddenField('', 'firstName', $firstName);
        $frm->addHiddenField('', 'lastName', $lastName);
        return $frm;
    }
}
