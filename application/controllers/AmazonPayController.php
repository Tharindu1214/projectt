<?php
require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/PayWithAmazon/Client.php';
class AmazonPayController extends PaymentController
{
    private $key_name="Amazon";
    private $error = false;
    private $paymentSettings = false;
    private $currencyCode = 'usd';

    public function charge($orderId)
    {
        $this->paymentSettings = $this->getPaymentSettings();
        $amazon = array(
        'merchant_id'        => trim($this->paymentSettings['amazon_merchantId']),
        'access_key'        => trim($this->paymentSettings['amazon_accessKey']),
        'secret_key'        => trim($this->paymentSettings['amazon_secretKey']),
        'client_id'            => trim($this->paymentSettings['amazon_clientId'])
        );
        $this->set('amazon', $amazon);
        if (!(strlen($amazon['merchant_id']) > 0 && strlen($amazon['access_key']) > 0 && strlen($amazon['secret_key']) > 0 && strlen($amazon['client_id']) > 0 && strlen(FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_STRING, '0')))) {
            $this->error = Labels::getLabel('AMAZON_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId);
        }
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $payableAmount = $this->formatPayableAmount($paymentAmount);
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!$orderInfo['id']) {
            FatUtility::exitWIthErrorCode(404);
        } elseif ($orderInfo && $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $this->currencyCode = strtolower($orderInfo["order_currency_code"]);
        } else {
            $this->error = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
        }
        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);
        $this->set('orderId', $orderId);
        if ($this->error) {
            $this->set('error', $this->error);
        }

        $this->set('exculdeMainHeaderDiv', true);
        $this->_template->addCss('css/payment.css');

        $queryStr = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?')+1);
        $queryStrArr = explode('&', $queryStr);

        foreach ($queryStrArr as $elm) {
            $elmArr = explode('=', $elm);
            if (sizeof($elmArr) == 2) {
                $queryString[$elmArr[0]] = $elmArr[1];
            }
        }

        if (isset($queryString['token_type'])) {
            if (strlen($queryString['token_type']) > 0) {
                $this->_template->render(true, false, 'amazon-pay/set-payment-details.php');
                return;
            }
        }
        $this->_template->render(true, false);
    }
    public function get_details($orderId)
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_POST_REQUEST', $this->siteLangId));
        }
        $postedData = FatApp::getPostedData();
        if (!isset($postedData['orderReferenceId']) && !isset($postedData['addressConsentToken'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_POST_REQUEST', $this->siteLangId));
        } elseif (strlen($postedData['orderReferenceId']) <= 0) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_POST_REQUEST', $this->siteLangId));
        }
        $this->paymentSettings = $this->getPaymentSettings();
        $config = array(
        'merchant_id'        => trim($this->paymentSettings['amazon_merchantId']),
        'access_key'        => trim($this->paymentSettings['amazon_accessKey']),
        'secret_key'        => trim($this->paymentSettings['amazon_secretKey']),
        'client_id'            => trim($this->paymentSettings['amazon_clientId'])
        );
        if (!(strlen($config['merchant_id']) > 0 && strlen($config['access_key']) > 0 && strlen($config['secret_key']) > 0 && strlen($config['client_id']) > 0)) {
            FatUtility::dieJsonError(Labels::getLabel('AMAZON_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId));
        }
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $payableAmount = $this->formatPayableAmount($paymentAmount);
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if ($orderInfo && $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $this->currencyCode = strtolower($orderInfo["order_currency_code"]);
            $config['region']        = 'us';
            $config['currency_Code'] = strtoupper($this->currencyCode);
            $config['sandbox']       = (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == false);
            if (!class_exists('\PayWithAmazon\Client')) {
                FatUtility::dieJsonError(Labels::getLabel('AMAZON_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId));
            }
            $client = new \PayWithAmazon\Client($config);
            $requestParameters = array();
            $requestParameters['amount']            = $paymentAmount;
            $requestParameters['currencyCode']     = strtoupper($this->currencyCode);
            $requestParameters['seller_order_id']   = 'order-'.$orderId;
            $requestParameters['seller_Id']         = null;
            $requestParameters['platform_id']       = null;
            $requestParameters['mws_auth_token']    = null;
            $requestParameters['amazon_order_reference_id'] = $postedData['orderReferenceId'];
            $response = $client->setOrderReferenceDetails($requestParameters);
            if ($client->success) {
                $requestParameters['address_consent_token'] = $postedData['addressConsentToken'];
                $response = $client->getOrderReferenceDetails($requestParameters);
                FatUtility::dieJsonSuccess($response->toJson());
            }
            FatUtility::dieJsonError($response->toJson());
        } else {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }
    }
    public function doPayment($orderId)
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_POST_REQUEST', $this->siteLangId));
        }
        $postedData = FatApp::getPostedData();
        if (!isset($postedData['amazon_order_reference_id'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_POST_REQUEST', $this->siteLangId));
        } elseif (strlen($postedData['amazon_order_reference_id']) <= 0) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_POST_REQUEST', $this->siteLangId));
        }
        $this->paymentSettings = $this->getPaymentSettings();
        $config = array(
        'merchant_id'        => trim($this->paymentSettings['amazon_merchantId']),
        'access_key'        => trim($this->paymentSettings['amazon_accessKey']),
        'secret_key'        => trim($this->paymentSettings['amazon_secretKey']),
        'client_id'            => trim($this->paymentSettings['amazon_clientId'])
        );
        if (!(strlen($config['merchant_id']) > 0 && strlen($config['access_key']) > 0 && strlen($config['secret_key']) > 0 && strlen($config['client_id']) > 0)) {
            FatUtility::dieJsonError(Labels::getLabel('AMAZON_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId));
        }
        $orderPaymentObj=new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount=$orderPaymentObj->getOrderPaymentGatewayAmount();
        $payableAmount = $this->formatPayableAmount($paymentAmount);
        $orderInfo=$orderPaymentObj->getOrderPrimaryinfo();
        if ($orderInfo && $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $this->currencyCode = strtolower($orderInfo["order_currency_code"]);
            $config['region']        = 'us';
            $config['currency_Code'] = strtoupper($this->currencyCode);
            $config['sandbox']       = (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == false);
            if (!class_exists('\PayWithAmazon\Client')) {
                FatUtility::dieJsonError(Labels::getLabel('AMAZON_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId));
            }
            $client = new \PayWithAmazon\Client($config);
            $requestParameters = array();
            $requestParameters['amazon_order_reference_id'] = $postedData['amazon_order_reference_id'];
            $requestParameters['mws_auth_token'] = null;
            $response = $client->confirmOrderReference($requestParameters);
            $responsearray['confirm'] = json_decode($response->toJson());
            if ($client->success) {
                $requestParameters['authorization_amount'] = $paymentAmount;
                $requestParameters['authorization_reference_id'] = uniqid('A01_REF_');
                $requestParameters['transaction_timeout'] = 0;
                $requestParameters['capture_now'] = false;
                $requestParameters['soft_descriptor'] = null;
                $response = $client->authorize($requestParameters);
                $res = $responsearray['authorize'] = json_decode($response->toJson());
                if ($client->success) {
                    $requestParameters['amazon_reference_id'] = uniqid('P01_');
                    $requestParameters['amazon_authorization_id'] = $res->AuthorizeResult->AuthorizationDetails->AmazonAuthorizationId;
                    $requestParameters['capture_reference_id'] = uniqid('A01_');
                    $requestParameters['capture_amount']= $paymentAmount;
                    $response = $client->capture($requestParameters);
                    $responsearray['capture'] = json_decode($response->toJson());
                    if ($client->success) {
                        $response = $client->closeOrderReference(
                            array(
                            'amazon_order_reference_id' => $postedData['amazon_order_reference_id'],
                            'cancelation_reason'        => 'My cancel reason.'
                            )
                        );
                        $responsearray['close'] = json_decode($response->toJson());
                        if ($client->success) {
                            /* Recording Payment in DB */
                            $orderPaymentObj->addOrderPayment($this->paymentSettings["pmethod_name"], $postedData['amazon_order_reference_id'], $paymentAmount, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), json_encode($responsearray));
                            /* End Recording Payment in DB */
                        }
                        if ($client->success) {
                            FatUtility::dieJsonSuccess(Labels::getLabel('AMAZON_PAYMENT_COMPLETE', $this->siteLangId));
                        }
                    }
                }
            }
            FatUtility::dieJsonError($responsearray);
        } else {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }
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
        $pmObj=new PaymentSettings($this->key_name);
        $paymentSettings=$pmObj->getPaymentSettings();
        return $paymentSettings;
    }
}
