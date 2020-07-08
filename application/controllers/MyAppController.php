<?php
class MyAppController extends FatController
{
    public $app_user = array();
    public $appToken = '';

    public function __construct($action)
    {            
        parent::__construct($action);
        $this->action = $action;

        if (FatApp::getConfig("CONF_MAINTENANCE", FatUtility::VAR_INT, 0) && (get_class($this) != "MaintenanceController") && (get_class($this) !=' Home' && $action != 'setLanguage')) {
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Site_under_maintenance', CommonHelper::getLangId()));
            }
            FatApp::redirectUser(CommonHelper::generateUrl('maintenance'));
        }

        CommonHelper::initCommonVariables();
        $this->initCommonVariables();
        $this->tempTokenLogin();
    }

    public function initCommonVariables()
    {
        $this->siteLangId = CommonHelper::getLangId();
        $this->siteCurrencyId = CommonHelper::getCurrencyId();

        $this->app_user['temp_user_id'] = 0;
        if (true ===  MOBILE_APP_API_CALL) {
            $this->setApiVariables();
        }

        $this->set('siteLangId', $this->siteLangId);
        $this->set('siteCurrencyId', $this->siteCurrencyId);
        $loginData = array(
        'loginFrm' => $this->getLoginForm(),
        'siteLangId' => $this->siteLangId,
        'showSignUpLink' => true);
        $this->set('loginData', $loginData);
        if (!defined('CONF_MESSAGE_ERROR_HEADING')) {
            define('CONF_MESSAGE_ERROR_HEADING', Labels::getLabel('LBL_Following_error_occurred', $this->siteLangId));
        }

        $controllerName = get_class($this);
        $arr = explode('-', FatUtility::camel2dashed($controllerName));
        array_pop($arr);
        $urlController = implode('-', $arr);
        $controllerName = ucfirst(FatUtility::dashed2Camel($urlController));

        /* to keep track of temporary hold the product stock, update time in each row of tbl_product_stock_hold against current user[ */
        $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id']);
        $cartProducts = $cartObj->getProducts($this->siteLangId);
        if ($cartProducts) {
            foreach ($cartProducts as $product) {
                $cartObj->updateTempStockHold($product['selprod_id'], $product['quantity']);
            }
        }
        /* ] */

        if (true ===  MOBILE_APP_API_CALL) {
            $this->cartItemsCount = $cartObj->countProducts();
            $this->set('cartItemsCount', $this->cartItemsCount);
        }

        $jsVariables = array(
        'confirmRemove' =>Labels::getLabel('LBL_Do_you_want_to_remove', $this->siteLangId),
        'confirmReset' =>Labels::getLabel('LBL_Do_you_want_to_reset_settings', $this->siteLangId),
        'confirmDelete' =>Labels::getLabel('LBL_Do_you_want_to_delete', $this->siteLangId),
        'confirmUpdateStatus' =>Labels::getLabel('LBL_Do_you_want_to_update_the_status', $this->siteLangId),
        'confirmDeleteOption' =>Labels::getLabel('LBL_Do_you_want_to_delete_this_option', $this->siteLangId),
        'confirmDefault' =>Labels::getLabel('LBL_Do_you_want_to_set_default', $this->siteLangId),
        'setMainProduct' => Labels::getLabel('LBL_Set_as_main_product', $this->siteLangId),
        'layoutDirection'=>CommonHelper::getLayoutDirection(),
        'selectPlan' =>Labels::getLabel('LBL_Please_Select_any_Plan_From_The_Above_Plans', $this->siteLangId),
        'alreadyHaveThisPlan' =>str_replace("{clickhere}", '<a href="'.CommonHelper::generateUrl('seller', 'subscriptions').'">'.Labels::getLabel('LBL_Click_Here', $this->siteLangId).'</a>', Labels::getLabel('LBL_You_have_already_Bought_this_plan._Please_choose_some_other_Plan_or_renew_it_from_{clickhere}', $this->siteLangId)),
        'processing' =>Labels::getLabel('LBL_Processing...', $this->siteLangId),
        'requestProcessing' =>Labels::getLabel('LBL_Request_Processing...', $this->siteLangId),
        'selectLocation' =>Labels::getLabel('LBL_Select_Location_to_view_Wireframe', $this->siteLangId),
        'favoriteToShop' =>Labels::getLabel('LBL_Favorite_To_Shop', $this->siteLangId),
        'unfavoriteToShop' =>Labels::getLabel('LBL_Unfavorite_To_Shop', $this->siteLangId),
        'userNotLogged' =>Labels::getLabel('MSG_User_Not_Logged', $this->siteLangId),
        'selectFile' =>Labels::getLabel('MSG_File_not_uploaded', $this->siteLangId),
        'thanksForSharing' =>Labels::getLabel('MSG_Thanks_For_Sharing', $this->siteLangId),
        'isMandatory' =>Labels::getLabel('VLBL_is_mandatory', $this->siteLangId),
        'pleaseEnterValidEmailId' =>Labels::getLabel('VLBL_Please_enter_valid_email_ID_for', $this->siteLangId),
        'charactersSupportedFor' =>Labels::getLabel('VLBL_Only_characters_are_supported_for', $this->siteLangId),
        'pleaseEnterIntegerValue' =>Labels::getLabel('VLBL_Please_enter_integer_value_for', $this->siteLangId),
        'pleaseEnterNumericValue' =>Labels::getLabel('VLBL_Please_enter_numeric_value_for', $this->siteLangId),
        'startWithLetterOnlyAlphanumeric' =>Labels::getLabel('VLBL_must_start_with_a_letter_and_can_contain_only_alphanumeric_characters._Length_must_be_between_4_to_20_characters', $this->siteLangId),
        'mustBeBetweenCharacters' =>Labels::getLabel('VLBL_Length_Must_be_between_6_to_20_characters', $this->siteLangId),
        'invalidValues' =>Labels::getLabel('VLBL_Length_Invalid_value_for', $this->siteLangId),
        'shouldNotBeSameAs' =>Labels::getLabel('VLBL_should_not_be_same_as', $this->siteLangId),
        'mustBeSameAs' =>Labels::getLabel('VLBL_must_be_same_as', $this->siteLangId),
        'mustBeGreaterOrEqual' =>Labels::getLabel('VLBL_must_be_greater_than_or_equal_to', $this->siteLangId),
        'mustBeGreaterThan' =>Labels::getLabel('VLBL_must_be_greater_than', $this->siteLangId),
        'mustBeLessOrEqual' =>Labels::getLabel('VLBL_must_be_less_than_or_equal_to', $this->siteLangId),
        'mustBeLessThan' =>Labels::getLabel('VLBL_must_be_less_than', $this->siteLangId),
        'lengthOf' =>Labels::getLabel('VLBL_Length_of', $this->siteLangId),
        'valueOf' =>Labels::getLabel('VLBL_Value_of', $this->siteLangId),
        'mustBeBetween' =>Labels::getLabel('VLBL_must_be_between', $this->siteLangId),
        'mustBeBetween' =>Labels::getLabel('VLBL_must_be_between', $this->siteLangId),
        'and' =>Labels::getLabel('VLBL_and', $this->siteLangId),
        'pleaseSelect' =>Labels::getLabel('VLBL_Please_select', $this->siteLangId),
        'to' =>Labels::getLabel('VLBL_to', $this->siteLangId),
        'options' =>Labels::getLabel('VLBL_options', $this->siteLangId),
        'isNotAvailable' =>Labels::getLabel('VLBL_is_not_available', $this->siteLangId),
        'RemoveProductFromFavourite' =>Labels::getLabel('LBL_Remove_product_from_favourite_list', $this->siteLangId),
        'AddProductToFavourite' =>Labels::getLabel('LBL_Add_Product_To_favourite_list', $this->siteLangId),
        'MovedSuccessfully' =>Labels::getLabel('LBL_Moved_Successfully', $this->siteLangId),
        'RemovedSuccessfully' =>Labels::getLabel('LBL_Removed_Successfully', $this->siteLangId),
        'siteCurrencyId' =>$this->siteCurrencyId,
        'controllerName' =>$controllerName,
        'confirmDeletePersonalInformation' =>Labels::getLabel('LBL_Do_you_really_want_to_remove_all_your_personal_information', $this->siteLangId),
        'preferredDimensions' =>Labels::getLabel('LBL_Preferred_Dimensions_%s', $this->siteLangId),
        'invalidCredentials' =>Labels::getLabel('LBL_Invalid_Credentials', $this->siteLangId),
        'searchString' =>Labels::getLabel('LBL_Search_string_must_be_atleast_3_characters_long.', $this->siteLangId),
        'atleastOneRecord' =>Labels::getLabel('LBL_Please_select_atleast_one_record.', $this->siteLangId)
        );

        $languages = Language::getAllNames(false);
        foreach ($languages as $val) {
            $jsVariables['language'.$val['language_id']] = $val['language_layout_direction'];
        }

        if (CommonHelper::getLayoutDirection() == 'rtl') {
            $this->_template->addCss('css/style--arabic.css');
        }

        $themeId = FatApp::getConfig('CONF_FRONT_THEME', FatUtility::VAR_INT, 1);

        if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
            $themeId = $_SESSION['preview_theme'];
        }
        $themeDetail = ThemeColor::getAttributesById($themeId);
        $currencySymbolLeft = CommonHelper::getCurrencySymbolLeft();
        $currencySymbolRight = CommonHelper::getCurrencySymbolRight();

        if (CommonHelper::demoUrl()) {
            $this->_template->addCss('css/demo.css');
        }

        $this->set('isUserDashboard', false);
        $this->set('currencySymbolLeft', $currencySymbolLeft);
        $this->set('currencySymbolRight', $currencySymbolRight);
        $this->set('themeDetail', $themeDetail);
        $this->set('jsVariables', $jsVariables);
        $this->set('controllerName', $controllerName);
        $this->set('isAppUser', commonhelper::isAppUser());
        $this->set('action', $this->action);
    }

    private function setApiVariables()
    {
        $this->db = FatApp::getDb();
        $post = FatApp::getPostedData();

        $this->appToken = CommonHelper::getAppToken();

        $this->app_user['temp_user_id'] = 0;
        if (!empty($_SERVER['HTTP_X_TEMP_USER_ID'])) {
            $this->app_user['temp_user_id'] = $_SERVER['HTTP_X_TEMP_USER_ID'];
        }

        $forTempTokenBasedActions = array('send_to_web');
        if (('1.0' == MOBILE_APP_API_VERSION || in_array($this->action, $forTempTokenBasedActions) || empty($this->appToken)) && array_key_exists('_token', $post)) {
            $this->appToken = ($post['_token']!='')?$post['_token']:'';
        }

        if ($this->appToken) {
            if (!UserAuthentication::isUserLogged('', $this->appToken)) {
                $arr = array('status'=>-1,'msg'=>Labels::getLabel('L_Invalid_Token', $this->siteLangId));
                die(json_encode($arr));
            }

            $userId = UserAuthentication::getLoggedUserId();
            $userObj = new User($userId);
            if (!$row = $userObj->getProfileData()) {
                $arr = array('status'=>-1,'msg'=>Labels::getLabel('L_Invalid_Token', $this->siteLangId));
                die(json_encode($arr));
            }
            $this->app_user = $row;
            $this->app_user['temp_user_id'] = 0;
        }

        if (array_key_exists('language', $post)) {
            $this->siteLangId = FatUtility::int($post['language']);
            $_COOKIE['defaultSiteLang'] = $this->siteLangId;
        }

        if (array_key_exists('currency', $post)) {
            $this->siteCurrencyId = FatUtility::int($post['currency']);
            $_COOKIE['defaultSiteCurrency'] = $this->siteCurrencyId;
        }

        $currencyRow = Currency::getAttributesById($this->siteCurrencyId);

        $this->currencySymbol = !empty($currencyRow['currency_symbol_left'])?$currencyRow['currency_symbol_left']:$currencyRow['currency_symbol_right'];
        $this->set('currencySymbol', $this->currencySymbol);

        $user_id = $this->getAppLoggedUserId();
        $userObj = new User($user_id);
        $srch = $userObj->getUserSearchObj();
        $srch->addMultipleFields(array('u.*'));
        $rs = $srch->getResultSet();
        $this->user_details = $this->db->fetch($rs, 'user_id');
        /*$cObj = new Cart($user_id, 0, $this->app_user['temp_user_id']);
        $this->cartItemsCount = $cObj->countProducts();
        $this->set('cartItemsCount', $this->cartItemsCount);*/

        $this->totalFavouriteItems = UserFavorite::getUserFavouriteItemCount($user_id);
        $this->set('totalFavouriteItems', $this->totalFavouriteItems);

        $threadObj = new Thread();
        $this->totalUnreadMessageCount = $threadObj->getMessageCount($user_id);
        $this->set('totalUnreadMessageCount', $this->totalUnreadMessageCount);

        $notificationObj = new Notifications();
        $this->totalUnreadNotificationCount = $notificationObj->getUnreadNotificationCount($user_id);
        $this->set('totalUnreadNotificationCount', $this->totalUnreadNotificationCount);
    }

    private function getAppLoggedUserId()
    {
        return isset($this->app_user["user_id"])?$this->app_user["user_id"]:0;
    }

    public function getStates($countryId, $stateId = 0, $return = false)
    {
        $countryId = FatUtility::int($countryId);
        $stateId = FatUtility::int($stateId);

        $stateObj = new States();
        $statesArr = $stateObj->getStatesByCountryId($countryId, $this->siteLangId);

        if (true === $return) {
            return $statesArr;
        }

        $this->set('statesArr', $statesArr);
        $this->set('stateId', $stateId);
        $this->_template->render(false, false, '_partial/states-list.php');
    }

    public function getCities($stateId, $cityId = 0, $return = false)
    {
        $stateId = FatUtility::int($stateId);
        $cityId = FatUtility::int($cityId);

        $citiesObj = new Cities();
        $citiesArr = $citiesObj->getCitiesByStateId($stateId, $this->siteLangId);

        if (true === $return) {
            return $citiesArr;
        }

        $this->set('citiesArr', $citiesArr);
        $this->set('cityId', $cityId);
        $this->_template->render(false, false, '_partial/cities-list.php');
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $className = get_class($this);
        $arr = explode('-', FatUtility::camel2dashed($className));
        array_pop($arr);
        $urlController = implode('-', $arr);
        $className = ucwords(implode(' ', $arr));

        if ($action == 'index') {
            $nodes[] = array('title'=>Labels::getLabel('LBL_'.ucwords($className), $this->siteLangId));
        } else {
            $nodes[] = array('title'=>ucwords($className), 'href'=>CommonHelper::generateUrl($urlController));
            $nodes[] = array('title'=>Labels::getLabel('LBL_'.ucwords($action), $this->siteLangId));
        }
        return $nodes;
    }

    public function checkIsShippingMode()
    {
        $json = array();
        $post = FatApp::getPostedData();
        if (isset($post["val"])) {
            if ($post["val"] == FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS")) {
                $json["shipping"] = 1;
            }
        }
        echo json_encode($json);
    }

    public function setUpNewsLetter()
    {
        include_once CONF_INSTALLATION_PATH . 'library/Mailchimp.php';
        $siteLangId = CommonHelper::getLangId();
        $post = FatApp::getPostedData();
        $frm = Common::getNewsLetterForm(CommonHelper::getLangId());
        $post = $frm->getFormDataFromArray($post);

        $api_key = FatApp::getConfig("CONF_MAILCHIMP_KEY");
        $list_id = FatApp::getConfig("CONF_MAILCHIMP_LIST_ID");
        if ($api_key == '' || $list_id == '') {
            Message::addErrorMessage(Labels::getLabel("LBL_Newsletter_is_not_configured_yet,_Please_contact_admin", $siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $MailchimpObj = new Mailchimp($api_key);
        $Mailchimp_ListsObj = new Mailchimp_Lists($MailchimpObj);

        try {
            $subscriber = $Mailchimp_ListsObj->subscribe($list_id, array( 'email' => htmlentities($post['email'])));
            if (empty($subscriber['leid'])) {
                Message::addErrorMessage(Labels::getLabel('MSG_Newsletter_subscription_valid_email', $siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        } catch (Mailchimp_Error $e) {
            Message::addErrorMessage($e->getMessage());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Successfully_subscribed', $siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    protected function getGuestUserForm($langId = 0)
    {
        $siteLangId = FatUtility::int($langId);
        $frm = new Form('frmGuestLogin');
        $frm->addTextBox(Labels::getLabel('LBL_Name', $siteLangId), 'user_name', '', array('placeholder'=>Labels::getLabel('LBL_Name', $siteLangId)));
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Email', $siteLangId), 'user_email', '', array('placeholder'=>Labels::getLabel('LBL_EMAIL_ADDRESS', $siteLangId)));
        $frm->addHtml('', 'space', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Guest_Sign_in', $siteLangId));
        return $frm;
    }

    protected function getLoginForm()
    {
        $siteLangId = CommonHelper::getLangId();
        $frm = new Form('frmLogin');
        $userName ='';
        $pass = '';
        if (CommonHelper::demoUrl()) {
            $userName = 'login@dummyid.com';
            $pass = 'kanwar@123';
        }
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Username_Or_Email', $siteLangId), 'username', $userName, array('placeholder'=>Labels::getLabel('LBL_Username_Or_Email', $siteLangId)));
        $pwd = $frm->addPasswordField(Labels::getLabel('LBL_Password', $siteLangId), 'password', $pass, array('placeholder'=>Labels::getLabel('LBL_Password', $siteLangId)));
        $pwd->requirements()->setRequired();
        $frm->addCheckbox(Labels::getLabel('LBL_Remember_Me', $siteLangId), 'remember_me', 1, array(), '', 0);
        $frm->addHtml('', 'forgot', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_LOGIN', $siteLangId));
        return $frm;
    }

    protected function getRegistrationForm($showNewsLetterCheckBox = true)
    {
        $siteLangId = $this->siteLangId;

        $frm = new Form('frmRegister');
        $frm->addHiddenField('', 'user_id', 0, array('id'=>'user_id'));
        $frm->addRequiredField(Labels::getLabel('LBL_NAME', $siteLangId), 'user_name', '', array('placeholder'=>Labels::getLabel('LBL_NAME', $siteLangId)));
        $fld = $frm->addTextBox(Labels::getLabel('LBL_USERNAME', $siteLangId), 'user_username', '', array('placeholder'=>Labels::getLabel('LBL_USERNAME', $siteLangId)));
        if (false ===  MOBILE_APP_API_CALL) {
            $fld->setUnique('tbl_user_credentials', 'credential_username', 'credential_user_id', 'user_id', 'user_id');
        }
        $fld->requirements()->setRequired();
        $fld->requirements()->setUsername();

        $fld = $frm->addEmailField(Labels::getLabel('LBL_EMAIL', $siteLangId), 'user_email', '', array('placeholder'=>Labels::getLabel('LBL_EMAIL', $siteLangId)));
        if (false ===  MOBILE_APP_API_CALL) {
            $fld->setUnique('tbl_user_credentials', 'credential_email', 'credential_user_id', 'user_id', 'user_id');
        }
        $fld = $frm->addPasswordField(Labels::getLabel('LBL_PASSWORD', $siteLangId), 'user_password', '', array('placeholder'=>Labels::getLabel('LBL_PASSWORD', $siteLangId)));
        $fld->requirements()->setRequired();
        $fld->requirements()->setRegularExpressionToValidate(ValidateElement::PASSWORD_REGEX);
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $siteLangId));

        $fld1 = $frm->addPasswordField(Labels::getLabel('LBL_CONFIRM_PASSWORD', $siteLangId), 'password1', '', array('placeholder'=>Labels::getLabel('LBL_CONFIRM_PASSWORD', $siteLangId)));
        $fld1->requirements()->setRequired();
        $fld1->requirements()->setCompareWith('user_password', 'eq', Labels::getLabel('LBL_PASSWORD', $siteLangId));

        $fld = $frm->addCheckBox('', 'agree', 1);
        $fld->requirements()->setRequired();
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Terms_Condition_is_mandatory.', $siteLangId));

        if ($showNewsLetterCheckBox && FatApp::getConfig('CONF_ENABLE_NEWSLETTER_SUBSCRIPTION')) {
            $api_key = FatApp::getConfig("CONF_MAILCHIMP_KEY");
            $list_id = FatApp::getConfig("CONF_MAILCHIMP_LIST_ID");
            if ($api_key != '' || $list_id != '') {
                $frm->addCheckBox(Labels::getLabel('LBL_Newsletter_Signup', $siteLangId), 'user_newsletter_signup', 1);
            }
        }

        $isCheckOutPage = false;
        if (isset($_SESSION['referer_page_url'])) {
            $checkoutPage = basename(parse_url($_SESSION['referer_page_url'], PHP_URL_PATH));
            if ($checkoutPage == 'checkout') {
                $isCheckOutPage=true;
            }
        }
        if ($isCheckOutPage) {
            $frm->addHiddenField('', 'isCheckOutPage', 1);
        }

        //$frm->addDateField(Labels::getLabel('LBL_DOB',CommonHelper::getLangId()), 'user_dob', '',array('readonly'=>'readonly'));
        //$frm->addTextBox(Labels::getLabel('LBL_PHONE',CommonHelper::getLangId()), 'user_phone');
        $frm->addSubmitButton(Labels::getLabel('LBL_Register', $siteLangId), 'btn_submit', Labels::getLabel('LBL_Register', $siteLangId));
        return $frm;
    }

    protected function getUserAddressForm($siteLangId)
    {
        $siteLangId = FatUtility::int($siteLangId);
        $frm = new Form('frmAddress');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Address_Label', $siteLangId), 'ua_identifier');
        $fld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_E.g:_My_Office_Address', $siteLangId));
        $frm->addRequiredField(Labels::getLabel('LBL_Name', $siteLangId), 'ua_name');
        $frm->addRequiredField(Labels::getLabel('LBL_Address_Line1', $siteLangId), 'ua_address1');
        $frm->addTextBox(Labels::getLabel('LBL_Address_Line2', $siteLangId), 'ua_address2');

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($siteLangId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $siteLangId), 'ua_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY'), array(), Labels::getLabel('LBL_Select', $siteLangId));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $siteLangId), 'ua_state_id', array(), '', array(), Labels::getLabel('LBL_Select', $siteLangId))->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_City', $siteLangId), 'ua_city', array(), '', array(), Labels::getLabel('LBL_Select', $siteLangId))->requirement->setRequired(true);
        
        
        //$zipFld = $frm->addRequiredField(Labels::getLabel('LBL_Postalcode', $this->siteLangId), 'ua_zip');
        //$zipFld->requirements()->setRegularExpressionToValidate(ValidateElement::ZIP_REGEX);
        //$zipFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_alphanumeric_value_is_allowed.', $this->siteLangId));

        $phnFld = $frm->addRequiredField(Labels::getLabel('LBL_Phone', $siteLangId), 'ua_phone', '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        // $phnFld->htmlAfterField='<small class="text--small">'.Labels::getLabel('LBL_e.g.', $this->siteLangId).': '.implode(', ', ValidateElement::PHONE_FORMATS).'</small>';
        $phnFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $this->siteLangId));

        $frm->addHiddenField('', 'ua_id');
        $fldCancel = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $siteLangId));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $siteLangId));
        $fldCancel->attachField($fldSubmit);
        return $frm;
    }

    protected function getProductSearchForm($addKeywordRelvancy = false)
    {
        $sortByArr = array(
            'price_asc' => Labels::getLabel('LBL_Price_(Low_to_High)', $this->siteLangId),
            'price_desc' => Labels::getLabel('LBL_Price_(High_to_Low)', $this->siteLangId),
            'popularity_desc' => Labels::getLabel('LBL_Sort_by_Popularity', $this->siteLangId),
            'discounted' => Labels::getLabel('LBL_Most_discounted', $this->siteLangId),
        );

        if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
            $sortByArr['rating_desc'] = Labels::getLabel('LBL_Sort_by_Rating', $this->siteLangId);
        }

        $sortBy = 'popularity_desc';
        if ($addKeywordRelvancy) {
            $sortByArr = array('keyword_relevancy' => Labels::getLabel('LBL_Keyword_Relevancy', $this->siteLangId)) + $sortByArr;
            $sortBy = 'keyword_relevancy';
        }

        $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
        //$pageSize = 10;
        $itemsTxt = Labels::getLabel('LBL_Items', $this->siteLangId);

        //$pageSizeArr[$pageSize] = $pageSize.' '.$itemsTxt;
        $pageSizeArr[$pageSize] = Labels::getLabel('LBL_Default', $this->siteLangId);
        $pageSizeArr[12] = 12 . ' '.$itemsTxt;
        $pageSizeArr[24] = 24 . ' '.$itemsTxt;
        $pageSizeArr[48] = 48 . ' '.$itemsTxt;
        $frm = new Form('frmProductSearch');
        $frm->addTextBox('', 'keyword', '', array('id'=>'keyword'));
        $frm->addSelectBox('', 'sortBy', $sortByArr, $sortBy, array('id'=>'sortBy'), '');
        $frm->addSelectBox('', 'pageSize', $pageSizeArr, $pageSize, array('id'=>'pageSize'), '');
        $frm->addHiddenField('', 'page', 1);
        $frm->addHiddenField('', 'sortOrder', 'asc');
        $frm->addHiddenField('', 'category', 0);
        $frm->addHiddenField('', 'shop_id', 0);
        $frm->addHiddenField('', 'brand_id', 0);
        $frm->addHiddenField('', 'collection_id', 0);
        $frm->addHiddenField('', 'join_price', 0);
        $frm->addHiddenField('', 'featured', 0);
        $frm->addHiddenField('', 'top_products', 0);
        $frm->addHiddenField('', 'currency_id', $this->siteCurrencyId);
        $frm->addSubmitButton('', 'btnProductSrchSubmit', '');
        return $frm;
    }

    public function fatActionCatchAll($action)
    {
        $this->_template->render(false, false, 'error-pages/404.php');
    }

    public function setupPoll()
    {
        $siteLangId = CommonHelper::getLangId();
        $pollId = FatApp::getPostedData('pollfeedback_polling_id', FatUtility::VAR_INT, 0);
        if ($pollId <= 0) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_Request', $siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = Common::getPollForm($pollId, $siteLangId);
        if (!$post = $frm->getFormDataFromArray(FatApp::getPostedData())) {
            Message::addErrorMessage($frm->getValidationErrors());
            FatUtility::dieWithError(Message::getHtml());
        }
        $pollFeedback = new PollFeedback();
        if ($pollFeedback->isPollAnsweredFromIP($pollId, $_SERVER['REMOTE_ADDR'])) {
            Message::addErrorMessage(Labels::getLabel('Msg_Poll_already_posted_from_this_IP', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post['pollfeedback_response_ip'] = $_SERVER['REMOTE_ADDR'];
        $post['pollfeedback_added_on'] = date('Y-m-d H:i:s');

        $pollFeedback->assignValues($post);
        if (!$pollFeedback->save()) {
            Message::addErrorMessage($pollFeedback->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('Msg_Poll_Feedback_Sent_Successfully', $siteLangId));
    }

    protected function getChangeEmailForm($passwordField = true)
    {
        $frm = new Form('changeEmailFrm');
        $newEmail = $frm->addEmailField(
            Labels::getLabel('LBL_NEW_EMAIL', $this->siteLangId),
            'new_email'
        );
        $newEmail->requirements()->setRequired();

        $conNewEmail = $frm->addEmailField(
            Labels::getLabel('LBL_CONFIRM_NEW_EMAIL', $this->siteLangId),
            'conf_new_email'
        );
        $conNewEmailReq = $conNewEmail->requirements();
        $conNewEmailReq->setRequired();
        $conNewEmailReq->setCompareWith('new_email', 'eq');

        if ($passwordField) {
            $curPwd = $frm->addPasswordField(Labels::getLabel('LBL_CURRENT_PASSWORD', $this->siteLangId), 'current_password');
            $curPwd->requirements()->setRequired();
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $this->siteLangId));
        return $frm;
    }

    protected function userEmailVerifications($userObj, $data, $configureEmail = false)
    {
        if (!$configureEmail) {
            $verificationCode = $userObj->prepareUserVerificationCode($data['user_new_email']);
        } else {
            $verificationCode = $userObj->prepareUserVerificationCode($data['user_email']);
        }

        $link = CommonHelper::generateFullUrl('GuestUser', 'changeEmailVerification', array('verify'=>$verificationCode));

        $email = new EmailHandler();
        $dataArr = array(
        'user_name' => $data['user_name'],
        'link' => $link,
        'user_new_email' => $data['user_email'],
        );

        if (!$configureEmail) {
            $dataArr = array(
            'user_name' => $data['user_name'],
            'link' => $link,
            'user_new_email' => $data['user_new_email'],
            'user_email' => $data['user_email'],
            );
            if (!$email->sendChangeEmailRequestNotification($this->siteLangId, array('user_name' => $dataArr['user_name'],'user_email' => $dataArr['user_email'],'user_new_email' => $dataArr['user_new_email']))) {
                return false;
            }
        }

        if (!$email->sendEmailVerificationLink($this->siteLangId, $dataArr)) {
            return false;
        }
        return true;
    }

    public function includeDateTimeFiles()
    {
        $this->_template->addCss(array('css/jquery-ui-timepicker-addon.css'), false);
        $this->_template->addJs(array('js/jquery-ui-timepicker-addon.js'), false);
    }

    public function includeProductPageJsCss()
    {
        $this->_template->addJs('js/masonry.pkgd.js');
        $this->_template->addJs('js/product-search.js');
        $this->_template->addJs('js/ion.rangeSlider.js');
        $this->_template->addJs('js/listing-functions.js');
        $this->_template->addCss('css/ion.rangeSlider.css');
        $this->_template->addCss('css/ion.rangeSlider.skinHTML5.css');
    }

    public function getAppTempUserId()
    {
        if (array_key_exists('temp_user_id', $this->app_user) && !empty($this->app_user["temp_user_id"])) {
            return $this->app_user["temp_user_id"];
        }

        if ($this->appToken && UserAuthentication::isUserLogged('', $this->appToken)) {
            $userId = UserAuthentication::getLoggedUserId();
            if ($userId > 0) {
                return $userId;
            }
        }

        $generatedTempId = substr(md5(rand(1, 99999) . microtime()), 0, UserAuthentication::TOKEN_LENGTH);
        return $this->app_user['temp_user_id'] = $generatedTempId;
    }

    public function tempTokenLogin()
    {
        $forTempTokenBasedGetActions = array('downloadDigitalFile');
        if (!in_array($this->action, $forTempTokenBasedGetActions)) {
            return;
        }

        $get = FatApp::getQueryStringData();
        if (empty($get) || !array_key_exists('ttk', $get)) {
            return;
        }
      
        $ttk = ($get['ttk']!='') ? $get['ttk'] : '';

        if (strlen($ttk) != UserAuthentication::TOKEN_LENGTH) {
            FatUtility::dieJSONError(Labels::getLabel('LBL_Invalid_Temp_Token', CommonHelper::getLangId()));
        }

        $userId = 0;
        if (!empty($get) && array_key_exists('user_id', $get)) {
            $userId = FatUtility::int($get['user_id']);
        }

        $uObj = new User($userId);
        if (!$user_temp_token_data = $uObj->validateAPITempToken($ttk)) {
            FatUtility::dieJSONError(Labels::getLabel('LBL_Invalid_Token_Data', CommonHelper::getLangId()));
        }

        if (!$user = $uObj->getUserInfo(array('credential_username','credential_password','user_id'), true, true)) {
            FatUtility::dieJSONError(Labels::getLabel('LBL_Invalid_Request', CommonHelper::getLangId()));
        }

        $authentication = new UserAuthentication();
        if ($authentication->login($user['credential_username'], $user['credential_password'], $_SERVER['REMOTE_ADDR'], false)) {
            $uObj->deleteUserAPITempToken() ;
        }
    }
}
