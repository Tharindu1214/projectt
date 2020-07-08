<?php
class RazorpayPayController extends PaymentController
{
    private $keyName="Razorpay";

    public function charge($orderId)
    {
        $pmObj=new PaymentSettings($this->keyName);
        if (!$paymentSettings=$pmObj->getPaymentSettings()) {
            Message::addErrorMessage($pmObj->getError());
            CommonHelper::redirectUserReferer();
        }
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if (!$orderInfo['id']) {
            FatUtility::exitWithErrorCode(404);
        } elseif ($orderInfo && $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $frm = $this->getPaymentForm($orderId);
            $this->set('frm', $frm);
        } else {
            $this->set('error', Labels::getLabel('M_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set('cancelBtnUrl', $cancelBtnUrl);

        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);
        $this->set('paymentSettings', $paymentSettings);
        $this->set('exculdeMainHeaderDiv', true);
        $this->_template->addCss('css/payment.css');
        $this->_template->render(true, false);
    }

    public function callback()
    {
        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();
        $post = FatApp::getPostedData();
        foreach ($post as $key => $value) {
            $request .= '&' . $key . '=' . urlencode(FatUtility::decodeHtmlEntities($value, ENT_QUOTES, 'UTF-8'));
        }
        $razorpay_payment_id = $post['razorpay_payment_id'];
        $merchant_order_id = (isset($post['merchant_order_id']))?$post['merchant_order_id']:0;
        $orderPaymentObj = new OrderPayment($merchant_order_id, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $payment_gateway_charge_in_paisa = $paymentGatewayCharge*100;
        if ($paymentGatewayCharge>0) {
            $success = false;
            $error = "";
            try {
                $url = 'https://api.razorpay.com/v1/payments/'.$razorpay_payment_id.'/capture';
                $fields_string="amount=$payment_gateway_charge_in_paisa";
                //cURL Request
                $ch = curl_init();
                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_USERPWD, $paymentSettings['merchant_key_id'] . ":" . $paymentSettings['merchant_key_secret']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                //execute post
                $result = curl_exec($ch);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($result === false) {
                    $success = false;
                    $error = 'Curl error: ' . curl_error($ch);
                } else {
                    $response_array = json_decode($result, true);
                    //Check success response
                    if ($http_status === 200 and isset($response_array['error']) === false) {
                        $success = true;
                    } else {
                        $success = false;
                        if (!empty($response_array['error']['code'])) {
                            $error = $response_array['error']['code'].":".$response_array['error']['description'];
                        } else {
                            $error = "RAZORPAY_ERROR:Invalid Response <br/>".$result;
                        }
                    }
                }
                //close connection
                curl_close($ch);
            } catch (Exception $e) {
                $success = false;
                $error ="ERROR:Request to Razorpay Failed";
            }
            if ($success === true) {
                $orderPaymentObj->addOrderPayment($paymentSettings["pmethod_name"], $razorpay_payment_id, $paymentGatewayCharge, Labels::getLabel("L_Received_Payment", $this->siteLangId), 'Payment Successful. Razorpay Payment Id:'.$razorpay_payment_id);
                FatApp::redirectUser(CommonHelper::generateUrl('custom', 'paymentSuccess', array($merchant_order_id)));
            } else {
                $orderPaymentObj->addOrderPaymentComments($error.' Payment Failed! Check Razorpay dashboard for details of Payment Id:'.$razorpay_payment_id);
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
        $frm = new Form('razorpay-form', array('id'=>'razorpay-form','action'=>CommonHelper::generateFullUrl('RazorpayPay', 'callback'), 'class' =>"form form--normal"));

        $frm->addHiddenField('', 'razorpay_payment_id', '', array('id' =>'razorpay_payment_id'));
        $frm->addHiddenField('', 'merchant_order_id', $orderId, array('id' =>'merchant_order_id'));
        return $frm;
    }
}
