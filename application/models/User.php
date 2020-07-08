<?php
class User extends MyAppModel
{
    const ADMIN_SESSION_ELEMENT_NAME = 'yokartAdmin';
    const DB_TBL = 'tbl_users';
    const DB_TBL_PREFIX = 'user_';

    const DB_TBL_CRED = 'tbl_user_credentials';
    const DB_TBL_CRED_PREFIX = 'credential_';

    const DB_TBL_USER_EMAIL_VER = 'tbl_user_email_verification';
    const DB_TBL_UEMV_PREFIX = 'uev_';

    const DB_TBL_USR_SUPP_REQ = 'tbl_user_supplier_requests';
    const DB_TBL_USR_SUPP_REQ_PREFIX = 'usuprequest_';

    const DB_TBL_USR_BANK_INFO = 'tbl_user_bank_details';
    const DB_TBL_USR_BANK_INFO_PREFIX = 'ub_';

    const DB_TBL_USR_RETURN_ADDR = 'tbl_user_return_address';
    const DB_TBL_USR_RETURN_ADDR_PREFIX = 'ura_';

    const DB_TBL_USR_RETURN_ADDR_LANG = 'tbl_user_return_address_lang';
    const DB_TBL_USR_RETURN_ADDR_LANG_PREFIX = 'uralang_';

    const DB_TBL_USR_CATALOG_REQ = 'tbl_seller_catalog_requests';
    const DB_TBL_USR_CATALOG_REQ_PREFIX = 'scatrequest_';

    const DB_TBL_USR_CATALOG_REQ_MSG = 'tbl_catalog_request_messages';
    const DB_TBL_USR_CATALOG_REQ_ERR_PREFIX = 'scatrequestERR_';

    const DB_TBL_USR_WITHDRAWAL_REQ = 'tbl_user_withdrawal_requests';
    const DB_TBL_USR_WITHDRAWAL_REQ_PREFIX = 'withdrawal_';

    const DB_TBL_USR_EXTRAS = 'tbl_user_extras';
    const DB_TBL_USR_EXTRAS_PREFIX = 'uextra_';

    const DB_TBL_USR_MOBILE_TEMP_TOKEN = 'tbl_user_temp_token_requests';
    const DB_TBL_USR_MOBILE_TEMP_TOKEN_PREFIX = 'uttr_';

    const USER_FIELD_TYPE_TEXT = 1;
    const USER_FIELD_TYPE_TEXTAREA = 2;
    const USER_FIELD_TYPE_FILE = 3;
    const USER_FIELD_TYPE_DATE = 4;
    const USER_FIELD_TYPE_DATETIME = 5;
    const USER_FIELD_TYPE_TIME = 6;
    const USER_FIELD_TYPE_PHONE = 7;

    const SUPPLIER_REQUEST_PENDING = 0;
    const SUPPLIER_REQUEST_APPROVED = 1;
    const SUPPLIER_REQUEST_CANCELLED = 2;

    const USER_BUYER_DASHBOARD = 1;
    const USER_SELLER_DASHBOARD = 2;
    const USER_AFFILIATE_DASHBOARD = 3;
    const USER_ADVERTISER_DASHBOARD = 4;

    const USER_TYPE_BUYER = 1;
    const USER_TYPE_SELLER = 2;
    const USER_TYPE_AFFILIATE = 3;
    const USER_TYPE_ADVERTISER = 4;
    const USER_TYPE_SHIPPING_COMPANY = 5;
    const USER_TYPE_BUYER_SELLER = 6;

    const CATALOG_REQUEST_PENDING = 0;
    const CATALOG_REQUEST_APPROVED = 1;
    const CATALOG_REQUEST_CANCELLED = 2;

    const AFFILIATE_PAYMENT_METHOD_CHEQUE = 1;
    const AFFILIATE_PAYMENT_METHOD_BANK = 2;
    const AFFILIATE_PAYMENT_METHOD_PAYPAL = 3;

    const RETURN_ADDRESS_ACCOUNT_TAB = 'return-address';
    const RETURN_ADDRESS_TAB_1 =1;

    const CLASS_PENDING = 'warning';
    const CLASS_APPROVED = 'success';
    const CLASS_CANCELLED = 'danger';

