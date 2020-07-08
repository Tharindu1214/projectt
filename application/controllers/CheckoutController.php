<?php
class CheckoutController extends MyAppController
{
    private $cartObj;
    private $errMessage;

    public function __construct($action)
    {
        parent::__construct($action);

        if (true ===  MOBILE_APP_API_CALL) {
            UserAuthentication::checkLogin();
        }
        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            FatApp::redirectUser(CommonHelper::generateUrl('Cart'));
        }

        if (UserAuthentication::isGuestUserLogged()) {
            $user_is_buyer = User::getAttributesById(UserAuthentication::getLoggedUserId(), 'user_is_buyer');
            if (!$user_is_buyer) {
                $this->errMessage = Labels::getLabel('MSG_Please_login_with_buyer_account', $this->siteLangId);
                Message::addErrorMessage($this->errMessage);
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(CommonHelper::generateUrl('Cart'));
            }
        }
        $this->cartObj = new Cart(UserAuthentication::getLoggedUserId(), $this->siteLangId, $this->app_user['temp_user_id']);

        if (1 > $this->cartObj->getCartBillingAddress()) {
            $this->cartObj->setCartBillingAddress();
        }

        if ($this->cartObj->hasPhysicalProduct() && 1 > $this->cartObj->getCartShippingAddress()) {
            $this->cartObj->setShippingAddressSameAsBilling();
        }
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
                    if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    break;
                case 'hasProducts':
                    if (!$this->cartObj->hasProducts()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Your_cart_seems_to_be_empty,_Please_try_after_reloading_the_page.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    break;
                case 'hasStock':
                    /* if( !$this->cartObj->hasStock() ){
                    $key = false;
                    Message::addErrorMessage(Labels::getLabel('MSG_Products_are_out_of_stock', $this->siteLangId));
                    return false;
                    } */

                    /* to check that product is temporary hold[ */
                    $cart_user_id = Cart::getCartUserId();
                    $intervalInMinutes = FatApp::getConfig('cart_stock_hold_minutes', FatUtility::VAR_INT, 15);
                    //$srch->addCondition('pshold_user_id', '!=', $cart_user_id);

                    /* ] */

                    $cartProducts = $this->cartObj->getProducts($this->siteLangId);
                    //CommonHelper::printArray($cartProducts); exit;
                    foreach ($cartProducts as $product) {
                        if (!$product['in_stock']) {
                            $stock = false;
                            $key = false;
                            $this->errMessage = Labels::getLabel('MSG_Products_are_out_of_stock.', $this->siteLangId);
                            Message::addErrorMessage($this->errMessage);
                            return false;
                            break;
                        }

                        if ($product['is_batch'] && !empty($product['products'])) {
                            foreach ($product['products'] as $pgproduct) {
                                $tempHoldStock = Product::tempHoldStockCount($pgproduct['selprod_id']);
                                $availableStock = $pgproduct['selprod_stock'] - $tempHoldStock;
                                $userTempHoldStock = Product::tempHoldStockCount($pgproduct['selprod_id'], $cart_user_id, $product['prodgroup_id'], true);
                                if ($availableStock < ($product['quantity'] - $userTempHoldStock)) {
                                    $key = false;
                                    $productName = (isset($pgproduct['selprod_title']) && $pgproduct['selprod_title'] != '') ? $pgproduct['selprod_title'] : $pgproduct['name'];

                                    $this->errMessage = str_replace('{product-name}', $productName, Labels::getLabel('MSG_{product-name}_is_temporary_out_of_stock_or_hold_by_other_customer', $this->siteLangId));
                                    Message::addErrorMessage($this->errMessage);
                                    return false;
                                }
                            }
                        } else {
                            $tempHoldStock = Product::tempHoldStockCount($product['selprod_id']);
                            $availableStock = $product['selprod_stock'] - $tempHoldStock;
                            $userTempHoldStock = Product::tempHoldStockCount($product['selprod_id'], $cart_user_id, 0, true);
                            if ($availableStock < ($product['quantity'] - $userTempHoldStock)) {
                                $key = false;
                                $productName = (isset($product['selprod_title']) && $product['selprod_title'] != '') ? $product['selprod_title'] : $product['name'];
                                $this->errMessage = str_replace('{product-name}', $productName, Labels::getLabel('MSG_{product-name}_is_temporary_out_of_stock_or_hold_by_other_customer', $this->siteLangId));
                                Message::addErrorMessage($this->errMessage);
                                return false;
                            }
                        }

                        /* $srch = new SearchBase('tbl_product_stock_hold');
                        $srch->doNotCalculateRecords();
                        $srch->addOrder('pshold_id', 'ASC');
                        $srch->addCondition( 'pshold_added_on', '>=', 'mysql_func_DATE_SUB( NOW(), INTERVAL ' . $intervalInMinutes . ' MINUTE )', 'AND', true );
                        $srch->addCondition( 'pshold_selprod_id', '=', $product['selprod_id'] );
                        $srch->addOrder('pshold_id');
                        $srch->setPageNumber(1);
                        $srch->setPageSize(1);
                        $rs = $srch->getResultSet();
                        $stockHoldRow = FatApp::getDb()->fetch($rs);
                        if( $stockHoldRow && ($stockHoldRow['pshold_user_id'] != $cart_user_id) && ($product['selprod_stock'] - $stockHoldRow['pshold_selprod_stock']) < $product['quantity'] ){
                        $key = false;
                        $productName = ( isset($product['selprod_title']) && $product['selprod_title'] != '' ) ? $product['selprod_title'] : $product['name'];
                        Message::addErrorMessage($productName . " is temporary out of stock or hold by other customer, please try after some time.");
                        return false;
                        } */
                        /* if( array_key_exists($product['selprod_id'], $rows ) && ($product['selprod_stock'] - $rows[$product['selprod_id']]['pshold_selprod_stock'] < $product['quantity'] ) ){
                        $key = false;
                        Message::addErrorMessage("Product Stock is currently hold by some other user, please try after some time.");
                        return false;
                        } */
                    }
                    break;
                case 'hasBillingAddress':
                    if (!$this->cartObj->getCartBillingAddress()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Billing_Address_is_not_provided.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    break;
                case 'hasShippingAddress':
                    if (!$this->cartObj->getCartShippingAddress()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Shipping_Address_is_not_provided.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    break;
                case 'isProductShippingMethodSet':
                    if (!$this->cartObj->isProductShippingMethodSet()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    break;
            }
        }
        return true;
    }

    public function index($appParam = '', $appLang = '1', $appCurrency = '1')
    {
        if ($appParam == 'api') {
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
            FatApp::redirectUser(CommonHelper::generateUrl('checkout', 'index'));
        }

        $criteria = array('hasProducts' => true, 'hasStock' => true);
        if (!$this->isEligibleForNextStep($criteria)) {
            FatApp::redirectUser(CommonHelper::generateUrl('cart'));
        }
        $cartHasPhysicalProduct = false;
        if ($this->cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }

        $obj = new Extrapage();
        $headerData = $obj->getContentByPageType(Extrapage::CHECKOUT_PAGE_HEADER_BLOCK, $this->siteLangId);

        $addresses = UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $this->siteLangId);
        // $products = $this->cartObj->getProducts($this->siteLangId);
        // $this->set('products', $products);
        $this->cartObj->removeProductShippingMethod();
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        // $this->set('cartSummary', $this->cartObj->getCartFinancialSummary($this->siteLangId));

        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::CHECKOUT_PAGE_RIGHT_BLOCK, $this->siteLangId);
        $this->set('pageData', $pageData);
        $this->set('addresses', $addresses);
        $this->set('headerData', $headerData);

