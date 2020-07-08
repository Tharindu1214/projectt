<?php
class WalletPayController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->set('exculdeMainHeaderDiv', true);
    }

    public function charge($orderId)
    {
        $isAjaxCall = FatUtility::isAjaxCall();

        if (!$orderId || ((isset($_SESSION['shopping_cart']) && $orderId != $_SESSION['shopping_cart']["order_id"])&& (isset($_SESSION['subscription_shopping_cart']))  && $orderId != $_SESSION['subscription_shopping_cart']["order_id"])) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            if ($isAjaxCall) {
                FatUtility::dieWithError(Message::getHtml());
            }
            CommonHelper::redirectUserReferer();
        }

        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            $message = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            if ($isAjaxCall) {
                FatUtility::dieWithError(Message::getHtml());
            }
            CommonHelper::redirectUserReferer();
        }

        $user_id = UserAuthentication::getLoggedUserId();

        $orderObj = new Orders();
        $srch = Orders::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_id', '=', $orderId);
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
        if (isset($_SESSION['subscription_shopping_cart']["order_id"]) && $orderId == $_SESSION['subscription_shopping_cart']["order_id"]) {
            $srch->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        } else {
            $srch->addCondition('order_type', '=', Orders::ORDER_PRODUCT);
        }
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        if (!$orderInfo) {
            $message = Labels::getLabel('MSG_Invalid_Access.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            if ($isAjaxCall) {
                FatUtility::dieWithError(Message::getHtml());
            }
            CommonHelper::redirectUserReferer();
        }
        $orderPaymentFinancials = $orderObj->getOrderPaymentFinancials($orderId);

        if ($orderPaymentFinancials["order_credits_charge"] > 0) {
            $orderPaymentObj = new OrderPayment($orderId);
            $orderPaymentObj->chargeUserWallet($orderPaymentFinancials["order_credits_charge"]);
        }

        if (!empty($_SESSION['subscription_shopping_cart']["order_id"]) && $orderId == $_SESSION['subscription_shopping_cart']["order_id"]) {
            $scartObj = new SubscriptionCart();
            $scartObj->clear();
            $scartObj->updateUserSubscriptionCart();
        } elseif (!empty($_SESSION['shopping_cart']["order_id"]) && $orderId == $_SESSION['shopping_cart']["order_id"]) {
            $cartObj = new Cart();
            $cartObj->clear();
            $cartObj->updateUserCart();
        }

        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('msg', Labels::getLabel("MSG_Payment_from_wallet_made_successfully", $this->siteLangId));
            $this->_template->render();
        }

        if ($isAjaxCall) {
            $this->set('redirectUrl', CommonHelper::generateUrl('Custom', 'paymentSuccess', array($orderId)));
            $this->set('msg', Labels::getLabel("MSG_Payment_from_wallet_made_successfully", $this->siteLangId));
            $this->_template->render(false, false, 'json-success.php');
        }
        FatApp::redirectUser(CommonHelper::generateUrl('Custom', 'paymentSuccess', array($orderId)));
    }

    public function Recharge($orderId, $appParam = '', $appLang = '1', $appCurrency = '1')
    {
        if ($appParam == 'api' && false ===  MOBILE_APP_API_CALL) {
            $langId =  FatUtility::int($appLang);
            if (0 < $langId) {
                $languages = Language::getAllNames();
                if (array_key_exists($langId, $languages)) {
                    setcookie('defaultSiteLang', $langId, time()+3600*24*10, CONF_WEBROOT_URL);
                }
            }

            $currencyId =  FatUtility::int($appCurrency);
            $currencyObj = new Currency();
            if (0 < $currencyId) {
                $currencies = Currency::getCurrencyAssoc($this->siteLangId);
                if (array_key_exists($currencyId, $currencies)) {
                    setcookie('defaultSiteCurrency', $currencyId, time()+3600*24*10, CONF_WEBROOT_URL);
                }
            }
            commonhelper::setAppUser();
            FatApp::redirectUser(CommonHelper::generateUrl('WalletPay', 'recharge', array($orderId)));
        }

        $isAjaxCall = FatUtility::isAjaxCall();

        if (!UserAuthentication::isUserLogged()) {
            $message = Labels::getLabel('MSG_Your_Session_seems_to_be_expired', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            if ($isAjaxCall) {
                FatUtility::dieWithError(Message::getHtml());
            }
            CommonHelper::redirectUserReferer();
        }
        if ($orderId == '' || ((isset($_SESSION['wallet_recharge_cart']) && !empty($_SESSION['wallet_recharge_cart']) && $orderId != $_SESSION['wallet_recharge_cart']["order_id"]))) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
            /* if( $isAjaxCall ){
            FatUtility::dieWithError( Message::getHtml() );
            }
            CommonHelper::redirectUserReferer(); */
        }

        $loggedUserId = UserAuthentication::getLoggedUserId();
        $orderObj = new Orders();
        $srch = Orders::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_id', '=', $orderId);
        $srch->addCondition('order_user_id', '=', $loggedUserId);
        $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
        $srch->addCondition('order_type', '=', Orders::ORDER_WALLET_RECHARGE);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        if (!$orderInfo) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            if ($isAjaxCall) {
                FatUtility::dieWithError(Message::getHtml());
            }
            CommonHelper::redirectUserReferer();
        }
        $this->set('orderInfo', $orderInfo);
        //CommonHelper::printArray( $orderInfo );
        $obj = new Extrapage();
        $headerData = $obj->getContentByPageType(Extrapage::CHECKOUT_PAGE_HEADER_BLOCK, $this->siteLangId);
        $pmSrch = PaymentMethods::getSearchObject($this->siteLangId);
        $pmSrch->doNotCalculateRecords();
        $pmSrch->doNotLimitRecords();
        $pmSrch->addMultipleFields(array('pmethod_id', 'IFNULL(pmethod_name, pmethod_identifier) as pmethod_name', 'pmethod_code', 'pmethod_description'));
        $pmRs = $pmSrch->getResultSet();
        $paymentMethods = FatApp::getDb()->fetchAll($pmRs);
        $excludePaymentGatewaysArr = applicationConstants::getExcludePaymentGatewayArr();
        $this->set('paymentMethods', $paymentMethods);
        $this->set('excludePaymentGatewaysArr', $excludePaymentGatewaysArr);
        $this->set('headerData', $headerData);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(true, true);
    }

    public function PaymentTab($order_id, $pmethod_id)
    {
        $pmethod_id = FatUtility::int($pmethod_id);
        if (!$pmethod_id) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Invalid_Request!", $this->siteLangId));
        }

        if (!UserAuthentication::isUserLogged()) {
            /* Message::addErrorMessage( Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId) );
            FatUtility::dieWithError( Message::getHtml() ); */
            FatUtility::dieWithError(Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId));
        }

        $srch = Orders::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_id', '=', $order_id);
        $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        /* $orderObj = new Orders();
        $orderInfo = $orderObj->getOrderById( $order_id, $this->siteLangId, array('payment_status' => 0) ); */
        if (!$orderInfo) {
            /* Message::addErrorMessage( Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId) );
            $this->set('error', Message::getHtml() ); */
            FatUtility::dieWithError(Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }

        //commonHelper::printArray($orderInfo);

        $pmSrch = PaymentMethods::getSearchObject($this->siteLangId);
        $pmSrch->doNotCalculateRecords();
        $pmSrch->doNotLimitRecords();
        $pmSrch->addMultipleFields(array('pmethod_id', 'IFNULL(pmethod_name, pmethod_identifier) as pmethod_name', 'pmethod_code', 'pmethod_description'));
        $pmSrch->addCondition('pmethod_id', '=', $pmethod_id);
        $pmRs = $pmSrch->getResultSet();
        $paymentMethod = FatApp::getDb()->fetch($pmRs);
        //var_dump($paymentMethod);
        if (!$paymentMethod) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Selected_Payment_method_not_found!", $this->siteLangId));
        }

        $frm = $this->getPaymentTabForm($this->siteLangId, $paymentMethod['pmethod_code']);
        $controller = $paymentMethod['pmethod_code'].'Pay';
        $frm->setFormTagAttribute('action', CommonHelper::generateUrl($controller, 'charge', array($orderInfo['order_id'])));
        $frm->fill(
            array(
                'order_type' => $orderInfo['order_type'],
                'order_id' => $order_id,
                'pmethod_id' => $pmethod_id
                )
        );

        $this->set('orderInfo', $orderInfo);
        $this->set('paymentMethod', $paymentMethod);
        $this->set('frm', $frm);
        /* Partial Payment is not allowed, Wallet + COD, So, disabling COD in case of Partial Payment Wallet Selected. [ */
        /* if( strtolower($paymentMethod['pmethod_code']) == "cashondelivery" ){
        if( $this->cartObj->hasDigitalProduct() ){
        $str = Labels::getLabel( 'MSG_{COD}_is_not_available_if_your_cart_has_any_Digital_Product', $this->siteLangId );
        $str = str_replace( '{cod}', $paymentMethod['pmethod_name'], $str );
        FatUtility::dieWithError( $str );
        }
        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
        $user_id = UserAuthentication::getLoggedUserId();
        $userWalletBalance = User::getUserBalance($user_id,true);

        if( $cartSummary['cartWalletSelected'] && $userWalletBalance < $cartSummary['orderNetAmount'] ){
        $str = Labels::getLabel('MSG_Wallet_can_not_be_used_along_with_{COD}', $this->siteLangId );
        $str = str_replace( '{cod}', $paymentMethod['pmethod_name'], $str );
        FatUtility::dieWithError( $str );
        //$this->set('error', $str );
        }
        } */
        /* ] */
        $this->_template->render(false, false, '', false, false);
    }

    private function getPaymentTabForm($langId, $paymentMethodCode = '')
    {
        $frm = new Form('frmPaymentTabForm');
        $frm->setFormTagAttribute('id', 'frmPaymentTabForm');

        if (strtolower($paymentMethodCode) == "cashondelivery" && FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '')!= '' && FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '')!= '') {
            $frm->addHtml('htmlNote', 'htmlNote', '<div class="g-recaptcha" data-sitekey="'.FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '').'"></div>');
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Confirm_Payment', $langId));
        $frm->addHiddenField('', 'order_type');
        $frm->addHiddenField('', 'order_id');
        $frm->addHiddenField('', 'pmethod_id');
        return $frm;
    }

    public function ConfirmOrder()
    {
        $order_type = FatApp::getPostedData('order_type', FatUtility::VAR_INT, 0);

        /* Loading Money to wallet[ */
        if ($order_type == Orders::ORDER_WALLET_RECHARGE) {
            $criteria = array( 'isUserLogged' => true );
            /* if( !$this->isEligibleForNextStep( $criteria ) ){
            if( Message::getErrorCount() > 0 ){
            $errMsg = Message::getHtml();
            } else {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId));
            $errMsg = Message::getHtml();
            }
            FatUtility::dieWithError( $errMsg );
            } */

            $user_id = UserAuthentication::getLoggedUserId();
            $pmethod_id = FatApp::getPostedData('pmethod_id', FatUtility::VAR_INT, 0);
            $paymentMethodRow = PaymentMethods::getAttributesById($pmethod_id);
            if (!$paymentMethodRow || $paymentMethodRow['pmethod_active'] != applicationConstants::ACTIVE) {
                Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Payment_method,_Please_contact_Webadmin.", $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }

            $order_id = FatApp::getPostedData("order_id", FatUtility::VAR_STRING, "");
            if ($order_id == '') {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_Request', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $orderObj = new Orders();

            $srch = Orders::getSearchObject();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addCondition('order_id', '=', $order_id);
            $srch->addCondition('order_user_id', '=', $user_id);
            $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
            $srch->addCondition('order_type', '=', Orders::ORDER_WALLET_RECHARGE);
            $rs = $srch->getResultSet();
            $orderInfo = FatApp::getDb()->fetch($rs);
            if (!$orderInfo) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }

            $orderObj->updateOrderInfo($order_id, array('order_pmethod_id' => $pmethod_id));
            $this->_template->render(false, false, 'json-success.php');
        }
        /* ] */

        /* ConfirmOrder function is called for both wallet payments and for paymentgateway selection as well. */
        $criteria = array( 'isUserLogged' => true, 'hasProducts' => true, 'hasStock' => true, 'hasBillingAddress' => true );
        if ($this->cartObj->hasPhysicalProduct()) {
            $criteria['hasShippingAddress'] = true;
            $criteria['isProductShippingMethodSet'] = true;
        }

        $user_id = UserAuthentication::getLoggedUserId();
        $userWalletBalance = User::getUserBalance($user_id, true);
        $pmethod_id = FatApp::getPostedData('pmethod_id', FatUtility::VAR_INT, 0);

        $post = FatApp::getPostedData();
        // commonHelper::printArray($post); die;

        $paymentMethodRow = PaymentMethods::getAttributesById($pmethod_id);
        if (!$paymentMethodRow || $paymentMethodRow['pmethod_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Payment_method,_Please_contact_Webadmin.", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (strtolower($paymentMethodRow['pmethod_code']) == 'cashondelivery' && FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '')!= '' && FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '')!= '') {
            if (!CommonHelper::verifyCaptcha()) {
                Message::addErrorMessage(Labels::getLabel('MSG_That_captcha_was_incorrect', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
                //FatApp::redirectUser(CommonHelper::generateUrl('Custom', 'ContactUs'));
            }
        }

        /* Enable it if we add Shipping Users in Project Check, System have shipping company user added or nor, if not, then COD is not allowed for Project[ */
        /* if( strtolower($paymentMethodRow['pmethod_code']) == 'cashondelivery' ){
        if( !CommonHelper::verifyCaptcha() ) {
        Message::addErrorMessage(Labels::getLabel('MSG_That_captcha_was_incorrect',$this->siteLangId));
        FatUtility::dieWithError( Message::getHtml() );
        //FatApp::redirectUser(CommonHelper::generateUrl('Custom', 'ContactUs'));
        }
        $srch = User::getSearchObject( true );
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition( 'user_type', '=', User::USER_TYPE_SHIPPING_COMPANY );
        $srch->addCondition( 'uc.credential_active', '=', applicationConstants::ACTIVE );
        $srch->addCondition( 'uc.credential_verified', '=', applicationConstants::YES );
        $srch->addMultipleFields( array('user_id') );
        $rs = $srch->getResultSet();
        $userRow = FatApp::getDb()->fetch($rs);
        if( !$userRow ){
        $str = Labels::getLabel("LBL_{paymentMethodName}_is_not_available_as_Shipping_Company_is_not_added_by_admin_as_yet.", $this->siteLangId );
        $str = str_replace('{paymentmethodname}', $paymentMethodRow['pmethod_identifier'] , $str);
        Message::addErrorMessage( $str );
        FatUtility::dieWithError( Message::getHtml() );
        }
        } */
        /* ] */

        $frm = $this->getPaymentTabForm($this->siteLangId);
        $post = $frm->getFormDataFromArray($post);
        if (!isset($post['order_id']) || $post['order_id'] == '') {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $orderObj = new Orders();
        $order_id = $post['order_id'];

        $srch = Orders::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_id', '=', $order_id);
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        if (!$orderInfo) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($pmethod_id) {
            $orderObj->updateOrderInfo($order_id, array('order_pmethod_id' => $pmethod_id));
            $this->cartObj->clear();
            $this->cartObj->updateUserCart();
        }

        /* if ( !$orderObj->addOrderHistory( $order_id, 1, Labels::getLabel("LBL_-NA-",$this->siteLangId), true, $this->siteLangId ) ){
        Message::addErrorMessage( $orderObj->getError() );
        FatUtility::dieWithError( Message::getHtml() );
        } */
        $this->_template->render(false, false, 'json-success.php');
    }
}
