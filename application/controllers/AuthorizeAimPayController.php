<?php
require CONF_INSTALLATION_PATH . 'library/payment-plugins/AuthorizeNet/autoload.php';
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

// define("AUTHORIZENET_LOG_FILE", "phplog");

class AuthorizeAimPayController extends PaymentController
{
    const SANDBOX = "https://apitest.authorize.net";
    const PRODUCTION = "https://api2.authorize.net";

    const VERSION = "2.0.0";

    private $keyName = "AuthorizeAim";
    private $currenciesAccepted = array(
                                            'Australian Dollar' => 'AUD',
                                            'Canadian Dollar' => 'CAD',
                                            'Danish Krone' => 'DKK',
                                            'Euro' => 'EUR',
                                            'Norwegian Krone' => 'NOK',
                                            'New Zealand Dollar' => 'NZD',
                                            'Polish Zloty' => 'PLN',
                                            'Pound Sterling' => 'GBP',
                                            'Swedish Krona' => 'SEK',
                                            'Swiss Franc' => 'CHF',
                                            'U.S. Dollar' => 'USD',
                                    );

    public function charge($orderId = '')
    {
        if (empty($orderId)) {
            FatUtility::exitWIthErrorCode(404);
        }

        $pmObj = new PaymentSettings($this->keyName);
        if (!$paymentSettings = $pmObj->getPaymentSettings()) {
            Message::addErrorMessage($pmObj->getError());
            CommonHelper::redirectUserReferer();
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!$orderInfo['id']) {
            FatUtility::exitWIthErrorCode(404);
        } elseif ($orderInfo["order_is_paid"] == Orders::ORDER_IS_PENDING) {
            $frm = $this->getPaymentForm($orderId);
            $this->set('frm', $frm);
            $this->set('paymentAmount', $paymentAmount);
        } else {
            $this->set('error', Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }

        if (count($this->currenciesAccepted) && !in_array($orderInfo["order_currency_code"], $this->currenciesAccepted)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_ORDER_CURRENCY_PASSED_TO_GATEWAY', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set('cancelBtnUrl', $cancelBtnUrl);
        $this->set('orderInfo', $orderInfo);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('exculdeMainHeaderDiv', true);
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

    private function getPaymentForm($orderId = '')
    {
        $frm = new Form('frmPaymentForm', array('id'=>'frmPaymentForm','action'=>CommonHelper::generateUrl('AuthorizeAimPay', 'send', array($orderId)), 'class' =>"form form--normal"));
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
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId));
        return $frm;
    }

    public function send($orderId)
    {
        $pmObj=new PaymentSettings($this->keyName);
        $paymentSettings=$pmObj->getPaymentSettings();

        $frm = $this->getPaymentForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            $message['error'] = Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError($message);
        }

        $invalidCardDetail = false;
        if (date('Y') == $post['cc_expire_date_year'] && date('m') > $post['cc_expire_date_month']) {
            $invalidCardDetail = true;
        } else if (date('Y') > $post['cc_expire_date_year']) {
            $invalidCardDetail = true;
        }

        if (true === $invalidCardDetail) {
            $message['error'] = Labels::getLabel("MSG_Invalid_card_detail", $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $orderPaymentObj=new OrderPayment($orderId, $this->siteLangId);
        /* Retrieve Payment to charge corresponding to your order */
        $orderPaymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();

        if ($orderPaymentAmount > 0) {
            $orderActualPaid = number_format(round($orderPaymentAmount, 2), 2, ".", "");

            $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

            /* Create a merchantAuthenticationType object with authentication details
               retrieved from the constants file */
            $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
            $merchantAuthentication->setName($paymentSettings['login_id']);
            $merchantAuthentication->setTransactionKey($paymentSettings['transaction_key']);

            // Set the transaction's refId
            $refId = $orderId;

            // Create the payment data for a credit card
            $creditCard = new AnetAPI\CreditCardType();
            $creditCard->setCardNumber(str_replace(' ', '', $post['cc_number']));

            $creditCard->setExpirationDate($post['cc_expire_date_year'] ."-".$post['cc_expire_date_month']);
            $creditCard->setCardCode($post['cc_cvv']);

            // Add the payment data to a paymentType object
            $paymentOne = new AnetAPI\PaymentType();
            $paymentOne->setCreditCard($creditCard);

            // Create order information
            $order = new AnetAPI\OrderType();
            $order->setInvoiceNumber($orderId);
            $orderPaymentGatewayDescription = sprintf(Labels::getLabel("MSG_Order_Payment_Gateway_Description", $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);
            $order->setDescription($orderPaymentGatewayDescription);

            // Set the customer's Bill To address
            $customerAddress = new AnetAPI\CustomerAddressType();
            $customerAddress->setFirstName(trim($orderInfo['customer_name']));
            $customerAddress->setLastName("");
            $customerAddress->setCompany(trim($orderInfo['customer_name']));
            $customerAddress->setAddress($orderInfo['customer_billing_address_1']);
            $customerAddress->setCity($orderInfo['customer_billing_city']);
            $customerAddress->setState($orderInfo['customer_billing_state']);
            $customerAddress->setZip($orderInfo['customer_billing_postcode']);
            $customerAddress->setCountry($orderInfo['customer_billing_country']);

            // Set the customer's identifying information
            $customerData = new AnetAPI\CustomerDataType();
            $customerData->setType("individual");
            $customerData->setId($orderInfo['customer_phone']);
            $customerData->setEmail($orderInfo['customer_email']);

            // Add values for transaction settings
            $duplicateWindowSetting = new AnetAPI\SettingType();
            $duplicateWindowSetting->setSettingName("duplicateWindow");
            $duplicateWindowSetting->setSettingValue("60");

            // Create a TransactionRequestType object and add the previous objects to it
            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType("authCaptureTransaction");
            $transactionRequestType->setAmount($orderActualPaid);
            $transactionRequestType->setOrder($order);
            $transactionRequestType->setPayment($paymentOne);
            $transactionRequestType->setBillTo($customerAddress);
            $transactionRequestType->setCustomer($customerData);
            $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);

            // Assemble the complete transaction request
            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setRefId($refId);
            $request->setTransactionRequest($transactionRequestType);

            // Create the controller and get the response
            $controller = new AnetController\CreateTransactionController($request);

            $actionUrl = (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true)?static::PRODUCTION:static::SANDBOX;
            $response = $controller->executeWithApiResponse($actionUrl);

            if ($response != null) {
                // Check to see if the API request was successfully received and acted upon
                if ($response->getMessages()->getResultCode() == "Ok") {
                    // Since the API request was successful, look for a transaction response
                    // and parse it to display the results of authorizing the card
                    $tresponse = $response->getTransactionResponse();

                    if ($tresponse != null && $tresponse->getMessages() != null) {
                        $str = Labels::getLabel("Successfully created transaction with Transaction ID: {txn-id}", $this->siteLangId);
                        $message = str_replace("{txn-id}", $tresponse->getTransId(), $str). "\n";

                        $str = Labels::getLabel("Transaction Response Code: {txn-resp-code}", $this->siteLangId);
                        $decription = str_replace("{txn-resp-code}", $tresponse->getResponseCode(), $str). "\n";

                        $str = Labels::getLabel("Message Code: {msg-code}", $this->siteLangId);
                        $decription .= str_replace("{msg-code}", $tresponse->getMessages()[0]->getCode(), $str). "\n";

                        $str = Labels::getLabel("Auth Code: {auth-code}", $this->siteLangId);
                        $decription .= str_replace("{auth-code}", $tresponse->getAuthCode(), $str). "\n";

                        $str = Labels::getLabel("Description: {description}", $this->siteLangId);
                        $decription .= str_replace("{description}", $tresponse->getMessages()[0]->getDescription(), $str). "\n";

                        if (!$orderPaymentObj->addOrderPayment($paymentSettings["pmethod_name"], $tresponse->getTransId(), $orderPaymentAmount, Labels::getLabel("MSG_Received_Payment", $this->siteLangId), $message)) {
                            $json['error'] = Labels::getLabel('MSS_Transaction_Failed', $this->siteLangId);
                        } else {
                            $json['msg'] = $message;
                            $json['description'] = $decription;
                            $json['redirect'] = CommonHelper::generateUrl('custom', 'paymentSuccess', array($orderId));
                        }
                    } else {
                        $json['errorMsg'] =  Labels::getLabel('Transaction Failed', $this->siteLangId);
                        if ($tresponse->getErrors() != null) {
                            $json['error'] = $tresponse->getErrors()[0]->getErrorText();
                            $json['errorCode'] = $tresponse->getErrors()[0]->getErrorCode();
                        }
                    }
                    // Or, print errors if the API request wasn't successful
                } else {
                    $json['errorMsg'] =  Labels::getLabel('MSG_Transaction Failed', $this->siteLangId);
                    $tresponse = $response->getTransactionResponse();
                    if ($tresponse != null && $tresponse->getErrors() != null) {
                        $json['error'] = $tresponse->getErrors()[0]->getErrorText();
                        $json['errorCode'] = $tresponse->getErrors()[0]->getErrorCode();
                    } else {
                        $json['error'] = $response->getMessages()->getMessage()[0]->getText();
                        $json['errorCode'] = $response->getMessages()->getMessage()[0]->getCode();
                    }
                }
            } else {
                $json['error'] = Labels::getLabel('MSG_No_response_returned', $this->siteLangId);
            }
        } else {
            $json['error'] = Labels::getLabel('MSG_Invalid_Request', $this->siteLangId);
        }
        echo json_encode($json);
    }
}