    public function __construct($userId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $userId);
        $this->objMainTableRecord->setSensitiveFields(
            array(
            'user_regdate','user_id'
            )
        );
    }

    public static function getUserTypesArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::USER_TYPE_BUYER    =>    Labels::getLabel('LBL_Buyer', $langId),
        static::USER_TYPE_SELLER    =>    Labels::getLabel('LBL_Seller', $langId),
        static::USER_TYPE_ADVERTISER    =>    Labels::getLabel('LBL_Advertiser', $langId),
        static::USER_TYPE_AFFILIATE    =>    Labels::getLabel('LBL_Affiliate', $langId)
        );
    }

    public static function getAffiliatePaymentMethodArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::AFFILIATE_PAYMENT_METHOD_CHEQUE    =>    Labels::getLabel('LBL_Cheque', $langId),
        static::AFFILIATE_PAYMENT_METHOD_BANK    =>    Labels::getLabel('LBL_Bank', $langId),
        static::AFFILIATE_PAYMENT_METHOD_PAYPAL    =>    Labels::getLabel('LBL_PayPal', $langId),
        );
    }

    public static function getSearchObject($joinUserCredentials = false, $skipDeleted = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'u');
        if ($skipDeleted == true) {
            $srch->addCondition('user_deleted', '=', applicationConstants::NO);
        }

        if ($joinUserCredentials) {
            $srch->joinTable(static::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.'.static::DB_TBL_CRED_PREFIX.'user_id = u.user_id', 'uc');
        }
        return $srch;
    }

    public function getMainTableRecordId()
    {
        return $this->mainTableRecordId;
    }

    public static function isSeller()
    {
        return (1 == UserAuthentication::getLoggedUserAttribute('user_is_supplier'));
    }

    public static function isBuyer()
    {
        return (1 == UserAuthentication::getLoggedUserAttribute('user_is_buyer'));
    }

    public static function isAdvertiser()
    {
        return (1 == UserAuthentication::getLoggedUserAttribute('user_is_advertiser'));
    }

    public static function isAffiliate()
    {
        return (1 == UserAuthentication::getLoggedUserAttribute('user_is_affiliate'));
    }

    public static function isSigningUpForSeller()
    {
        return (static::USER_TYPE_SELLER == UserAuthentication::getLoggedUserAttribute('user_registered_initially_for'));
    }

    public static function isSigningUpBuyer()
    {
        return (static::USER_TYPE_BUYER == UserAuthentication::getLoggedUserAttribute('user_registered_initially_for'));
    }

    public static function isSigningUpAdvertiser()
    {
        return (static::USER_TYPE_ADVERTISER == UserAuthentication::getLoggedUserAttribute('user_registered_initially_for'));
    }

    public static function isSigningUpAffiliate()
    {
        return (static::USER_TYPE_AFFILIATE == UserAuthentication::getLoggedUserAttribute('user_registered_initially_for'));
    }

    public static function canAccessSupplierDashboard()
    {
        /* if(!FatApp::getConfig('CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION')){
        return true;
        }

        if(FatApp::getConfig('CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION')){
        if(static::isSeller()){
        return true;
        }
        } */
        if (static::isSeller()) {
            return true;
        }
        return false;
    }

    public static function isRequestedForSeller($userId)
    {
        $userId = FatUtility::int($userId);
        $userObj = new User($userId);
        $srch = $userObj->getUserSupplierRequestsObj();
        $srch->addFld(array('usuprequest_attempts','usuprequest_id'));
        $rs = $srch->getResultSet();
        $supplierRequest = FatApp::getDb()->fetch($rs);
        if ($supplierRequest) {
            return true;
        }
        return false;
    }

    public static function canViewSupplierTab()
    {
        if (self::isSeller()) {
            return true;
        }

        if (self::isAdvertiser()) {
            return false;
        }

        if (self::isAffiliate()) {
            return false;
        }

        if (!FatApp::getConfig('CONF_ACTIVATE_SEPARATE_SIGNUP_FORM', FatUtility::VAR_INT, 1)) {
            return true;
        }

        if (FatApp::getConfig('CONF_ACTIVATE_SEPARATE_SIGNUP_FORM', FatUtility::VAR_INT, 1)) {
            if (FatApp::getConfig('CONF_BUYER_CAN_SEE_SELLER_TAB', FatUtility::VAR_INT, 0) && self::isBuyer()) {
                return true;
            }
        }
        if (FatApp::getConfig('CONF_ACTIVATE_SEPARATE_SIGNUP_FORM', FatUtility::VAR_INT, 1)) {
            if (!self::isBuyer()) {
                return true;
            }
        }

        if (static::isRequestedForSeller(UserAuthentication::getLoggedUserId())) {
            return true;
        }
        return false;
    }

    public static function canViewBuyerTab()
    {
        if (self::isBuyer()) {
            return true;
        }

        if (!FatApp::getConfig('CONF_ACTIVATE_SEPARATE_SIGNUP_FORM', FatUtility::VAR_INT, 1) && self::isBuyer()) {
            return true;
        }

        return false;
    }

    public static function canViewAdvertiserTab()
    {
        if (self::isAdvertiser()) {
            return true;
        }
        return false;
    }

    public static function canViewAffiliateTab()
    {
        if (self::isAffiliate()) {
            return true;
        }

        return false;
    }

    public static function canAddCustomProduct()
    {
        return (1 == FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0));
    }

    public static function canRequestProduct()
    {
        /* return (1 == FatApp::getConfig('CONF_SELLER_CAN_REQUEST_PRODUCT', FatUtility::VAR_INT, 0)); */
        return false;
    }

    public static function canAddCustomProductAvailableToAllSellers()
    {
        return (1 == FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0));
    }

    public static function getFieldTypes($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('ERR_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr=array(
        static::USER_FIELD_TYPE_TEXT => Labels::getLabel('LBL_Textbox', $langId),
        static::USER_FIELD_TYPE_TEXTAREA => Labels::getLabel('LBL_Textarea', $langId),
        static::USER_FIELD_TYPE_FILE => Labels::getLabel('LBL_File', $langId),
        static::USER_FIELD_TYPE_DATE => Labels::getLabel('LBL_Date', $langId),
        static::USER_FIELD_TYPE_DATETIME => Labels::getLabel('LBL_Datetime', $langId),
        static::USER_FIELD_TYPE_TIME => Labels::getLabel('LBL_Time', $langId),
        static::USER_FIELD_TYPE_PHONE => Labels::getLabel('LBL_Phone', $langId),
        );
        return $arr;
    }

    public static function getUserDashboard($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('ERR_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr=array(
        static::USER_BUYER_DASHBOARD => Labels::getLabel('LBL_Buyer', $langId),
        static::USER_SELLER_DASHBOARD => Labels::getLabel('LBL_Seller', $langId),
        static::USER_ADVERTISER_DASHBOARD => Labels::getLabel('LBL_Advertiser', $langId),
        static::USER_AFFILIATE_DASHBOARD => Labels::getLabel('LBL_Affiliate', $langId),
        );
        return $arr;
    }

    public static function getPreferedDashbordRedirectUrl($preferredDashboard)
    {
        switch ($preferredDashboard) {
            case User::USER_BUYER_DASHBOARD:
                return CommonHelper::generateFullUrl('buyer');
             break;
            case User::USER_SELLER_DASHBOARD:
                return CommonHelper::generateFullUrl('seller');
             break;
            case User::USER_ADVERTISER_DASHBOARD:
                return CommonHelper::generateFullUrl('advertiser');
             break;
            case User::USER_AFFILIATE_DASHBOARD:
                return CommonHelper::generateFullUrl('affiliate');
             break;
        }
        return CommonHelper::generateFullUrl('account');
    }

    public static function getSupplierReqStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('ERR_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr=array(
        static::SUPPLIER_REQUEST_PENDING => Labels::getLabel('LBL_Pending', $langId),
        static::SUPPLIER_REQUEST_APPROVED => Labels::getLabel('LBL_Approved', $langId),
        static::SUPPLIER_REQUEST_CANCELLED => Labels::getLabel('LBL_Cancelled', $langId)
        );
        return $arr;
    }

    public static function getCatalogRequestClassArr()
    {
        return array(
        static::CATALOG_REQUEST_PENDING        =>    static::CLASS_PENDING,
        static::CATALOG_REQUEST_APPROVED    =>    static::CLASS_APPROVED,
        static::CATALOG_REQUEST_CANCELLED    =>    static::CLASS_CANCELLED,
        );
    }

    public static function getCatalogReqStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('ERR_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr=array(
        static::CATALOG_REQUEST_PENDING => Labels::getLabel('LBL_Pending', $langId),
        static::CATALOG_REQUEST_APPROVED => Labels::getLabel('LBL_Approved', $langId),
        static::CATALOG_REQUEST_CANCELLED => Labels::getLabel('LBL_Cancelled', $langId)
        );
        return $arr;
    }

    public function getUserSearchObj($attr = null, $joinUserCredentials = true, $skipDeleted = true)
    {
        $srch = static::getSearchObject($joinUserCredentials, $skipDeleted);

        if ($this->mainTableRecordId>0) {
            $srch->addCondition('u.'.static::DB_TBL_PREFIX.'id', '=', $this->mainTableRecordId);
        }

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addField($attr);
            }
        } else {
            $srch->addMultipleFields(
                array(
                'u.'.static::DB_TBL_PREFIX.'id',
                'u.'.static::DB_TBL_PREFIX.'name',
                'u.'.static::DB_TBL_PREFIX.'phone',
                'u.'.static::DB_TBL_PREFIX.'profile_info',
                'u.'.static::DB_TBL_PREFIX.'regdate',
                'u.'.static::DB_TBL_PREFIX.'preferred_dashboard',
                'u.'.static::DB_TBL_PREFIX.'registered_initially_for',
                'uc.'.static::DB_TBL_CRED_PREFIX.'username',
                'uc.'.static::DB_TBL_CRED_PREFIX.'email',
                'uc.'.static::DB_TBL_CRED_PREFIX.'active',
                'uc.'.static::DB_TBL_CRED_PREFIX.'verified'
                )
            );
        }
        return $srch;
    }

    public function getUserInfo($attr = null, $isActive = true, $isVerified = true, $joinUserCredentials = false)
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $srch = $this->getUserSearchObj($attr);
        if ($isActive) {
            $srch->addCondition('uc.'.static::DB_TBL_CRED_PREFIX.'active', '=', 1);
        }

        if ($isVerified) {
            $srch->addCondition('uc.'.static::DB_TBL_CRED_PREFIX.'verified', '=', 1);
        }

        if ($joinUserCredentials) {
            $srch->joinTable(static::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.'.static::DB_TBL_CRED_PREFIX.'user_id = u.user_id', 'uc');
        }

        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);

        if (!empty($record)) {
            // if (!empty($record['credential_password'])) {
            //     unset($record['credential_password']);
            // }
            return $record;
        }
        return false;
    }

    public function getUserSupplierRequestsObj($requestId = 0)
    {
        $requestId = FatUtility::int($requestId);

        $srch = new SearchBase(static::DB_TBL_USR_SUPP_REQ, 'tusr');
        $srch->joinTable(
            static::DB_TBL,
            'INNER JOIN',
            'tusr.'.static::DB_TBL_USR_SUPP_REQ_PREFIX.'user_id = u.'.static::DB_TBL_PREFIX.'id',
            'u'
        );
        $srch->joinTable(static::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.'.static::DB_TBL_CRED_PREFIX.'user_id = u.user_id', 'uc');
        /* $srch = $this->getUserSearchObj();
        $srch->joinTable(static::DB_TBL_USR_SUPP_REQ,'INNER JOIN',
        'tusr.'.static::DB_TBL_USR_SUPP_REQ_PREFIX.'user_id = u.'.static::DB_TBL_PREFIX.'id','tusr'); */

        $srch->addCondition('uc.'.static::DB_TBL_CRED_PREFIX.'active', '=', 1);

        if ($this->mainTableRecordId>0) {
            $srch->addCondition('u.'.static::DB_TBL_PREFIX.'id', '=', $this->mainTableRecordId);
        }

        if ($requestId > 0) {
            $srch->addCondition('tusr.'.static::DB_TBL_USR_SUPP_REQ_PREFIX.'id', '=', $requestId);
        }

        $srch->addMultipleFields(
            array(
            'u.'.static::DB_TBL_PREFIX.'id',
            'u.'.static::DB_TBL_PREFIX.'name',
            'uc.'.static::DB_TBL_CRED_PREFIX.'username',
            'uc.'.static::DB_TBL_CRED_PREFIX.'email',
                            )
        );
        return $srch;
    }

    public function getSupplierFormFields($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId <= 0) {
            trigger_error("Lang id not passed", E_USER_ERROR);
        }
        $srch = SupplierFormFields::getSearchObject();

        $srch->joinTable(
            SupplierFormFields::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'sf_l.sformfieldlang_sformfield_id = sf.sformfield_id
		AND sf_l.sformfieldlang_lang_id = ' . $langId,
            'sf_l'
        );

        $srch->addOrder('sformfield_display_order');

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();

        $records = FatApp::getDb()->fetchAll($rs, 'sformfield_id');
        if (!empty($records)) {
            return $records;
        }

        return array();
    }

    public function getUserBankInfo()
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_USR_BANK_INFO, 'tub');
        $srch->addCondition(static::DB_TBL_USR_BANK_INFO_PREFIX.'user_id', '=', $this->mainTableRecordId);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    public function updateBankInfo($data = array())
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $assignValues = array(
        'ub_user_id'=>$this->mainTableRecordId,
        'ub_bank_name'=>$data['ub_bank_name'],
        'ub_account_holder_name'=>$data['ub_account_holder_name'],
        'ub_account_number'=>$data['ub_account_number'],
        'ub_ifsc_swift_code'=>$data['ub_ifsc_swift_code'],
        'ub_bank_address'=>$data['ub_bank_address']
        );
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_USR_BANK_INFO, $assignValues, false, array(), $assignValues)) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
    }

    public function deleteBankInfo()
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        if (!FatApp::getDb()->deleteRecords(static::DB_TBL_USR_BANK_INFO, array('smt' => 'ub_user_id = ?', 'vals' => array($this->mainTableRecordId)))) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function updateSettingsInfo($data = array())
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $assignValues = array(
        'user_id'=>$this->mainTableRecordId,
        'user_autorenew_subscription'=>$data['user_autorenew_subscription'],

        );
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL, $assignValues, false, array(), $assignValues)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }
    public function updateInfo($data = array(), $userId)
    {
        $assignValues = array(
        'user_company'=>$data['user_company'],
        'user_profile_info'=>$data['user_profile_info'],
        'user_products_services'=>$data['user_products_services'],
        );
        if (!FatApp::getDb()->updateFromArray(static::DB_TBL, $assignValues, array('smt' => static::DB_TBL_PREFIX . 'id = ? ', 'vals' => array((int)$userId)))) {
            $this->error = FatApp::getDb()->getError();
            echo $this->error;
            die;
        }
        return true;
    }

    public function truncateUserInfo()
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        /* Delete User Addresses [ */
        $userAddress = new UserAddress();
        if (!$userAddress->deleteUserAddresses($this->mainTableRecordId)) {
            $db->rollbackTransaction();
            $this->error = $userAddress->getError();
            return false;
        }
        /* ] */

        /* Update User information [ */
        $data = array(
        'user_name'=>'',
        'user_phone'=>'',
        'user_dob'=>'',
        'user_city'=>'',
        'user_country_id'=>'',
        'user_state_id'=>'',
        'user_company'=>'',
        'user_profile_info'=>'',
        'user_address1'=>'',
        'user_address2'=>'',
        'user_zip'=>'',
        'user_products_services'=>'',
        );

        if (!$db->updateFromArray(static::DB_TBL, $data, array('smt' => static::DB_TBL_PREFIX . 'id = ? ', 'vals' => array($this->mainTableRecordId)))) {
            $this->error = $db->getError();
            return false;
        }
        /* ] */

        /* Delete User's Profile Image [ */
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $this->mainTableRecordId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE, $this->mainTableRecordId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        /* Delete Bank Info [ */
        if (!$this->deleteBankInfo()) {
            $db->rollbackTransaction();
            $this->error = $db->getError();
            return false;
        }
        /* ] */

        /* Delete Seller's Return Address [ */
        $srch = $this->getUserSearchObj(array('user_is_supplier','user_registered_initially_for'));
        $rs = $srch->getResultSet();

        $userData = $db->fetch($rs, 'user_id');

        if ($userData['user_is_supplier'] || $userData['user_registered_initially_for']) {
            if (!$this->deleteUserReturnAddress()) {
                $db->rollbackTransaction();
                $this->error = $db->getError();
                return false;
            }
        }
        /* ] */

        /* Update Order User Address [ */
        $order = new Orders();
        if (!$order->updateOrderUserAddress($this->mainTableRecordId)) {
            $db->rollbackTransaction();
            $this->error = $order->getError();
            return false;
        }
        /* ] */

        /* Deactivate Account [ */
        $this->assignValues(array('user_deleted'=>applicationConstants::YES));
        if (!$this->save()) {
            $db->rollbackTransaction();
            $this->error = $db->getError();
            return false;
        }
        /* ] */

        $db->commitTransaction();
        return true;
    }

    public function updateCredInfo($data = array(), $userId)
    {
        $assignValues = array(
        static::DB_TBL_CRED_PREFIX.'password' => UserAuthentication::encryptPassword($data['user_password'])
        );
        if (!FatApp::getDb()->updateFromArray(static::DB_TBL_CRED, $assignValues, array('smt' => static::DB_TBL_CRED_PREFIX . 'user_id = ? ', 'vals' => array((int)$userId)))) {
            $this->error = FatApp::getDb()->getError();
            echo $this->error;
            die;
        }
        return true;
    }

    public function getUserReturnAddress($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_USR_RETURN_ADDR, 'tura');
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'c.country_id = tura.ura_country_id', 'c');
        $srch->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 's.state_id = tura.ura_state_id', 's');

        $srch->addCondition(static::DB_TBL_USR_RETURN_ADDR_PREFIX.'user_id', '=', $this->mainTableRecordId);
        if ($langId > 0) {
            $srch->joinTable(static::DB_TBL_USR_RETURN_ADDR_LANG, 'LEFT OUTER JOIN', 'tura_l.uralang_user_id = tura.ura_user_id and tura_l.uralang_lang_id = '.$langId, 'tura_l');
            $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c_l.countrylang_country_id = tura.ura_country_id and c_l.countrylang_lang_id = '.$langId, 'c_l');
            $srch->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 's_l.statelang_state_id = tura.ura_state_id and s_l.statelang_lang_id = '.$langId, 's_l');
            $srch->addMultipleFields(array('tura_l.*','IFNULL(country_name,country_code) as country_name','IFNULL(state_name,state_identifier) as state_name'));
        }

        $srch->addMultipleFields(array('tura.*'));
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    public function updateUserReturnAddress($data = array())
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $assignValues = array(
        'ura_user_id'=>$this->mainTableRecordId,
        'ura_state_id'=>$data['ura_state_id'],
        'ura_country_id'=>$data['ura_country_id'],
        'ura_zip'=>$data['ura_zip'],
        'ura_phone'=>$data['ura_phone']
        );
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_USR_RETURN_ADDR, $assignValues, false, array(), $assignValues)) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
    }

    public function updateUserReturnAddressLang($data = array())
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $assignValues = array(
        'uralang_user_id'=>$this->mainTableRecordId,
        'uralang_lang_id'=>$data['lang_id'],
        'ura_name'=>$data['ura_name'],
        'ura_city'=>$data['ura_city'],
        'ura_address_line_1'=>$data['ura_address_line_1'],
        'ura_address_line_2'=>$data['ura_address_line_2']
        );
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_USR_RETURN_ADDR_LANG, $assignValues, false, array(), $assignValues)) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
    }

    public function deleteUserReturnAddress()
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        if (!FatApp::getDb()->deleteRecords(static::DB_TBL_USR_RETURN_ADDR, array('smt' => 'ura_user_id = ?', 'vals' => array($this->mainTableRecordId)))) {
            $this->error = $db->getError();
            return false;
        }
        if (!FatApp::getDb()->deleteRecords(static::DB_TBL_USR_RETURN_ADDR_LANG, array('smt' => 'uralang_user_id = ?', 'vals' => array($this->mainTableRecordId)))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function getSupplierRequestFieldsValueArr($requestId, $langId)
    {
        $requestId = FatUtility::int($requestId);
        if (1 > $requestId) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST', $langId);
            return false;
        }
        $srch = new SearchBase(static::DB_TBL_USR_SUPP_REQ, 'tusr');
        $srch->joinTable('tbl_user_supplier_request_values', 'INNER JOIN', 'tusr.usuprequest_id = tusrv.sfreqvalue_request_id', 'tusrv');
        $srch->joinTable('tbl_user_supplier_request_values_lang', 'LEFT OUTER JOIN', 'tusrv.sfreqvalue_id = tusrv_lang.sfreqvaluelang_sfreqvalue_id AND tusrv_lang.sfreqvaluelang_lang_id = '.$langId, 'tusrv_lang');
        $srch->joinTable('tbl_user_supplier_form_fields', 'LEFT OUTER JOIN', 'tusrv.sfreqvalue_formfield_id=tusff.sformfield_id', 'tusff');
        $srch->joinTable(
            'tbl_user_supplier_form_fields_lang',
            'LEFT OUTER JOIN',
            'tusff_l.sformfieldlang_sformfield_id=tusff.sformfield_id and tusff_l.sformfieldlang_lang_id = '.$langId,
            'tusff_l'
        );
        $srch->joinTable(
            'tbl_attached_files',
            'LEFT OUTER JOIN',
            'af.afile_type ='.AttachedFile::FILETYPE_SELLER_APPROVAL_FILE.' and
			af.afile_record_id = tusr.usuprequest_user_id and af.afile_record_subid = tusrv.sfreqvalue_formfield_id',
            'af'
        );
        $srch->addCondition('tusrv.sfreqvalue_request_id', '=', $requestId);
        $srch->addMultipleFields(
            array('tusrv.*','tusff_l.sformfield_caption','tusff.*','af.afile_id','afile_physical_path','afile_name', 'IFNULL(tusrv_lang.sfreqvalue_sformfield_caption, tusff_l.sformfield_caption) as sformfield_caption')
        );
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public function addSupplierRequestData($data, $langId)
    {
        $user_id = FatUtility::int($data['user_id']);
        unset($data['user_id']);
        if (($user_id < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST', $langId);
            return false;
        }
        $db = FatApp::getDb();

        $record = new TableRecord(static::DB_TBL_USR_SUPP_REQ);

        $assign_fields = array();
        $assign_fields['usuprequest_user_id'] = $user_id;
        $assign_fields['usuprequest_reference'] = $data["reference"];
        $assign_fields['usuprequest_date'] = date('Y-m-d H:i:s');
        $assign_fields['usuprequest_attempts'] = 1;
        /*
        if(FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM",FatUtility::VAR_INT,1)){
        $status = 1;
        }else{
        $status = 0;
        }

        if(FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION",FatUtility::VAR_INT,1)){
        $status = 0;
        }
        */
        $status = 0;
        if (!FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1)) {
            $status = 1;
        }

        $assign_fields['usuprequest_status'] = $status;

        $record->assignValues($assign_fields, false, '', '', true);

        $record->setFldValue('usuprequest_attempts', 1, true);
        $onDuplicateKeyUpdate=array(
        'usuprequest_status'=>(FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1)) ? 0 : 1,
        'usuprequest_attempts'=>'mysql_func_usuprequest_attempts+1'
        );
        if (!$record->addNew(array(), $onDuplicateKeyUpdate)) {
            $this->error = $record->getError();
            return false;
        }

        $supplier_request_id = $record->getId();
        if ($supplier_request_id == 0) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST', $langId);
            return false;
        }

        /* user update user_is_supplier */
        $userObj = new User($user_id);
        $userObj->activateSupplier(applicationConstants::ACTIVE, $status);

        if (!$db->deleteRecords('tbl_user_supplier_request_values', array('smt' => 'sfreqvalue_request_id = ?', 'vals' => array($supplier_request_id)))) {
            $this->error = $db->getError();
            return false;
        }

        $record = new TableRecord('tbl_user_supplier_request_values');
        if (empty($data['fieldIdsArr'])) {
            return false;
        }

        /* [ */
        $langs = Language::getAllNames();
        $sformFieldCaptionsArr = array();
        if ($langs) {
            foreach ($langs as $language_id => $langName) {
                $sformFieldCaptionsArr[$language_id] = $this->getSupplierFormFields($language_id);
            }
        }
        /* ] */

        foreach ($data['fieldIdsArr'] as $key => $fieldId) {
            if (isset($data['sformfield_'.$fieldId]) && $data['sformfield_'.$fieldId]!='') {
                $arr = array(
                'sfreqvalue_request_id' => (int)$supplier_request_id,
                'sfreqvalue_formfield_id' => (int)$fieldId,
                'sfreqvalue_text' => $data['sformfield_'.$fieldId],
                );
                $record->assignValues($arr);
                if (!$record->addNew()) {
                    $this->error = $record->getError();
                    return false;
                }
                $sfreqvalue_id = $record->getId();

                /* [ */
                if ($langs) {
                    foreach ($langs as $language_id => $langName) {
                        $langData = array(
                        'sfreqvaluelang_sfreqvalue_id' => $sfreqvalue_id,
                        'sfreqvaluelang_lang_id'    =>    $language_id,
                        'sfreqvalue_sformfield_caption'    =>    $sformFieldCaptionsArr[$language_id][$fieldId]['sformfield_caption']
                        );
                        $db->insertFromArray('tbl_user_supplier_request_values_lang', $langData);
                        /* foreach( $sformFieldCaptionsArr[$language_id] as $data ){
                        $langData = array(
                        'sfreqvaluelang_sfreqvalue_id' => $sfreqvalue_id,
                        'sfreqvaluelang_lang_id'=>$language_id,
                        'sfreqvalue_sformfield_caption'=>$data['sformfield_caption']
                        );
                        $db->insertFromArray( 'tbl_user_supplier_request_values_lang', $langData );
                        } */
                    }
                }
                /* ] */
            }
        }


        /* [ */

        /* ] */
        return $supplier_request_id;
    }

    public function updateSupplierRequest($data = array())
    {
        if (empty($data)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST', $this->commonLangId);
            return false;
        }

        $srequest_id = FatUtility::int($data['request_id']);

        $assignValues = array(
        'usuprequest_status'=>$data['status'],
        'usuprequest_comments'=>isset($data['comments'])?$data['comments']:'',
        );
        if (!FatApp::getDb()->updateFromArray(
            static::DB_TBL_USR_SUPP_REQ,
            $assignValues,
            array('smt' => 'usuprequest_id = ? ', 'vals' => array((int)$srequest_id))
        )) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
    }

    public function getUserCatalogRequestsObj($requestId = 0)
    {
        $requestId = FatUtility::int($requestId);

        $srch = new SearchBase(static::DB_TBL_USR_CATALOG_REQ, 'tucr');
        $srch->joinTable(
            static::DB_TBL,
            'INNER JOIN',
            'tucr.'.static::DB_TBL_USR_CATALOG_REQ_PREFIX.'user_id = u.'.static::DB_TBL_PREFIX.'id',
            'u'
        );
        $srch->joinTable(static::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.'.static::DB_TBL_CRED_PREFIX.'user_id = u.user_id', 'uc');

        $srch->addCondition('uc.'.static::DB_TBL_CRED_PREFIX.'active', '=', 1);

        if ($this->mainTableRecordId>0) {
            $srch->addCondition('u.'.static::DB_TBL_PREFIX.'id', '=', $this->mainTableRecordId);
        }

        if ($requestId > 0) {
            $srch->addCondition('tucr.'.static::DB_TBL_USR_CATALOG_REQ_PREFIX.'id', '=', $requestId);
        }
        $srch->addCondition('tucr.'.static::DB_TBL_USR_CATALOG_REQ_PREFIX.'deleted', '=', 0);

        $srch->addMultipleFields(
            array(
            'u.'.static::DB_TBL_PREFIX.'id',
            'u.'.static::DB_TBL_PREFIX.'name',
            'uc.'.static::DB_TBL_CRED_PREFIX.'username',
            'uc.'.static::DB_TBL_CRED_PREFIX.'email',
                            )
        );
        return $srch;
    }

    public function addCatalogRequest($data = array())
    {
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_USR_CATALOG_REQ, $data)) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
    }

    public function notifyAdminCatalogRequest($data, $langId)
    {
        $data = array(
            'reference_number' => $data['scatrequest_reference'],
            'request_title' => $data['scatrequest_title'],
        'request_content' => $data['scatrequest_content'],
        );
        $email = new EmailHandler();

        if (!$email->sendNewCatalogNotification($langId, $data)) {
            Message::addMessage(Labels::getLabel("ERR_ERROR_IN_SENDING_NOTIFICATION_EMAIL_TO_ADMIN", $langId));
            return false;
        }
        return true;
    }

    public function updateCatalogRequest($data = array())
    {
        if (empty($data)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST.', $this->commonLangId);
            return false;
        }

        $scatrequest_id = FatUtility::int($data['request_id']);

        $assignValues = array(
        'scatrequest_status'=>$data['status'],
        'scatrequest_comments'=>isset($data['comments'])?$data['comments']:'',
        );
        if (!FatApp::getDb()->updateFromArray(
            static::DB_TBL_USR_CATALOG_REQ,
            $assignValues,
            array('smt' => 'scatrequest_id = ? ', 'vals' => array((int)$scatrequest_id))
        )) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
    }

    public function deleteCatalogRequest($scatrequest_id)
    {
        $scatrequest_id = FatUtility::int($scatrequest_id);

        if (1 > $scatrequest_id) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST.', $this->commonLangId);
            return false;
        }

        $assignValues = array(
        'scatrequest_deleted'=> 1
        );
        if (!FatApp::getDb()->updateFromArray(
            static::DB_TBL_USR_CATALOG_REQ,
            $assignValues,
            array('smt' => 'scatrequest_id = ? ', 'vals' => array((int)$scatrequest_id))
        )) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
    }

    public function save()
    {
        $broken = false;
        if (! ($this->mainTableRecordId > 0)) {
            $this->setFldValue('user_regdate', date('Y-m-d H:i:s'));
            $this->setFldValue('user_referral_code', uniqid());
        }
        return parent::save();
    }


    /* this function is called for newly signup/registered user, will manage the crediting of referral reward points if any upon new sign up and handle the affilaite user rewarding.*/
    public function setUpRewardEntry($referredUserId, $langId)
    {
        $referredUserId = FatUtility::int($referredUserId);
        $langId = FatUtility::int($langId);
        if ($referredUserId <=0 || $langId <= 0) {
            trigger_error("Parameters are not passed", E_USER_ERROR);
        }
        $broken = false;
        /* rewarding will work on the basis of latest cookie date, if both cookies are saved, i.e "Share&Earn Module from Buyer account" and "Affiliate Module" */
        $isAffiliateCookieSet = false;
        $isReferrerCookieSet = false;

        if (isset($_COOKIE['affiliate_referrer_code_signup']) && $_COOKIE['affiliate_referrer_code_signup'] != '') {
            $isAffiliateCookieSet = true;
        }

        if (isset($_COOKIE['referrer_code_signup']) && $_COOKIE['referrer_code_signup'] != '') {
            $isReferrerCookieSet = true;
        }

        /* prioritize only when, both cookies are set, then credit on the basis of latest cookie set. [ */
        if ($isAffiliateCookieSet && $isReferrerCookieSet) {
            $affiliateReferrerCookieArr = unserialize($_COOKIE['affiliate_referrer_code_signup']);
            $referrerCookieArr = unserialize($_COOKIE['referrer_code_signup']);
            if ($affiliateReferrerCookieArr['creation_time'] > $referrerCookieArr['creation_time']) {
                $isReferrerCookieSet = false;
            } else {
                $isAffiliateCookieSet = false;
            }
        }
        /* ] */

        if ($isReferrerCookieSet) {
            $this->setUpReferrarRewarding($referredUserId, $langId);
        }

        if ($isAffiliateCookieSet) {
            $this->setUpAffiliateRewarding($referredUserId, $langId);
        }

        /* if( $broken === false  ){
        //FatApp::getDb()->commitTransaction();
        return true;
        }

        //FatApp::getDb()->rollbackTransaction();
        return false; */
    }

    private function setUpReferrarRewarding($referredUserId, $langId)
    {
        $broken = false;
        $referredUserId = FatUtility::int($referredUserId);
        $langId = FatUtility::int($langId);
        if ($referredUserId <=0 || $langId <= 0) {
            trigger_error("Parameters are not passed", E_USER_ERROR);
        }
        /* store refferer details, if any[ */
        $isReferrerRewarded = false;
        $isReferralRewarded = false;
        $referrerUserId = 0;
        $referrerUserName = '';
        if (isset($_COOKIE['referrer_code_signup']) && !empty($_COOKIE['referrer_code_signup'])) {
            $cookieDataArr = unserialize($_COOKIE['referrer_code_signup']);
            $userReferrerCode = $cookieDataArr['data'];
            $userSrchObj = User::getSearchObject();
            $userSrchObj->doNotCalculateRecords();
            $userSrchObj->doNotLimitRecords();
            $userSrchObj->addCondition('user_referral_code', '=', $userReferrerCode);
            $userSrchObj->addMultipleFields(array('user_id', 'user_referral_code', 'user_name' ));
            $rs = $userSrchObj->getResultSet();
            $referrerUserRow = FatApp::getDb()->fetch($rs);

            if ($referrerUserRow && $referrerUserRow['user_referral_code'] == $userReferrerCode && $userReferrerCode != '' && $referrerUserRow['user_referral_code'] != '') {
                $referrerUserId = $referrerUserRow['user_id'];
                $referrerUserName = $referrerUserRow['user_name'];
                $this->setUserInfo(array( 'user_referrer_user_id' => $referrerUserId ));
            }
        }
        /* ] */

        //FatApp::getDb()->startTransaction();

        /* add Rewards points, upon signing up, referrer will get rewarded[ */
        $CONF_REGISTRATION_REFERRER_REWARD_POINTS = FatApp::getConfig("CONF_REGISTRATION_REFERRER_REWARD_POINTS", FatUtility::VAR_INT, 0);
        if (($referrerUserId > 0) && FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE") && $CONF_REGISTRATION_REFERRER_REWARD_POINTS > 0) {
            $rewardExpiryDate = '0000-00-00';
            $CONF_REGISTRATION_REFERRER_REWARD_POINTS_VALIDITY = FatApp::getConfig("CONF_REGISTRATION_REFERRER_REWARD_POINTS_VALIDITY", FatUtility::VAR_INT, 0);
            if ($CONF_REGISTRATION_REFERRER_REWARD_POINTS_VALIDITY > 0) {
                $rewardExpiryDate = date('Y-m-d', strtotime('+'. $CONF_REGISTRATION_REFERRER_REWARD_POINTS_VALIDITY .' days'));
            }

            $rewardsRecord = new UserRewards();
            $referralUserName = User::getAttributesById($referredUserId, "user_name");
            $urpComments = Labels::getLabel("LBL_Signup_Reward_Points._Your_Referral_{username}_registered.", $langId);
            $urpComments = str_replace("{username}", $referralUserName, $urpComments);
            $rewardsRecord->assignValues(
                array(
                'urp_user_id'            => $referrerUserId,
                'urp_referral_user_id'    =>    $referredUserId,
                'urp_points'    =>    $CONF_REGISTRATION_REFERRER_REWARD_POINTS,
                'urp_comments'    =>    $urpComments,
                'urp_used'        =>    0,
                'urp_date_expiry'    =>    $rewardExpiryDate
                )
            );
            if ($rewardsRecord->save()) {
                $isReferrerRewarded = true;
                $urpId = $rewardsRecord->getMainTableRecordId();
                $emailObj = new EmailHandler();
                $emailObj->sendRewardPointsNotification(CommonHelper::getLangId(), $urpId);
            } else {
                $this->error = $rewardsRecord->getError();
                $broken = true;
            }
        }
        /* ] */


        /* add Rewards points, upon signing up, referral will get rewarded[ */
        $CONF_REGISTRATION_REFERRAL_REWARD_POINTS = FatApp::getConfig("CONF_REGISTRATION_REFERRAL_REWARD_POINTS", FatUtility::VAR_INT, 0);
        if (($referrerUserId > 0) && FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE") && $CONF_REGISTRATION_REFERRAL_REWARD_POINTS > 0) {
            $CONF_REGISTRATION_REFERRAL_REWARD_POINTS_VALIDITY = FatApp::getConfig("CONF_REGISTRATION_REFERRAL_REWARD_POINTS_VALIDITY", FatUtility::VAR_INT, 0);
            $rewardReferralExpiryDate = '0000-00-00';
            if ($CONF_REGISTRATION_REFERRAL_REWARD_POINTS_VALIDITY > 0) {
                $rewardReferralExpiryDate = date('Y-m-d', strtotime('+'. $CONF_REGISTRATION_REFERRAL_REWARD_POINTS_VALIDITY .' days'));
            }

            $rewardsRecord2 = new UserRewards();
            $urpComments = Labels::getLabel("LBL_Signup_Reward_Points._Registered_through_referral_link_of_your_friend_{referrerusername}.", $langId);
            $urpComments = str_replace("{referrerusername}", $referrerUserName, $urpComments);
            $rewardsRecord2->assignValues(
                array(
                'urp_user_id'            => $referredUserId,
                'urp_referral_user_id'    =>    $referrerUserId,
                'urp_points'    =>    $CONF_REGISTRATION_REFERRAL_REWARD_POINTS,
                'urp_comments'    =>    $urpComments,
                'urp_used'        =>    0,
                'urp_date_expiry'    =>    $rewardReferralExpiryDate
                )
            );
            if ($rewardsRecord2->save()) {
                $isReferralRewarded = true;
                $urpId = $rewardsRecord2->getMainTableRecordId();
                $emailObj = new EmailHandler();
                $emailObj->sendRewardPointsNotification(CommonHelper::getLangId(), $urpId);
            } else {
                $this->error = $rewardsRecord2->getError();
                $broken = true;
            }
        }
        /* ] */

        /* remove referrer signup cookie, becoz, new user and referrer rewarded[ */
        /* if( ($isReferrerRewarded || $isReferralRewarded) && $broken === false ){ */
        /* removing cookie */
        CommonHelper::setCookie('referrer_code_signup', '', time() - 3600);
        /* } */
        /* ] */
    }


    private function setUpAffiliateRewarding($referredUserId, $langId)
    {
        $referredUserId = FatUtility::int($referredUserId);
        $langId = FatUtility::int($langId);
        if ($referredUserId <=0 || $langId <= 0) {
            trigger_error("Parameters are not passed", E_USER_ERROR);
        }

        $broken = false;
        $affiliateReferrerUserId = 0;

        /* binding user to its referrer affiliate user[ */
        if (isset($_COOKIE['affiliate_referrer_code_signup']) && $_COOKIE['affiliate_referrer_code_signup'] != '') {
            $cookieDataArr = unserialize($_COOKIE['affiliate_referrer_code_signup']);
            $affiliateReferrerCode = $cookieDataArr['data'];

            $userSrchObj = User::getSearchObject();
            $userSrchObj->doNotCalculateRecords();
            $userSrchObj->doNotLimitRecords();
            $userSrchObj->addCondition('user_referral_code', '=', $affiliateReferrerCode);
            $userSrchObj->addMultipleFields(array('user_id', 'user_referral_code', 'user_name' ));
            $rs = $userSrchObj->getResultSet();
            $affiliateReferrerUserRow = FatApp::getDb()->fetch($rs);
            if ($affiliateReferrerUserRow && $affiliateReferrerUserRow['user_referral_code'] == $affiliateReferrerCode && $affiliateReferrerCode != '' && $affiliateReferrerUserRow['user_referral_code'] != '') {
                $affiliateReferrerUserId = $affiliateReferrerUserRow['user_id'];
                $referrerUserName = $affiliateReferrerUserRow['user_name'];
                $this->setUserInfo(array( 'user_affiliate_referrer_user_id' => $affiliateReferrerUserId ));
            }
        }
        /* ] */

        /* crediting wallet money to affiliate referrer as per admin configuration[ */
        $CONF_AFFILIATE_SIGNUP_COMMISSION = FatApp::getConfig("CONF_AFFILIATE_SIGNUP_COMMISSION", FatUtility::VAR_INT, 0);
        if ($affiliateReferrerUserId > 0 && $CONF_AFFILIATE_SIGNUP_COMMISSION > 0) {
            $referredUserName = User::getAttributesById($referredUserId, "user_name");

            $utxn_comments = Labels::getLabel('LBL_Signup_Commission_Received.{username}_Registered.', $langId);
            $utxn_comments = str_replace('{username}', $referredUserName, $utxn_comments);
            $transObj = new Transactions();

            /* $txnArray["utxn_user_id"] = $affiliateReferrerUserId;
            $txnArray["utxn_credit"] = $CONF_AFFILIATE_SIGNUP_COMMISSION;
            $txnArray["utxn_status"] = Transactions::STATUS_COMPLETED;
            $txnArray["utxn_comments"] = $utxn_comments;
            $txnArray["utxn_date"] = date('Y-m-d H:i:s');
            $txnArray["utxn_type"] = Transactions::TYPE_AFFILIATE_REFERRAL_SIGN_UP;

            if( $txnId = $transObj->addTransaction( $txnArray ) ){
            $emailNotificationObj = new EmailHandler();
            $emailNotificationObj->sendTxnNotification( $txnId, $langId );
            }else{
            $this->error = $transObj->getError();
            $broken = true;
            } */

            $txnDataArr = array(
            'utxn_user_id'    =>    $affiliateReferrerUserId,
            'utxn_credit'    =>    $CONF_AFFILIATE_SIGNUP_COMMISSION,
            'utxn_status'    =>    Transactions::STATUS_COMPLETED,
            'utxn_comments'    =>    $utxn_comments,
            'utxn_type'        =>    Transactions::TYPE_AFFILIATE_REFERRAL_SIGN_UP
            );
            if (!$txnId = $transObj->addTransaction($txnDataArr)) {
                $this->error = $transObj->getError();
                $broken = true;
            }
            /* Send email to User[ */
            $emailNotificationObj = new EmailHandler();
            $emailNotificationObj->sendTxnNotification($txnId, $langId);
            /* ] */
        }
        /* ] */

        /* if( $affiliateReferrerUserId > 0 && $broken === false ){ */
        /* removing cookie */
        CommonHelper::setCookie('affiliate_referrer_code_signup', '', time() - 3600);
        /* } */
        return true;
    }

    public function setLoginCredentials($username, $email, $password, $active = null, $verified = null)
    {
        if (! ($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $record = new TableRecord(static::DB_TBL_CRED);
        $arrFlds = array(
        static::DB_TBL_CRED_PREFIX.'username' => $username,
        static::DB_TBL_CRED_PREFIX.'email' => $email,
        static::DB_TBL_CRED_PREFIX.'password' => UserAuthentication::encryptPassword($password)
        );

        if (null != $active) {
            $arrFlds [static::DB_TBL_CRED_PREFIX.'active'] = $active;
        }
        if (null != $verified) {
            $arrFlds [static::DB_TBL_CRED_PREFIX.'verified'] = $verified;
        }

        $record->setFldValue(static::DB_TBL_CRED_PREFIX.'user_id', $this->mainTableRecordId);
        $record->assignValues($arrFlds);
        if (! $record->addNew(array(), $arrFlds)) {
            $this->error = $record->getError();
            return false;
        }

        return true;
    }

    public function setUserInfo($data = array())
    {
        if (empty($data)) {
            return false;
        }

        if (! ($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_TBL);
        $record->setFldValue(static::DB_TBL_PREFIX.'id', $this->mainTableRecordId);
        $record->assignValues($data);
        if (! $record->addNew(array(), $data)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function setLoginPassword($password)
    {
        if (! ($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_TBL_CRED);
        $arrFlds = array(
        static::DB_TBL_CRED_PREFIX.'password' => UserAuthentication::encryptPassword($password)
        );
        $record->setFldValue(static::DB_TBL_CRED_PREFIX.'user_id', $this->mainTableRecordId);
        $record->assignValues($arrFlds);
        if (! $record->addNew(array(), $arrFlds)) {
            $this->error = $record->getError();
            return false;
        }

        return true;
    }

    public function changeEmail($email)
    {
        if (trim($email) == '') {
            return false;
        }

        if (! ($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $record = new TableRecord(static::DB_TBL_CRED);
        $arrFlds = array(
        static::DB_TBL_CRED_PREFIX.'email' => $email
        );
        $record->setFldValue(static::DB_TBL_CRED_PREFIX.'user_id', $this->mainTableRecordId);
        $record->assignValues($arrFlds);
        if (! $record->addNew(array(), $arrFlds)) {
            $this->error = $record->getError();
            return false;
        }

        return true;
    }

    public function verifyAccount($v = 1)
    {
        if (!($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $db = FatApp::getDb();
        if (! $db->updateFromArray(
            static::DB_TBL_CRED,
            array(
            static::DB_TBL_CRED_PREFIX . 'verified' => $v
            ),
            array(
            'smt' => static::DB_TBL_CRED_PREFIX . 'user_id = ?',
            'vals' => array(
                    $this->mainTableRecordId
            )
            )
        )) {
            $this->error = $db->getError();
            return false;
        }
        // You may want to send some email notification to user that his account is verified.
        return true;
    }

    public function activateAccount($v = 1)
    {
        if (!($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $db = FatApp::getDb();
        if (! $db->updateFromArray(
            static::DB_TBL_CRED,
            array(
            static::DB_TBL_CRED_PREFIX . 'active' => $v
            ),
            array(
            'smt' => static::DB_TBL_CRED_PREFIX . 'user_id = ?',
            'vals' => array(
                        $this->mainTableRecordId
            )
            )
        )) {
            $this->error = $db->getError();
            return false;
        }

        return true;
    }

    public function activateSupplier($v = 1, $activateAdveracc = 0)
    {
        if (!($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $supplierArr = array(
        static::DB_TBL_PREFIX. 'is_supplier' => $v
        );
        $arrToUpdate = $supplierArr;
        if ($v==1 && $activateAdveracc ==1) {
            $advertiserArr = array(static::DB_TBL_PREFIX. 'is_advertiser' => $v);
            $arrToUpdate = array_merge($supplierArr, $advertiserArr);
        }


        $db = FatApp::getDb();
        if (! $db->updateFromArray(
            static::DB_TBL,
            $arrToUpdate,
            array(
            'smt' => static::DB_TBL_PREFIX . 'id = ?',
            'vals' => array(
            $this->mainTableRecordId
            )
            )
        )) {
            $this->error = $db->getError();
            return false;
        }

        return true;
    }

    public function getProfileData()
    {
        if (!$this->mainTableRecordId>0) {
            return false;
        }
        $srch = static::getSearchObject(true);
        $srch->addCondition('u.'.static::DB_TBL_PREFIX.'id', '=', $this->mainTableRecordId);
        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        unset($record['credential_password']);
        $record['user_email'] = $record['credential_email'];
        return $record;
        //return $this->getAttributesById($this->mainTableRecordId);
    }

    public function prepareUserVerificationCode($email = '')
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST.', $this->commonLangId);
            return false;
        }

        $verificationCode = $this->mainTableRecordId . '_' . FatUtility::getRandomString(15);
        $data = array(
            static::DB_TBL_UEMV_PREFIX . 'user_id' => $this->mainTableRecordId,
            static::DB_TBL_UEMV_PREFIX . 'token' => $verificationCode,
            static::DB_TBL_UEMV_PREFIX . 'email' => trim($email),
        );

        $tblRec = new TableRecord(static::DB_TBL_USER_EMAIL_VER);

        $tblRec->assignValues($data);

        if ($tblRec->addNew(array(), $data)) {
            return $verificationCode;
        } else {
            return false;
        }
    }

    public function verifyUserEmailVerificationCode($code)
    {
        $arrCode = explode('_', $code, 2);
        if (!is_numeric($arrCode[0])) {
            $this->error = Labels::getLabel('ERR_INVALID_CODE', $this->commonLangId);
            return false;
        }
        $userId = FatUtility::int($arrCode[0]);

        $emvSrch = new SearchBase(static::DB_TBL_USER_EMAIL_VER);
        $emvSrch->addCondition(static::DB_TBL_UEMV_PREFIX . 'user_id', '=', $userId);
        $emvSrch->addCondition(static::DB_TBL_UEMV_PREFIX . 'token', '=', $code, 'AND');

        $emvSrch->addFld(array(static::DB_TBL_UEMV_PREFIX . 'user_id',static::DB_TBL_UEMV_PREFIX . 'email'));

        $rs = $emvSrch->getResultSet();
        if ($row = FatApp::getDb()->fetch($rs)) {
            $this->deleteEmailVerificationToken($userId);
            if (trim($row['uev_email']) == '') {
                return true;
            }
            return $row['uev_email'];
        } else {
            $this->error = Labels::getLabel('ERR_INVALID_CODE.', $this->commonLangId);
            return false;
        }
        return false;
    }

    public function resetPassword($pwd)
    {
        if (!($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $db = FatApp::getDb();
        if (! $db->updateFromArray(
            static::DB_TBL_CRED,
            array(
            static::DB_TBL_CRED_PREFIX . 'password' => $pwd
            ),
            array(
            'smt' => static::DB_TBL_CRED_PREFIX . 'user_id = ?',
            'vals' => array(
                        $this->mainTableRecordId
            )
            )
        )) {
            $this->error = $db->getError();
            return false;
        }

        return true;
    }

    public function notifyAdminRegistration($data, $langId)
    {
        $user_type = $data['user_registered_initially_for'];
        $data = array(
            'user_name' => $data['user_name'],
            'user_username' => $data['user_username'],
        'user_email' => $data['user_email'],
        'user_type' => $user_type,
        );
        $email = new EmailHandler();

        if (!$email->sendNewRegistrationNotification($langId, $data)) {
            Message::addMessage(Labels::getLabel("ERR_ERROR_IN_SENDING_NOTIFICATION_EMAIL_TO_ADMIN", $langId));
            return false;
        }
        return true;
    }

    public function userEmailVerification($userObj, $data, $langId)
    {
        $verificationCode = $userObj->prepareUserVerificationCode();

        $link = CommonHelper::generateFullUrl('GuestUser', 'userCheckEmailVerification', array('verify'=>$verificationCode));
        $data = array(
            'user_name' => $data['user_name'],
            'link' => $link,
        'user_email' => $data['user_email'],
        );

        $email = new EmailHandler();

        if (!$email->sendSignupVerificationLink($langId, $data)) {
            Message::addMessage(Labels::getLabel("ERR_ERROR_IN_SENDING_VERFICATION_EMAIL", $langId));
            return false;
        }

        return true;
    }

    public function guestUserWelcomeEmail($data, $langId)
    {
        $link = CommonHelper::generateFullUrl('GuestUser', 'loginForm');

        $data = array(
            'user_name' => $data['user_name'],
        'user_email' => $data['user_email'],
        'link' => $link,
        );

        $email = new EmailHandler();

        if (!$email->sendWelcomeEmailToGuestUser($langId, $data)) {
            Message::addMessage(Labels::getLabel("ERR_ERROR_IN_SENDING_WELCOME_EMAIL", $langId));
            return false;
        }

        return true;
    }

    public function userWelcomeEmailRegistration($userObj, $data, $langId)
    {
        $link = CommonHelper::generateFullUrl('GuestUser', 'loginForm');

        $data = array(
            'user_name' => $data['user_name'],
        'user_email' => $data['user_email'],
        'link' => $link,
        );

        $email = new EmailHandler();

        if (!$email->sendWelcomeEmail($langId, $data)) {
            Message::addMessage(Labels::getLabel("ERR_ERROR_IN_SENDING_WELCOME_EMAIL", $langId));
            return false;
        }

        return true;
    }

    public function notifyAdminSupplierApproval($userObj, $data, $approval_request = 1, $langId)
    {
        $attr = array('user_name','credential_username','credential_email');
        $userData = $userObj->getUserInfo($attr, false, false);

        if ($userData === false) {
            return false;
        }

        $data = array(
            'user_name' => $userData['user_name'],
            'username' => $userData['credential_username'],
        'user_email' => $userData['credential_email'],
        'reference_number' => $data['reference'],
        );

        $email = new EmailHandler();

        if (!$email->sendSupplierApprovalNotification($langId, $data, $approval_request)) {
            Message::addMessage(Labels::getLabel("ERR_ERROR_IN_SENDING_SUPPLIER_APPROVAL_EMAIL", $langId));
            return false;
        }
        return true;
    }

    public static function getUserBalance($user_id, $excludePendingWidrawReq = true, $excludePromotion = true)
    {
        $user_id = FatUtility::int($user_id);
        $srch = new SearchBase('tbl_user_transactions', 'txn');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('txn.utxn_user_id');
        $srch->addMultipleFields(array("SUM(utxn_credit - utxn_debit) as userBalance"));
        $srch->addCondition('utxn_user_id', '=', $user_id);
        $srch->addCondition('utxn_status', '=', Transactions::STATUS_COMPLETED);
        $rs = $srch->getResultSet();
        if (!$row = FatApp::getDb()->fetch($rs)) {
            return 0;
        }

        $userBalance = $row["userBalance"];

        if ($excludePendingWidrawReq) {
            $srch = new SearchBase('tbl_user_withdrawal_requests', 'uwr');
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addGroupBy('uwr.withdrawal_user_id');
            $srch->addMultipleFields(array("SUM(withdrawal_amount) as withdrawal_amount"));
            $srch->addCondition('withdrawal_user_id', '=', $user_id);
            $srch->addCondition('withdrawal_status', '=', Transactions::WITHDRAWL_STATUS_PENDING);
            $rs = $srch->getResultSet();
            if ($res = FatApp::getDb()->fetch($rs)) {
                $userBalance = $userBalance - $res["withdrawal_amount"];
            }
        }

        if ($excludePromotion) {
            $promotionCharges = Promotion::getPromotionWalleToBeCharged($user_id);
            $userBalance = $userBalance - $promotionCharges;
        }
        return $userBalance ;
    }

    public static function getUserWithdrawnRequestAmount($user_id)
    {
        $srch = new SearchBase('tbl_user_withdrawal_requests', 'uwr');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('uwr.withdrawal_user_id');
        $srch->addMultipleFields(array("SUM(withdrawal_amount) as withdrawal_amount"));
        $srch->addCondition('withdrawal_user_id', '=', $user_id);
        $srch->addCondition('withdrawal_status', '=', Transactions::WITHDRAWL_STATUS_PENDING);
        $rs = $srch->getResultSet();
        $withdrawlAmount = 0;
        if ($res = FatApp::getDb()->fetch($rs)) {
            $withdrawlAmount = $res["withdrawal_amount"];
        }
        return $withdrawlAmount;
    }


    public static function getAffiliateUserRevenue($user_id, $date = '')
    {
        $user_id = FatUtility::int($user_id);
        $srch = new SearchBase('tbl_user_transactions', 'txn');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('txn.utxn_user_id');
        $srch->addMultipleFields(array("SUM(utxn_credit) as userRevenue"));
        $srch->addCondition('utxn_user_id', '=', $user_id);
        $srch->addCondition('utxn_status', '=', Transactions::STATUS_COMPLETED);
        $cnd = $srch->addCondition('utxn_type', '=', Transactions::TYPE_AFFILIATE_REFERRAL_SIGN_UP);
        $cnd->attachCondition('utxn_type', '=', Transactions::TYPE_AFFILIATE_REFERRAL_ORDER);
        if (!empty($date)) {
            $srch->addCondition('mysql_func_DATE(utxn_date)', '=', $date, 'AND', true);
        }
        $rs = $srch->getResultSet();
        if (!$row = FatApp::getDb()->fetch($rs)) {
            return 0;
        }
        return $row["userRevenue"];
    }

    public static function getUserLastWithdrawalRequest($userId)
    {
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_USR_WITHDRAWAL_REQ, 'tuwr');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('withdrawal_user_id', '=', $userId);
        $srch->addOrder('withdrawal_request_date', 'desc');
        $rs = $srch->getResultSet();

        if (!$rs) {
            return false;
        }

        if (!$row = FatApp::getDb()->fetch($rs)) {
            return false;
        }
        return $row;
    }

    public function addWithdrawalRequest($data, $langId)
    {
        $userId = FatUtility::int($data['ub_user_id']);
        unset($data['ub_user_id']);
        if ($userId < 1) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST.', $this->commonLangId);
            return false;
        }

        /* $assignFields = array(
        'withdrawal_amount'=>$data['withdrawal_amount'],
        'withdrawal_bank'=>$data['ub_bank_name'],
        'withdrawal_account_holder_name'=>$data['ub_account_holder_name'],
        'withdrawal_account_number'=>$data['ub_account_number'],
        'withdrawal_ifc_swift_code'=>$data['ub_ifsc_swift_code'],
        'withdrawal_bank_address'=>$data['ub_bank_address'],
        'withdrawal_comments'=>$data['withdrawal_comments'],
        'withdrawal_status'=>0,
        'withdrawal_request_date'=>date('Y-m-d H:i:s'),
        'withdrawal_user_id'=>$userId,
        ); */
        $assignFields = array(
        'withdrawal_user_id'=>$userId,
        'withdrawal_payment_method'    =>    $data['withdrawal_payment_method'],
        'withdrawal_amount'=>$data['withdrawal_amount'],

        'withdrawal_bank'=>$data['ub_bank_name'],
        'withdrawal_account_holder_name'=>$data['ub_account_holder_name'],
        'withdrawal_account_number'=>$data['ub_account_number'],
        'withdrawal_ifc_swift_code'=>$data['ub_ifsc_swift_code'],
        'withdrawal_bank_address'=>$data['ub_bank_address'],

        'withdrawal_comments'=>$data['withdrawal_comments'],
        'withdrawal_status'=>0,
        'withdrawal_request_date'=>date('Y-m-d H:i:s'),
        'withdrawal_cheque_payee_name'    => $data['withdrawal_cheque_payee_name'],
        'withdrawal_paypal_email_id'    =>    $data['withdrawal_paypal_email_id']
        );

        $broken = false;

        if (FatApp::getDb()->startTransaction() && FatApp::getDb()->insertFromArray(static::DB_TBL_USR_WITHDRAWAL_REQ, $assignFields)) {
            $withdrawRequestId = FatApp::getDb()->getInsertId();

            $formattedRequestValue = '#'.str_pad($withdrawRequestId, 6, '0', STR_PAD_LEFT);

            $txnArray["utxn_user_id"] = $userId;
            $txnArray["utxn_debit"] = $data["withdrawal_amount"];
            $txnArray["utxn_status"] = Transactions::STATUS_PENDING;
            $txnArray["utxn_comments"] = Labels::getLabel('LBL_Funds_Withdrawn', $langId).'. '.Labels::getLabel('LBL_Request_ID', $langId).' '.$formattedRequestValue;
            $txnArray["utxn_withdrawal_id"] = $withdrawRequestId;
            $txnArray['utxn_type']    =    Transactions::TYPE_MONEY_WITHDRAWN;

            $transObj = new Transactions();
            if ($txnId = $transObj->addTransaction($txnArray)) {
                /*
                becoz email sent while requesting wallet withdrawal.
                $emailNotificationObj = new EmailHandler();
                $emailNotificationObj->sendTxnNotification($txnId,$langId) ;*/
            } else {
                $this->error = $transObj->getError();
                $broken = true;
            }
        }

        if ($broken === false && FatApp::getDb()->commitTransaction()) {
            return $withdrawRequestId;
        }

        FatApp::getDb()->rollbackTransaction();
        return false;
    }

    private function deleteEmailVerificationToken($userId)
    {
        FatApp::getDb()->deleteRecords(static::DB_TBL_USER_EMAIL_VER, array('smt' => static::DB_TBL_UEMV_PREFIX . 'user_id = ?', 'vals' => array($userId)));
        return true;
    }

    /* function getUser($data = array()){
    $srch = new SearchBase(static::DB_TBL,'tu');
    $srch->joinTable('tbl_states', 'LEFT JOIN', 'tu.user_state_county=ts.state_id', 'ts');
    $srch->joinTable('tbl_countries', 'LEFT JOIN','tu.user_country=tc.country_id', 'tc');

    foreach($data as $key=>$val) {
    if(strval($val)=='') continue;
        switch($key) {
            case 'user_id':
    case 'id':
                $srch->addCondition('tu.user_id', '=', intval($val));
            break;
            case 'user_email':
                $srch->addCondition('tu.user_email', '=', $val);
            break;
    case 'user_username':
                $srch->addCondition('tu.user_username', '=', $val);
            break;
    case 'user_name':
                $srch->addCondition('tu.user_name', '=', $val);
            break;
    case 'user_email_username':
                $cndCondition=$srch->addCondition('tu.user_email', '=', $val);
                $cndCondition->attachCondition('tu.user_username', '=', $val,'OR');
            break;
    case 'facebook_id':
                $srch->addCondition('tu.user_facebook_id', '=', $val);
            break;
    case 'googleplus_id':
                $srch->addCondition('tu.user_googleplus_id', '=', $val);
                break;
    case 'token':
                $srch->addCondition('tu.user_app_token', '=', $val);
                break;
    case 'refer_code':
                $srch->addCondition('tu.user_referral_code', '=', $val);
                break;
            }
        }

    $rs = $srch->getResultSet();
    if(!$row = $this->db->fetch($rs)){
    return false;
    }

    return $row;
    } */
    public static function getUserShopName($user_id = 0)
    {
        $user_id = FatUtility::int($user_id);
        $srch = new SearchBase(static::DB_TBL, 'tu');
        $srch->joinTable('tbl_shops', 'LEFT JOIN', 'tu.user_id=ts.shop_user_id', 'ts');
        $srch->joinTable(static::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.'.static::DB_TBL_CRED_PREFIX.'user_id = tu.user_id', 'uc');
        $srch->addMultipleFields(array('user_id', 'user_name', 'shop_identifier'));
        $srch->addOrder('user_name', 'asc');
        if ($user_id>0) {
            $srch->addCondition('tu.user_id', '=', intval($user_id));
        }
        $srch->addCondition('uc.'.static::DB_TBL_CRED_PREFIX.'active', '=', 1);
        $rs = $srch->getResultSet();
        if (!$row = FatApp::getDb()->fetch($rs)) {
            return false;
        }

        return $row;
    }

    public static function isAdminLogged($ip = '')
    {
        if ($ip == '') {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SESSION[static::ADMIN_SESSION_ELEMENT_NAME]) && $_SESSION[static::ADMIN_SESSION_ELEMENT_NAME]['admin_ip'] == $ip) {
            return true;
        }

        return false;
    }

    public static function isSellerVerified($userId)
    {
        $userId = FatUtility::int($userId);
        $userObj = new User($userId);
        $srch = $userObj->getUserSupplierRequestsObj();
        $srch->addFld(array('usuprequest_attempts','usuprequest_id','usuprequest_status'));
        $rs = $srch->getResultSet();
        $supplierRequest = FatApp::getDb()->fetch($rs);
        if ($supplierRequest['usuprequest_status'] == User::SUPPLIER_REQUEST_APPROVED) {
            return true;
        }
        return false;
    }

    public static function getUserExtraData($user_id, $attr = null)
    {
        $user_id = FatUtility::int($user_id);
        $srch = new SearchBase(static::DB_TBL_USR_EXTRAS);
        $srch->doNotCalculateRecords();

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }
        $srch->addCondition('uextra_user_id', '=', $user_id);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    public static function isCatalogRequestSubmittedForApproval($preqId)
    {
        $row = ProductRequest::getAttributesById($preqId, array('preq_submitted_for_approval'));
        if (!empty($row)&& $row['preq_submitted_for_approval'] ==  applicationConstants::YES) {
            return true;
        }
        return false;
    }

    public function setMobileAppToken()
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $generatedToken = substr(md5(rand(1, 99999) . microtime()), 0, UserAuthentication::TOKEN_LENGTH);

        $expiry = strtotime("+7 DAYS");
        $values = array(
        'uauth_user_id'=>$this->mainTableRecordId,
        'uauth_token'=>$generatedToken,
        'uauth_expiry'=>date('Y-m-d H:i:s', $expiry),
        'uauth_browser'=>CommonHelper::userAgent(),
        'uauth_last_access'=>date('Y-m-d H:i:s'),
        'uauth_last_ip'=>CommonHelper::getClientIp(),
        );
        if (! UserAuthentication::saveLoginToken($values)) {
            return false;
        }

        return $generatedToken;
    }

    public function createUserTempToken($generatedToken)
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        FatApp::getDb()->deleteRecords(static::DB_TBL_USR_MOBILE_TEMP_TOKEN, array('smt' => static::DB_TBL_USR_MOBILE_TEMP_TOKEN_PREFIX.'user_id = ?', 'vals' => array((int)$this->mainTableRecordId)));
        $assignValues = array(
        static::DB_TBL_USR_MOBILE_TEMP_TOKEN_PREFIX.'user_id'=>$this->mainTableRecordId,
        static::DB_TBL_USR_MOBILE_TEMP_TOKEN_PREFIX.'token'=>$generatedToken,
        static::DB_TBL_USR_MOBILE_TEMP_TOKEN_PREFIX.'expiry'=>date('Y-m-d H:i:s', strtotime("+10 MINUTE")),
        );
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_USR_MOBILE_TEMP_TOKEN, $assignValues, false, array(), $assignValues)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function validateAPITempToken($token)
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $srch = new SearchBase(static::DB_TBL_USR_MOBILE_TEMP_TOKEN);
        $srch->addCondition('uttr_user_id', '=', $this->mainTableRecordId);
        $srch->addCondition('uttr_token', '=', $token);
        $srch->addCondition('uttr_expiry', '>=', date('Y-m-d H:i:s'));
        $srch->addMultipleFields(array('uttr_user_id', 'uttr_token'));
        $srch->doNotCalculateRecords();
        $srch->setPagesize(1);
        $rs = $srch->getResultSet();
        if ((!$row = FatApp::getDb()->fetch($rs)) || ($row['uttr_token'] !== $token)) {
            return false;
        }
        return $row;
    }

    public function deleteUserAPITempToken()
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        if (FatApp::getDb()->deleteRecords(static::DB_TBL_USR_MOBILE_TEMP_TOKEN, array('smt' => static::DB_TBL_USR_MOBILE_TEMP_TOKEN_PREFIX.'user_id = ?', 'vals' => array((int)$this->mainTableRecordId)))) {
            return true;
        }
    }

    public function setPushNotificationToken($appToken, $fcmDeviceId)
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $expiry = strtotime("+7 DAYS");
        $values = array(
        /* 'uauth_user_id'=>$this->mainTableRecordId,
        'uauth_token'=>$appToken, */
        'uauth_expiry'=>date('Y-m-d H:i:s', $expiry),
        'uauth_browser'=>CommonHelper::userAgent(),
        'uauth_fcm_id'=>$fcmDeviceId,
        'uauth_last_access'=>date('Y-m-d H:i:s'),
        'uauth_last_ip'=>CommonHelper::getClientIp(),
        );

        FatApp::getDb()->deleteRecords(
            UserAuthentication::DB_TBL_USER_AUTH,
            array(
            'smt' => 'uauth_fcm_id = ? and uauth_token != ?',
            'vals' => array($fcmDeviceId,$appToken)
            )
        );

        $where = array('smt' => 'uauth_user_id = ? and uauth_token = ?', 'vals' => array((int)$this->mainTableRecordId,$appToken));

        if (!UserAuthentication::updateFcmDeviceToken($values, $where)) {
            return false;
        }
        /* if (!FatApp::getDb()->updateFromArray(static::DB_TBL_USER_AUTH, array('user_push_notification_api_token'=>$uToken), array('smt' => static::DB_TBL_PREFIX . 'id = ? ', 'vals' => array((int)$this->mainTableRecordId)))){
        $this->error = FatApp::getDb()->getError();
        echo $this->error; die;
        } */
        return true;
    }

    public function getPushNotificationTokens()
    {
        if (($this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $db = FatApp::getDb();

        $srch = self::getSearchObject(true, true);
        $srch->joinTable(UserAuthentication::DB_TBL_USER_AUTH, 'LEFT OUTER JOIN', 'uauth.uauth_user_id = u.user_id', 'uauth');
        $srch->addCondition('user_id', '=', $this->mainTableRecordId);
        $srch->addCondition('uc.'.static::DB_TBL_CRED_PREFIX.'active', '=', 1);
        $srch->addCondition('uc.'.static::DB_TBL_CRED_PREFIX.'verified', '=', 1);
        $srch->addCondition('uauth_fcm_id', '!=', '');
        $srch->addCondition('uauth_last_access', '>=', date('Y-m-d H:i:s', strtotime("-7 DAYS")));
        $srch->addFld('uauth_fcm_id');
        $rs = $srch->getResultSet();
        if (!$row = $db->fetchAll($rs)) {
            return array();
        }
        return $row;
    }

    public function referredByAffilates($affilateUserId)
    {
        if ($affilateUserId < 1) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST_USER_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $srch = $this->getUserSearchObj(null, true);
        $srch->addCondition('user_affiliate_referrer_user_id', '=', $affilateUserId);

        return $srch;
    }

    public static function setImageUpdatedOn($userId, $date = '')
    {
        $date = empty($date) ? date('Y-m-d  H:i:s') : $date;
        $where = array('smt'=>'user_id = ?', 'vals'=>array($userId));
        FatApp::getDb()->updateFromArray(static::DB_TBL, array('user_img_updated_on'=>date('Y-m-d  H:i:s')), $where);
    }
}
