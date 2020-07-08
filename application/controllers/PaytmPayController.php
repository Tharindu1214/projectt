<?php
require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/paytm/PaytmKit/lib/encdec_paytm.php';
class PaytmPayController extends PaymentController
{
    private $keyName="Paytm";
    private $currenciesAccepted = array(
                                        'India' => 'INR',
                                    );

    public function charge($orderId)
    {
        if (empty(trim($orderId))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $pmObj = new PaymentSettings($this->keyName);
        if (!$paymentSettings = $pmObj->getPaymentSettings()) {
            Message::addErrorMessage($pmObj->getError());
            CommonHelper::redirectUserReferer();
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        /* To check for valid currencies accepted by paypal gateway [ */

        if (count($this->currenciesAccepted) && !in_array($orderInfo["order_currency_code"], $this->currenciesAccepted)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_ORDER_CURRENCY_PASSED_TO_GATEWAY', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        if (!$orderInfo['id']) {
            FatUtility::exitWIthErrorCode(404);
        } elseif ($orderInfo && $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $frm=$this->getPaymentForm($orderId);
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

        $request ='';
        foreach ($post as $key => $value) {
            $request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
        }

        $isValidChecksum = false;
        $paytmChecksum = isset($post["CHECKSUMHASH"]) ? $post["CHECKSUMHASH"] : ""; //Sent by Paytm pg
        $isValidChecksum = verifychecksum_e($post, $paymentSettings['merchant_key'], $paytmChecksum); //will return TRUE or FALSE string.
        $arrOrder= explode("_", $post['ORDERID']);
        $orderId = (!empty($arrOrder[1]))?$arrOrder[1]:0;
        $txnInfo = $this->PaytmTransactionStatus($post['ORDERID']);

        $orderPaymentObj = new OrderPayment($orderId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        if ($paymentGatewayCharge>0) {
            if ($isValidChecksum) {
                $paid_amount = (float)$txnInfo['TXNAMOUNT'];
                $totalPaidMatch = ($paid_amount == $paymentGatewayCharge);
                if (!$totalPaidMatch) {
                    $request .= "\n\n Paytm :: TOTAL PAID MISMATCH! " . strtolower($paid_amount) . "\n\n";
                }

                if ($txnInfo['STATUS'] == "TXN_SUCCESS" && $totalPaidMatch) {
                    $orderPaymentObj->addOrderPayment($paymentSettings["pmethod_name"], $post['TXNID'], $paymentGatewayCharge, Labels::getLabel("MSG_Received_Payment", $this->siteLangId), $request);
                    FatApp::redirectUser(CommonHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
                } else {
                    $orderPaymentObj->addOrderPaymentComments($request);
                    if (isset($post['PAYMENTMODE'])) {
                        FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
                    } else {
                        FatApp::redirectUser(CommonHelper::getPaymentCancelPageUrl());
                    }
                }
            } else {
                FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
            }
        } else {
            FatUtility::exitWithErrorCode(404);
        }
    }

    public function PaytmTransactionStatus($orderId)
    {
        header("Pragma: no-cache");
        header("Cache-Control: no-cache");
        header("Expires: 0");
        $pmObj = new Paymentsettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        $checkSum = "";
        $data = array(
        "MID"=>$paymentSettings["merchant_id"],
        "ORDER_ID"=>$orderId,
        );

        $key = $paymentSettings['merchant_key'];
        $checkSum =getChecksumFromArray($data, $key);

        $request=array("MID"=>$paymentSettings["merchant_id"],"ORDERID"=>$orderId,"CHECKSUMHASH"=>$checkSum);

        $JsonData =json_encode($request);
        $postData = 'JsonData='.urlencode($JsonData);
        if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) {
            $url = "https://securegw.paytm.in/order/status";
        } else {
            $url = "https://securegw-stage.paytm.in/order/status";
        }
        $HEADER[] = "Content-Type: application/json";
        $HEADER[] = "Accept: application/json";

        $args['HEADER'] = $HEADER;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $args['HEADER']);
        $server_output = curl_exec($ch);
        return json_decode($server_output, true);
    }

    private function getPaymentForm($orderId)
    {
        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) {
            $action_url = "https://securegw.paytm.in/order/process";
        } else {
            $action_url = "https://securegw-stage.paytm.in/order/process";
        }
        $orderPaymentGatewayDescription = sprintf(Labels::getLabel('MSG_Order_Payment_Gateway_Description', $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);

        $frm = new Form('frmPaytm', array('id'=>'frmPaytm','action'=>$action_url, 'class' =>"form form--normal"));

        $parameters = array(
        "MID" => $paymentSettings["merchant_id"],
        "ORDER_ID"  => date("ymdhis")."_".$orderId,
        "CUST_ID" => $orderInfo['customer_id'],
        "TXN_AMOUNT" => $paymentGatewayCharge,
        "CHANNEL_ID" => $paymentSettings['merchant_channel_id'],
        "INDUSTRY_TYPE_ID" => $paymentSettings['merchant_industry_type'],
        "WEBSITE" => $paymentSettings['merchant_website'],
        "MOBILE_NO" => $orderInfo['customer_phone'],
        "EMAIL" => $orderInfo['customer_email'],
        "CALLBACK_URL" => CommonHelper::generateFullUrl('PaytmPay', 'callback'),
        "ORDER_DETAILS" => $orderPaymentGatewayDescription,
        );

        $checkSumHash = getChecksumFromArray($parameters, $paymentSettings['merchant_key']);

        $frm->addHiddenField('', 'CHECKSUMHASH', $checkSumHash);
        foreach ($parameters as $paramkey => $paramval) {
            $frm->addHiddenField('', $paramkey, $paramval);
        }
        return $frm;
    }
}
