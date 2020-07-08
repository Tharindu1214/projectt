<?php
//require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/PayFort/PayfortIntegration.php';
class PayherePayController extends PaymentController
{
    private $keyName = "Payhere";
    private $testEnvironmentUrl = 'https://sandbox.payhere.lk/pay/checkout';
    private $liveEnvironmentUrl = 'https://www.payhere.lk/pay/checkout';
    private $error = false;
    private $currency = 'LKR'; // replace $currency with strtoupper( $orderInfo['order_currency_code'])
    private $currenciesAccepted = array(
                                            'U.S. Dollar' => 'USD',
                                            'Sri Lankan rupee' => 'LKR'
                                        );

    public function charge($orderId = '')
    {
        if (empty($orderId)) {
            FatUtility::exitWIthErrorCode(404);
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = 0.00;
		
        $orderInfo  = array();
        $requestParams = $this->generatePaymentFormParams($orderId, $orderPaymentObj, $orderInfo, $paymentGatewayCharge);
        if ($requestParams) {
            //$frm = $this->getPaymentForm($requestParams);
            $this->set('paymentAmount', $paymentGatewayCharge);
            //$this->set('frm', $frm);
            $this->set('orderInfo', $orderInfo);
            $this->set('requestParams', $requestParams);
        } else {
            $this->error = Labels::getLabel('PAYFORT_Invalid_request_parameters', $this->siteLangId);
        }

        if ($this->error) {
            $this->set('error', $this->error);
        }
        $this->set('paymentAmount', $paymentGatewayCharge);
        $this->set('orderInfo', $orderInfo);
        $this->set('exculdeMainHeaderDiv', true);
        $this->_template->addCss('css/payment.css');
        $this->_template->render(true, false);
    }

    public function doPayment($orderId = '')
    {
		
        if (!$orderId) {
            Message::addErrorMessage(Labels::getLabel('PAYFORT_INVALID_REQUEST'));
            FatApp::redirectUser(CommonHelper::generateUrl('Account', 'profileInfo'));
        }
		$db = FatApp::getDb();
		$srch = new SearchBase('tbl_payhere_order_status', 'tpos');
        $srch->addCondition('tpos.order_id', '=', $orderId);
        $rs = $srch->getResultSet();
        $orderStatus = $db->fetch($rs);
        

        $paymentChargeUrl = CommonHelper::generateUrl('PayherePay', 'charge', array( $orderId ));
		
		if($orderStatus['status'] != 2){
			 Message::addErrorMessage('Sorry, your order payment failed. Please try again.');
             FatApp::redirectUser($paymentChargeUrl);
		}
        /*if (!(isset($_REQUEST['signature']) and !empty($_REQUEST['signature']))) {
            Message::addErrorMessage(Labels::getLabel('PAYFORT_INVALID_REQUEST', $this->siteLangId));
            FatApp::redirectUser($paymentChargeUrl);
        }*/

        $paymentSettings = $this->getPaymentSettings();
        if (!$this->validatePayFortSettings($paymentSettings)) {
            Message::addErrorMessage(Labels::getLabel('PAYFORT_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId));
            redirectUser($paymentChargeUrl);
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = 0.00;
        $orderInfo = array();

        $requestFormParams = $this->generatePaymentFormParams($orderId, $orderPaymentObj, $orderInfo, $paymentGatewayCharge, true);

        if ($requestFormParams === false || !$orderInfo) {
            Message::addErrorMessage($this->error);
            FatApp::redirectUser($paymentChargeUrl);
        }
		
		$gateWayCharges = $paymentGatewayCharge;
		$orderPaymentObj->addOrderPayment($paymentSettings["pmethod_code"], '#320025047910', $gateWayCharges, 'Received Payment', 'Payment Status Done');

		FatApp::redirectUser(CommonHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));

    }
	
	public function notifyPayment($orderId = ''){

		$db = FatApp::getDb();
		
		$merchant_id         = $_POST['merchant_id'];
		$order_id             = $_POST['order_id'];
		$payhere_amount     = $_POST['payhere_amount'];
		$payhere_currency    = $_POST['payhere_currency'];
		$status_code         = $_POST['status_code'];
		$md5sig                = $_POST['md5sig'];
		$merchant_secret = '4aF3HR60PgH4qC5GsKmvJo8X3qmXmtd6i4ZHmX3VCRle'; // Replace with your Merchant Secret (Can be found on your PayHere account's Settings page)
		$local_md5sig = strtoupper (md5 ( $merchant_id . $order_id . $payhere_amount . $payhere_currency . $status_code . strtoupper(md5($merchant_secret)) ) );
		if (($local_md5sig === $md5sig)){
			 $db->deleteRecords('tbl_payhere_order_status', array('smt'=>'`order_id`=?', 'vals'=>array($order_id)));			 
			if($status_code == 2){
				//TODO: Update your database as payment success
				$dataArrToSave = array('order_id'=>$order_id, 'status'=>$status_code);
				$db->insertFromArray('tbl_payhere_order_status', $dataArrToSave);
			}else{
				$dataArrToSave = array('order_id'=>$order_id, 'status'=>$status_code);
				$db->insertFromArray('tbl_payhere_order_status', $dataArrToSave);
			}
		}
	}
	
    private function generatePaymentFormParams($orderId, $orderPaymentObj, &$orderInfo, &$paymentGatewayCharge = 0.00, $returnParams = true)
    {
        if (!$orderId || !$orderPaymentObj) {
            $this->error = Labels::getLabel('MSG_Invalid_order_request', $this->siteLangId);
            return false;
        }

        $paymentGatewayCharge = $this->formatPayableAmount($orderPaymentObj->getOrderPaymentGatewayAmount());
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        $paymentSettings = $this->getPaymentSettings();
		
        if (!$this->validatePayFortSettings($paymentSettings)) {
            $this->error = Labels::getLabel('PAYFORT_Invalid_Payment_Gateway_Setup_Error', $this->siteLangId);
        } elseif (count($this->currenciesAccepted) && !in_array($orderInfo["order_currency_code"], $this->currenciesAccepted)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_ORDER_CURRENCY_PASSED_TO_GATEWAY', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        if (!$orderInfo['id']) {
            $this->error = Labels::getLabel('MSG_INVALID_ACCESS', $this->siteLangId);
            return false;
        } elseif ($orderInfo["order_is_paid"] != Orders::ORDER_IS_PENDING) {
            $this->error = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            return false;
        }

        $orderPaymentGatewayDescription = sprintf(Labels::getLabel('MSG_Order_Payment_Gateway_Description', $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);

        if ($returnParams) {
            $return_url = CommonHelper::generateFullUrl('PayherePay', 'doPayment', array($orderId), '', false);
			
			
			
            $paramsValues = array(
                                    //'secret_key' => $paymentSettings['secret_key'],
                                    'amount' => $paymentGatewayCharge,
                                    'currency' => strtoupper($orderInfo['order_currency_code']),
                                    'merchant_id' => $paymentSettings['merchant_id'],
                                    'order_description' => $orderPaymentGatewayDescription,
                                    'return_url' => $return_url
                                );

            return $paramsValues;
        } else {
            return array();
        }
    }

    private function getPaymentSettings()
    {
        $pmObj = new PaymentSettings($this->keyName);
        return $pmObj->getPaymentSettings();
    }

    private function formatPayableAmount($amount = null)
    {
        if ($amount == null) {
            return false;
        }
        $amount = number_format($amount, 2, '.', '');
        return $amount;
    }

    private function validatePayFortSettings($paymentSettings = array())
    {
		$settingVal = array('merchant_id','secret_key');
        foreach ($settingVal as $val) {
            if (!isset($paymentSettings[$val]) || strlen(trim($paymentSettings[$val])) == 0) {
                return false;
            }
        }
        return true;
    }
}
