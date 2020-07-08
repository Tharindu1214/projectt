<?php
require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/omise/lib/Omise.php';

class OmisePayController extends PaymentController
{
    private $keyName = "omise";
    private $paymentSettings = "omise";

    public function __construct($action)
    {
        parent::__construct($action);
        $pmObj = new PaymentSettings($this->keyName);
        if (!$this->paymentSettings = $pmObj->getPaymentSettings()) {
            Message::addErrorMessage($pmObj->getError());
            CommonHelper::redirectUserReferer();
        }
        if (!defined('OMISE_PUBLIC_KEY')) {
            define('OMISE_PUBLIC_KEY', $this->paymentSettings['public_key']);
        }
        if (!defined('OMISE_SECRET_KEY')) {
            define('OMISE_SECRET_KEY', $this->paymentSettings['secret_key']);
        }
    }

    public function charge($orderId = '')
    {
        if (empty(trim($orderId))) {
            FatUtility::exitWIthErrorCode(404);
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

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set('cancelBtnUrl', $cancelBtnUrl);

        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);
        $this->set('exculdeMainHeaderDiv', true);
        $this->_template->addCss('css/payment.css');
        $this->_template->render(true, false);
    }
    public function send($orderId)
    {
        $post = FatApp::getPostedData();
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $orderPaymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();

        if ($orderPaymentAmount > 0) {
            $orderInfo=$orderPaymentObj->getOrderPrimaryinfo();
            $orderActualPaid = ceil($orderPaymentAmount)*100; /* payment accepted in satang. i.e. to charge ฿20.00, you should set amount=2000 (฿20.00). */
            $livemode = true;
            if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == false) {
                $livemode = false;
            }
            $json = array();
            try {
                $token = OmiseToken::create(
                    array(
                    'card' => array(
                    'name'                 => FatUtility::decodeHtmlEntities($orderInfo['customer_name'], ENT_QUOTES, 'UTF-8'),
                    'number'            => str_replace(' ', '', $post['cc_number']),
                    'expiration_month'  => $post['cc_expire_date_month'],
                    'expiration_year'   => $post['cc_expire_date_year'],
                    'city'              => FatUtility::decodeHtmlEntities($orderInfo['customer_billing_city'], ENT_QUOTES, 'UTF-8'),
                    'city'              => FatUtility::decodeHtmlEntities($orderInfo['customer_billing_city'], ENT_QUOTES, 'UTF-8'),
                    'postal_code'       => FatUtility::decodeHtmlEntities($orderInfo['customer_billing_postcode'], ENT_QUOTES, 'UTF-8'),
                    'security_code'     => $post['cc_cvv'],
                    'livemode'            => $livemode
                    ))
                );
                $token_ref = $token->offsetGet('id');
                $customer = OmiseCustomer::create(
                    array(
                    'email'         => $orderInfo['customer_email'],
                    'description' => $orderInfo['customer_name']. ' (id: '.$orderInfo['customer_id'].')',
                    'card'         => $token_ref,
                    'livemode'    => $livemode
                    )
                );
                $response = OmiseCharge::create(
                    array(
                    'amount'      => $orderActualPaid,
                    'currency'    => 'thb', /* $orderInfo["order_currency_code"], */
                    'description' => 'Order-'.$orderId,
                    'ip'          => $_SERVER['REMOTE_ADDR'],
                    'customer'    => $customer->offsetGet('id'),
                    // 'card'        => $token_ref,
                    'livemode'      => $livemode
                    )
                );
                if (!$response) {
                    throw new Exception(Labels::getLabel('MSG_EMPTY_GATEWAY_RESPONSE', $this->siteLangId));
                }
                if (strtolower($response->offsetGet('status'))!='successful' || strtolower($response->offsetGet('paid')) != true) {
                    throw new Excetpion($response->offsetGet('failure_message'));
                }
                $trans = OmiseTransaction::retrieve($response->offsetGet('transaction'));
                $omise_fee = round($orderActualPaid*('.0365'), 0);
                $vat = round($omise_fee*('.07'), 0);
                $trans_fee = intval($omise_fee + $vat);
                if ($trans->offsetGet('amount') != $orderActualPaid-$trans_fee) {
                    throw new Exception(Labels::getLabel('MSG_INVALID_TRANSACTION_AMOUNT', $this->siteLangId));
                }
                /* Recording Payment in DB */
                if (!$orderPaymentObj->addOrderPayment($this->paymentSettings["pmethod_name"], $response->offsetGet('transaction'), $orderPaymentAmount, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), json_encode((array)$response))) {
                    $json['error'] = "Invalid Action";
                } else {
                    $json['redirect'] = CommonHelper::generateUrl('custom', 'paymentSuccess', array($orderId));
                }
                /* End Recording Payment in DB */
            } catch (OmiseNotFoundException $e) {
                $json['error'] = 'ERROR: ' . $e->getMessage();
            } catch (exception $e) {
                $json['error'] = 'ERROR: ' . $e->getMessage();
            }
        } else {
            $json['error'] = Labels::getLabel('MSG_Invalid_Request', $this->siteLangId);
        }
        echo json_encode($json);
    }

    private function getPaymentForm($orderId)
    {
        $frm = new Form('frmPaymentForm', array('id'=>'frmPaymentForm','action'=>CommonHelper::generateUrl('OmisePay', 'send', array($orderId)), 'class' =>"form form--normal"));
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
        $frm->addPasswordField(Labels::getLabel('LBL_CVV_SECURITY_CODE', $this->siteLangId), 'cc_cvv')->requirements()->setRequired(true);
        /* $frm->addCheckBox(Labels::getLabel('LBL_SAVE_THIS_CARD_FOR_FASTER_CHECKOUT',$this->siteLangId), 'cc_save_card','1'); */
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId), array('id'=> 'button-confirm'));
        return $frm;
    }
}
