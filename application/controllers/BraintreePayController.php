<?php
require_once CONF_INSTALLATION_PATH . 'library/braintree/Braintree.php';

class BraintreePayController extends PaymentController
{
    const ENVOIRMENT_LIVE = 'live';
    const ENVOIRMENT_SANDBOX = 'sandbox';

    private $keyName = "Braintree";
    private $error = false;
    private $paymentSettings = false;
    private $currenciesAccepted = array(
                                    'United Arab Emirates Dirham' => 'AED',
                                    'Armenian Dram' => 'AMD',
                                    'Angolan Kwanza' => 'AOA',
                                    'Argentine Peso' => 'ARS',
                                    'Australian Dollar' => 'AUD',
                                    'Aruban Florin' => 'AWG',
                                    'Azerbaijani Manat' => 'AZN',
                                    'Bosnia and Herzegovina Convertible Mark' => 'BAM',
                                    'Barbadian Dollar' => 'BBD',
                                    'Bangladeshi Taka' => 'BDT',
                                    'Bulgarian Lev' => 'BGN',
                                    'Burundian Franc' => 'BIF',
                                    'Bermudian Dollar' => 'BMD',
                                    'Brunei Dollar' => 'BND',
                                    'Bolivian Boliviano' => 'BOB',
                                    'Brazilian Real' => 'BRL',
                                    'Bahamian Dollar' => 'BSD',
                                    'Botswana Pula' => 'BWP',
                                    'Belarusian Ruble' => 'BYN',
                                    'Belize Dollar' => 'BZD',
                                    'Canadian Dollar' => 'CAD',
                                    'Swiss Franc' => 'CHF',
                                    'Chilean Peso' => 'CLP',
                                    'Chinese Renminbi Yuan' => 'CNY',
                                    'Colombian Peso' => 'COP',
                                    'Costa Rican Colón' => 'CRC',
                                    'Cape Verdean Escudo' => 'CVE',
                                    'Czech Koruna' => 'CZK',
                                    'Djiboutian Franc' => 'DJF',
                                    'Danish Krone' => 'DKK',
                                    'Dominican Peso' => 'DOP',
                                    'Algerian Dinar' => 'DZD',
                                    'Egyptian Pound' => 'EGP',
                                    'Ethiopian Birr' => 'ETB',
                                    'Euro' => 'EUR',
                                    'Fijian Dollar' => 'FJD',
                                    'Falkland Pound' => 'FKP',
                                    'British Pound' => 'GBP',
                                    'Georgian Lari' => 'GEL',
                                    'Ghanaian Cedi' => 'GHS',
                                    'Gibraltar Pound' => 'GIP',
                                    'Gambian Dalasi' => 'GMD',
                                    'Guinean Franc' => 'GNF',
                                    'Guatemalan Quetzal' => 'GTQ',
                                    'Guyanese Dollar' => 'GYD',
                                    'Hong Kong Dollar' => 'HKD',
                                    'Honduran Lempira' => 'HNL',
                                    'Croatian Kuna' => 'HRK',
                                    'Haitian Gourde' => 'HTG',
                                    'Hungarian Forint' => 'HUF',
                                    'Indonesian Rupiah' => 'IDR',
                                    'Israeli New Sheqel' => 'ILS',
                                    'Indian Rupee' => 'INR',
                                    'Icelandic Króna' => 'ISK',
                                    'Jamaican Dollar' => 'JMD',
                                    'Japanese Yen' => 'JPY',
                                    'Kenyan Shilling' => 'KES',
                                    'Kyrgyzstani Som' => 'KGS',
                                    'Cambodian Riel' => 'KHR',
                                    'Comorian Franc' => 'KMF',
                                    'South Korean Won' => 'KRW',
                                    'Cayman Islands Dollar' => 'KYD',
                                    'Kazakhstani Tenge' => 'KZT',
                                    'Lao Kip' => 'LAK',
                                    'Lebanese Lira' => 'LBP',
                                    'Sri Lankan Rupee' => 'LKR',
                                    'Liberian Dollar' => 'LRD',
                                    'Lesotho Loti' => 'LSL',
                                    'Lithuanian Litas' => 'LTL',
                                    'Moroccan Dirham' => 'MAD',
                                    'Moldovan Leu' => 'MDL',
                                    'Macedonian Denar' => 'MKD',
                                    'Mongolian Tögrög' => 'MNT',
                                    'Macanese Pataca' => 'MOP',
                                    'Mauritian Rupee' => 'MUR',
                                    'Maldivian Rufiyaa' => 'MVR',
                                    'Malawian Kwacha' => 'MWK',
                                    'Mexican Peso' => 'MXN',
                                    'Malaysian Ringgit' => 'MYR',
                                    'Mozambican Metical' => 'MZN',
                                    'Namibian Dollar' => 'NAD',
                                    'Nigerian Naira' => 'NGN',
                                    'Nicaraguan Córdoba' => 'NIO',
                                    'Norwegian Krone' => 'NOK',
                                    'Nepalese Rupee' => 'NPR',
                                    'New Zealand Dollar' => 'NZD',
                                    'Panamanian Balboa' => 'PAB',
                                    'Peruvian Nuevo Sol' => 'PEN',
                                    'Papua New Guinean Kina' => 'PGK',
                                    'Philippine Peso' => 'PHP',
                                    'Pakistani Rupee' => 'PKR',
                                    'Polish Złoty' => 'PLN',
                                    'Paraguayan Guaraní' => 'PYG',
                                    'Qatari Riyal' => 'QAR',
                                    'Romanian Leu' => 'RON',
                                    'Serbian Dinar' => 'RSD',
                                    'Russian Ruble' => 'RUB',
                                    'Rwandan Franc' => 'RWF',
                                    'Saudi Riyal' => 'SAR',
                                    'Solomon Islands Dollar' => 'SBD',
                                    'Seychellois Rupee' => 'SCR',
                                    'Swedish Krona' => 'SEK',
                                    'Singapore Dollar' => 'SGD',
                                    'Saint Helenian Pound' => 'SHP',
                                    'Sierra Leonean Leone' => 'SLL',
                                    'Somali Shilling' => 'SOS',
                                    'Surinamese Dollar' => 'SRD',
                                    'São Tomé and Príncipe Dobra' => 'STD',
                                    'Salvadoran Colón' => 'SVC',
                                    'Syrian Pound' => 'SYP',
                                    'Swazi Lilangeni' => 'SZL',
                                    'Thai Baht' => 'THB',
                                    'Tajikistani Somoni' => 'TJS',
                                    'Tongan Paʻanga' => 'TOP',
                                    'Turkish Lira' => 'TRY',
                                    'Trinidad and Tobago Dollar' => 'TTD',
                                    'New Taiwan Dollar' => 'TWD',
                                    'Tanzanian Shilling' => 'TZS',
                                    'Ukrainian Hryvnia' => 'UAH',
                                    'Ugandan Shilling' => 'UGX',
                                    'United States Dollar' => 'USD',
                                    'Uruguayan Peso' => 'UYU',
                                    'Uzbekistani Som' => 'UZS',
                                    'Bolívar Soberano' => 'VES',
                                    'Vietnamese Đồng' => 'VND',
                                    'Vanuatu Vatu' => 'VUV',
                                    'Samoan Tala' => 'WST',
                                    'Central African Cfa Franc' => 'XAF',
                                    'East Caribbean Dollar' => 'XCD',
                                    'West African Cfa Franc' => 'XOF',
                                    'Cfp Franc' => 'XPF',
                                    'Yemeni Rial' => 'YER',
                                    'South African Rand' => 'ZAR',
                                    'Zambian Kwacha' => 'ZMK',
                                    'Zimbabwean Dollar' => 'ZWD'
                                );

