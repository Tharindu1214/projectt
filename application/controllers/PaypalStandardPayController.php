<?php
class PaypalStandardPayController extends PaymentController
{
    private $keyName = "PaypalStandard";
    private $testEnvironmentUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    private $liveEnvironmentUrl = 'https://www.paypal.com/cgi-bin/webscr';
    private $currenciesAccepted = array(
                                        'Australian Dollar' => 'AUD',
                                        'Brazilian Real' => 'BRL',
                                        'Canadian Dollar' => 'CAD',
                                        'Czech Koruna' => 'CZK',
                                        'Danish Krone' => 'DKK',
                                        'Euro' => 'EUR',
                                        'Hong Kong Dollar' => 'HKD',
                                        'Hungarian Forint' => 'HUF',
                                        'Israeli New Sheqel' => 'ILS',
                                        'Malaysian Ringgit' => 'MYR',
                                        'Mexican Peso' => 'MXN',
                                        'Norwegian Krone' => 'NOK',
                                        'New Zealand Dollar' => 'NZD',
                                        'Philippine Peso' => 'PHP',
                                        'Polish Zloty' => 'PLN',
                                        'Pound Sterling' => 'GBP',
                                        'Russian Ruble' => 'RUB',
                                        'Singapore Dollar' => 'SGD',
                                        'Swedish Krona' => 'SEK',
                                        'Swiss Franc' => 'CHF',
                                        'Taiwan New Dollar' => 'TWD',
                                        'Thai Baht' => 'THB',
                                        'U.S. Dollar' => 'USD',
                                    );

