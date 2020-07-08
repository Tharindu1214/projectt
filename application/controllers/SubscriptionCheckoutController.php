<?php
class SubscriptionCheckoutController extends MyAppController
{
    private $cartObj;
    public function __construct($action)
    {
        parent::__construct($action);
        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl());
        }
        $user_id = 0;
        if (!UserAuthentication::isUserLogged() || !User::canViewSupplierTab()) {
            $errMsg = Labels::getLabel('MSG_Please_login_with_seller_account', $this->siteLangId);
            Message::addErrorMessage($errMsg);
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Message::getHtml());
            }
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'loginForm'));
        }
        $user_id = UserAuthentication::getLoggedUserId();
        $this->scartObj = new SubscriptionCart($user_id, $this->siteLangId);
        $this->set('exculdeMainHeaderDiv', true);
    }

    private function isEligibleForNextStep(&$criteria = array())
    {
        if (empty($criteria)) {
            return true;
        }
        foreach ($criteria as $key => $val) {
            switch ($key) {
                case 'isUserLogged':
                    if (!UserAuthentication::isUserLogged()) {
                        $key = false;
                        Message::addErrorMessage(Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId));
                        return false;
                    }
                    break;
                case 'hasSubscription':
                    if (!$this->scartObj->hasSusbscription()) {
                        $key = false;
                        Message::addErrorMessage(Labels::getLabel('MSG_Your_cart_seems_to_be_empty,_Please_try_after_reloading_the_page.', $this->siteLangId));
                        return false;
                    }
                    break;
            }
        }
        return true;
    }

    public function index()
    {
        $criteria = array('hasSubscription' => true);
        if (!$this->isEligibleForNextStep($criteria)) {
            FatApp::redirectUser(CommonHelper::generateUrl('seller', 'packages'));
        }
        $obj = new Extrapage();
        $headerData = $obj->getContentByPageType(Extrapage::CHECKOUT_PAGE_HEADER_BLOCK, $this->siteLangId);
        $this->set('sCartSummary', $this->scartObj->getSubscriptionCartFinancialSummary($this->siteLangId));
        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::CHECKOUT_PAGE_RIGHT_BLOCK, $this->siteLangId);
        $this->set('pageData', $pageData);
        $this->set('headerData', $headerData);
        $this->_template->render();
    }

    public function login()
    {
        $loginFormData = array(
        'frm'             => $this->getLoginForm(),
        'siteLangId'    => $this->siteLangId,
        'showSignUpLink' => false,
        'onSubmitFunctionName' => 'setUpLogin'
        );
        $this->set('loginFormData', $loginFormData);

        $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
        $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_TERMS_AND_CONDITIONS_PAGE', FatUtility::VAR_INT, 0));
        $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
        if (!empty($cpage) && is_array($cpage)) {
            $termsAndConditionsLinkHref = CommonHelper::generateUrl('Cms', 'view', array($cpage['cpage_id']));
        } else {
            $termsAndConditionsLinkHref = 'javascript:void(0)';
        }

        $signUpFrm = $this->getRegistrationForm(false);
        $signUpFrm->addHiddenField('', 'isCheckOutPage', 1);

        $signUpFormData = array(
        'frm'            =>    $signUpFrm,
        'siteLangId'    =>    $this->siteLangId,
        'showLogInLink' => false,
        'onSubmitFunctionName'        =>    'setUpRegisteration',
        'termsAndConditionsLinkHref'=> $termsAndConditionsLinkHref,
        );

        $this->set('signUpFormData', $signUpFormData);
        $this->_template->render(false, false);
    }

    public function loginDetails()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $user_email = UserAuthentication::getLoggedUserAttribute('user_email');
        $this->set('user_email', $user_email);
        $this->_template->render(false, false);
    }

    public function reviewScart()
    {
        $criteria = array( 'isUserLogged' => true, 'hasSubscription' => true);

        if (!$this->isEligibleForNextStep($criteria)) {
            if (Message::getErrorCount()) {
                $errMsg = Message::getHtml();
            } else {
                Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId));
                $errMsg = Message::getHtml();
            }
            FatUtility::dieWithError($errMsg);
        }

        $this->set('subscriptions', $this->scartObj->getSubscription($this->siteLangId));
        $this->set('scartSummary', $this->scartObj->getSubscriptionCartFinancialSummary($this->siteLangId));
        $this->_template->render(false, false);
    }

    private function getCartSubscriptionInfo($spplan_id)
    {
        $selprod_id = FatUtility::int($spplan_id);
        $prodSrch = new SellerPackagePlansSearch($this->siteLangId);

        $prodSrch->joinPackage();

        $prodSrch->addCondition('spplan_id', '=', $spplan_id);
        $fields = array( 'spplan_id','spplan_price' ,'spackage_images_per_product','spackage_type','spackage_products_allowed','spackage_inventory_allowed','spplan_interval','spplan_frequency','spackage_commission_rate' );
        $prodSrch->addMultipleFields($fields);
        $rs = $prodSrch->getResultSet();
        return $subscriptionInfo = FatApp::getDb()->fetch($rs);
    }

    private function getSubscriptionCartLangData($spplan_id, $lang_id)
    {
        $langProdSrch = new SellerPackagePlansSearch();
        $langProdSrch->joinPackage($lang_id);
        $langProdSrch->doNotCalculateRecords();
        $langProdSrch->doNotLimitRecords();
        $langProdSrch->addCondition('spplan_id', '=', $spplan_id);
        $fields = array( 'IFNULL(spackage_name, spackage_identifier) as spackage_name' );
        $langProdSrch->addMultipleFields($fields);
        $langProdRs = $langProdSrch->getResultSet();
        return $langSpecificsubscriptionInfo = FatApp::getDb()->fetch($langProdRs);
    }

    public function PaymentSummary()
    {
        $criteria = array( 'isUserLogged' => true, 'hasSubscription' => true );

        if (!$this->isEligibleForNextStep($criteria)) {
            if (Message::getErrorCount()) {
                $errMsg = Message::getHtml();
            } else {
                Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId));
                $errMsg = Message::getHtml();
            }
            FatUtility::dieWithError($errMsg);
        }


        $cartSummary = $this->scartObj->getSubscriptionCartFinancialSummary($this->siteLangId);

        $pmSrch = PaymentMethods::getSearchObject($this->siteLangId);
        $pmSrch->doNotCalculateRecords();
        $pmSrch->doNotLimitRecords();
        $pmSrch->addMultipleFields(array('pmethod_id', 'IFNULL(pmethod_name, pmethod_identifier) as pmethod_name', 'pmethod_code', 'pmethod_description'));
        $pmRs = $pmSrch->getResultSet();
        $paymentMethods = FatApp::getDb()->fetchAll($pmRs);

        $orderData = array();
        /* add Order Data[ */
        $order_id = isset($_SESSION['subscription_shopping_cart']["order_id"]) ? $_SESSION['subscription_shopping_cart']["order_id"] : false;

        $userId =  UserAuthentication::getLoggedUserId();
        $orderData['order_id'] = $order_id;
        $orderData['order_user_id'] = $userId;
        /* $orderData['order_user_name'] = $userDataArr['user_name'];
        $orderData['order_user_email'] = $userDataArr['credential_email'];
        $orderData['order_user_phone'] = $userDataArr['user_phone']; */
        $orderData['order_is_paid'] = Orders::ORDER_IS_PENDING;
        $orderData['order_date_added'] = date('Y-m-d H:i:s');
        $orderData['order_type'] = Orders::ORDER_SUBSCRIPTION;
        $orderData['order_renew']= 0;

        /* order extras[ */
        $orderData['extra'] = array(
        'oextra_order_id'    =>    $order_id,
        'order_ip_address'    =>    $_SERVER['REMOTE_ADDR']
        );

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $orderData['extra']['order_forwarded_ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $orderData['extra']['order_forwarded_ip'] = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $orderData['extra']['order_forwarded_ip'] = '';
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $orderData['extra']['order_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $orderData['extra']['order_user_agent'] = '';
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $orderData['extra']['order_accept_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $orderData['extra']['order_accept_language'] = '';
        }
        /* ] */

        $languageRow = Language::getAttributesById($this->siteLangId);
        $orderData['order_language_id'] =  $languageRow['language_id'];
        $orderData['order_language_code'] =  $languageRow['language_code'];

        $currencyRow = Currency::getAttributesById($this->siteCurrencyId);
        $orderData['order_currency_id'] =  $currencyRow['currency_id'];
        $orderData['order_currency_code'] =  $currencyRow['currency_code'];
        $orderData['order_currency_value'] =  $currencyRow['currency_value'];

        $orderData['order_user_comments'] =  '';
        $orderData['order_admin_comments'] =  '';

        if (!empty($cartSummary["cartDiscounts"])) {
            $orderData['order_discount_coupon_code'] = $cartSummary["cartDiscounts"]["coupon_code"];
            $orderData['order_discount_type'] = $cartSummary["cartDiscounts"]["coupon_discount_type"];
            $orderData['order_discount_value'] = $cartSummary["cartDiscounts"]["coupon_discount_value"];
            $orderData['order_discount_total'] = $cartSummary["cartDiscounts"]["coupon_discount_total"];
            $orderData['order_discount_info'] = $cartSummary["cartDiscounts"]["coupon_info"];
        }

        $orderData['order_reward_point_used'] = $cartSummary["cartRewardPoints"];
        $orderData['order_reward_point_value'] = CommonHelper::convertRewardPointToCurrency($cartSummary["cartRewardPoints"]);

        $orderData['order_net_amount'] = $cartSummary["orderNetAmount"];
        $orderData['order_wallet_amount_charge'] = $cartSummary["WalletAmountCharge"];

        $orderData['order_cart_data'] = SubscriptionCart::getSubscriptionCartData();

        $allLanguages = Language::getAllNames();
        //$productSelectedShippingMethodsArr = $this->cartObj->getProductShippingMethod();

        $orderLangData = array();

        $orderData['orderLangData'] = $orderLangData;

        /* order products[ */
        $cartSubscription = $this->scartObj->getSubscription($this->siteLangId);

        $orderData['subscriptions'] = array();
        $orderData['subscrCharges'] = array();
        $subscriptionType ='';
        if ($cartSubscription) {
            foreach ($cartSubscription as $cartSubscription) {
                $subscriptionInfo = $this->getCartSubscriptionInfo($cartSubscription['spplan_id']);
                if (!$subscriptionInfo) {
                    continue;
                }
                $subscriptionLangData = array();
                foreach ($allLanguages as $lang_id => $language_name) {
                    $langSpecificsubscriptionInfo = $this->getSubscriptionCartLangData($subscriptionInfo['spplan_id'], $lang_id);
                    if (!$langSpecificsubscriptionInfo) {
                        continue;
                    }
                    $op_subscription_title = ($langSpecificsubscriptionInfo['spackage_name'] != '') ? $langSpecificsubscriptionInfo['spackage_name'] : '';
                    $subscriptionLangData[$lang_id] = array(
                    OrderSubscription::DB_TBL_LANG_PREFIX.'lang_id'    =>    $lang_id,
                    'ossubs_subscription_name'    =>    $langSpecificsubscriptionInfo['spackage_name'],
                    );
                }
                $orderData['subscriptions'][SUBSCRIPTIONCART::SUBSCRIPTION_CART_KEY_PREFIX_PRODUCT.$subscriptionInfo['spplan_id']] = array(
                OrderSubscription::DB_TBL_PREFIX.'price'        =>    $subscriptionInfo['spplan_price'],
                OrderSubscription::DB_TBL_PREFIX.'images_allowed'        =>    $subscriptionInfo['spackage_images_per_product'],
                OrderSubscription::DB_TBL_PREFIX.'products_allowed'        =>    $subscriptionInfo['spackage_products_allowed'],
                OrderSubscription::DB_TBL_PREFIX.'inventory_allowed'        =>    $subscriptionInfo['spackage_inventory_allowed'],
                OrderSubscription::DB_TBL_PREFIX.'type'        =>    $subscriptionInfo['spackage_type'],
                OrderSubscription::DB_TBL_PREFIX.'plan_id'        =>    $subscriptionInfo['spplan_id'],
                OrderSubscription::DB_TBL_PREFIX.'interval'        =>    $subscriptionInfo['spplan_interval'],
                OrderSubscription::DB_TBL_PREFIX.'frequency'        =>    $subscriptionInfo['spplan_frequency'],
                OrderSubscription::DB_TBL_PREFIX.'commission'        =>    $subscriptionInfo['spackage_commission_rate'],
                OrderSubscription::DB_TBL_PREFIX.'status_id'        =>    FatApp::getConfig("CONF_DEFAULT_SUBSCRIPTION_ORDER_STATUS"),
                'subscriptionsLangData'    =>    $subscriptionLangData,
                );
                $subscriptionType = $subscriptionInfo['spackage_type'];
                $adjustedAmount=0;
                if (FatApp::getConfig('CONF_ENABLE_ADJUST_AMOUNT_CHANGE_PLAN')) {
                    $adjustedAmount = $cartSummary["cartAdjustableAmount"];
                }

                $discount = 0;
                if (!empty($cartSummary["cartDiscounts"]["discountedSPPlanId"])) {
                    if (array_key_exists($subscriptionInfo['spplan_id'], $cartSummary["cartDiscounts"]["discountedSPPlanId"])) {
                        $discount = $cartSummary["cartDiscounts"]["discountedSPPlanId"][$subscriptionInfo['spplan_id']];
                    }
                }

                $rewardPoints = $orderData['order_reward_point_value'];
                $usedRewardPoint = 0;
                if ($rewardPoints > 0) {
                    $selProdAmount = ($cartSubscription['spplan_price'])  - $discount -$adjustedAmount ;
                    $usedRewardPoint = round((($rewardPoints * $selProdAmount)/($orderData['order_net_amount']+$rewardPoints)), 2);
                }
                //CommonHelper::printArray($cartSubscription); die();
                $orderData['subscrCharges'][SubscriptionCart::SUBSCRIPTION_CART_KEY_PREFIX_PRODUCT.$subscriptionInfo['spplan_id']] = array(

                OrderProduct::CHARGE_TYPE_DISCOUNT =>array(
                'amount' => -$discount /*[Should be negative value]*/
                ),

                OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT =>array(
                'amount' => -$usedRewardPoint /*[Should be negative value]*/
                ),
                OrderProduct::CHARGE_TYPE_ADJUST_SUBSCRIPTION_PRICE =>array(
                'amount' => -$adjustedAmount /*[Should be negative value]*/
                ),
                );
                /* [ Add order Type[ */
                $orderData['order_type']= Orders::ORDER_SUBSCRIPTION;
                /* ] */
            }
        }
        /* ] */
        /* ] */

        $orderObj = new Orders();
        if ($orderObj->addUpdateOrder($orderData, $this->siteLangId)) {
            $order_id = $orderObj->getOrderId();
        } else {
            Message::addErrorMessage($orderObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = Orders::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_id', '=', $order_id);
        $srch->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        /* $orderInfo = $orderObj->getOrderById( $order_id, $this->siteLangId, array('payment_status' => 0) ); */
        if (!$orderInfo) {
            $this->scartObj->clear();
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'viewOrder', array($order_id)));
        }
        $WalletPaymentForm = $this->getWalletPaymentForm($this->siteLangId);
        $confirmPaymentFrm = $this->getConfirmPaymentForm($this->siteLangId);
        $userWalletBalance = User::getUserBalance($userId);

        if ($userWalletBalance >= $cartSummary['orderNetAmount'] && $cartSummary['cartWalletSelected']) {
            $WalletPaymentForm->addFormTagAttribute('action', CommonHelper::generateUrl('WalletPay', 'Charge', array($order_id)));
            $WalletPaymentForm->fill(array('order_id' => $order_id));
            $WalletPaymentForm->setFormTagAttribute('onsubmit', 'confirmOrder(this); return(false);');
            $WalletPaymentForm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId));
        }

        if ($cartSummary['orderNetAmount']==0 || $cartSummary['orderNetAmount']==0) {
            $confirmPaymentFrm->addFormTagAttribute('action', CommonHelper::generateUrl('ConfirmPay', 'Charge', array($order_id)));
            $confirmPaymentFrm->fill(array('order_id' => $order_id));
            $confirmPaymentFrm->setFormTagAttribute('onsubmit', 'confirmOrder(this); return(false);');
            $confirmPaymentFrm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Confirm', $this->siteLangId));
        }
        $excludePaymentGatewaysArr = applicationConstants::getExcludePaymentGatewayArr();

        $redeemRewardFrm = $this->getRewardsForm($this->siteLangId);
        $this->set('subscriptionType', $subscriptionType);
        $this->set('redeemRewardFrm', $redeemRewardFrm);
        $this->set('paymentMethods', $paymentMethods);
        $this->set('excludePaymentGatewaysArr', $excludePaymentGatewaysArr);
        $this->set('cartSummary', $cartSummary);
        $this->set('orderInfo', $orderInfo);
        $this->set('userWalletBalance', $userWalletBalance);
        $this->set('WalletPaymentForm', $WalletPaymentForm);
        $this->set('confirmPaymentFrm', $confirmPaymentFrm);
        $this->_template->render(false, false);
    }
    public function getFinancialSummary()
    {
        //$this->scartObj->adjustPreviousPlan($this->siteLangId);
        $cartSummary = $this->scartObj->getSubscriptionCartFinancialSummary($this->siteLangId);
        $cartSubscription = $this->scartObj->getSubscription($this->siteLangId);
        $cartSubscription = current($cartSubscription);
        $this->set('spackage_type', $cartSubscription['spackage_type']);
        $this->set('cartSummary', $cartSummary);
        $this->_template->render(false, false);
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

        $pmSrch = PaymentMethods::getSearchObject($this->siteLangId);
        $pmSrch->doNotCalculateRecords();
        $pmSrch->doNotLimitRecords();
        $pmSrch->addMultipleFields(array('pmethod_id', 'IFNULL(pmethod_name, pmethod_identifier) as pmethod_name', 'pmethod_code', 'pmethod_description'));
        $pmSrch->addCondition('pmethod_id', '=', $pmethod_id);
        $pmRs = $pmSrch->getResultSet();
        $paymentMethod = FatApp::getDb()->fetch($pmRs);
        if (!$paymentMethod) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Selected_Payment_method_not_found!", $this->siteLangId));
        }

        $frm = $this->getPaymentTabForm($this->siteLangId, $paymentMethod['pmethod_code']);
        $controller = $paymentMethod['pmethod_code'].'Pay';
        $frm->setFormTagAttribute('action', CommonHelper::generateUrl($controller, 'charge', array($orderInfo['order_id'])));
        $frm->fill(
            array(
            'order_id' => $order_id,
            'pmethod_id' => $pmethod_id
            )
        );


        $this->set('paymentMethod', $paymentMethod);
        $this->set('frm', $frm);

        $this->_template->render(false, false, '', false, false);
    }

    public function walletSelection()
    {
        $post = FatApp::getPostedData();
        $payFromWallet = $post['payFromWallet'];
        //$this->cartObj = new Cart();
        $this->scartObj->updateCartWalletOption($payFromWallet);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function useRewardPoints()
    {
        $post = FatApp::getPostedData();

        if (false == $post) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (empty($post['redeem_rewards'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $rewardPoints = $post['redeem_rewards'];
        $totalBalance = UserRewardBreakup::rewardPointBalance(UserAuthentication::getLoggedUserId());
        /* var_dump($totalBalance);exit; */
        if ($totalBalance == 0 || $totalBalance < $rewardPoints) {
            Message::addErrorMessage(Labels::getLabel('ERR_Insufficient_reward_point_balance', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $scartObj = new SubscriptionCart();
        $cartSummary = $scartObj->getSubscriptionCartFinancialSummary($this->siteLangId);
        $rewardPointValues = min(CommonHelper::convertRewardPointToCurrency($rewardPoints), $cartSummary['orderNetAmount']);
        $rewardPoints = CommonHelper::convertCurrencyToRewardPoint($rewardPointValues);

        if ($rewardPoints < FatApp::getConfig('CONF_MIN_REWARD_POINT') || $rewardPoints > FatApp::getConfig('CONF_MAX_REWARD_POINT')) {
            $msg = Labels::getLabel('ERR_PLEASE_USE_REWARD_POINT_BETWEEN_{MIN}_to_{MAX}', $this->siteLangId);
            $msg = str_replace('{MIN}', FatApp::getConfig('CONF_MIN_REWARD_POINT'), $msg);
            $msg = str_replace('{MAX}', FatApp::getConfig('CONF_MAX_REWARD_POINT'), $msg);
            Message::addErrorMessage($msg);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$scartObj->updateCartUseRewardPoints($rewardPoints)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel("MSG_Used_Reward_point", $this->siteLangId).'-'.$rewardPoints);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeRewardPoints()
    {
        $scartObj = new SubscriptionCart();
        if (!$scartObj->removeUsedRewardPoints()) {
            Message::addErrorMessage(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel("MSG_used_reward_point_removed", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function ConfirmOrder()
    {
        /* ConfirmOrder function is called for both wallet payments and for paymentgateway selection as well. */
        $criteria = array( 'isUserLogged' => true, 'hasSubscription' => true );

        if (!$this->isEligibleForNextStep($criteria)) {
            if (Message::getErrorCount() > 0) {
                $errMsg = Message::getHtml();
            } else {
                Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId));
                $errMsg = Message::getHtml();
            }
            FatUtility::dieWithError($errMsg);
        }
        $user_id = UserAuthentication::getLoggedUserId();
        $cartSummary = $this->scartObj->getSubscriptionCartFinancialSummary($this->siteLangId);

        $userWalletBalance = User::getUserBalance($user_id);

        $post = FatApp::getPostedData();
        $pmethod_id = FatApp::getPostedData('pmethod_id', FatUtility::VAR_INT, 0);


        if ($userWalletBalance >= $cartSummary['orderNetAmount'] && $cartSummary['cartWalletSelected'] && !$pmethod_id) {
            $frm = $this->getWalletPaymentForm($this->siteLangId);
        } else {
            $frm = $this->getPaymentTabForm($this->siteLangId);
        }

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
        $srch->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        if (!$orderInfo) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }


        if ($cartSummary['orderPaymentGatewayCharges'] == 0 && $pmethod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Amount_for_payment_gateway_must_be_greater_than_zero.', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($cartSummary['cartWalletSelected'] && $userWalletBalance >= $cartSummary['orderNetAmount'] && !$pmethod_id) {
            $this->_template->render(false, false, 'json-success.php');
            exit;
        }
        if ($cartSummary['orderPaymentGatewayCharges'] == 0) {
            $this->_template->render(false, false, 'json-success.php');
            exit;
        }

        $paymentMethodRow = PaymentMethods::getAttributesById($pmethod_id);

        if (!$paymentMethodRow || $paymentMethodRow['pmethod_active'] != applicationConstants::ACTIVE  && $cartSummary['orderPaymentGatewayCharges']>0) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Payment_method,_Please_contact_Webadmin.", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }


        if ($cartSummary['cartWalletSelected'] && $cartSummary['orderPaymentGatewayCharges'] == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Try_to_pay_using_wallet_balance_as_amount_for_payment_gateway_is_not_enough.', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }



        if ($pmethod_id) {
            $_SESSION['order_type'] = Orders::ORDER_SUBSCRIPTION;
            $orderObj->updateOrderInfo($order_id, array('order_pmethod_id' => $pmethod_id));
            $this->scartObj->clear();
            $this->scartObj->updateUserSubscriptionCart();
        }

        /* if ( !$orderObj->addOrderHistory( $order_id, 1, Labels::getLabel("LBL_-NA-",$this->siteLangId), true, $this->siteLangId ) ){
        Message::addErrorMessage( $orderObj->getError() );
        FatUtility::dieWithError( Message::getHtml() );
        } */
        $this->_template->render(false, false, 'json-success.php');
    }


    private function getPaymentTabForm($langId, $paymentMethodCode = '')
    {
        $frm = new Form('frmPaymentTabForm');
        $frm->setFormTagAttribute('id', 'frmPaymentTabForm');

        if (strtolower($paymentMethodCode) == "cashondelivery") {
            $frm->addHtml('', 'htmlNote', '<div class="g-recaptcha" data-sitekey="'.FatApp::getConfig('CONF_RECAPTACHA_SITEKEY', FatUtility::VAR_STRING, '').'"></div>');
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Confirm_Payment', $langId));
        $frm->addHiddenField('', 'order_id');
        $frm->addHiddenField('', 'pmethod_id');
        return $frm;
    }

    private function getWalletPaymentForm($langId)
    {
        $frm = new Form('frmWalletPayment');
        $frm->addHiddenField('', 'order_id');
        return $frm;
    }
    private function getConfirmPaymentForm($langId)
    {
        $frm = new Form('frmConfirmPayment');
        $frm->addHiddenField('', 'order_id');
        return $frm;
    }

    private function getRewardsForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmRewards');
        $frm->addTextBox(Labels::getLabel('LBL_Reward_Points', $langId), 'redeem_rewards', '', array('placeholder'=>Labels::getLabel('LBL_Use_Reward_Point', $langId)));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Apply', $langId));
        return $frm;
    }

    public function getReviewScart()
    {
        $criteria = array( 'isUserLogged' => true, 'hasSubscription' => true);
        if (!$this->isEligibleForNextStep($criteria)) {
            if (Message::getErrorCount()) {
                $errMsg = Message::getHtml();
            } else {
                Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId));
                $errMsg = Message::getHtml();
            }
            FatUtility::dieWithError($errMsg);
        }
        $this->set('subscriptions', $this->scartObj->getSubscription($this->siteLangId));
        $this->set('scartSummary', $this->scartObj->getSubscriptionCartFinancialSummary($this->siteLangId));
        $this->_template->render(false, false);
    }

    public function getCouponForm()
    {
        $currDate = date('Y-m-d');
        $interval = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 15 minute'));
        $loggedUserId = UserAuthentication::getLoggedUserId();

        $cartObj = new SubscriptionCart();
        $cartSubTotal = $cartObj->getSubTotal($this->siteLangId);

        /* coupon history[ */
        $cHistorySrch = CouponHistory::getSearchObject();
        $cHistorySrch->doNotLimitRecords();
        $cHistorySrch->doNotCalculateRecords();
        $cHistorySrch->addMultipleFields(array('couponhistory_coupon_id','couponhistory_id'));
        /* ] */

        /* coupon User History[ */
        $userCouponHistorySrch = CouponHistory::getSearchObject();
        $userCouponHistorySrch->addCondition('couponhistory_user_id', '=', $loggedUserId);
        $userCouponHistorySrch->doNotLimitRecords();
        $userCouponHistorySrch->doNotCalculateRecords();
        //$userCouponHistorySrch->addMultipleFields(array('count(couponhistory_id) as user_coupon_used_count'));
        /* ] */

        /* coupon temp hold[ */
        $cHoldSrch = new SearchBase(DiscountCoupons::DB_TBL_COUPON_HOLD);
        $cHoldSrch->addCondition('couponhold_added_on', '>=', $interval);
        $cHoldSrch->addCondition('couponhold_user_id', '!=', $loggedUserId);
        $cHoldSrch->addMultipleFields(array('couponhold_coupon_id'));
        $cHoldSrch->doNotLimitRecords();
        $cHoldSrch->doNotCalculateRecords();
        /* ] */

        /* Coupon Users[ */
        /* $cUsersSrch = new SearchBase( DiscountCoupons::DB_TBL_COUPON_TO_USER );
        $cUsersSrch->doNotCalculateRecords();
        $cUsersSrch->doNotLimitRecords();
        $cUsersSrch->addGroupBy('ctu_coupon_id');
        $cUsersSrch->addMultipleFields( array('ctu_coupon_id','GROUP_CONCAT(ctu_user_id) as grouped_coupon_users') ); */
        /* ] */

        /* Coupon Plans[ */
        $cPlanSrch = new SearchBase(DiscountCoupons::DB_TBL_COUPON_TO_PLAN);
        $cPlanSrch->doNotCalculateRecords();
        $cPlanSrch->doNotLimitRecords();
        $cPlanSrch->addGroupBy('ctplan_coupon_id');
        $cPlanSrch->addMultipleFields(array('ctplan_coupon_id','GROUP_CONCAT(ctplan_spplan_id) as grouped_coupon_plans'));
        /* ] */

        $srch = DiscountCoupons::getSearchObject($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $srch->joinTable('('.$cHistorySrch->getQuery().')', 'LEFT OUTER JOIN', 'coupon_history.couponhistory_coupon_id = dc.coupon_id', 'coupon_history');
        $srch->joinTable('('.$cHoldSrch->getQuery().')', 'LEFT OUTER JOIN', 'dc.coupon_id = coupon_hold.couponhold_coupon_id', 'coupon_hold');
        //$srch->joinTable( '('.$cUsersSrch->getQuery().')', 'LEFT OUTER JOIN', 'dc.coupon_id = ctu.ctu_coupon_id', 'ctu' );

        $srch->joinTable('('. $userCouponHistorySrch->getQuery() .')', 'LEFT OUTER JOIN', 'dc.coupon_id = user_coupon_history.couponhistory_coupon_id', 'user_coupon_history');

        $srch->joinTable('('.$cPlanSrch->getQuery().')', 'LEFT OUTER JOIN', 'dc.coupon_id = ctplan.ctplan_coupon_id', 'ctplan');

        $srch->addCondition('coupon_type', '=', DiscountCoupons::TYPE_SELLER_PACKAGE);
        $cnd = $srch->addCondition('coupon_start_date', '=', '0000-00-00', 'AND');
        $cnd->attachCondition('coupon_start_date', '<=', $currDate, 'OR');
        $cnd1 = $srch->addCondition('coupon_end_date', '=', '0000-00-00', 'AND');
        $cnd1->attachCondition('coupon_end_date', '>=', $currDate, 'OR');
        $srch->addCondition('coupon_min_order_value', '<=', $cartSubTotal);
        $srch->addMultipleFields(array( 'dc.*', 'dc_l.coupon_description', 'IFNULL(COUNT(coupon_history.couponhistory_id), 0) as coupon_used_count', 'IFNULL(COUNT(coupon_hold.couponhold_coupon_id), 0) as coupon_hold_count', 'count(user_coupon_history.couponhistory_id) as user_coupon_used_count', 'ctplan.grouped_coupon_plans'));

        //$srch->addDirectCondition( 'IF(grouped_coupon_users != "NULL", FIND_IN_SET('.$loggedUserId.', grouped_coupon_users), 1 = 1 )');

        /* checking current coupon is valid for current subscription plan[ */
        $cartSubscription = $this->scartObj->getSubscription($this->siteLangId);

        foreach ($cartSubscription as $cartSubscription) {
            $srch->addDirectCondition('IF(grouped_coupon_plans != "NULL", FIND_IN_SET('.$cartSubscription['spplan_id'].', grouped_coupon_plans), 1 = 1 )');
        }
        /* ] */

        $srch->addHaving('coupon_uses_count', '>', 'coupon_used_count + coupon_hold_count');
        $srch->addHaving('coupon_uses_coustomer', '>', 'mysql_func_user_coupon_used_count', 'AND', true);
        $srch->addGroupBy('dc.coupon_id');

        $rs = $srch->getResultSet();
        $couponsList = FatApp::getDb()->fetchAll($rs, 'coupon_id');
        $this->set('couponsList', $couponsList);

        $this->set('spackage_type', $cartSubscription['spackage_type']);

        $PromoCouponsFrm = $this->getPromoCouponsForm($this->siteLangId);
        $this->set('PromoCouponsFrm', $PromoCouponsFrm);
        $this->_template->render(false, false);
    }

    private function getPromoCouponsForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmPromoCoupons');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Coupon_code', $langId), 'coupon_code', '', array('placeholder'=>Labels::getLabel('LBL_Enter_Your_code', $langId)));
        $fld->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Apply', $langId));
        return $frm;
    }

    public function applyPromoCode()
    {
        UserAuthentication::checkLogin();

        $post = FatApp::getPostedData();

        if (false == $post) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (empty($post['coupon_code'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $couponCode = $post['coupon_code'];

        $couponObj = new DiscountCoupons();
        $couponInfo = $couponObj->getSubscriptionCoupon($couponCode, $this->siteLangId);
        if ($couponInfo == false) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Coupon_Code', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $cartObj = new SubscriptionCart();
        if (!$cartObj->updateCartDiscountCoupon($couponInfo['coupon_code'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $holdCouponData = array(
        'couponhold_coupon_id'=>$couponInfo['coupon_id'],
        'couponhold_user_id'=>UserAuthentication::getLoggedUserId(),
        'couponhold_added_on'=>date('Y-m-d H:i:s'),
        );

        if (!FatApp::getDb()->insertFromArray(DiscountCoupons::DB_TBL_COUPON_HOLD, $holdCouponData, true, array(), $holdCouponData)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->_template->render(false, false, 'json-success.php');
    }

    public function removePromoCode()
    {
        $scartObj = new SubscriptionCart();
        if (!$scartObj->removeCartDiscountCoupon()) {
            Message::addErrorMessage(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel("MSG_cart_discount_coupon_removed", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    public function PaymentBlankDiv()
    {
        $this->_template->render(false, false);
    }
    public function renewSubscriptionOrder($ossubs_id  = 0)
    {
        $statusArr  = Orders::getActiveSubscriptionStatusArr();
        $endDate  = date("Y-m-d");
        $srch = new OrderSubscriptionSearch();
        $srch->joinOrders();
        $srch->joinOrderUser();
        $srch->addCondition('order_is_paid', '=', ORDERS::ORDER_IS_PAID);
        $srch->addCondition('ossubs_status_id', 'in', $statusArr);
        $srch->addCondition('ossubs_id', '=', $ossubs_id);
        $srch->addCondition('ossubs_type', '=', SellerPackages::PAID_TYPE);
        $srch->addCondition('order_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addCondition('ossubs_till_date', '<=', $endDate);
        $srch->addCondition('ossubs_till_date', '!=', '0000-00-00');
        $srch->addCondition('user_autorenew_subscription', '!=', 1);
        $srch->addMultipleFields(array('order_user_id','order_id','ossubs_id','ossubs_type','ossubs_price','ossubs_images_allowed','ossubs_products_allowed','ossubs_inventory_allowed','ossubs_plan_id','ossubs_interval','ossubs_frequency','ossubs_commission'));
        /* $srch->addGroupBy('order_user_id');  */
        $srch->addOrder('ossubs_id', 'desc');

        $rs = $srch->getResultSet();
        $activeSub = FatApp::getDb()->fetch($rs, 'ossubs_id');

        if (empty($activeSub) && count($activeSub)==0) {
            Message::addErrorMessage(Labels::getLabel("MSG_Subscription_is_not_active", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'subscriptions'));
        }

        $userId = $activeSub['order_user_id'];
        $userBalance = User::getUserBalance($userId);

        if ($userBalance<$activeSub['ossubs_price']) {
            $low_bal_msg = str_replace("{clickhere}", '<a href="'.CommonHelper::generateUrl('account', 'credits').'">'.Labels::getLabel('LBL_Click_Here', $this->siteLangId).'</a>', Labels::getLabel('MSG_Please_Maintain_your_wallet_balance_to_renew_subscription_{clickhere}', $this->siteLangId));

            Message::addErrorMessage($low_bal_msg);
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'subscriptions'));
        }

        $orderData = array();
        /* add Order Data[ */
        $order_id =  false;
        $orderData['order_id'] = $order_id;
        $orderData['order_user_id'] = $userId;
        /* $orderData['order_user_name'] = $userDataArr['user_name'];
        $orderData['order_user_email'] = $userDataArr['credential_email'];
        $orderData['order_user_phone'] = $userDataArr['user_phone']; */
        $orderData['order_is_paid'] = Orders::ORDER_IS_PENDING;
        $orderData['order_date_added'] = date('Y-m-d H:i:s');
        $orderData['order_type'] = Orders::ORDER_SUBSCRIPTION;



        /* order extras[ */
        $orderData['extra'] = array(
        'oextra_order_id'    =>    $order_id,
        'order_ip_address'    =>    $_SERVER['REMOTE_ADDR']
        );

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $orderData['extra']['order_forwarded_ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $orderData['extra']['order_forwarded_ip'] = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $orderData['extra']['order_forwarded_ip'] = '';
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $orderData['extra']['order_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $orderData['extra']['order_user_agent'] = '';
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $orderData['extra']['order_accept_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $orderData['extra']['order_accept_language'] = '';
        }
        /* ] */

        $languageRow = Language::getAttributesById($this->siteLangId);
        $orderData['order_language_id'] =  $languageRow['language_id'];
        $orderData['order_language_code'] =  $languageRow['language_code'];

        $currencyRow = Currency::getAttributesById($this->siteCurrencyId);
        $orderData['order_currency_id'] =  $currencyRow['currency_id'];
        $orderData['order_currency_code'] =  $currencyRow['currency_code'];
        $orderData['order_currency_value'] =  $currencyRow['currency_value'];

        $orderData['order_user_comments'] =  '';
        $orderData['order_admin_comments'] =  '';

        $orderData['order_reward_point_used'] = 0;
        $orderData['order_reward_point_value'] = 0;




        $orderData['order_net_amount'] = $activeSub['ossubs_price'];
        $orderData['order_wallet_amount_charge'] = $activeSub['ossubs_price'];

        // Discussin Required
        $orderData['order_cart_data'] = '';

        $allLanguages = Language::getAllNames();
        //$productSelectedShippingMethodsArr = $this->cartObj->getProductShippingMethod();

        $orderLangData = array();

        $orderData['orderLangData'] = $orderLangData;




        $subscriptionLangData = array();
        foreach ($allLanguages as $lang_id => $language_name) {
            $subscriptionInfo = OrderSubscription::getAttributesByLangId($lang_id, $activeSub['ossubs_id']);


            $op_subscription_title = $subscriptionInfo['ossubs_subscription_name'];



            $subscriptionLangData[$lang_id] = array(
            'ossubslang_lang_id'    =>    $lang_id,
            'ossubs_subscription_name'    =>    $op_subscription_title,


            );
        }

        $orderData['subscriptions'][SubscriptionCart::SUBSCRIPTION_CART_KEY_PREFIX_PRODUCT.$activeSub['ossubs_plan_id']] = array(


         OrderSubscription::DB_TBL_PREFIX.'price'        =>    $activeSub['ossubs_price'],
         OrderSubscription::DB_TBL_PREFIX.'images_allowed'        =>    $activeSub['ossubs_images_allowed'],
         OrderSubscription::DB_TBL_PREFIX.'products_allowed'        =>    $activeSub['ossubs_products_allowed'],
         OrderSubscription::DB_TBL_PREFIX.'inventory_allowed'        =>    $activeSub['ossubs_inventory_allowed'],
         OrderSubscription::DB_TBL_PREFIX.'plan_id'        =>    $activeSub['ossubs_plan_id'],
         OrderSubscription::DB_TBL_PREFIX.'type'        =>    $activeSub['ossubs_type'],
         OrderSubscription::DB_TBL_PREFIX.'interval'        =>    $activeSub['ossubs_interval'],
         OrderSubscription::DB_TBL_PREFIX.'frequency'        =>    $activeSub['ossubs_frequency'],
         OrderSubscription::DB_TBL_PREFIX.'commission'        =>    $activeSub['ossubs_commission'],
         OrderSubscription::DB_TBL_PREFIX.'status_id'        =>    FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"),

         'subscriptionsLangData'    =>    $subscriptionLangData,
        );

        $adjustAmount=0;
        $discount = 0;
        $rewardPoints = 0;
        $usedRewardPoint = 0;

        //CommonHelper::printArray($cartSubscription); die();
        $orderData['subscrCharges'][SubscriptionCart::SUBSCRIPTION_CART_KEY_PREFIX_PRODUCT.$activeSub['ossubs_plan_id']] = array(

         OrderProduct::CHARGE_TYPE_DISCOUNT =>array(
          'amount' => 0 /*[Should be negative value]*/
         ),


         OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT =>array(
          'amount' => 0 /*[Should be negative value]*/
         ),
         OrderProduct::CHARGE_TYPE_ADJUST_SUBSCRIPTION_PRICE =>array(
          'amount' => 0 /*[Should be negative value]*/
         ),



        );
        /* [ Add order Type[ */
        $orderData['order_type']= Orders::ORDER_SUBSCRIPTION;
        $orderData['order_renew']= 1;

        /* ] */
        $orderObj = new Orders();
        if ($orderObj->addUpdateOrder($orderData, $this->siteLangId)) {
            $order_id = $orderObj->getOrderId();

            $orderPaymentObj = new OrderPayment($order_id);
            if ($orderPaymentObj->chargeUserWallet($activeSub['ossubs_price'])) {
                Message::addMessage(Labels::getLabel("MSG_Subscription_Successfully_renewed", $this->siteLangId));
                FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'subscriptions'));
            }
        }
        Message::addErrorMessage($orderObj->getError());
        FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'subscriptions'));

        /* ] */
        /* ] */
    }
}
