<?php
require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/khipu/init.php';
class KhipuPayController extends PaymentController
{
    private $keyName="khipu";

    public function charge($orderId)
    {
        $pmObj = new PaymentSettings($this->keyName);
        if (!$paymentSettings = $pmObj->getPaymentSettings()) {
            Message::addErrorMessage($pmObj->getError());
            CommonHelper::redirectUserReferer();
        }
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $payment_amount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!$orderInfo['id']) {
            FatUtility::exitWithErrorCode(404);
        } elseif ($orderInfo && $orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $receiver_id = $paymentSettings['receiver_id'];
            $subject = Labels::getLabel('MSG_YoKart_Payment', $this->siteLangId);
            $body = '';
            $return_url = CommonHelper::generateFullUrl('custom', 'paymentSuccess', array($orderId));
            $notify_url = CommonHelper::generateNoAuthUrl('KhipuPay', 'send');
            $cancel_url = CommonHelper::getPaymentCancelPageUrl();
            $custom = $orderId;
            $transaction_id = 'Order-'.$orderId;
            $picture_url = '';
            $payer_email = $orderInfo['customer_email'];
            $secret = $paymentSettings['secret_key'];
            $concatenated = "receiver_id=$receiver_id&subject=$subject&body=$body&amount=$payment_amount&return_url=$return_url&cancel_url=$cancel_url&custom=$custom&transaction_id=$transaction_id&picture_url=$picture_url&payer_email=$payer_email&secret=$secret";
            $hash = sha1($concatenated);
            $configuration = new Configuration();
            $configuration->setReceiverId($paymentSettings['receiver_id']);
            $configuration->setSecret($paymentSettings['secret_key']);
            //$configuration-> setDebug (true);
            $client = new ApiClient($configuration);
            $payments = new PaymentsApi($client);
            try {
                $response = $payments->paymentsPost(
                    FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId), // Reason for purchase
                    "CLP", // Currency
                    ceil($payment_amount), // Amount
                    $transaction_id, // transaction ID in trade
                    $custom, // optional field greater long to send information to the URL notification
                    null, // Payment Description
                    null, // ID of the bank to pay
                    $return_url, // return URL
                    $cancel_url, // URL rejection
                    $picture_url, // URL Product Image
                    $notify_url, // URL notification
                    "1.3",  // notification version of the API
                    null, // Expiry Date
                    null, // Send the payment by email
                    null, // Name of payer
                    null, // Email payer
                    null, // Send email reminders
                    null, // E-mail of responsible payment
                    null, // Personal identifier of the payer, if used only you are paid with this
                    null // Commission for the integrator
                );
                FatApp::redirectUser($response->getPaymentUrl());
            } catch (exception $e) {
                Message::addErrorMessage($e->getMessage());
            }
        } else {
            Message::addErrorMessage(Labels::getLabel('M_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
    }
    public function send()
    {
        $pmObj=new PaymentSettings($this->keyName);
        $paymentSettings=$pmObj->getPaymentSettings();
        $post = FatApp::getPostedData();
        $api_version = $post['api_version'];
        $notification_token = $post['notification_token'];
        try {
            if ($api_version == '1.3') {
                $configuration = new Configuration();
                $configuration-> setSecret($paymentSettings['secret_key']);
                $configuration-> setReceiverId($paymentSettings['receiver_id']);
                $client = new ApiClient($configuration);
                $payments = new PaymentsApi($client);
                $response = $payments->paymentsGet($notification_token);
                $orderId = $response->getCustom();
                $orderPaymentObj=new OrderPayment($orderId, $this->siteLangId);
                /* Retrieve Payment to charge corresponding to your order */
                $order_payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();

                if ($order_payment_amount>0) {
                    /* Retrieve Primary Info corresponding to your order */
                    $orderInfo=$orderPaymentObj->getOrderPrimaryinfo();
                    $order_actual_paid = ceil($order_payment_amount);
                    $json = array();
                    if (!$response) {
                        throw new Exception(Labels::getLabel('MSG_EMPTY_GATEWAY_RESPONSE', $this->siteLangId));
                    }
                    if ($response-> getReceiverId() == $paymentSettings['receiver_id']) {
                        if (strtolower($response-> getStatus()) == 'done') {
                            if ($response->getAmount() == $order_actual_paid) {
                                // Make payment as complete and deliver the good or service
                                if (!$orderPaymentObj->addOrderPayment($paymentSettings["pmethod_name"], $response->getTransactionId(), $response->getAmount(), Labels::getLabel("LBL_Received_Payment", $this->siteLangId), $response->__toString())) {
                                }
                            } else {
                                $request = $response->__toString()."\n\n KHIPU :: TOTAL PAID MISMATCH! " . $response-> getAmount() . "\n\n";
                                $orderPaymentObj->addOrderPaymentComments($request);
                            }
                        }
                    } else {
                        $request = $response->__toString()."\n\n KHIPU :: RECEIVER MISMATCH! " . $response-> getReceiverId() . "\n\n";
                        $orderPaymentObj->addOrderPaymentComments($request);
                    }
                } else {
                    $json['error'] = Labels::getLabel('MSG_Invalid_Request', $this->siteLangId);
                }
            } else {
                // Use previous version of Notification API
            }
        } catch (OmiseNotFoundException $e) {
            $json['error'] = 'ERROR: ' . $e->getMessage();
        } catch (exception $e) {
            $json['error'] = 'ERROR: ' . $e->getMessage();
        }
        echo json_encode($json);
    }
}
