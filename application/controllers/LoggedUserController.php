<?php
class LoggedUserController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);

        UserAuthentication::checkLogin();

        $userObj = new User(UserAuthentication::getLoggedUserId());

        $userInfo = $userObj->getUserInfo(array(), false, false);

        if (false == $userInfo || (!UserAuthentication::isGuestUserLogged() && $userInfo['credential_active'] != applicationConstants::ACTIVE)) {
            if (FatUtility::isAjaxCall()) {
                Message::addErrorMessage(Labels::getLabel('MSG_Session_seems_to_be_expired', CommonHelper::getLangId()));
                FatUtility::dieWithError(Message::getHtml());
            }
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'logout'));
        }

        if (!isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'])) {
            $userPreferedDashboardType = ($userInfo['user_preferred_dashboard'])?$userInfo['user_preferred_dashboard']:$userInfo['user_registered_initially_for'];

            switch ($userPreferedDashboardType) {
                case User::USER_TYPE_BUYER:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'B';
                    break;
                case User::USER_TYPE_SELLER:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'S';
                    break;
                case User::USER_TYPE_AFFILIATE:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'AFFILIATE';
                    break;
                case User::USER_TYPE_ADVERTISER:
                    $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'Ad';
                    break;
            }
        }

        if ((!UserAuthentication::isGuestUserLogged() && $userInfo['credential_verified'] != 1) && !($_SESSION[USER::ADMIN_SESSION_ELEMENT_NAME] && $_SESSION[USER::ADMIN_SESSION_ELEMENT_NAME]>0)) {
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'logout'));
        }

        if (UserAuthentication::getLoggedUserId() < 1) {
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'logout'));
        }

        if (empty($userInfo['credential_email'])) {
            $message = Labels::getLabel('MSG_Please_Configure_Your_Email', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(CommonHelper::generateUrl('GuestUser', 'configureEmail'));
        }
        $this->initCommonValues();
    }

    private function initCommonValues()
    {
        $this->_template->addCss('css/dashboard.css');
        $this->set('isUserDashboard', true);
    }

    protected function getOrderCancellationRequestsSearchForm($langId)
    {
        $frm = new Form('frmOrderCancellationRequest');
        $frm->addTextBox('', 'op_invoice_number');
        $frm->addSelectBox('', 'ocrequest_status', array( '-1' => Labels::getLabel('LBL_Status_Does_Not_Matter', $langId)  ) + OrderCancelRequest::getRequestStatusArr($langId), '', array(), '');
        $frm->addDateField('', 'ocrequest_date_from', '', array('readonly'=>'readonly'));
        $frm->addDateField('', 'ocrequest_date_to', '', array('readonly'=>'readonly'));

        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearOrderCancelRequestSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    protected function getOrderReturnRequestsSearchForm($langId)
    {
        $frm = new Form('frmOrderReturnRequest');
        $frm->addTextBox('', 'keyword');
        $frm->addSelectBox('', 'orrequest_status', array( '-1' => Labels::getLabel('LBL_Status_Does_Not_Matter', $langId) ) + OrderReturnRequest::getRequestStatusArr($langId), '', array(), '');
        $returnRquestArray = OrderReturnRequest::getRequestTypeArr($langId);
        if (count($returnRquestArray) > applicationConstants::YES) {
            $frm->addSelectBox('', 'orrequest_type', array( '-1' => Labels::getLabel('LBL_Request_Type_Does_Not_Matter', $langId) ) + $returnRquestArray, '', array(), '');
        } else {
            $frm->addHiddenField('', 'orrequest_type', '-1');
        }
        $frm->addDateField('', 'orrequest_date_from', '', array('readonly'=>'readonly'));
        $frm->addDateField('', 'orrequest_date_to', '', array('readonly'=>'readonly'));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearOrderReturnRequestSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    protected function getShippingSettingsForm($langId)
    {
        $frm = new Form('frmShippingSettings');

        $frm->addSelectBox('', 'city_list', array( '-1' => "Select City" ) + ShippingSettings::getCityLists($langId), '', array(), '');

        $frm->addSelectBox('', 'shipping_company', array( '-1' => "Shipping Company" ) + ShippingSettings::getShippingMethods($langId), '', array(), '');

        $frm->addSelectBox('', 'businessdays', array( '-1' => "Business Days" ) + ShippingSettings::getBusinessDays($langId), '', array(), '');

        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', "Search");
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearOrderReturnRequestSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    protected function getOrderReturnRequestMessageSearchForm($langId)
    {
        $frm = new Form('frmOrderReturnRequestMsgsSrch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'orrequest_id');
        return $frm;
    }

    protected function getOrderReturnRequestMessageForm($langId)
    {
        $frm = new Form('frmOrderReturnRequestMessge');
        $frm->setRequiredStarPosition('');
        $fld = $frm->addTextArea('', 'orrmsg_msg');
        $fld->requirements()->setRequired();
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_Message_is_mandatory', $langId));
        $frm->addHiddenField('', 'orrmsg_orrequest_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $langId));
        return $frm;
    }
}
