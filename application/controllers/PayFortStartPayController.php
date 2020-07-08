<?php
require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/PayFortStart/autoload.php';
class PayFortStartPayController extends PaymentController
{
    private $keyName = "PayFortStart";
    private $error = false;

    public function charge($orderId = '')
    {
        if (empty($orderId)) {
            FatUtility::exitWIthErrorCode(404);
        }

        $paymentSettings = $this->getPaymentSettings();

        if (!$this->validatePayFortStartSettings($paymentSettings)) {
            $this->error = Labels::getLabel('PAYFORTSTART_Invalid_Payment_Gateway_Setup_Error', $this->siteLangId);
        } else {
            $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
            $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
            $amount_in_cents = $this->formatPayableAmount($paymentGatewayCharge);

            $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
            $orderPaymentGatewayDescription = sprintf(Labels::getLabel('MSG_Order_Payment_Gateway_Description', $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);
        }

        $this->set('open_key', $paymentSettings['open_key']);
        $this->set('paymentAmount', $paymentGatewayCharge);
        $this->set('amount_in_cents', $amount_in_cents);
        $this->set('orderId', $orderId);
        $this->set('paymentgatewayImg', '');

        $this->set('orderInfo', $orderInfo);
        $this->set('currency', $orderInfo['order_currency_code']);
        $this->set('customer_email', $orderInfo['customer_email']);
        $this->set('orderPaymentGatewayDescription', $orderPaymentGatewayDescription);
        $this->set('exculdeMainHeaderDiv', true);
        $this->_template->addCss('css/payment.css');
        $this->_template->render(true, false);
    }

    public function payFortCharge()
    {
        $post = FatApp::getPostedData();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $orderId = $post["ord"];
            $paymentSettings = $this->getPaymentSettings();
            $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
            $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
            $orderPaymentGatewayDescription = sprintf(Labels::getLabel('MSG_Order_Payment_Gateway_Description', $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);
            $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
            $amount_in_cents = $this->formatPayableAmount($paymentGatewayCharge);
            $token = $post["startToken"];
            $email = $post["startEmail"];
            Start::setApiKey($paymentSettings["secret_key"]);
            try {
                $charge = Start_Charge::create(
                    array(
                    "amount" => $amount_in_cents,
                    "currency" => $orderInfo['order_currency_code'],
                    "card" => $token,
                    "email" => $email,
                    "ip" => $_SERVER["REMOTE_ADDR"],
                    "description" => $orderPaymentGatewayDescription
                    )
                );
                $charge['order_id'] = $orderId;
                /* CommonHelper::printArray($charge); die; */
                $this->notifyCallBack($charge);
            } catch (Start_Error $e) {
                $error_code = $e->getErrorCode();
                $error_message = $e->getMessage();
                if ($error_code === "card_declined") {
                    $msg = "Charge was declined";
                } else {
                    $msg = "Charge was not processed.";
                }
                $failUrl = CommonHelper::generateUrl('custom', 'paymentFailed');
                FatApp::redirectUser($failUrl);
            }
        }
        Message::addErrorMessage(Labels::getLabel('LBL_Page_not_found', $this->siteLangId));
        CommonHelper::redirectUserReferer();
    }

    protected function notifyCallBack($response)
    {
        $order_id = $response['order_id'];
        $pmObj = new Paymentsettings($this->keyName);
        if (!$payment_settings = $pmObj->getPaymentSettings()) {
            Message::addErrorMessage($pmObj->getError());
            CommonHelper::redirectUserReferer();
        }
        $orderPaymentObj = new OrderPayment($order_id, $this->siteLangId);
        $payment_gateway_charge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $order_info = $orderPaymentObj->getOrderPrimaryinfo();

        if ($order_info) {
            if (count($response) > 0 and isset($response['state'])) {
                $order_payment_status = 0;
                switch (strtolower($response['state'])) {
                case 'captured':
                    $order_payment_status = 1;
                    break;
                case 'Failed':
                    $order_payment_status = 0;
                    break;
                default:
                    $order_payment_status = 0;
                    break;
                }
                $request = '';
                $payfortReceivePayment = $response['captured_amount'] / 100;
                $total_paid_match = ((float) $payfortReceivePayment == $payment_gateway_charge);
                /* if (!$receiver_match) {
                  $request .= "\n\n PP_STANDARD :: RECEIVER EMAIL MISMATCH! " . strtolower($post['receiver_email']) . "\n\n";
                  } */
                if (!$total_paid_match) {
                    $request .= "\n\n PP_STANDARD :: TOTAL PAID MISMATCH! " . strtolower($response['captured_amount']) . "\n\n";
                }
                if ($order_payment_status == 1 && $total_paid_match) {
                    $orderPaymentObj->addOrderPayment($payment_settings["pmethod_code"], $response['id'], $payment_gateway_charge, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), $request . "#" . print_r($response, true));
                    FatApp::redirectUser(CommonHelper::generateUrl('custom', 'paymentSuccess', array($order_id)));
                } else {
                    $orderPaymentObj->addOrderPaymentComments($request);
                }
            }
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
        return $amount*100;
        return $amount;
    }

    private function validatePayFortStartSettings($paymentSettings = array())
    {
        $settingVal = array('transaction_mode','secret_key','open_key');
        foreach ($settingVal as $val) {
            if (!isset($paymentSettings[$val]) || strlen(trim($paymentSettings[$val])) == 0) {
                return false;
            }
        }
        return true;
    }

    private function getPaymentForm($order_id)
    {
        return '';
    }
}