    private function getPaymentForm($orderId)
    {
        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        $actionUrl = (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true)?$this->liveEnvironmentUrl:$this->testEnvironmentUrl;

        $frm = new Form('frmPayPalStandard', array('id'=>'frmPayPalStandard','action'=>$actionUrl));
        $frm->addHiddenField('', 'cmd', "_cart");
        $frm->addHiddenField('', 'upload', "1");
        $frm->addHiddenField('', 'business', $paymentSettings["merchant_email"]);

        $orderPaymentGatewayDescription = sprintf(Labels::getLabel('MSG_Order_Payment_Gateway_Description', $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);
        $frm->addHiddenField('', 'item_name_1', $orderPaymentGatewayDescription);
        $frm->addHiddenField('', 'item_number_1', $orderInfo['invoice']);
        $frm->addHiddenField('', 'amount_1', $paymentGatewayCharge);
        $frm->addHiddenField('', 'quantity_1', 1);
        $frm->addHiddenField('', 'currency_code', $orderInfo["order_currency_code"]);
        $frm->addHiddenField('', 'first_name', $orderInfo["customer_name"]);
        $frm->addHiddenField('', 'address1', isset($orderInfo["customer_billing_address_1"]) ? $orderInfo["customer_billing_address_1"] : '');
        $frm->addHiddenField('', 'address2', isset($orderInfo["customer_billing_address_2"]) ? $orderInfo["customer_billing_address_2"] : '');
        $frm->addHiddenField('', 'city', isset($orderInfo["customer_billing_city"]) ? $orderInfo["customer_billing_city"] : '');
        $frm->addHiddenField('', 'zip', isset($orderInfo["customer_billing_postcode"]) ? $orderInfo["customer_billing_postcode"] : '');
        $frm->addHiddenField('', 'country', isset($orderInfo["customer_billing_country"]) ? $orderInfo["customer_billing_country"] : '');
        $frm->addHiddenField('', 'address_override', 0);
        $frm->addHiddenField('', 'email', $orderInfo['customer_email']);
        $frm->addHiddenField('', 'invoice', $orderInfo['invoice']);
        $frm->addHiddenField('', 'lc', $orderInfo['order_language']);
        $frm->addHiddenField('', 'rm', 2);
        $frm->addHiddenField('', 'no_note', 1);
        $frm->addHiddenField('', 'no_shipping', 1);
        $frm->addHiddenField('', 'charset', "utf-8");
        $frm->addHiddenField('', 'return', CommonHelper::generateFullUrl('custom', 'paymentSuccess', array($orderId)));
        $frm->addHiddenField('', 'notify_url', CommonHelper::generateNoAuthUrl('PaypalStandardPay', 'callback'));
        $frm->addHiddenField('', 'cancel_return', CommonHelper::getPaymentCancelPageUrl());
        $frm->addHiddenField('', 'paymentaction', 'sale');  // authorization or sale
        $frm->addHiddenField('', 'custom', $orderId);
        $frm->addHiddenField('', 'bn', $orderInfo["paypal_bn"]);
        return $frm;
    }

    public function charge($orderId)
    {
        if ($orderId == '') {
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

        /* ] */

        if ($orderInfo && $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $frm = $this->getPaymentForm($orderId);
            $this->set('frm', $frm);
            $this->set('paymentAmount', $paymentAmount);
        } else {
            $this->set('error', Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }
        $this->set('orderInfo', $orderInfo);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('exculdeMainHeaderDiv', true);
        $this->_template->addCss('css/payment.css');
        $this->_template->render(true, false);
    }

    private function toArray($obj)
    {
        if (is_object($obj)) {
            $obj = (array)$obj;
        }
        if (is_array($obj)) {
            $new = array();
            foreach ($obj as $key => $val) {
                $new[$key] = $this->toArray($val);
            }
        } else {
            $new = $obj;
        }

        return $new;
    }

    public function callback()
    {
        $pmObj = new PaymentSettings($this->keyName);
        $paymentSettings = $pmObj->getPaymentSettings();

        $post = FatApp::getPostedData();

        $orderId = (isset($post['custom']))?$post['custom']:0;

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();

        if ($paymentGatewayCharge > 0) {
            $request = 'cmd=_notify-validate';

            foreach ($post as $key => $value) {
                $request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
            }

            $actionUrl = (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true)?$this->liveEnvironmentUrl:$this->testEnvironmentUrl;

            $curl = curl_init($actionUrl);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);

            if ((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0) && isset($post['payment_status'])) {
                $orderPaymentStatus = $paymentSettings['order_status_initial'];
                switch (strtoupper($post['payment_status'])) {
                    case 'PENDING':
                        $orderPaymentStatus = $paymentSettings['order_status_pending'];
                        break;
                    case 'PROCESSED':
                        $orderPaymentStatus = $paymentSettings['order_status_processed'];
                        break;
                    case 'COMPLETED':
                        $orderPaymentStatus = $paymentSettings['order_status_completed'];
                        break;
                    default:
                        $orderPaymentStatus = $paymentSettings['order_status_others'];
                        break;
                }

                $receiverMatch = (strtolower($post['receiver_email']) == strtolower($paymentSettings['merchant_email']));

                $totalPaidMatch = ((float)$post['mc_gross'] == $paymentGatewayCharge);

                if (!$receiverMatch) {
                    $request .= "\n\n PP_STANDARD :: RECEIVER EMAIL MISMATCH! " . strtolower($post['receiver_email']) . "\n\n";
                }

                if (!$totalPaidMatch) {
                    $request .= "\n\n PP_STANDARD :: TOTAL PAID MISMATCH! " . strtolower($post['mc_gross']) . "\n\n";
                }

                if ($orderPaymentStatus == Orders::ORDER_IS_PAID && $receiverMatch && $totalPaidMatch) {
                    $orderPaymentObj->addOrderPayment($paymentSettings["pmethod_code"], $post["txn_id"], $paymentGatewayCharge, Labels::getLabel('MSG_Payment_Received', $this->siteLangId), $request."#".$response);
                } else {
                    $orderPaymentObj->addOrderPaymentComments($request);
                }
            }

            curl_close($curl);
        }
    }
}
