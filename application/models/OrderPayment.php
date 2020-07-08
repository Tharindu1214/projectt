<?php
class OrderPayment extends Orders
{
    protected $attributes;

    public function __construct($orderId = 0, $langId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $orderId);

        $this->paymentOrderId = $orderId;
        $this->orderLangId = $langId;
        $this->loadData();
    }

    protected function loadData()
    {
        $this->attributes = $this->getOrderInfo();
    }

    private function getOrderInfo()
    {
        return $orderInfo = $this->getOrderById($this->paymentOrderId);
    }

    public function getOrderPaymentGatewayAmount()
    {
        $orderInfo = $this->attributes;
        $orderPaymentGatewayCharge = $orderInfo["order_net_amount"] - $orderInfo['order_wallet_amount_charge'];
        return round($orderPaymentGatewayCharge, 2);
    }

    public function getOrderPrimaryinfo()
    {
        $arrOrder = array();
        $orderInfo = $this->attributes;

        $userObj = new User($orderInfo["order_user_id"]);
        $userInfo = $userObj->getUserInfo(array('user_name','credential_email','user_phone'));
        $addresses = $this->getOrderAddresses($orderInfo["order_id"]);
        $currencyArr = Currency::getCurrencyAssoc($this->orderLangId);
        $orderCurrencyCode = !empty($currencyArr[FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)]) ? $currencyArr[FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)] : '' ;

        $billingArr = array(
        "customer_billing_name"=>'',
        "customer_billing_address_1"=>'',
        "customer_billing_address_2"=>'',
        "customer_billing_city"=>'',
        "customer_billing_postcode"=>'',
        "customer_billing_state"=>'',
        "customer_billing_country"=>'',
        "customer_billing_country_code"=>'',
        "customer_billing_phone"=>'',
        );

        $shippingArr = array(
        "customer_shipping_name"=>'',
        "customer_shipping_address_1"=>'',
        "customer_shipping_address_2"=>'',
        "customer_shipping_city"=>'',
        "customer_shipping_state"=>'',
        "customer_shipping_postcode"=>'',
        "customer_shipping_country"=>'',
        "customer_shipping_country_code"=>'',
        "customer_shipping_phone"=>'',
        );

        $arrOrder = array(
        "id"=>$orderInfo["order_id"],
        "invoice"=>$orderInfo["order_id"],
        "customer_id"=>$orderInfo["order_user_id"],
        "customer_name"=>$userInfo["user_name"],
        "customer_email"=>$userInfo["credential_email"],
        "customer_phone"=>$userInfo["user_phone"],
        "order_currency_code"=> $orderCurrencyCode,
        "order_type"=>$orderInfo['order_type'],
        "order_is_paid"=>$orderInfo["order_is_paid"],
        "order_language"=>$orderInfo["order_language_code"],
        "site_system_name"=>FatApp::getConfig("CONF_WEBSITE_NAME_".$orderInfo["order_language_id"]),
        "site_system_admin_email"=>FatApp::getConfig("CONF_SITE_OWNER_EMAIL", FatUtility::VAR_STRING, ''),
        "order_wallet_amount_charge"    =>    $orderInfo['order_wallet_amount_charge'],
        "paypal_bn"=>"FATbit_SP",
        );

        /* if (empty($orderInfo) || empty($userInfo) || empty($addresses)){
        return $arrOrder;
        } */
        /* CommonHelper::printArray($addresses);die; */
        if (!empty($addresses[Orders::BILLING_ADDRESS_TYPE])) {
            $billingArr = array(
            "customer_billing_name"=>$addresses[Orders::BILLING_ADDRESS_TYPE]["oua_name"],
            "customer_billing_address_1"=>$addresses[Orders::BILLING_ADDRESS_TYPE]["oua_address1"],
            "customer_billing_address_2"=>$addresses[Orders::BILLING_ADDRESS_TYPE]["oua_address2"],
            "customer_billing_city"=>$addresses[Orders::BILLING_ADDRESS_TYPE]["oua_city"],
            "customer_billing_postcode"=>$addresses[Orders::BILLING_ADDRESS_TYPE]["oua_zip"],
            "customer_billing_state"=>$addresses[Orders::BILLING_ADDRESS_TYPE]["oua_state"],
            "customer_billing_country"=>$addresses[Orders::BILLING_ADDRESS_TYPE]["oua_country"],
            "customer_billing_country_code"=>$addresses[Orders::BILLING_ADDRESS_TYPE]["oua_country_code"],
            "customer_billing_phone"=>$addresses[Orders::BILLING_ADDRESS_TYPE]["oua_phone"],
            );
        }

        if (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) {
            $shippingArr = array(
            "customer_shipping_name"=>$addresses[Orders::SHIPPING_ADDRESS_TYPE]["oua_name"],
            "customer_shipping_address_1"=>$addresses[Orders::SHIPPING_ADDRESS_TYPE]["oua_address1"],
            "customer_shipping_address_2"=>$addresses[Orders::SHIPPING_ADDRESS_TYPE]["oua_address2"],
            "customer_shipping_city"=>$addresses[Orders::SHIPPING_ADDRESS_TYPE]["oua_city"],
            "customer_shipping_state"=>$addresses[Orders::SHIPPING_ADDRESS_TYPE]["oua_state"],
            "customer_shipping_postcode"=>$addresses[Orders::SHIPPING_ADDRESS_TYPE]["oua_zip"],
            "customer_shipping_country"=>$addresses[Orders::SHIPPING_ADDRESS_TYPE]["oua_country"],
            "customer_shipping_country_code"=>$addresses[Orders::SHIPPING_ADDRESS_TYPE]["oua_country_code"],
            "customer_shipping_phone"=>$addresses[Orders::SHIPPING_ADDRESS_TYPE]["oua_phone"],
            );
        } else {
            //$shippingArr = $billingArr;
        }

        $arrOrder = array_merge($arrOrder, $billingArr, $shippingArr);
        return $arrOrder;
    }

    public function addOrderPayment($paymentMethodName, $txnId, $amount, $comments = '', $response = '', $isWallet = false, $opId = 0)
    {
        $paymentOrderId = $this->paymentOrderId;
        $defaultSiteLangId = FatApp::getConfig('conf_default_site_lang');

        $orderInfo = $this->attributes;
        if (!empty($orderInfo)) {
            /* if(isset($_SESSION['subscription_shopping_cart']["order_id"]) && ($orderInfo['order_id'] == $_SESSION['subscription_shopping_cart']["order_id"])){
            $scartObj = new SubscriptionCart();
            $scartObj->clear();
            $scartObj->updateUserSubscriptionCart();
            }elseif(isset($_SESSION['shopping_cart']["order_id"]) && ($orderInfo['order_id'] == $_SESSION['shopping_cart']["order_id"])){
            $cartObj=new Cart($orderInfo['order_user_id']);
            $cartObj->clear();
            $cartObj->updateUserCart();
            } */

            $orderPaymentFinancials = $this->getOrderPaymentFinancials($paymentOrderId, $this->orderLangId);
            $orderCredits = $orderPaymentFinancials["order_credits_charge"];

            if ($orderCredits > 0 && !$isWallet) {
                $this->chargeUserWallet($orderCredits);
            }

            $orderDetails = $this->getOrderById($paymentOrderId);

            if (!FatApp::getDb()->insertFromArray('tbl_order_payments', array('opayment_order_id' => $paymentOrderId, 'opayment_method' => $paymentMethodName,'opayment_gateway_txn_id'=>$txnId,'opayment_amount'=>$amount,'opayment_comments'=>$comments,'opayment_gateway_response'=>$response,'opayment_date' => date('Y-m-d H:i:s')))) {
                $this->error = FatApp::getDb()->getError();
                return false;
            }

            $totalPaymentPaid = $this->getOrderPaymentPaid($paymentOrderId);
            $orderBalance = ($orderDetails['order_net_amount'] - $totalPaymentPaid) ;

            if ($orderBalance <= 0) {
                $this->addOrderPaymentHistory($paymentOrderId, Orders::ORDER_IS_PAID, Labels::getLabel('LBL_Received_Payment', $defaultSiteLangId), 1);

                $notificationData = array(
                 'notification_record_type' => Notification::TYPE_ORDER,
                 'notification_record_id' => $paymentOrderId,
                 'notification_user_id' => $orderInfo['order_user_id'],
                 'notification_label_key' => Notification::NEW_ORDER_STATUS_NOTIFICATION,
                 'notification_added_on' => date('Y-m-d H:i:s'),
                                        );

                Notification::saveNotifications($notificationData);

                if (!empty($orderDetails['order_discount_coupon_code'])) {
                    $srch = DiscountCoupons::getSearchObject();
                    $srch->addCondition('coupon_code', '=', $orderDetails['order_discount_coupon_code']);
                    $rs = $srch->getResultSet();
                    $row = FatApp::getDb()->fetch($rs);
                    if (!empty($row)) {
                        if (!FatApp::getDb()->insertFromArray(CouponHistory::DB_TBL, array('couponhistory_coupon_id' => $row['coupon_id'], 'couponhistory_order_id' => $orderDetails['order_id'],'couponhistory_user_id'=>$orderDetails['order_user_id'],'couponhistory_amount'=>$orderDetails['order_discount_total'],'couponhistory_added_on' => date('Y-m-d H:i:s')))) {
                            $this->error = FatApp::getDb()->getError();
                            return false;
                        }
                        FatApp::getDb()->deleteRecords(DiscountCoupons::DB_TBL_COUPON_HOLD, array('smt' => 'couponhold_coupon_id = ? and couponhold_user_id = ?', 'vals' => array($row['coupon_id'], $orderDetails['order_user_id'] ) ));
                        FatApp::getDb()->deleteRecords(DiscountCoupons::DB_TBL_COUPON_HOLD_PENDING_ORDER, array( 'smt' => 'ochold_order_id = ?', 'vals'=> array( $orderDetails['order_id'] ) ));
                    }
                }
            }

            /* code added for COD Orders, if Order Product is associated with Shipping Company Users, then credit Shipping Company User's wallet to make balance 0, as debited when the Order Product is marked as delivered[ */
            /* $opId = FatUtility::int($opId);
            if( $opId ){
            $srch = new OrderProductSearch();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addCondition( 'op_id', '=', $opId );
            $rs = $srch->getResultSet();
            $childOrderInfo = FatApp::getDb()->fetch( $rs );
            if( $childOrderInfo ){
            $srch = new SearchBase(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, 'optosu');
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addCondition( 'optosu.optsu_op_id', '=', $childOrderInfo['op_id'] );
            $rs = $srch->getResultSet();
            $shippingUserRow = FatApp::getDb()->fetch( $rs );
            if( $shippingUserRow ){
            $comments = 'Cash Collected against Invoice ID: '.$childOrderInfo['op_invoice_number'];
            $txnObj = new Transactions();
            $txnDataArr = array(
             'utxn_user_id'=> $shippingUserRow['optsu_user_id'],
             'utxn_comments'=> $comments,
             'utxn_status'=> Transactions::STATUS_COMPLETED,
             'utxn_credit'=> $amount,
             'utxn_op_id'=> $childOrderInfo['op_id'],
            );
            if( !$txnObj->addTransaction($txnDataArr) ){
             $this->error = $txnObj->getError();
             return false;
            }
            }
            }
            } */
            /* ] */


            /* Credit money to user's wallet, if order_type = Orders::ORDER_WALLET_RECHARGE, i.e loading money to wallet[ */
            if ($orderDetails['order_type'] ==  Orders::ORDER_WALLET_RECHARGE) {
                $formattedOrderValue = "#".$orderDetails["order_id"];
                $transObj = new Transactions();

                /* $txnArray["utxn_user_id"]= $orderDetails["order_user_id"];
                $txnArray["utxn_credit"]= $amount;
                $txnArray["utxn_status"]= Transactions::STATUS_COMPLETED;
                $txnArray["utxn_order_id"]= $orderDetails["order_id"];
                $txnArray["utxn_date"]= date('Y-m-d H:i:s');
                $txnArray["utxn_comments"]= sprintf(Labels::getLabel('LBL_Loaded_Money_to_Wallet',$defaultSiteLangId),$formattedOrderValue);
                $transObj->assignValues($txnArray);
                if (!$transObj->save()) { $this->error = $transObj->getError(); return false;} */

                $txnDataArr = array(
                'utxn_user_id'    =>    $orderDetails["order_user_id"],
                'utxn_credit'    =>    $amount,
                'utxn_status'    =>    Transactions::STATUS_COMPLETED,
                'utxn_order_id'    =>    $orderDetails["order_id"],
                'utxn_comments'    =>    sprintf(Labels::getLabel('LBL_Loaded_Money_to_Wallet', $defaultSiteLangId), $formattedOrderValue),
                'utxn_type'        =>    Transactions::TYPE_LOADED_MONEY_TO_WALLET
                );
                if (!$txnId = $transObj->addTransaction($txnDataArr)) {
                    $this->error = $transObj->getError();
                    return false;
                }
                /* Send email to User[ */
                $emailNotificationObj = new EmailHandler();
                $emailNotificationObj->sendTxnNotification($txnId, $defaultSiteLangId);
                /* ] */
            }
            /* ] */
            return true;
        } else {
            $this->error = Labels::getLabel('MSG_Invalid_Order', $this->commonLangId);
            return false;
        }
    }

    public function confirmCodOrder($orderId, $langId)
    {
        $langId = FatUtility::int($langId);

        $db = FatApp::getDb();
        if (!$db->updateFromArray('tbl_order_products', array('op_status_id'=>FatApp::getConfig('CONF_COD_ORDER_STATUS', FatUtility::VAR_INT, 0)), array('smt' => 'op_order_id = ? ', 'vals' => array($orderId)))) {
            $this->error = $db->getError();
            return false;
        }

        $request = 'CashOnDelivery';
        $orderPaymentObj = new OrderPayment($orderId, $langId);
        $orderPaymentObj->addOrderPaymentComments($request);

        $paymentOrderId = $this->paymentOrderId;
        $orderDetails = $this->getOrderById($paymentOrderId);
        if (!empty($orderDetails['order_discount_coupon_code'])) {
            $srch = DiscountCoupons::getSearchObject();
            $srch->addCondition('coupon_code', '=', $orderDetails['order_discount_coupon_code']);
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs);
            if (!empty($row)) {
                if (!FatApp::getDb()->insertFromArray(CouponHistory::DB_TBL, array('couponhistory_coupon_id' => $row['coupon_id'], 'couponhistory_order_id' => $orderDetails['order_id'],'couponhistory_user_id'=>$orderDetails['order_user_id'],'couponhistory_amount'=>$orderDetails['order_discount_total'],'couponhistory_added_on' => date('Y-m-d H:i:s')))) {
                    $this->error = FatApp::getDb()->getError();
                    return false;
                }
                FatApp::getDb()->deleteRecords(DiscountCoupons::DB_TBL_COUPON_HOLD, array('smt' => 'couponhold_coupon_id = ? and couponhold_user_id = ?', 'vals' => array($row['coupon_id'], $orderDetails['order_user_id'] ) ));
            }
        }

        return true;
    }

    public function addOrderPaymentComments($comments)
    {
        $paymentOrderId = $this->paymentOrderId;
        $orderInfo = $this->attributes;
        if (!empty($orderInfo)) {
            $this->addOrderPaymentHistory($paymentOrderId, Orders::ORDER_IS_PENDING, $comments, false);
        } else {
            $this->error = Labels::getLabel('MSG_Invalid_Order', $this->commonLangId);
            return false;
        }
    }

    public function chargeUserWallet($amountToBeCharge)
    {
        $defaultSiteLangId = FatApp::getConfig('conf_default_site_lang');
        $orderInfo = $this->attributes;
        $userWalletBalance = User::getUserBalance($orderInfo["order_user_id"]);

        if ($userWalletBalance < $amountToBeCharge) {
            $this->error = Message::addErrorMessage(Labels::getLabel('MSG_Wallet_Balance_is_less_than_amount_to_be_charge', $defaultSiteLangId));
            return false;
        }

        $formattedOrderValue = "#".$orderInfo["order_id"];
        $transObj = new Transactions();

        /* $txnArray["utxn_user_id"] = $orderInfo["order_user_id"];
        $txnArray["utxn_debit"] = $amountToBeCharge;
        $txnArray["utxn_status"] = Transactions::STATUS_COMPLETED;
        $txnArray["utxn_order_id"] = $orderInfo["order_id"];
        $txnArray["utxn_date"] = date('Y-m-d H:i:s');
        $txnArray["utxn_comments"] = sprintf(Labels::getLabel('LBL_ORDER_PLACED_NUMBER',$defaultSiteLangId),$formattedOrderValue);
        $transObj->assignValues($txnArray);
        if (!$transObj->save()) { $this->error = $transObj->getError(); return false; } */
        $transaction_comment = Orders::getOrderCommentById($orderInfo["order_id"], $defaultSiteLangId);
        $txnDataArr = array(
        'utxn_user_id'    =>    $orderInfo["order_user_id"],
        'utxn_debit'    =>    $amountToBeCharge,
        'utxn_status'    =>    Transactions::STATUS_COMPLETED,
        'utxn_order_id'    =>    $orderInfo["order_id"],
        'utxn_comments'    =>    $transaction_comment,
        /* 'utxn_comments'=>sprintf( Labels::getLabel( 'LBL_ORDER_PLACED_NUMBER', $defaultSiteLangId ), $formattedOrderValue ), */
        'utxn_type'        =>    Transactions::TYPE_ORDER_PAYMENT
        );
        if (!$txnId = $transObj->addTransaction($txnDataArr)) {
            $this->error = $transObj->getError();
            return false;
        }
        /* Send email to User[ */
        // $emailNotificationObj = new EmailHandler();
        // $emailNotificationObj->sendTxnNotification( $txnId, $defaultSiteLangId );
        /* ] */

        // Update Order table user wallet charge amount
        $orderWalletAmountCharge = $orderInfo['order_wallet_amount_charge'] - $amountToBeCharge;
        if (!FatApp::getDb()->updateFromArray(Orders::DB_TBL, array('order_wallet_amount_charge' => $orderWalletAmountCharge), array('smt'=>'order_id = ?', 'vals'=>array($orderInfo["order_id"])))) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }

        $this->addOrderPayment(Labels::getLabel('LBL_User_Wallet', $defaultSiteLangId), 'W-'.time(), $amountToBeCharge, Labels::getLabel("LBL_Received_Payment", $defaultSiteLangId), Labels::getLabel('LBL_Payment_From_User_Wallet', $defaultSiteLangId), true);
        return true;
    }

    public function chargeFreeOrder($amountToBeCharge = 0)
    {
        $defaultSiteLangId = FatApp::getConfig('conf_default_site_lang');
        $orderInfo = $this->attributes;

        if ($amountToBeCharge > 0) {
            $this->error = Labels::getLabel('MSG_Invalid_Order', $defaultSiteLangId);
            return false;
        }

        $transObj = new Transactions();
        $formattedOrderValue = "#".$orderInfo["order_id"];
        /*
        $txnArray["utxn_user_id"]= $orderInfo["order_user_id"];
        $txnArray["utxn_debit"]= $amountToBeCharge;
        $txnArray["utxn_status"]= Transactions::STATUS_COMPLETED;
        $txnArray["utxn_order_id"]= $orderInfo["order_id"];
        $txnArray["utxn_date"]= date('Y-m-d H:i:s');
        $txnArray["utxn_comments"]= sprintf(Labels::getLabel('LBL_ORDER_PLACED_NUMBER',$defaultSiteLangId),$formattedOrderValue);
        $transObj->assignValues($txnArray);
        if (!$transObj->save()) { $this->error = $transObj->getError(); return false;} */
        if ($orderInfo['order_type'] == Orders::ORDER_PRODUCT) {
            $txnComment = sprintf(Labels::getLabel('LBL_Product_Purchased_%s', $defaultSiteLangId), $formattedOrderValue);
        } else {
            $txnComment = sprintf(Labels::getLabel('LBL_Subscription_Purchased_%s', $defaultSiteLangId), $formattedOrderValue);
        }
        $txnDataArr = array(
        'utxn_user_id'    =>    $orderInfo["order_user_id"],
        'utxn_debit'    =>    $amountToBeCharge,
        'utxn_status'    =>    Transactions::STATUS_COMPLETED,
        'utxn_order_id'    =>    $orderInfo["order_id"],
        'utxn_comments'    =>    $txnComment,
        'utxn_type'        =>    Transactions::TYPE_ORDER_PAYMENT
        );
        if (!$txnId = $transObj->addTransaction($txnDataArr)) {
            $this->error = $transObj->getError();
            return false;
        }
        /* Send email to User[ */
        /* $emailNotificationObj = new EmailHandler();
        $emailNotificationObj->sendTxnNotification( $txnId, $defaultSiteLangId ); */
        /* ] */

        $this->addOrderPayment(Labels::getLabel('LBL_User_Wallet', $defaultSiteLangId), 'W-'.time(), $amountToBeCharge, Labels::getLabel("LBL_Received_Payment", $defaultSiteLangId), Labels::getLabel('LBL_Payment_From_User_Wallet', $defaultSiteLangId), true);
        return true;
    }
}