    public function charge($orderId)
    {
        if (empty(trim($orderId))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $clientToken = $this->getClientToken();

        if (!$clientToken) {
            Message::addErrorMessage(Labels::getLabel('BRAINTREE_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $payableAmount = $this->formatPayableAmount($paymentAmount);
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!in_array(strtoupper($orderInfo["order_currency_code"]), $this->currenciesAccepted)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_ORDER_CURRENCY_PASSED_TO_GATEWAY', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        if (!$orderInfo['id']) {
            FatUtility::exitWithErrorCode(404);
        }
        $currencyCode = '';
        if (count($orderInfo) < 1 || (count($orderInfo) > 1 && $orderInfo["order_is_paid"] != Orders::ORDER_IS_PENDING)) {
            $this->error = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
        } else {
            $currencyCode = strtolower($orderInfo["order_currency_code"]);
            $checkPayment = $this->doPayment($payableAmount, $orderInfo);
            $frm=$this->getPaymentForm($orderId);
            $this->set('frm', $frm);
            if ($checkPayment) {
                $this->set('success', true);
            }
        }

        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);
        if ($this->error) {
            $this->set('error', $this->error);
        }

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set("currencyCode", $currencyCode);
        $this->set('orderId', $orderId);
        $this->set('cancelBtnUrl', $cancelBtnUrl);
        $this->set('exculdeMainHeaderDiv', true);
        $this->set('clientToken', $clientToken);
        $this->_template->addCss('css/payment.css');

        $this->_template->render(true, false);
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
        $frm = new Form('frmPaymentForm', array('id'=>'frmPaymentForm','action'=>CommonHelper::generateUrl('BraintreePay', 'charge', array($orderId)), 'class' =>"form form--normal"));
        $frm->addButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId), array("disabled"=>"disabled","id"=>"submit-button"));
        return $frm;
    }