        $this->_template->render(true, false);
    }

    public function loadLoginDiv()
    {
        $this->_template->render(false, false);
    }

    public function login()
    {
        $loginFormData = array(
        'loginFrm'         => $this->getLoginForm(),
        'guestLoginFrm' => $this->getGuestUserForm($this->siteLangId),
        'siteLangId'    => $this->siteLangId,
        'showSignUpLink' => true,
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

    public function addresses()
    {
        $criteria = array( 'isUserLogged' => true );
        $cartObj = new Cart();
        if (!$this->isEligibleForNextStep($criteria)) {
            $this->set('redirectUrl', CommonHelper::generateUrl('GuestUser', 'LoginForm'));
            Message::addErrorMessage(Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $addressFrm = $this->getUserAddressForm($this->siteLangId);
        $addresses = UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $this->siteLangId);

        $cartHasPhysicalProduct = false;
        if ($cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }
        $cart_products = $this->cartObj->getProducts($this->siteLangId);
        if (count($cart_products)==0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_Cart_is_empty.', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $selected_billing_address_id = $cartObj->getCartBillingAddress();
        $selected_shipping_address_id = $cartObj->getCartShippingAddress();

        $this->set('selected_billing_address_id', $selected_billing_address_id);
        $this->set('selected_shipping_address_id', $selected_shipping_address_id);

        $isShippingSameAsBilling = $cartObj->getShippingAddressSameAsBilling();
        $this->set('isShippingSameAsBilling', $isShippingSameAsBilling);
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->set('addresses', $addresses);
        $this->set('stateId', 0);
        $this->set('cityId', 0);
        $this->set('addressFrm', $addressFrm);
        $this->set('checkoutAddressFrm', $this->getCheckoutAddressForm($this->siteLangId));
        $this->_template->render(false, false);
    }

    public function loadBillingShippingAddress()
    {
        $cartObj = new Cart();
        $selected_shipping_address_id = $cartObj->getCartShippingAddress();
        $address =  UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $this->siteLangId, 0, $selected_shipping_address_id);
        $hasPhysicalProduct = $this->cartObj->hasPhysicalProduct();

        $this->set('hasPhysicalProduct', $hasPhysicalProduct);
        if (!$hasPhysicalProduct) {
            $selected_billing_address_id = $cartObj->getCartBillingAddress();
            $address =  UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $this->siteLangId, 0, $selected_billing_address_id);
        }
        $this->set('defaultAddress', $address);
        $this->_template->render(false, false);
    }

    public function setUpAddressSelection()
    {
        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            $this->set('redirectUrl', CommonHelper::generateUrl('GuestUser', 'LoginForm'));
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        $shipping_address_id = FatApp::getPostedData('shipping_address_id', FatUtility::VAR_INT, 0);

        $billing_address_id = FatApp::getPostedData('billing_address_id', FatUtility::VAR_INT, 0);
        $isShippingSameAsBilling = FatApp::getPostedData('isShippingSameAsBilling', FatUtility::VAR_INT, 0);

        // Validate cart has products and has stock.
        //$this->cartObj = new Cart();

        $hasProducts = $this->cartObj->hasProducts();
        $hasStock = $this->cartObj->hasStock();

        if ((!$hasProducts) || (!$hasStock)) {
            $this->errMessage = Labels::getLabel('MSG_Cart_seems_to_be_empty_or_products_are_out_of_stock.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            $this->set('redirectUrl', CommonHelper::generateUrl('cart'));
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        $hasPhysicalProduct = $this->cartObj->hasPhysicalProduct();

        if (1 > $billing_address_id) {
            $this->errMessage = Labels::getLabel('MSG_Please_select_Billing_address.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($hasPhysicalProduct && 1 > $shipping_address_id) {
            $this->errMessage = Labels::getLabel('MSG_Please_select_shipping_address.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            $this->set('loadAddressDiv', true);
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        /* setup billing address[ */
        $BillingAddressDetail = UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), 0, 0, $billing_address_id);
        if (!$BillingAddressDetail) {
            $this->errMessage = Labels::getLabel('MSG_Invalid_Billing_Address.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->cartObj->setCartBillingAddress($BillingAddressDetail['ua_id']);

        /* ] */

        if ($hasPhysicalProduct && $shipping_address_id) {
            if ($isShippingSameAsBilling) {
                $this->cartObj->setShippingAddressSameAsBilling();
                $shipping_address_id = $billing_address_id;
            }
            $ShippingAddressDetail = UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), 0, 0, $shipping_address_id);
            if (!$ShippingAddressDetail) {
                $this->errMessage = Labels::getLabel('MSG_Invalid_Shipping_Address.', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($this->errMessage);
                }
                Message::addErrorMessage($this->errMessage);
                FatUtility::dieWithError(Message::getHtml());
            }
            $this->cartObj->setCartShippingAddress($ShippingAddressDetail['ua_id']);
        }

        if (!$isShippingSameAsBilling) {
            $this->cartObj->unSetShippingAddressSameAsBilling();
        }

        if (!$hasPhysicalProduct) {
            $this->cartObj->unSetShippingAddressSameAsBilling();
            $this->cartObj->unsetCartShippingAddress();
        }

        $this->cartObj->removeProductShippingMethod();
        $this->set('hasPhysicalProduct', $hasPhysicalProduct);
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->set('msg', Labels::getLabel('MSG_Address_Selection_Successfull', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function shippingSummary(){
    $criteria = array( 'isUserLogged' => true, 'hasBillingAddress' => true, 'hasShippingAddress' => true );
    if( !$this->isEligibleForNextStep( $criteria ) ){
    if( Message::getErrorCount() ){
                $this->errMessage = Message::getHtml();
    } else {
                Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId));
                $this->errMessage = Message::getHtml();
    }
    FatUtility::dieWithError( $this->errMessage );
    }

    $selectedShippingapi_id = $this->cartObj->getCartShippingApi();
    $frm_data = array('shippingapi_id' => $selectedShippingapi_id );
    $frm = $this->getShippingApiForm( $this->siteLangId );
    $frm->fill($frm_data);
    $this->set( 'frmShippingApi', $frm);
    $this->_template->render( false, false);
    } */

    public function shippingSummary()
    {
        $criteria = array( 'isUserLogged' => true );
        if (!$this->isEligibleForNextStep($criteria)) {
            if (Message::getErrorCount()) {
                $this->errMessage = Message::getHtml();
            } else {
                Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId));
                $this->errMessage = Message::getHtml();
            }
            if (true ===  MOBILE_APP_API_CALL) {
                $this->errMessage = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
                FatUtility::dieJsonError($this->errMessage);
            }
            FatUtility::dieWithError($this->errMessage);
        }

        /* $frm = $this->getShippingApiForm( $this->siteLangId );
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $shippingapi_id = FatUtility::int($post['shippingapi_id']);
        if( !$shippingapi_id ){
        FatUtility::dieWithError( Labels::getLabel('MSG_Please_select_shipping_api', $this->siteLangId) );
        } */
        $productSelectedShippingMethodsArr = $this->cartObj->getProductShippingMethod();

        $selectedShippingapi_id = $this->cartObj->getCartShippingApi();
        $user_id = UserAuthentication::getLoggedUserId();

        $manualShippingArt = array('Seller Shiping');
        $frm_data = array('shippingapi_id' => $selectedShippingapi_id );
        $shippingMethods = $this->getShippingMethods($this->siteLangId);
        if (false ===  MOBILE_APP_API_CALL) {
            $frm = $this->getShippingApiForm($this->siteLangId);
            $frm->fill($frm_data);
            $this->set('frmShippingApi', $frm);
        }

        /* $shippingDurationError = '';
        if( $shippingDurationError ){
        FatUtility::dieWithError( $shippingDurationError );
        } */
        $cart_products=$this->cartObj->getProducts($this->siteLangId);
        /* get user shipping address[ */
        $shippingAddressDetail = UserAddress::getUserAddresses($user_id, $this->siteLangId, 0, $this->cartObj->getCartShippingAddress());
        /* ] */

        $ua_city_id = isset($shippingAddressDetail['ua_city_id'])?$shippingAddressDetail['ua_city_id']:0;
        $citiesRow = Cities::getCityNameById($ua_city_id, $this->siteLangId);
        if($citiesRow){
            $cityName = $citiesRow[0]['city_name'];
        }else{
            $cityName = '';
        }
        foreach ($cart_products as $cartkey => $cartval) {
            $cart_products[$cartkey]['pship_id']= 0;
            $shipBy = 0;

            if ($cart_products[$cartkey]['psbs_user_id']) {
                $shipBy = $cart_products[$cartkey]['psbs_user_id'];
            }else{
                $shipBy = $cart_products[$cartkey]['selprod_user_id'];
            }

            /* $limit = 1; */
            $ua_country_id = isset($shippingAddressDetail['ua_country_id'])?$shippingAddressDetail['ua_country_id']:0;

            $ua_city_id = isset($shippingAddressDetail['ua_city_id'])?$shippingAddressDetail['ua_city_id']:0;

            $shipping_options = Product::getProductShippingRates($cartval['product_id'], $this->siteLangId, $ua_city_id, $shipBy, 1, 'checkout');

            $free_shipping_options = Product::getProductFreeShippingAvailabilty($cartval['product_id'], $this->siteLangId, $ua_country_id, $shipBy);

            $cart_products[$cartkey]['is_shipping_selected'] =  isset($productSelectedShippingMethodsArr['product'][$cartval['selprod_id']])?$productSelectedShippingMethodsArr['product'][$cartval['selprod_id']]['mshipapi_id']:false;
            if ($cart_products[$cartkey]['is_shipping_selected'] && $productSelectedShippingMethodsArr['product'][$cartval['selprod_id']]['mshipapi_id']== SHIPPINGMETHODS::SHIPSTATION_SHIPPING) {
                $cart_products[$cartkey]['selected_shipping_option']=$productSelectedShippingMethodsArr['product'][$cartval['selprod_id']];
            } elseif ($cart_products[$cartkey]['is_shipping_selected'] && $productSelectedShippingMethodsArr['product'][$cartval['selprod_id']]['mshipapi_id']== SHIPPINGMETHODS::MANUAL_SHIPPING) {
                $cart_products[$cartkey]['pship_id']=$productSelectedShippingMethodsArr['product'][$cartval['selprod_id']]['pship_id'];
            }
            $cart_products[$cartkey]['shipping_rates'] = $shipping_options;
            $cart_products[$cartkey]['shipping_free_availbilty']=$free_shipping_options;
            if (true ===  MOBILE_APP_API_CALL) {
                $optionTitle = '';
                if (is_array($cartval['options']) && count($cartval['options'])) {
                    foreach ($cartval['options'] as $op) {
                        $optionTitle .= $op['option_name'].': '.$op['optionvalue_name'].', ';
                    }
                }
                $cart_products[$cartkey]['optionsTitle'] = rtrim($optionTitle, ', ');
            }
        }
        if (count($cart_products)==0) {
            $this->errMessage = Labels::getLabel('MSG_Your_Cart_is_empty', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!$this->cartObj->hasPhysicalProduct()) {
            $this->cartObj->unSetShippingAddressSameAsBilling();
            $this->cartObj->unsetCartShippingAddress();
        }

        $this->set('productSelectedShippingMethodsArr', $productSelectedShippingMethodsArr);
        $this->set('shipStationCarrierList', $this->cartObj->shipStationCarrierList());
        $this->set('shippingMethods', $shippingMethods);
        $this->set('products', $cart_products);
        $this->set('cityName', $cityName);
        $this->set('cartSummary', $this->cartObj->getCartFinancialSummary($this->siteLangId));
        $this->set('shippingAddressDetail', UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $this->siteLangId, 0, $this->cartObj->getCartShippingAddress()));

        $this->set('selectedProductShippingMethod', $this->cartObj->getProductShippingMethod());

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'checkout/shipping-summary-inner.php');
    }

    public function getCarrierServicesList($product_key, $carrier_id = 0)
    {
        if (empty($product_key)) {
            $this->errMessage = Labels::getLabel('MSG_Invalid_Request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
            FatUtility::dieJsonError($this->errMessage);
        }
        $this->Cart = new Cart(UserAuthentication::getLoggedUserId());
        $carrierList = $this->Cart->getCarrierShipmentServicesList($product_key, $carrier_id, $this->siteLangId);
        $json = array('status'=>1, 'isCarriersFound' => 0);
        $isCarriersFound = 0;
        $html = $this->_template->render(false, false, 'checkout/shipping-api-carriers-services-not-found.php', true);
        if (isset($carrierList) && count($carrierList) > 1) {
            $json['isCarriersFound'] = 1;
            $this->set('options', $carrierList);
            $html = $this->_template->render(false, false, '', true);
        }
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $json['html'] = $html;
        die(json_encode($json));
    }

    public function setUpShippingMethod()
    {
        $post = FatApp::getPostedData();

        if (true ===  MOBILE_APP_API_CALL) {
            $post['data'] = (!empty($post['data']) ? json_decode($post['data'], true) : array());
        }
        $cartProducts = $this->cartObj->getProducts($this->siteLangId);

        //$this->cartObj = new Cart();
        $productToShippingMethods = array();
        $user_id = UserAuthentication::getLoggedUserId();

        /* get user shipping address[ */
        $shippingAddressDetail = UserAddress::getUserAddresses($user_id, $this->siteLangId, 0, $this->cartObj->getCartShippingAddress());
        /* ] */

        $sn= 0;
        $json= array();
        if (!empty($cartProducts)) {
            $prodSrchObj = new ProductSearch();
            foreach ($cartProducts as $cartkey => $cartval) {
                $sn++;
                $shipping_address = UserAddress::getUserAddresses($user_id, $this->siteLangId);
                $shipBy=0;

                if ($cartProducts[$cartkey]['psbs_user_id']) {
                    $shipBy =$cartProducts[$cartkey]['psbs_user_id'];
                }else{
                    $shipBy = $cartProducts[$cartkey]['selprod_user_id'];
                }
                $ua_country_id = isset($shippingAddressDetail['ua_country_id'])?$shippingAddressDetail['ua_country_id']:0;

                $ua_city_id = isset($shippingAddressDetail['ua_city_id'])?$shippingAddressDetail['ua_city_id']:0;

                $shipping_options = Product::getProductShippingRates($cartval['product_id'], $this->siteLangId, $ua_city_id, $shipBy,0, 'checkout');

                $free_shipping_options = Product::getProductFreeShippingAvailabilty($cartval['product_id'], $this->siteLangId, $ua_country_id, $shipBy);
                $productKey = md5($cartval["key"]);
                if ($cartval && $cartval['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
                    /* get Product Data[ */
                    $prodSrch = clone $prodSrchObj;
                    $prodSrch->setDefinedCriteria();
                    $prodSrch->joinProductToCategory();
                    $prodSrch->joinProductShippedBy();
                    $prodSrch->joinProductFreeShipping();
                    $prodSrch->joinSellerSubscription();
                    $prodSrch->addSubscriptionValidCondition();
                    $prodSrch->doNotCalculateRecords();
                    $prodSrch->doNotLimitRecords();
                    $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
                    $prodSrch->addCondition('selprod_id', '=', $cartval['selprod_id']);
                    /* $prodSrch->addDirectCondition( "( isnull(psbs.psbs_user_id) or psbs.psbs_user_id = '".$cartval['selprod_user_id']."')" ); */
                    $prodSrch->addMultipleFields(array('selprod_id','product_seller_id','psbs_user_id as shippedBySellerId'));
                    $productRs = $prodSrch->getResultSet();
                    $product = FatApp::getDb()->fetch($productRs);
                    /* ] */

                    if (isset($post["data"][$productKey]['shipping_type']) && ($post["data"][$productKey]['shipping_type'] ==  ShippingCompanies::MANUAL_SHIPPING) &&  !empty($post["data"][$productKey]['shipping_locations'])) {
                        foreach ($shipping_options as $shipOption) {
                            if ($shipOption['pship_id']==$post['data'][$productKey]["shipping_locations"]) {
                                $productToShippingMethods['product'][$cartval['selprod_id']] = array(
                                'selprod_id'    =>    $cartval['selprod_id'],
                                'pship_id'    =>    $post['data'][$productKey]["shipping_locations"],
                                'sduration_id'    =>    $shipOption['sduration_id'],
                                'sduration_name' => $shipOption['sduration_name'],
                                'sduration_from' => $shipOption['sduration_from'],
                                'sduration_to' => $shipOption['sduration_to'],
                                'sduration_days_or_weeks' => $shipOption['sduration_days_or_weeks'],
                                'mshipapi_id'    =>    $post['data'][$productKey]["shipping_type"],
                                'mshipcompany_id'    =>    $shipOption['scompanylang_scompany_id'],
                                'mshipcompany_name'    =>    $shipOption['scompany_name'],
                                'shipped_by_seller'    =>    Product::isShippedBySeller($cartval['selprod_user_id'], $product['product_seller_id'], $product['shippedBySellerId']),
                                'mshipapi_cost' =>  ($free_shipping_options == 0)? ($shipOption['pship_charges'] + ($shipOption['pship_additional_charges'] * ($cartval['quantity'] -1))) : 0 ,
                                );
                                continue;
                            }
                        }
                    } elseif (isset($post['data'][$productKey]["shipping_type"]) && ($post['data'][$productKey]["shipping_type"] ==  ShippingCompanies::SHIPSTATION_SHIPPING) && !empty($post['data'][$productKey]["shipping_services"])) {
                        list($carrier_name, $carrier_price) = explode("-", $post['data'][$productKey]["shipping_services"]);
                        $productToShippingMethods['product'][$cartval['selprod_id']] = array(
                          'selprod_id'    =>    $cartval['selprod_id'],
                          'mshipapi_id'    =>    $post['data'][$productKey]["shipping_type"],
                          'mshipcompany_name'    =>    ($carrier_name),
                          'mshipapi_cost' =>  $carrier_price ,
                          'mshipapi_key' =>  $post['data'][$productKey]["shipping_services"],
                          'mshipapi_label' =>  str_replace("_", " ", $post['data'][$productKey]["shipping_services"]) ,
                          'shipped_by_seller'    =>    Product::isShippedBySeller($cartval['selprod_user_id'], $product['product_seller_id'], $product['shippedBySellerId']),
                                            );
                        continue;
                    } else {
                        $json['error']['product'][$sn] = sprintf(Labels::getLabel('M_Shipping_Info_Required_for_%s', $this->siteLangId), htmlentities($cartval['product_name']));
                    }
                }
            }


            if (!$json) {
                $this->cartObj->setProductShippingMethod($productToShippingMethods);
                if (!$this->cartObj->isProductShippingMethodSet()) {

                    $this->errMessage = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart', $this->siteLangId);
                    if (true ===  MOBILE_APP_API_CALL) {
                        FatUtility::dieJsonError($this->errMessage);
                    }
                    //MSG_Error_in_Shipping_Method_Selection
                    Message::addErrorMessage($this->errMessage);
                    FatUtility::dieWithError(Message::getHtml());
                }

                $this->set('msg', Labels::getLabel('MSG_Shipping_Method_selected_successfully.', $this->siteLangId));
                if (true ===  MOBILE_APP_API_CALL) {
                    $userWalletBalance = User::getUserBalance($user_id, true);
                    $cartObj = new Cart();
                    $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
                    $this->set('cartSummary', $cartSummary);
                    $this->set('recordCount', !empty($cartProducts) ? count($cartProducts) : 0);
                    $this->set('userWalletBalance', $userWalletBalance);
                    $this->_template->render();
                }
                $this->_template->render(false, false, 'json-success.php');
            } else {
                $this->errMessage = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($this->errMessage);
                }
                Message::addErrorMessage($this->errMessage);
                FatUtility::dieWithError(Message::getHtml());
            }
        } else {
            $this->errMessage = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
            FatUtility::dieJsonError($this->errMessage);
        }
    }

    public function reviewCart()
    {
        $criteria = array('isUserLogged' => true, 'hasProducts' => true, 'hasStock' => true, 'hasBillingAddress' => true );
        if ($this->cartObj->hasPhysicalProduct()) {
            $criteria['hasShippingAddress'] = true;
            $criteria['isProductShippingMethodSet'] = true;
        }

        if (!$this->isEligibleForNextStep($criteria)) {
            if (Message::getErrorCount()) {
                $this->errMessage = Message::getHtml();
            } else {
                $this->errMessage = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
                Message::addErrorMessage($this->errMessage);
                $this->errMessage = Message::getHtml();
            }
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            FatUtility::dieWithError($this->errMessage);
        }
        $cartHasDigitalProduct = $this->cartObj->hasDigitalProduct();
        $cartHasPhysicalProduct = $this->cartObj->hasPhysicalProduct();
        $cart_products = $this->cartObj->getProducts($this->siteLangId);
        // CommonHelper::printArray($this->cartObj, true);
        if (1 > count($cart_products)) {
            $this->errMessage = Labels::getLabel('MSG_Your_Cart_is_empty', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('cartHasDigitalProduct', $cartHasDigitalProduct);
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->set('products', $cart_products);

        $this->set('cartSummary', $this->cartObj->getCartFinancialSummary($this->siteLangId));
        $this->set('selectedProductShippingMethod', $this->cartObj->getProductShippingMethod());
        if (true ===  MOBILE_APP_API_CALL) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
            $billingAddressDetail = array();
            $billingAddressId = $this->cartObj->getCartBillingAddress();
            if (0 < $billingAddressId) {
                $billingAddressDetail = UserAddress::getUserAddresses($loggedUserId, $this->siteLangId, 0, $billingAddressId);
            }
            $shippingddressDetail = array();
            $shippingAddressId = $this->cartObj->getCartShippingAddress();
            if ($shippingAddressId > 0) {
                $shippingddressDetail = UserAddress::getUserAddresses($loggedUserId, $this->siteLangId, 0, $shippingAddressId);
            }

            $this->set('billingAddress', $billingAddressDetail);
            $this->set('shippingAddress', $shippingddressDetail);
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    private function getCartProductInfo($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $prodSrch = new ProductSearch($this->siteLangId);
        $prodSrch->setDefinedCriteria();
        $prodSrch->joinBrands();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->joinProductToCategory();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $fields = array( 'product_id', 'product_type', 'product_length', 'product_width', 'product_height',
        'product_dimension_unit', 'product_weight', 'product_weight_unit', 'product_model',
        'selprod_id', 'selprod_user_id', 'selprod_stock','IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_sku',
        'selprod_condition', 'selprod_code',
        'special_price_found', 'theprice', 'shop_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title','IFNULL(brand_name, brand_identifier) as brand_name','shop_name',
        'seller_user.user_name as shop_onwer_name', 'seller_user_cred.credential_username as shop_owner_username',
        'seller_user.user_phone as shop_owner_phone','seller_user_cred.credential_email as shop_owner_email','selprod_download_validity_in_days','selprod_max_download_times' );
        $prodSrch->addMultipleFields($fields);
        $rs = $prodSrch->getResultSet();
        return $productInfo = FatApp::getDb()->fetch($rs);
    }

    private function getCartProductLangData($selprod_id, $lang_id)
    {
        $langProdSrch = new ProductSearch($lang_id);
        $langProdSrch->setDefinedCriteria();
        $langProdSrch->joinBrands();
        $langProdSrch->joinProductToCategory();
        $langProdSrch->joinSellerSubscription();
        $langProdSrch->addSubscriptionValidCondition();
        $langProdSrch->doNotCalculateRecords();
        $langProdSrch->doNotLimitRecords();
        $langProdSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $langProdSrch->addCondition('selprod_id', '=', $selprod_id);
        $fields = array( 'IFNULL(product_name, product_identifier) as product_name','IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title','IFNULL(brand_name, brand_identifier) as brand_name','IFNULL(shop_name, shop_identifier) as shop_name' );
        $langProdSrch->addMultipleFields($fields);
        $langProdRs = $langProdSrch->getResultSet();
        return $langSpecificProductInfo = FatApp::getDb()->fetch($langProdRs);
    }

    public function PaymentSummary()
    {
        if (true ===  MOBILE_APP_API_CALL) {
            $payFromWallet = FatApp::getPostedData('payFromWallet', Fatutility::VAR_INT, 0);
            $this->cartObj->updateCartWalletOption($payFromWallet);
        }

        $criteria = array( 'isUserLogged' => true, 'hasProducts' => true, 'hasStock' => true, 'hasBillingAddress' => true );
        if ($this->cartObj->hasPhysicalProduct()) {
            $criteria['hasShippingAddress'] = true;
            $criteria['isProductShippingMethodSet'] = true;
        }

        if (!$this->isEligibleForNextStep($criteria)) {
            $this->errMessage = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            if (Message::getErrorCount()) {
                $this->errMessage = Message::getHtml();
            }
            FatUtility::dieWithError($this->errMessage);
        }

        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
        $userId = UserAuthentication::getLoggedUserId();

        /* Payment Methods[ */
        $pmSrch = PaymentMethods::getSearchObject($this->siteLangId);
        $pmSrch->doNotCalculateRecords();
        $pmSrch->doNotLimitRecords();
        $pmSrch->addMultipleFields(array('pmethod_id', 'IFNULL(pmethod_name, pmethod_identifier) as pmethod_name', 'pmethod_code', 'pmethod_description'));
        if (!$cartSummary["isCodEnabled"]) {
            $pmSrch->addCondition('pmethod_code', '!=', 'CashOnDelivery');
        }

        /* if( $this->cartObj->hasDigitalProduct() ){

        } */

        $pmRs = $pmSrch->getResultSet();
        $paymentMethods = FatApp::getDb()->fetchAll($pmRs);
        /* ] */

        $orderData = array();
        /* add Order Data[ */
        if (true ===  MOBILE_APP_API_CALL) {
            $order_id = FatApp::getPostedData('orderId', Fatutility::VAR_STRING, false);
        } else {
            $order_id = isset($_SESSION['shopping_cart']["order_id"]) ? $_SESSION['shopping_cart']["order_id"] : false;
        }


        /* if($order_id){
        $orderObj =  new Orders();
        $orderInfo = $orderObj->getOrderById( $order_id, $this->siteLangId );
        if($orderInfo['order_is_paid']){
        $order_id = false;
        }
        } */

        $shippingAddressArr = array();
        $billingAddressArr = array();
        $shippingAddressId = $this->cartObj->getCartShippingAddress();
        $billingAddressId = $this->cartObj->getCartBillingAddress();

        if ($shippingAddressId) {
            $shippingAddressArr = UserAddress::getUserAddresses($userId, $this->siteLangId, 0, $shippingAddressId);
        }
        if ($billingAddressId) {
            $billingAddressArr = UserAddress::getUserAddresses($userId, $this->siteLangId, 0, $billingAddressId);
        }

        $orderData['order_id'] = $order_id;
        $orderData['order_user_id'] = $userId;
        /* $orderData['order_user_name'] = $userDataArr['user_name'];
        $orderData['order_user_email'] = $userDataArr['credential_email'];
        $orderData['order_user_phone'] = $userDataArr['user_phone']; */
        $orderData['order_is_paid'] = Orders::ORDER_IS_PENDING;
        $orderData['order_date_added'] = date('Y-m-d H:i:s');

        /* addresses[ */
        $userAddresses[0] = array(
        'oua_order_id'    =>    $order_id,
        'oua_type'        =>    Orders::BILLING_ADDRESS_TYPE,
        'oua_name'        =>    $billingAddressArr['ua_name'],
        'oua_address1'    =>    $billingAddressArr['ua_address1'],
        'oua_address2'    =>    $billingAddressArr['ua_address2'],
        'oua_city'        =>    $billingAddressArr['ua_city'],
        'oua_state'        =>    $billingAddressArr['state_name'],
        'oua_country'    =>    $billingAddressArr['country_name'],
        'oua_country_code'    =>    $billingAddressArr['country_code'],
        'oua_phone'        =>    $billingAddressArr['ua_phone'],
        'oua_zip'        =>    $billingAddressArr['ua_zip'],
        );

        if (!empty($shippingAddressArr)) {
            $userAddresses[1] = array(
            'oua_order_id'    =>    $order_id,
            'oua_type'        =>    Orders::SHIPPING_ADDRESS_TYPE,
            'oua_name'        =>    $shippingAddressArr['ua_name'],
            'oua_address1'    =>    $shippingAddressArr['ua_address1'],
            'oua_address2'    =>    $shippingAddressArr['ua_address2'],
            'oua_city'        =>    $shippingAddressArr['ua_city'],
            'oua_state'        =>    $shippingAddressArr['state_name'],
            'oua_country'    =>    $shippingAddressArr['country_name'],
            'oua_country_code'    =>    $shippingAddressArr['country_code'],
            'oua_phone'        =>    $shippingAddressArr['ua_phone'],
            'oua_zip'        =>    $shippingAddressArr['ua_zip'],
            );
        }
        $orderData['userAddresses'] = $userAddresses;
        /* ] */

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

        /* $cartShippingApiId = $this->cartObj->getCartShippingApi();
        $order_shippingapi_id = 0;
        $order_shippingapi_code = '';

        if( $cartShippingApiId > 0 ){
        $shippingApiRow = ShippingApi::getAttributesById($cartShippingApiId);
        $order_shippingapi_id = $shippingApiRow['shippingapi_id'];
        $order_shippingapi_code = $shippingApiRow['shippingapi_code'];
        }

        if( $order_shippingapi_id > 0 ){
        $orderData['order_shippingapi_id'] = $order_shippingapi_id;
        $orderData['order_shippingapi_code'] = $order_shippingapi_code;
        } */
        /* if( $order_shippingapi_id > 0 ){
        $shippingData['opshipping_method_id'] = $shippingApiRow['shippingapi_code'];
        $shippingData['opshipping_pship_id'] =  $shippingApiRow['shippingapi_code'];
        $shippingData['opshipping_carrier'] =  $shippingApiRow['shippingapi_code'];
        $shippingData['opshipping_company_id'] =  $shippingApiRow['shippingapi_code'];
        $shippingData['opshipping_duration'] =  $shippingApiRow['shippingapi_code'];
        } */
        if (!empty($cartSummary["cartDiscounts"])) {
            $orderData['order_discount_coupon_code'] = $cartSummary["cartDiscounts"]["coupon_code"];
            $orderData['order_discount_type'] = $cartSummary["cartDiscounts"]["coupon_discount_type"];
            $orderData['order_discount_value'] = $cartSummary["cartDiscounts"]["coupon_discount_value"];
            $orderData['order_discount_total'] = $cartSummary["cartDiscounts"]["coupon_discount_total"];
            $orderData['order_discount_info'] = $cartSummary["cartDiscounts"]["coupon_info"];
        }

        $orderData['order_reward_point_used'] = $cartSummary["cartRewardPoints"];
        $orderData['order_reward_point_value'] = CommonHelper::convertRewardPointToCurrency($cartSummary["cartRewardPoints"]);

        //$orderData['order_payment_gateway_charges'] = $cartSummary["orderPaymentGatewayCharges"];
        //$orderData['order_cart_total'] = $cartSummary["cartTotal"];
        //$orderData['order_shipping_charged'] = $cartSummary["shippingTotal"];
        $orderData['order_tax_charged'] = $cartSummary["cartTaxTotal"];
        $orderData['order_site_commission'] = $cartSummary["siteCommission"];
        $orderData['order_volume_discount_total'] = $cartSummary["cartVolumeDiscount"];
        //$orderData['order_sub_total'] = $cartSummary["netTotalWithoutDiscount"];
        //$orderData['order_net_charged'] = $cartSummary["netTotalAfterDiscount"];
        //$orderData['order_actual_paid'] = $cartSummary["cartActualPaid"];
        $orderData['order_net_amount'] = $cartSummary["orderNetAmount"];
        $orderData['order_is_wallet_selected'] = $cartSummary["cartWalletSelected"];
        $orderData['order_wallet_amount_charge'] = $cartSummary["WalletAmountCharge"];
        $orderData['order_type'] = Orders::ORDER_PRODUCT;

        /* referrer details[ */
        $srchOrder = new OrderSearch();
        $srchOrder->doNotCalculateRecords();
        $srchOrder->doNotLimitRecords();
        $srchOrder->addCondition('order_user_id', '=', $userId);
        $srchOrder->addCondition('order_is_paid', '=', Orders::ORDER_IS_PAID);
        $srchOrder->addCondition('order_referrer_user_id', '!=', 0);
        $srchOrder->addMultipleFields(array( 'count(o.order_id) as totalOrders' ));
        $rs = $srchOrder->getResultSet();
        $existingReferrerOrderRow = FatApp::getDb()->fetch($rs);

        $orderData['order_referrer_user_id'] = 0;
        $orderData['order_referrer_reward_points'] = 0;
        $orderData['order_referral_reward_points'] = 0;
        $orderData['order_cart_data'] = Cart::getCartData($userId);

        $referrerUserId = 0;
        if (isset($_COOKIE['referrer_code_checkout']) && !empty($_COOKIE['referrer_code_checkout'])) {
            $userReferrerCode = $_COOKIE['referrer_code_checkout'];

            $userSrchObj = User::getSearchObject();
            $userSrchObj->doNotCalculateRecords();
            $userSrchObj->doNotLimitRecords();
            $userSrchObj->addCondition('user_referral_code', '=', $userReferrerCode);
            $userSrchObj->addCondition('user_id', '!=', $userId);
            $userSrchObj->addMultipleFields(array('user_id', 'user_referral_code', 'user_name' ));
            $rs = $userSrchObj->getResultSet();
            $referrerUserRow = FatApp::getDb()->fetch($rs);
            if ($referrerUserRow && $referrerUserRow['user_referral_code'] == $userReferrerCode && $userReferrerCode != '' && $referrerUserRow['user_referral_code'] != '') {
                $referrerUserId = $referrerUserRow['user_id'];
                //$referrerUserName = $referrerUserRow['user_name'];
            }
        }

        if ($referrerUserId > 0 && FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE") && $existingReferrerOrderRow['totalOrders'] == 0) {
            $orderData['order_referrer_user_id'] = $referrerUserId;
            $orderData['order_referrer_reward_points'] = FatApp::getConfig("CONF_SALE_REFERRER_REWARD_POINTS", FatUtility::VAR_INT, 0);
            $orderData['order_referral_reward_points'] = FatApp::getConfig("CONF_SALE_REFERRAL_REWARD_POINTS", FatUtility::VAR_INT, 0);
        }
        /* ] */

        $allLanguages = Language::getAllNames();
        $productSelectedShippingMethodsArr = $this->cartObj->getProductShippingMethod();

        $orderLangData = array();
        foreach ($allLanguages as $lang_id => $language_name) {
            $order_shippingapi_name = '';

            if ($this->cartObj->getCartShippingApi()) {
                $shippingApiLangRow = ShippingApi::getAttributesByLangId($lang_id, $this->cartObj->getCartShippingApi());
                $order_shippingapi_name = $shippingApiLangRow['shippingapi_name'];
                if (empty($shippingApiLangRow)) {
                    $order_shippingapi_name = $shippingApiRow['shippingapi_identifier'];
                }
            }

            $orderLangData[$lang_id] = array(
            'orderlang_lang_id'            =>    $lang_id,
            'order_shippingapi_name'    =>    $order_shippingapi_name
            );
        }
        $orderData['orderLangData'] = $orderLangData;

        /* order products[ */
        $cartProducts = $this->cartObj->getProducts($this->siteLangId);

        $orderData['products'] = array();
        $orderData['prodCharges'] = array();

        $order_affiliate_user_id = 0;
        $order_affiliate_total_commission = 0;
		
		/*--- For COD Enable ---*/
		$cod_enable = 0;
        if ($cartProducts) {
            $productShippingData = array();
            foreach ($cartProducts as $cartProduct) {
				if($cartProduct['product_cod_enabled'] == 1){
					$cod_enable = 1;
				}
                $productInfo = $this->getCartProductInfo($cartProduct['selprod_id']);
                if (!$productInfo) {
                    continue;
                }

                $sduration_name = '';
                $shippingDurationTitle = '';
                $shippingDurationRow = array();

                if (!empty($productSelectedShippingMethodsArr['product']) && isset($productSelectedShippingMethodsArr['product'][$productInfo['selprod_id']])) {
                    $shippingDurationRow = $productSelectedShippingMethodsArr['product'][$productInfo['selprod_id']];
                    if ($shippingDurationRow['mshipapi_id']== ShippingMethods::MANUAL_SHIPPING) {
                        $productShippingData = array(
                        'opshipping_method_id' =>$shippingDurationRow['mshipapi_id'],
                        'opshipping_pship_id' =>$shippingDurationRow['pship_id'],
                        'opshipping_company_id' =>$shippingDurationRow['mshipcompany_id'],
                        'opshipping_max_duration' =>$shippingDurationRow['sduration_to'],
                        'opshipping_duration_id' =>$shippingDurationRow['sduration_id'],
                        );
                    } elseif ($shippingDurationRow['mshipapi_id']== ShippingMethods::SHIPSTATION_SHIPPING) {
                        $productShippingData = array( 'opshipping_method_id' =>$shippingDurationRow['mshipapi_id'] );
                    }
                    $productShippingData['opshipping_by_seller_user_id'] = $shippingDurationRow['shipped_by_seller'];
                }
                $productsLangData = array();
                $productShippingLangData = array();
                foreach ($allLanguages as $lang_id => $language_name) {
                    $langSpecificProductInfo = $this->getCartProductLangData($productInfo['selprod_id'], $lang_id);
                    if (!$langSpecificProductInfo) {
                        continue;
                    }

                    if (!empty($shippingDurationRow)) {
                        if ($shippingDurationRow['mshipapi_id']== ShippingMethods::MANUAL_SHIPPING) {
                            $shippingDurationTitle = ShippingDurations::getShippingDurationTitle($shippingDurationRow, $lang_id);
                            $sduration_name = $shippingDurationRow['mshipcompany_name'];
                            $productShippingLangData[$lang_id] =  array(
                            'opshipping_duration'=>$shippingDurationTitle,
                            'opshipping_duration_name'=>$shippingDurationRow['mshipcompany_name'],
                            'opshippinglang_lang_id' => $lang_id
                            );
                        } elseif ($shippingDurationRow['mshipapi_id']== ShippingMethods::SHIPSTATION_SHIPPING) {
                            $sduration_name = $shippingDurationRow['mshipapi_label'];
                            $productShippingLangData[$lang_id] =  array(
                            'opshipping_carrier'=>$shippingDurationRow['mshipcompany_name'],
                            'opshipping_duration_name'=>$sduration_name,
                            'opshippinglang_lang_id' => $lang_id
                            );
                        }
                    }

                    $weightUnitsArr = applicationConstants::getWeightUnitsArr($lang_id);
                    $lengthUnitsArr = applicationConstants::getLengthUnitsArr($lang_id);
                    $op_selprod_title = ($langSpecificProductInfo['selprod_title'] != '') ? $langSpecificProductInfo['selprod_title'] : '';

                    /* stamping/locking of product options language based [ */
                    $op_selprod_options = '';
                    $productOptionsRows = SellerProduct::getSellerProductOptions($productInfo['selprod_id'], true, $lang_id);
                    if (!empty($productOptionsRows)) {
                        $optionCounter = 1;
                        foreach ($productOptionsRows as $poLang) {
                            $op_selprod_options .= $poLang['option_name'].': '.$poLang['optionvalue_name'];
                            if ($optionCounter != count($productOptionsRows)) {
                                $op_selprod_options .= ' | ';
                            }
                            $optionCounter++;
                        }
                    }
                    /* ] */

                    $op_products_dimension_unit_name = ($productInfo['product_dimension_unit']) ? $lengthUnitsArr[$productInfo['product_dimension_unit']] : '';
                    $op_product_weight_unit_name = ($productInfo['product_weight_unit']) ? $weightUnitsArr[$productInfo['product_weight_unit']] : '';

                    $productsLangData[$lang_id] = array(
                    'oplang_lang_id'    =>    $lang_id,
                    'op_product_name'    =>    $langSpecificProductInfo['product_name'],
                    'op_selprod_title'    =>    $op_selprod_title,
                    'op_selprod_options'=>  $op_selprod_options,
                    'op_brand_name'        =>    $langSpecificProductInfo['brand_name'],
                    'op_shop_name'        =>    $langSpecificProductInfo['shop_name'],
                    'op_shipping_duration_name'    =>    $sduration_name,
                    'op_shipping_durations'    =>    $shippingDurationTitle,
                    'op_products_dimension_unit_name'    =>    $op_products_dimension_unit_name,
                    'op_product_weight_unit_name'        =>    $op_product_weight_unit_name,
                    );
                }

                /* $taxCollectedBySeller = applicationConstants::NO;
                if(FatApp::getConfig('CONF_TAX_COLLECTED_BY_SELLER',FatUtility::VAR_INT,0)){
                $taxCollectedBySeller = applicationConstants::YES;
                } */

                $orderData['products'][CART::CART_KEY_PREFIX_PRODUCT.$productInfo['selprod_id']] = array(
                'op_selprod_id'        =>    $productInfo['selprod_id'],
                'op_is_batch'        =>    0,
                'op_selprod_user_id'=>    $productInfo['selprod_user_id'],
                'op_selprod_code'    =>    $productInfo['selprod_code'],
                'op_qty'            =>    $cartProduct['quantity'],
                'op_unit_price'        =>    $cartProduct['theprice'],
                'op_unit_cost'        =>    $cartProduct['selprod_cost'],
                'op_selprod_sku'    =>    $productInfo['selprod_sku'],
                'op_selprod_condition'    =>    $productInfo['selprod_condition'],
                'op_product_model'    =>    $productInfo['product_model'],
                'op_product_type'    =>    $productInfo['product_type'],
                'op_product_length'    =>    $productInfo['product_length'],
                'op_product_width'    =>    $productInfo['product_width'],
                'op_product_height'    =>    $productInfo['product_height'],
                'op_product_dimension_unit'    =>    $productInfo['product_dimension_unit'],
                'op_product_weight'    =>    $productInfo['product_weight'],
                'op_product_weight_unit'    =>    $productInfo['product_weight_unit'],
                'op_shop_id'        =>    $productInfo['shop_id'],
                'op_shop_owner_username'=>    $productInfo['shop_owner_username'],
                'op_shop_owner_name'=>    $productInfo['shop_onwer_name'],
                'op_shop_owner_email'    =>    $productInfo['shop_owner_email'],
                'op_shop_owner_phone'    =>    $productInfo['shop_owner_phone'],
                'op_selprod_max_download_times' => ($productInfo['selprod_max_download_times']!='-1')?$cartProduct['quantity']*$productInfo['selprod_max_download_times']:$productInfo['selprod_max_download_times'],
                'op_selprod_download_validity_in_days' => $productInfo['selprod_download_validity_in_days'],
                'op_sduration_id'            =>    $cartProduct['sduration_id'],
                //'op_discount_total'    =>    0, //todo:: after coupon discount integration
                //'op_tax_total'    =>    $cartProduct['tax'],
                'op_commission_charged' => $cartProduct['commission'],
                'op_commission_percentage'    => $cartProduct['commission_percentage'],
                'op_affiliate_commission_percentage' => $cartProduct['affiliate_commission_percentage'],
                'op_affiliate_commission_charged' => $cartProduct['affiliate_commission'],
                'op_status_id'        =>    FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"),
                // 'op_volume_discount_percentage'    =>    $cartProduct['volume_discount_percentage'],
                'productsLangData'    =>    $productsLangData,
                'productShippingData'    =>    $productShippingData,
                'productShippingLangData'    =>    $productShippingLangData,
                /* 'op_tax_collected_by_seller'    =>    $taxCollectedBySeller, */
                'op_free_ship_upto'    =>    $cartProduct['shop_free_ship_upto'],
                'op_actual_shipping_charges'    =>    $cartProduct['shipping_cost'],
                );

                $order_affiliate_user_id = isset($cartProduct['affiliate_user_id'])?$cartProduct['affiliate_user_id']:'';
                $order_affiliate_total_commission += isset($cartProduct['affiliate_commission'])?$cartProduct['affiliate_commission']:'';

                $discount = 0;
                if (!empty($cartSummary["cartDiscounts"]["discountedSelProdIds"])) {
                    if (array_key_exists($productInfo['selprod_id'], $cartSummary["cartDiscounts"]["discountedSelProdIds"])) {
                        $discount = $cartSummary["cartDiscounts"]["discountedSelProdIds"][$productInfo['selprod_id']];
                    }
                }

                $shippingCost = $cartProduct['shipping_cost'];
                if ($cartProduct['shop_eligible_for_free_shipping'] && $cartProduct['psbs_user_id'] > 0) {
                    $shippingCost = 0;
                }

                $rewardPoints = 0;
                $rewardPoints = $orderData['order_reward_point_value'];
                $usedRewardPoint = 0;
                if ($rewardPoints > 0) {
                    $selProdAmount = ($cartProduct['quantity'] * $cartProduct['theprice']) + $shippingCost +  $cartProduct['tax']  - $discount - $cartProduct['volume_discount_total'] ;
                    $usedRewardPoint = round((($rewardPoints * $selProdAmount)/($orderData['order_net_amount']+$rewardPoints)), 2);
                }

                $orderData['prodCharges'][CART::CART_KEY_PREFIX_PRODUCT.$productInfo['selprod_id']] = array(
                OrderProduct::CHARGE_TYPE_SHIPPING => array(
                'amount' => $shippingCost
                ),
                OrderProduct::CHARGE_TYPE_TAX =>array(
                'amount' => $cartProduct['tax']
                ),
                OrderProduct::CHARGE_TYPE_DISCOUNT =>array(
                'amount' => -$discount /*[Should be negative value]*/
                ),
                OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT =>array(
                'amount' => -$usedRewardPoint
                ),
                /* OrderProduct::CHARGE_TYPE_BATCH_DISCOUNT => array(
                'amount' => -$cartProduct['batch_discount_single_product'] */
                OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT => array(
                'amount'    =>    -$cartProduct['volume_discount_total']
                ),

                );
            }
        }
		$this->set('codStatus', $cod_enable);
        $orderData['order_affiliate_user_id'] = $order_affiliate_user_id;
        $orderData['order_affiliate_total_commission'] = $order_affiliate_total_commission;
        /* ] */
        /* ] */
        $orderObj = new Orders();
        if ($orderObj->addUpdateOrder($orderData, $this->siteLangId)) {
            $order_id = $orderObj->getOrderId();
            $_SESSION['order_id'] = $order_id;
        } else {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($orderObj->getError());
            }
            Message::addErrorMessage($orderObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = Orders::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_id', '=', $order_id);
        $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        /* $orderInfo = $orderObj->getOrderById( $order_id, $this->siteLangId, array('payment_status' => 0) ); */
        if (!$orderInfo) {
            $this->cartObj->clear();
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'viewOrder', array($order_id)));
        }

        $userWalletBalance = User::getUserBalance($userId, true);

        if (false ===  MOBILE_APP_API_CALL) {
            $WalletPaymentForm = $this->getWalletPaymentForm($this->siteLangId);
            $confirmForm = $this->getConfirmFormWithNoAmount($this->siteLangId);

            if ((FatUtility::convertToType($userWalletBalance, FatUtility::VAR_FLOAT) > 0) && $cartSummary['cartWalletSelected'] && $cartSummary['orderNetAmount'] > 0) {
                $WalletPaymentForm->addFormTagAttribute('action', CommonHelper::generateUrl('WalletPay', 'Charge', array($order_id)));
                $WalletPaymentForm->fill(array('order_id' => $order_id));
                $WalletPaymentForm->setFormTagAttribute('onsubmit', 'confirmOrder(this); return(false);');
                $WalletPaymentForm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId));
            }

            if ($cartSummary['orderNetAmount'] <= 0) {
                $confirmForm->addFormTagAttribute('action', CommonHelper::generateUrl('ConfirmPay', 'Charge', array($order_id)));
                $confirmForm->fill(array('order_id' => $order_id));
                /* $confirmForm->setFormTagAttribute('onsubmit', 'confirmOrderWithoutPayment(this); return(false);'); */
                $confirmForm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Confirm_Order', $this->siteLangId));
            }

            $redeemRewardFrm = $this->getRewardsForm($this->siteLangId);
            $this->set('redeemRewardFrm', $redeemRewardFrm);
        }



        $this->set('paymentMethods', $paymentMethods);
        $this->set('userWalletBalance', $userWalletBalance);
        $this->set('cartSummary', $cartSummary);
        if (false ===  MOBILE_APP_API_CALL) {
            $excludePaymentGatewaysArr = applicationConstants::getExcludePaymentGatewayArr();
            $cartHasPhysicalProduct = false;
            if ($this->cartObj->hasPhysicalProduct()) {
                $cartHasPhysicalProduct = true;
            }
            $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
            $this->set('excludePaymentGatewaysArr', $excludePaymentGatewaysArr);
            $this->set('orderInfo', $orderInfo);
            $this->set('WalletPaymentForm', $WalletPaymentForm);
            $this->set('confirmForm', $confirmForm);
        }

        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('products', $cartProducts);
            $this->set('orderId', $order_id);
            $this->set('orderType', $orderInfo['order_type']);
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    private function getPaymentMethodData($pmethod_id)
    {
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
        return $paymentMethod;
    }

    public function PaymentTab($order_id, $pmethod_id)
    {
        $pmethod_id = FatUtility::int($pmethod_id);
        if (!$pmethod_id) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Invalid_Request!", $this->siteLangId));
        }

        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
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

        $paymentMethod = $this->getPaymentMethodData($pmethod_id);
        $frm = $this->getPaymentTabForm($this->siteLangId, $paymentMethod['pmethod_code']);
        $controller = $paymentMethod['pmethod_code'].'Pay';
        $frm->setFormTagAttribute('action', CommonHelper::generateUrl($controller, 'charge', array($order_id)));
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
        if (strtolower($paymentMethod['pmethod_code']) == "cashondelivery") {
            if ($this->cartObj->hasDigitalProduct()) {
                $str = Labels::getLabel('MSG_{COD}_is_not_available_if_your_cart_has_any_Digital_Product', $this->siteLangId);
                $str = str_replace('{cod}', $paymentMethod['pmethod_name'], $str);
                FatUtility::dieWithError($str);
            }
            $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
            $user_id = UserAuthentication::getLoggedUserId();
            $userWalletBalance = User::getUserBalance($user_id, true);

            if (!$cartSummary['isCodValidForNetAmt']) {
                $str = Labels::getLabel('MSG_Sorry_{COD}_is_not_available_on_this_order.', $this->siteLangId).' <br/>'.Labels::getLabel('MSG_{COD}_is_available_on_payable_amount_between_{MIN}_and_{MAX}', $this->siteLangId);
                $str = str_replace('{cod}', $paymentMethod['pmethod_name'], $str);
                $str = str_replace('{min}', CommonHelper::displayMoneyFormat(FatApp::getConfig("CONF_MIN_COD_ORDER_LIMIT")), $str);
                $str = str_replace('{max}', CommonHelper::displayMoneyFormat(FatApp::getConfig("CONF_MAX_COD_ORDER_LIMIT")), $str);
                FatUtility::dieWithError($str);
            }

            if ($cartSummary['cartWalletSelected'] && $userWalletBalance < $cartSummary['orderNetAmount']) {
                $str = Labels::getLabel('MSG_Wallet_can_not_be_used_along_with_{COD}', $this->siteLangId);
                $str = str_replace('{cod}', $paymentMethod['pmethod_name'], $str);
                FatUtility::dieWithError($str);
                //$this->set('error', $str );
            }
        }
        /* ] */
        $this->_template->render(false, false, '', false, false);
    }

    public function walletSelection()
    {
        $post = FatApp::getPostedData();
        $payFromWallet = $post['payFromWallet'];
        //$this->cartObj = new Cart();
        $this->cartObj->updateCartWalletOption($payFromWallet);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function useRewardPoints()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();

        if (empty($post)) {
            $this->errMessage = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (empty($post['redeem_rewards'])) {
            $this->errMessage = Labels::getLabel('LBL_You_cannot_use_0_reward_points._Please_add_reward_points_greater_than_0', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        $orderId = isset($_SESSION['order_id']) ? $_SESSION['order_id'] : '';
        if (true ===  MOBILE_APP_API_CALL) {
            if (empty($post['orderId'])) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Order_Id_Is_Required', $this->siteLangId));
            }
            $orderId = $post['orderId'];
        }

        $rewardPoints = $post['redeem_rewards'];
        $totalBalance = UserRewardBreakup::rewardPointBalance($loggedUserId, $orderId);

        /* var_dump($totalBalance);exit; */
        if ($totalBalance == 0 || $totalBalance < $rewardPoints) {
            $this->errMessage = Labels::getLabel('ERR_Insufficient_reward_point_balance', $this->siteLangId);
            FatUtility::dieJsonError($this->errMessage);
        }

        $cartObj = new Cart($loggedUserId, $this->siteLangId, $this->app_user['temp_user_id']);

        // $cartObj = new Cart();

        $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);

        $cartTotalWithoutDiscount = $cartSummary['cartTotal'] - $cartSummary["cartDiscounts"]["coupon_discount_total"];

        $rewardPointValues = min(CommonHelper::convertRewardPointToCurrency($rewardPoints), $cartTotalWithoutDiscount);
        $rewardPoints = CommonHelper::convertCurrencyToRewardPoint($rewardPointValues);

        if ($rewardPoints < FatApp::getConfig('CONF_MIN_REWARD_POINT') || $rewardPoints > FatApp::getConfig('CONF_MAX_REWARD_POINT')) {
            $msg = Labels::getLabel('ERR_PLEASE_USE_REWARD_POINT_BETWEEN_{MIN}_TO_{MAX}', $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, array('{MIN}' => FatApp::getConfig('CONF_MIN_REWARD_POINT'), '{MAX}' => FatApp::getConfig('CONF_MAX_REWARD_POINT')));
            LibHelper::dieJsonError($msg);
        }
        if (!$cartObj->updateCartUseRewardPoints($rewardPoints)) {
            $this->errMessage = Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel("MSG_Used_Reward_point", $this->siteLangId).'-'.$rewardPoints);
        if (true ===  MOBILE_APP_API_CALL) {
            $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
            $cartProducts = $cartObj->getProducts($this->siteLangId);

            $this->set('cartSummary', $cartSummary);
            $this->set('products', $cartProducts);
            $this->_template->render();
        }

        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeRewardPoints()
    {
        $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id']);
        if (!$cartObj->removeUsedRewardPoints()) {
            $this->errMessage = Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel("MSG_used_reward_point_removed", $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
            $cartProducts = $cartObj->getProducts($this->siteLangId);

            $this->set('cartSummary', $cartSummary);
            $this->set('products', $cartProducts);
            $this->_template->render(true, true, 'checkout/use-reward-points.php');
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function ConfirmOrder()
    {
        $order_type = FatApp::getPostedData('order_type', FatUtility::VAR_INT, 0);
        $pmethod_id = FatApp::getPostedData('pmethod_id', FatUtility::VAR_INT, 0);
        $order_id = FatApp::getPostedData("order_id", FatUtility::VAR_STRING, "");
        $user_id = UserAuthentication::getLoggedUserId();
        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
        $userWalletBalance = FatUtility::convertToType(User::getUserBalance($user_id, true), FatUtility::VAR_FLOAT);
        $orderNetAmount = isset($cartSummary['orderNetAmount']) ? FatUtility::convertToType($cartSummary['orderNetAmount'], FatUtility::VAR_FLOAT) : 0;

        if (true ===  MOBILE_APP_API_CALL) {
            $paymentUrl = '';
            $sendToWeb = 1;
            if (0 < $pmethod_id) {
                $paymentMethod = $this->getPaymentMethodData($pmethod_id);
                $controller = $paymentMethod['pmethod_code'].'Pay';
                $paymentUrl = CommonHelper::generateFullUrl($controller, 'charge', array($order_id));
            }
            if (Orders::ORDER_WALLET_RECHARGE != $order_type && $cartSummary['cartWalletSelected'] && $userWalletBalance >= $orderNetAmount) {
                $sendToWeb = $pmethod_id = 0;
                $paymentUrl = CommonHelper::generateFullUrl('WalletPay', 'charge', array($order_id));
            }
            if (empty($paymentUrl)) {
                LibHelper::dieJsonError(Labels::getLabel('MSG_Please_Select_Payment_Method', $this->siteLangId));
            }
            $this->set('sendToWeb', $sendToWeb);
            $this->set('orderPayment', $paymentUrl);
        }

        /* Loading Money to wallet[ */
        if ($order_type == Orders::ORDER_WALLET_RECHARGE) {
            $criteria = array( 'isUserLogged' => true );
            if (!$this->isEligibleForNextStep($criteria)) {
                $this->errMessage = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($this->errMessage);
                }
                if (Message::getErrorCount()) {
                    $this->errMessage = Message::getHtml();
                }
                FatUtility::dieWithError($this->errMessage);
            }

            $user_id = UserAuthentication::getLoggedUserId();

            $paymentMethodRow = PaymentMethods::getAttributesById($pmethod_id);
            if (!$paymentMethodRow || $paymentMethodRow['pmethod_active'] != applicationConstants::ACTIVE) {
                $this->errMessage = Labels::getLabel("LBL_Invalid_Payment_method,_Please_contact_Webadmin.", $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($this->errMessage);
                }
                Message::addErrorMessage($this->errMessage);
                FatUtility::dieWithError(Message::getHtml());
            }

            if ($order_id == '') {
                $this->errMessage = Labels::getLabel("MSG_INVALID_Request", $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($this->errMessage);
                }
                Message::addErrorMessage($this->errMessage);
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
                $this->errMessage = Labels::getLabel("MSG_INVALID_ORDER_PAID_CANCELLED", $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($this->errMessage);
                }
                Message::addErrorMessage($this->errMessage);
                FatUtility::dieWithError(Message::getHtml());
            }

            //No Need to clear cart in case of wallet recharge
            /*$this->cartObj->clear();
            $this->cartObj->updateUserCart();*/

            $orderObj->updateOrderInfo($order_id, array('order_pmethod_id' => $pmethod_id));

            if (true ===  MOBILE_APP_API_CALL) {
                $this->_template->render();
            }
            $this->_template->render(false, false, 'json-success.php');
        }
        /* ] */

        /* ConfirmOrder function is called for both wallet payments and for paymentgateway selection as well. */
        $criteria = array( 'isUserLogged' => true, 'hasProducts' => true, 'hasStock' => true, 'hasBillingAddress' => true );
        if ($this->cartObj->hasPhysicalProduct()) {
            $criteria['hasShippingAddress'] = true;
            $criteria['isProductShippingMethodSet'] = true;
        }
        if (!$this->isEligibleForNextStep($criteria)) {
            $this->errMessage = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            if (Message::getErrorCount()) {
                $this->errMessage = Message::getHtml();
            }
            FatUtility::dieWithError($this->errMessage);
        }

        if ($cartSummary['cartWalletSelected'] && $userWalletBalance >= $orderNetAmount && !$pmethod_id) {
            if (true ===  MOBILE_APP_API_CALL) {
                $this->_template->render();
            }
            $this->_template->render(false, false, 'json-success.php');
            exit;
        }

        $post = FatApp::getPostedData();
        // commonHelper::printArray($post); die;

        $paymentMethodRow = PaymentMethods::getAttributesById($pmethod_id);

        if (!$paymentMethodRow || $paymentMethodRow['pmethod_active'] != applicationConstants::ACTIVE) {
            $this->errMessage = Labels::getLabel("LBL_Invalid_Payment_method,_Please_contact_Webadmin.", $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (false ===  MOBILE_APP_API_CALL && strtolower($paymentMethodRow['pmethod_code']) == 'cashondelivery' && FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '' && FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '')!= '')) {
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

        if ($userWalletBalance >= $cartSummary['orderNetAmount'] && $cartSummary['cartWalletSelected'] && !$pmethod_id) {
            $frm = $this->getWalletPaymentForm($this->siteLangId);
        } else {
            $frm = $this->getPaymentTabForm($this->siteLangId);
        }

        $post = $frm->getFormDataFromArray($post);
        if (!isset($post['order_id']) || $post['order_id'] == '') {
            $this->errMessage = Labels::getLabel('MSG_Invalid_Request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
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
            $this->errMessage = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($cartSummary['cartWalletSelected'] && $cartSummary['orderPaymentGatewayCharges'] == 0) {
            $this->errMessage = Labels::getLabel('MSG_Try_to_pay_using_wallet_balance_as_amount_for_payment_gateway_is_not_enough.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($cartSummary['orderPaymentGatewayCharges'] == 0 && $pmethod_id) {
            $this->errMessage = Labels::getLabel('MSG_Amount_for_payment_gateway_must_be_greater_than_zero.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($pmethod_id) {
            $_SESSION['cart_order_id'] = $order_id;
            $_SESSION['order_type'] = $order_type;
            $orderObj->updateOrderInfo($order_id, array('order_pmethod_id' => $pmethod_id));
            // $this->cartObj->clear();
            // $this->cartObj->updateUserCart();
        }

        /* Deduct reward point in case of cashondelivery [ */
        if (strtolower($paymentMethodRow['pmethod_code']) == 'cashondelivery' && $orderInfo['order_reward_point_used'] > 0) {
            $rewardDebited = UserRewards::debit($orderInfo['order_user_id'], $orderInfo['order_reward_point_used'], $order_id, $orderInfo['order_language_id']);
            if (!$rewardDebited) {
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError(Message::getHtml());
                }
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        
        if (strtolower($paymentMethodRow['pmethod_code']) == 'cashondelivery')
        {
            
            $orderProducts = OrderProduct::getOpArrByOrderId($order_id);
            $db = FatApp::getDb();
            foreach ($orderProducts as $op) {
                if (empty($op)) {
                    continue;
                }
            
                $db->query("UPDATE tbl_seller_products SET selprod_stock = (selprod_stock - " . (int)$op['op_qty'] . "),selprod_sold_count = (selprod_sold_count + " . (int)$op['op_qty'] . ") WHERE selprod_id = '" . (int)$op['op_selprod_id'] . "' AND selprod_subtract_stock = '1'");

                $sellProdInfo = SellerProduct::getAttributesById($op['op_selprod_id'], array('selprod_stock','selprod_subtract_stock','selprod_track_inventory','selprod_threshold_stock_level'));
                if (($sellProdInfo["selprod_threshold_stock_level"] >= $sellProdInfo["selprod_stock"]) && ($sellProdInfo["selprod_track_inventory"] == 1)) {
                    $emailNotificationObj->sendProductStockAlert($op['op_selprod_id']);
                }
            }
        }

        /*]*/

        /* if ( !$orderObj->addOrderHistory( $order_id, 1, Labels::getLabel("LBL_-NA-",$this->siteLangId), true, $this->siteLangId ) ){
        Message::addErrorMessage( $orderObj->getError() );
        FatUtility::dieWithError( Message::getHtml() );
        } */
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function editAddress()
    {
        $post = FatApp::getPostedData();
        $address_id = isset($post['address_id']) ? FatUtility::int($post['address_id']) : 0;
        $addressFrm = $this->getUserAddressForm($this->siteLangId);
        $address =  UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $this->siteLangId, 0, $address_id);
        if ($address_id) {
            $stateId =  $address['ua_state_id'];
            $cityId =  $address['ua_city_id'];
        } else {
            $stateId = 0;
            $cityId = 0;
        }
        $addressFrm->fill($address);
        $this->set('addressFrm', $addressFrm);
        $this->set('address_id', $address_id);
        if ($address_id > 0) {
            $labelHeading =  Labels::getLabel('LBL_Edit_Address', $this->siteLangId);
        } else {
            $labelHeading =  Labels::getLabel('LBL_Add_Address', $this->siteLangId);
        }

        $cartHasPhysicalProduct = false;
        if ($this->cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }

        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->set('labelHeading', $labelHeading);
        $this->set('stateId', $stateId);
        $this->set('cityId', $cityId);
        $this->_template->render(false, false, 'checkout/address-form.php');
    }

    private function getCheckoutAddressForm($langId)
    {
        $frm = new Form('frmAddress');
        $addresses = UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $langId);
        $addressAssoc = array();
        foreach ($addresses as $address) {
            $city = $address['ua_city'];
            $state = (strlen($address['ua_city']) > 0) ? ', '. $address['state_name'] : $address['state_name'];
            $country = (strlen($state) > 0) ? ', '.$address['country_name'] : $address['country_name'];
            $location = $city . $state. $country;
            $addressAssoc[$address['ua_id']] = $location;
        }
        $frm->addRadioButtons('', 'shipping_address_id', $addressAssoc);
        $frm->addRadioButtons('', 'billing_address_id', $addressAssoc);
        return $frm;
    }

    private function getShippingApiForm($langId)
    {
        $srch = ShippingMethods::getListingObj($langId, array('shippingapi_id'));
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $shippingApis = FatApp::getDb()->fetchAllAssoc($rs);
        $frm = new Form('frmShippingApi');
        $frm->addSelectBox(Labels::getLabel('MSG_Select_Shipping_Type', $langId), 'shippingapi_id', $shippingApis, '', array(), '')->requirements()->setRequired();
        /* $frm->addSubmitButton( '', 'btn_submit', Labels::getLabel('LBL_Continue', $langId) ); */
        return $frm;
    }

    private function getShippingMethods($langId)
    {
        $srch = ShippingMethods::getListingObj($langId, array('shippingapi_id'));
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $shippingApis = FatApp::getDb()->fetchAllAssoc($rs);

        return $shippingApis;
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

    private function getWalletPaymentForm($langId)
    {
        $frm = new Form('frmWalletPayment');
        $frm->addHiddenField('', 'order_id');
        return $frm;
    }

    private function getConfirmFormWithNoAmount($langId)
    {
        $frm = new Form('frmConfirmForm');
        $frm->addHiddenField('', 'order_id');
        return $frm;
    }

    private function getRewardsForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmRewards');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Reward_Points', $langId), 'redeem_rewards', '', array('placeholder'=>Labels::getLabel('LBL_Use_Reward_Point', $langId)));
        $fld->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Apply', $langId));
        return $frm;
    }

    public function resetShippingSummary()
    {
        $this->_template->render(false, false);
    }

    public function resetCartReview()
    {
        $cartHasPhysicalProduct = false;
        if ($this->cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->_template->render(false, false);
    }

    public function resetPaymentSummary()
    {
        $cartHasPhysicalProduct = false;
        if ($this->cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->_template->render(false, false);
    }

    public function loadCartReview()
    {
        $cartHasPhysicalProduct = false;
        if ($this->cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }
        $products = $this->cartObj->getProducts($this->siteLangId);
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->set('products', $products);
        $this->_template->render(false, false);
    }

    public function loadShippingSummary()
    {
        $products = $this->cartObj->getProducts($this->siteLangId);
        $this->set('products', $products);
        $this->_template->render(false, false);
    }

    public function removeShippingSummary()
    {
        $this->cartObj->removeProductShippingMethod();
    }

    public function getFinancialSummary()
    {
        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
        $products = $this->cartObj->getProducts($this->siteLangId);

        $hasPhysicalProd = $this->cartObj->hasPhysicalProduct();
        if (!$hasPhysicalProd) {
            $selected_shipping_address_id = $this->cartObj->getCartBillingAddress();
        } else {
            $selected_shipping_address_id = $this->cartObj->getCartShippingAddress();
        }

        $address =  UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $this->siteLangId, 0, $selected_shipping_address_id);

        $this->set('products', $products);
        $this->set('cartSummary', $cartSummary);
        $this->set('defaultAddress', $address);
        $this->set('hasPhysicalProd', $hasPhysicalProd);
        $this->_template->render(false, false);
    }

    public function getCouponForm()
    {
        /* if( !UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()){
        Message::addErrorMessage(Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId));
        FatUtility::dieWithError( Message::getHtml() );
    } */
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $orderId = isset($_SESSION['order_id'])?$_SESSION['order_id']:'';
        $couponsList = DiscountCoupons::getValidCoupons($loggedUserId, $this->siteLangId, '', $orderId);
        $this->set('couponsList', $couponsList);

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
}
