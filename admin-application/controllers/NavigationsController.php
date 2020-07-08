<?php
class NavigationsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewNavigationManagement($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditNavigationManagement($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewNavigationManagement();
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewNavigationManagement();

        $srch = Navigations::getSearchObject($this->adminLangId, false);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('nav_active', 'DESC');
        $srch->addOrder('nav_id', 'DESC');
        $rs = $srch->getResultSet();

        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        $this->set("arr_listing", $records);
        $this->_template->render(false, false);
    }

    public function form($nav_id = 0)
    {
        $this->objPrivilege->canViewNavigationManagement();

        $nav_id = FatUtility::int($nav_id);
        $frm = $this->getForm($nav_id);

        if (0 < $nav_id) {
            $data = Navigations::getAttributesById($nav_id, array('nav_id','nav_identifier','nav_active','nav_type','nav_deleted'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('nav_id', $nav_id);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditNavigationManagement();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $nav_id = $post['nav_id'];
        if (1 > $nav_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = Navigations::getAttributesById($nav_id, array('nav_id','nav_identifier'));
        if ($data === false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $record = new Navigations($nav_id);
        if (!$record->updateContent($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Navigations::getAttributesByLangId($langId, $nav_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('navId', $nav_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($nav_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewNavigationManagement();

        $nav_id = FatUtility::int($nav_id);
        $lang_id = FatUtility::int($lang_id);

        if ($nav_id==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getLangForm($nav_id, $lang_id);
        $langData = Navigations::getAttributesByLangId($lang_id, $nav_id);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('nav_id', $nav_id);
        $this->set('nav_lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditNavigationManagement();
        $post = FatApp::getPostedData();

        $nav_id = $post['nav_id'];
        $lang_id = $post['lang_id'];

        if ($nav_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($nav_id, $lang_id);
        $post = $frm->getFormDataFromArray($post);
        unset($post['nav_id']);
        unset($post['lang_id']);
        $data = array(
        'navlang_nav_id'=>$nav_id,
        'navlang_lang_id'=>$lang_id,
        'nav_name'=>$post['nav_name']
        );

        $obj = new Navigations($nav_id);
        if (!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Navigations::getAttributesByLangId($langId, $nav_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('navId', $nav_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function pages($nav_id)
    {
        $this->objPrivilege->canViewNavigationManagement();
        $nav_id = FatUtility::int($nav_id);
        if (!$nav_id) {
            Message::addErrorMessage($this->str_invalid_request);
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieWithError(Message::getHtml());
            }
            FatApp::redirectUser(CommonHelper::generateUrl('navigations'));
        }

        $srch = new NavigationLinkSearch($this->adminLangId);
        $srch->joinNavigation();
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(
            array( 'nlink_id', 'nlink_nav_id', 'nlink_cpage_id', 'nlink_target', 'nlink_type', 'nlink_parent_id',
            'nlink_caption', 'nlink_identifier' )
        );
        $srch->addCondition('nav_id', '=', $nav_id);
        $srch->addOrder('nlink_display_order', 'asc');
        $rs = $srch->getResultSet();
        $arrListing = FatApp::getDb()->fetchAll($rs);
        $this->set('nav_id', $nav_id);
        $this->set('arrListing', $arrListing);
        $this->_template->render(false, false);
    }

    public function navigationLinkForm()
    {
        $this->objPrivilege->canEditNavigationManagement();
        $post = FatApp::getPostedData();
        $nav_id = FatUtility::int($post['nav_id']);
        $nlink_id = FatUtility::int($post['nlink_id']);
        if (!$nav_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = $this->getNavigationLinksForm();
        if (!$nlink_id) {
            $frm->fill(array( 'nlink_nav_id' => $nav_id, 'nlink_id' => $nlink_id  ));
        } else {
            $srch = new NavigationLinkSearch($this->adminLangId);
            $srch->joinNavigation();
            $srch->doNotLimitRecords();
            $srch->doNotCalculateRecords();
            $srch->addCondition('nlink_id', '=', $nlink_id);
            $rs = $srch->getResultSet();
            $nlinkRow = FatApp::getDb()->fetch($rs);
            $frm->fill($nlinkRow);
        }
        $this->set('nav_id', $nav_id);
        $this->set('nlink_id', $nlink_id);
        $this->set('frm', $frm);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setupNavigationLink()
    {
        $this->objPrivilege->canEditNavigationManagement();

        $frm = $this->getNavigationLinksForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $nlink_nav_id = FatUtility::int($post['nlink_nav_id']);
        $nlink_id = FatUtility::int($post['nlink_id']);
        unset($post['nlink_id']);

        if (1 > $nlink_nav_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $db = FatApp::getDb();

        $srch = Navigations::getSearchObject($this->adminLangId, false);
        $srch->addCondition('nav_id', '=', $nlink_nav_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $navRow = $db->fetch($rs);
        if (!$navRow) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post['nlink_category_id'] = FatApp::getPostedData('nlink_category_id', FatUtility::VAR_INT, 0);
        $post['nlink_cpage_id'] = FatApp::getPostedData('nlink_cpage_id', FatUtility::VAR_INT, 0);

        if ($post['nlink_type'] == NavigationLinks::NAVLINK_TYPE_CMS) {
            $post['nlink_url'] = '';
            $post['nlink_category_id'] = 0;
        }
        if ($post['nlink_type'] == NavigationLinks::NAVLINK_TYPE_EXTERNAL_PAGE) {
            $post['nlink_cpage_id'] = 0;
            $post['nlink_category_id'] = 0;
        }
        if ($post['nlink_type'] == NavigationLinks::NAVLINK_TYPE_CATEGORY_PAGE) {
            $post['nlink_url'] = '';
            $post['nlink_cpage_id'] = 0;
        }

        $navLinkObj = new NavigationLinks($nlink_id);
        $dataToSaveArr = $post;
        $navLinkObj->assignValues($dataToSaveArr);
        if (!$navLinkObj->save()) {
            Message::addErrorMessage($navLinkObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $newTabLangId = 0;
        if ($nlink_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = Navigations::getAttributesByLangId($langId, $nlink_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $nlink_id = $navLinkObj->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $this->set('langId', $newTabLangId);
        $this->set('nlinkId', $nlink_id);
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
        /* $data = Navigations::getAttributesById($nav_id,array('nav_id','nav_identifier'));
        if ($data === false) {
        Message::addErrorMessage($this->str_invalid_request);
        FatUtility::dieJsonError( Message::getHtml() );
        }

        $record = new Navigations($nav_id);
        if (!$record->updateContent($post)) {
        Message::addErrorMessage($record->getError());
        FatUtility::dieJsonError( Message::getHtml() );
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach($languages as $langId => $langName ){
        if(!$row = Navigations::getAttributesByLangId($langId,$nav_id)){
        $newTabLangId = $langId;
        break;
        }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('navId', $nav_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php'); */
    }

    public function navigationLinkLangForm()
    {
        $post = FatApp::getPostedData();
        $nav_id = FatUtility::int($post['nav_id']);
        $nlink_id = FatUtility::int($post['nlink_id']);
        $lang_id = FatUtility::int($post['lang_id']);
        if (!$nav_id || !$lang_id || !$nlink_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $langFrm = $this->getNavigationLinksLangForm($lang_id);
        $langData = NavigationLinks::getAttributesByLangId($lang_id, $nlink_id);
        if ($langData) {
            $langData['nlink_id'] = $langData['nlinklang_nlink_id'];
            $langData['nav_id'] = $nav_id;
            $langFrm->fill($langData);
        } else {
            $langFrm->fill(array('lang_id' => $lang_id, 'nav_id' => $nav_id,'nlink_id' => $nlink_id ));
        }

        /* if( !$nlink_id ){
        $langFrm->fill( array('lang_id' => $lang_id, 'nav_id' => $nav_id ) );
        } */

        $this->set('languages', Language::getAllNames());
        $this->set('nav_id', $nav_id);
        $this->set('nlink_id', $nlink_id);
        $this->set('nav_lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function setupNavigationLinksLang()
    {
        $this->objPrivilege->canEditNavigationManagement();
        $post = FatApp::getPostedData();

        $nlink_id = FatUtility::int($post['nlink_id']);
        $lang_id = $post['lang_id'];

        if ($nlink_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getNavigationLinksLangForm($lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['nlink_id']);
        unset($post['lang_id']);

        $data=array(
        'nlinklang_nlink_id'=>$nlink_id,
        'nlinklang_lang_id'=>$lang_id,
        'nlink_caption'=>$post['nlink_caption'],
        );

        $navLinkObj = new NavigationLinks($nlink_id);
        if (!$navLinkObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($navLinkObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $newTabLangId = 0;
        if ($nlink_id>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = NavigationLinks::getAttributesByLangId($langId, $nlink_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $nlink_id = $navLinkObj->getMainTableRecordId();
            $newTabLangId=FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $this->set('langId', $newTabLangId);
        $this->set('nlinkId', $nlink_id);
        $this->set('msg', Labels::getLabel('LBL_Navigation_Link_Setup_Successful', $this->adminLangId));

        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteNavigationLink()
    {
        $this->objPrivilege->canEditNavigationManagement();

        $nlinkId = FatApp::getPostedData('nlinkId', FatUtility::VAR_INT, 0);
        if ($nlinkId < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new NavigationLinks($nlinkId);
        if (!$obj->deleteRecord(true)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function updateNlinkOrder()
    {
        $this->objPrivilege->canEditNavigationManagement();

        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $nlinkObj = new NavigationLinks();
            if (!$nlinkObj->updateOrder($post['pageList'])) {
                Message::addErrorMessage($nlinkObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Order_Updated_Successful', $this->adminLangId));
        }
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditNavigationManagement();
        $navId = FatApp::getPostedData('navId', FatUtility::VAR_INT, 0);
        if (0 == $navId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $navigationData = Navigations::getAttributesById($navId, array('nav_active'));

        if (!$navigationData) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($navigationData['nav_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateNavigationStatus($navId, $status);

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditNavigationManagement();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $navIdsArr = FatUtility::int(FatApp::getPostedData('nav_ids'));
        if (empty($navIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($navIdsArr as $navId) {
            if (1 > $navId) {
                continue;
            }

            $this->updateNavigationStatus($navId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateNavigationStatus($navId, $status)
    {
        $status = FatUtility::int($status);
        $navId = FatUtility::int($navId);
        if (1 > $navId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $navObj = new Navigations($navId);
        if (!$navObj->changeStatus($status)) {
            Message::addErrorMessage($navObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    private function getNavigationLinksForm()
    {
        $frm = new Form('frmNavigationLink');
        $frm->addRequiredField(Labels::getLabel('LBL_Caption_Identifier', $this->adminLangId), 'nlink_identifier');
        $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->adminLangId), 'nlink_type', NavigationLinks::getLinkTypeArr($this->adminLangId), '', array(), '')->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Link_Target', $this->adminLangId), 'nlink_target', NavigationLinks::getLinkTargetArr($this->adminLangId), '', array(), '')->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Login_Protected', $this->adminLangId), 'nlink_login_protected', NavigationLinks::getLinkLoginTypeArr($this->adminLangId), '', array(), '')->requirements()->setRequired();

        $contentPages = ContentPage:: getPagesForSelectBox($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Link_to_CMS_Page', $this->adminLangId), 'nlink_cpage_id', $contentPages);

        $categoryPages = ProductCategory::getProdCatParentChildWiseArr($this->adminLangId, '', false, true);
        $frm->addSelectBox(Labels::getLabel('LBL_Link_to_Category', $this->adminLangId), 'nlink_category_id', $categoryPages);

        $fld = $frm->addTextBox(Labels::getLabel('LBL_External_Page', $this->adminLangId), 'nlink_url');
        $fld->htmlAfterField = '<br/>'.Labels::getLabel('LBL_Prefix_with_{SITEROOT}_if_u_want_to_generate_system_site_url', $this->adminLangId).'<br/>E.g: {SITEROOT}products, {SITEROOT}contact_us'.Labels::getLabel('LBL_etc', $this->adminLangId).'.'    ;

        $frm->addTextBox(Labels::getLabel('LBL_Display_Order', $this->adminLangId), 'nlink_display_order')->requirements()->setInt();

        $frm->addHiddenField('', 'nlink_nav_id');
        $frm->addHiddenField('', 'nlink_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getNavigationLinksLangForm($lang_id)
    {
        $frm = new Form('frmNavigationLink');
        $frm->addHiddenField('', 'nav_id');
        $frm->addHiddenField('', 'nlink_id');
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Caption', $this->adminLangId), 'nlink_caption');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getForm()
    {
        $this->objPrivilege->canViewNavigationManagement();

        $frm = new Form('frmNavigation');
        $frm->addHiddenField('', 'nav_id', 0);
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->adminLangId), 'nav_identifier');
        $fld->setUnique('tbl_navigations', 'nav_identifier', 'nav_id', 'nav_id', 'nav_id');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'nav_active', $activeInactiveArr, '', array(), '');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($nav_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmNavigationLang');
        $frm->addHiddenField('', 'nav_id', $nav_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->adminLangId), 'nav_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }
}