    private function doPayment($payment_amount = null, $orderInfo = null)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $this->paymentSettings = $this->getPaymentSettings();
        if ($payment_amount == null || !$this->paymentSettings || $orderInfo['id'] == null) {
            return false;
        }
        $checkPayment = false;
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            try {
                if (!isset($_POST['paymentMethodNonce'])) {
                    throw new Exception("The paymentMethod Nonce was not generated correctly");
                } else {
                    if (!$this->getClientToken()) {
                        Message::addErrorMessage(Labels::getLabel('BRAINTREE_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId));
                        CommonHelper::redirectUserReferer();
                    }
                    $charge =     Braintree_Transaction::sale(
                        array(
                                    'amount' => $_POST['amount'],
                                    'paymentMethodNonce' => $_POST['paymentMethodNonce'],
                                    'options' => [
                                        'submitForSettlement' => true
                                    ]
                                )
                    );

                    $charge = (array)$charge;

                    if (isset($charge['success'])) {
                        $message = '';
                        $orderPaymentObj = new OrderPayment($orderInfo['id']);

                        if ($charge['success'] || (isset($charge['transaction']) && !is_null($charge['transaction']))) {
                            $message .= 'Id: '.(string)$charge['transaction']->_attributes['id']. "&";
                            $message .= 'Object: '.(string)$charge['transaction']. "&";
                            $message .= 'Amount: '.(string)$charge['transaction']->_attributes['amount']. "&";

                            $message .= 'Status: '.(string)$charge['transaction']->_attributes['status']. "&";
                            /* Recording Payment in DB */

                            $orderPaymentObj->addOrderPayment($this->paymentSettings["pmethod_name"], $charge['transaction']->_attributes['id'], ($payment_amount/100), Labels::getLabel("MSG_Received_Payment", $this->siteLangId), $message);
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

    private function getClientToken()
    {
        try {
            $this->paymentSettings = $this->getPaymentSettings();
            if (!isset($this->paymentSettings['private_key']) || !isset($this->paymentSettings['public_key']) || !isset($this->paymentSettings['merchant_id'])) {
                return false;
            }
            $envoirment = (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true)? static::ENVOIRMENT_LIVE : static::ENVOIRMENT_SANDBOX;

            Braintree_Configuration::environment($envoirment);
            Braintree_Configuration::merchantId($this->paymentSettings['merchant_id']);
            Braintree_Configuration::publicKey($this->paymentSettings['public_key']);
            Braintree_Configuration::privateKey($this->paymentSettings['private_key']);
            return Braintree_ClientToken::generate();
        } catch (Exception $e) {
            // return $e->getMessage();
            return false;
        }
    }
}
