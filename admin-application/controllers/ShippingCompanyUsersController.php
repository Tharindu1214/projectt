<?php
class ShippingCompanyUsersController extends AdminBaseController
{
    public function __construct($action)
    {
        $ajaxCallArray = array();
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die('Invalid Action');
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewShippingCompanyUsers($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditShippingCompanyUsers($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewShippingCompanyUsers();
        $this->set('frmSearch', $this->getUserSearchForm());
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewShippingCompanyUsers();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $frmSearch = $this->getUserSearchForm();

        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }

        $userObj = new User();
        $srch = $userObj->getUserSearchObj();
        $srch->addOrder('u.user_id', 'DESC');
        $srch->addCondition('u.user_is_shipping_company', '=', applicationConstants::YES);

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            /* $srch->addCondition('u.user_name', 'LIKE', '%' . $post['keyword'] . '%')
            ->attachCondition('uc.credential_username', 'LIKE', '%' . $post['keyword'] . '%');     */
            $srch->addCondition('uc.credential_username', '=', $keyword);
        }

        /* $user_active = FatApp::getPostedData( 'user_active', FatUtility::VAR_INT, -1 );
        if( $user_active > -1 ){
        $srch->addCondition('uc.credential_active', '=', $user_active );
        }

        $user_verified = FatApp::getPostedData( 'user_verified', FatUtility::VAR_INT, -1 );
        if ( $user_verified > -1) {
        $srch->addCondition( 'uc.credential_verified', '=', $user_verified );
        }

        $type = FatApp::getPostedData( 'type', FatUtility::VAR_INT, 0 );
        if ( $type > 0) {
        if ( $type == User::USER_TYPE_SELLER ) {
        $srch->addCondition('u.user_is_supplier', '=', 1);
        }
        if( $type == User::USER_TYPE_BUYER ) {
        $srch->addCondition('u.user_is_buyer', '=', 1);
        }
        } */

        /* $user_regdate_from = FatApp::getPostedData('user_regdate_from', FatUtility::VAR_DATE, '') ;
        if ( !empty($user_regdate_from) ) {
        $srch->addCondition('user_regdate', '>=', $user_regdate_from. ' 00:00:00');
        }

        $user_regdate_to = FatApp::getPostedData('user_regdate_to', FatUtility::VAR_DATE, '') ;
        if ( !empty($user_regdate_to) ) {
        $srch->addCondition('user_regdate', '<=', $user_regdate_to. ' 23:59:59');
        } */

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'user_id');

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }

    public function Orders($shipping_company_user_id = 0)
    {
        $shipping_company_user_id  = FatUtility::int($shipping_company_user_id);
        $userRow = User::getAttributesById($shipping_company_user_id);

        if (!$userRow || $userRow['user_is_shipping_company'] == applicationConstants::NO) {
            Message::addErrorMessage("Invalid User or User is not of Shipping Company");
            FatApplication::redirectUser(CommonHelper::generateUrl('ShippingCompanyUsers'));
        }
        $this->_template->addJs('seller-orders/page-js/index.js');

        $frmSearch = $this->getSellerOrderSearchForm($this->adminLangId);

        $FldShippingCompanyUserId = $frmSearch->getField('shipping_company_user_id');
        $FldShippingCompanyUserId->value = $shipping_company_user_id;

        $this->set('frmSearch', $frmSearch);
        $this->set('userRow', $userRow);
        $this->_template->render();
    }

    public function form($user_id)
    {
        $this->objPrivilege->canEditShippingCompanyUsers();
        $frmUser = $this->getUserForm($user_id, User::USER_TYPE_SHIPPING_COMPANY);

        $fldCredentialUserName = $frmUser->getField('credential_username');
        $fldCredentialUserName->requirements()->setRequired(true);
        $fldCredentialUserName->setUnique('tbl_user_credentials', 'credential_username', 'credential_user_id', 'user_id', 'user_id');

        $fldCredentialEmail = $frmUser->getField('credential_email');
        $fldCredentialEmail->requirements()->setRequired(true);
        $fldCredentialEmail->setUnique('tbl_user_credentials', 'credential_email', 'credential_user_id', 'user_id', 'user_id');

        $fldUserType = $frmUser->getField('user_type');
        $fldUserType->value = User::USER_TYPE_SHIPPING_COMPANY;

        $stateId = 0;

        if ($user_id > 0) {
            $userObj = new User($user_id);
            $srch = $userObj->getUserSearchObj();
            $srch->addMultipleFields(array('u.*'));
            $rs = $srch->getResultSet();
            $data = FatApp::getDb()->fetch($rs, 'user_id');

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            /* if(isset($data['credential_username'])){
            $data['credential_username'] = htmlentities($data['credential_username']);
            } */
            $stateId = $data['user_state_id'];
            $frmUser->fill($data);
        }

        $this->set('user_id', $user_id);
        $this->set('stateId', $stateId);
        $this->set('frmUser', $frmUser);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditShippingCompanyUsers();
        $frm = $this->getUserForm(0, User::USER_TYPE_SHIPPING_COMPANY);

        $post = FatApp::getPostedData();
        $user_state_id = FatUtility::int($post['user_state_id']);
        $post = $frm->getFormDataFromArray($post);
        $post['user_state_id'] = $user_state_id;

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post['user_is_shipping_company'] = 1;
        $post['user_is_supplier'] = 0;
        $post['user_is_buyer'] = 0;
        $post['user_is_advertiser'] = 0;
        $post['user_is_affiliate'] = 0;
        $post['user_preferred_dashboard'] = 1;

        $user_id = FatUtility::int($post['user_id']);
        /* if( $user_id <= 0 ){
        Message::addErrorMessage( "No functionality provided to add more shipping company users and only one already created user is by default associated with COD Orders, if you will create more user then system will automatically pick/associate last created active user with the COD Orders. Please contact Technical Team" );
        FatUtility::dieJsonError( Message::getHtml() );
        } */

        $userObj = new User($user_id);
        $userObj->assignValues($post);
        if (!$userObj->save()) {
            Message::addErrorMessage($userObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $user_id = $userObj->getMainTableRecordId();
        if ($post['user_id'] <= 0) {
            $post['user_password'] = $post['credential_username'].'@123';
            if (!$userObj->setLoginCredentials($post['credential_username'], $post['credential_email'], $post['user_password'], 1, 1)) {
                Message::addErrorMessage(Labels::getLabel("MSG_LOGIN_CREDENTIALS_COULD_NOT_BE_SET", $this->adminLangId) . $userObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $this->set('msg', 'Setup Successful.');
        $this->_template->render(false, false, 'json-success.php');
    }

    public function userWalletTransactions()
    {
        $op_id = FatApp::getPostedData('op_id', FatUtility::VAR_INT, 0);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0)?1:$post['page'];
        $page = (empty($page) || $page <= 0)?1:FatUtility::int($page);

        $srch = Transactions::getSearchObject();
        $srch->addCondition('utxn.utxn_op_id', '=', $op_id);
        $srch->addMultipleFields(array('utxn.*'));
        $srch->addOrder('utxn_id', 'DESC');
        $srch->addGroupBy('utxn.utxn_id');
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->set('statusArr', Transactions::getStatusArr($this->adminLangId));
        $this->_template->render(false, false);
    }
}
