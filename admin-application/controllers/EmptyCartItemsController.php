<?php
class EmptyCartItemsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewEmptyCartItems($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditEmptyCartItems($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewEmptyCartItems();
        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewEmptyCartItems();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = EmptyCartItems::getSearchObject($this->adminLangId, false);

        if (!empty($post['keyword'])) {
            $srch->addCondition('emptycartitem_identifier', 'like', '%'.$post['keyword'].'%');
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('emptycartitem_id', 'DESC');
        $rs = $srch->getResultSet();

        $arrListing =array();
        if ($rs) {
            $arrListing = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arrListing", $arrListing);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function form($emptycartitem_id = 0)
    {
        $this->objPrivilege->canViewEmptyCartItems();

        $emptycartitem_id = FatUtility::int($emptycartitem_id);
        $emptyCartItemFrm = $this->getForm($emptycartitem_id);

        if (0 < $emptycartitem_id) {
            $data = EmptyCartItems::getAttributesById($emptycartitem_id);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $emptyCartItemFrm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('emptycartitem_id', $emptycartitem_id);
        $this->set('emptyCartItemFrm', $emptyCartItemFrm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditEmptyCartItems();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $emptycartitem_id = $post['emptycartitem_id'];
        unset($post['emptycartitem_id']);

        $record = new EmptyCartItems($emptycartitem_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($emptycartitem_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = EmptyCartItems::getAttributesByLangId($langId, $emptycartitem_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $emptycartitem_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('emptycartitemId', $emptycartitem_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($emptycartitem_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewEmptyCartItems();

        $emptycartitem_id = FatUtility::int($emptycartitem_id);
        $lang_id = FatUtility::int($lang_id);

        if ($emptycartitem_id == 0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $emptyCartItemLangFrm = $this->getLangForm($lang_id);
        $langData = EmptyCartItems::getAttributesByLangId($lang_id, $emptycartitem_id);


        $langData['emptycartitem_id'] = $emptycartitem_id;
        $emptyCartItemLangFrm->fill($langData);

        $this->set('languages', Language::getAllNames());
        $this->set('emptycartitem_id', $emptycartitem_id);
        $this->set('emptycartitem_lang_id', $lang_id);
        $this->set('emptyCartItemLangFrm', $emptyCartItemLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditEmptyCartItems();
        $post=FatApp::getPostedData();

        $emptycartitem_id = $post['emptycartitem_id'];
        $lang_id = $post['lang_id'];

        if ($emptycartitem_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['emptycartitem_id']);
        unset($post['lang_id']);
        $data = array(
        'emptycartitemlang_emptycartitem_id'=>$emptycartitem_id,
        'emptycartitemlang_lang_id'=>$lang_id,
        'emptycartitem_title'=>$post['emptycartitem_title']
        );

        $emptyCartItemObj = new EmptyCartItems($emptycartitem_id);
        if (!$emptyCartItemObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($emptyCartItemObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = EmptyCartItems::getAttributesByLangId($langId, $emptycartitem_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('emptycartitemId', $emptycartitem_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditEmptyCartItems();

        $emptycartitem_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($emptycartitem_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($emptycartitem_id);
        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditEmptyCartItems();
        $emptyCartItemIdsArr = FatUtility::int(FatApp::getPostedData('emptycartitem_ids'));

        if (empty($emptyCartItemIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($emptyCartItemIdsArr as $emptycartitem_id) {
            if (1 > $emptycartitem_id) {
                continue;
            }
            $this->markAsDeleted($emptycartitem_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($emptycartitem_id)
    {
        $emptycartitem_id = FatUtility::int($emptycartitem_id);
        if (1 > $emptycartitem_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new EmptyCartItems($emptycartitem_id);
        if (!$obj->canRecordMarkDelete($emptycartitem_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$obj->deleteRecord(true)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditEmptyCartItems();
        $emptycartitemId = FatApp::getPostedData('emptycartitemId', FatUtility::VAR_INT, 0);
        if (0 >= $emptycartitemId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = EmptyCartItems::getAttributesById($emptycartitemId, array( 'emptycartitem_id', 'emptycartitem_active' ));

        if ($data==false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['emptycartitem_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateEmptyCartItemStatus($emptycartitemId, $status);

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditEmptyCartItems();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $emptyCartItemIdsArr = FatUtility::int(FatApp::getPostedData('emptycartitem_ids'));
        if (empty($emptyCartItemIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($emptyCartItemIdsArr as $emptycartitemId) {
            if (1 > $emptycartitemId) {
                continue;
            }

            $this->updateEmptyCartItemStatus($emptycartitemId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateEmptyCartItemStatus($emptycartitemId, $status)
    {
        $status = FatUtility::int($status);
        $emptycartitemId = FatUtility::int($emptycartitemId);
        if (1 > $emptycartitemId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $emptyCartItemObj = new EmptyCartItems($emptycartitemId);
        if (!$emptyCartItemObj->changeStatus($status)) {
            Message::addErrorMessage($emptyCartItemObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    private function getForm()
    {
        $this->objPrivilege->canViewEmptyCartItems();
        $frm = new Form('frmEmptyCartItem');
        $frm->addHiddenField('', 'emptycartitem_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Empty_Cart_Item_Identifier', $this->adminLangId), 'emptycartitem_identifier');
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Empty_Cart_Item_URL', $this->adminLangId), 'emptycartitem_url');
        $fld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Prefix_with_{SITEROOT},_if_needs_to_generate_system\'s_url.', $this->adminLangId).'</small>';
        $frm->addSelectBox(Labels::getLabel('LBL_Open_Link_in_New_Tab', $this->adminLangId), 'emptycartitem_url_is_newtab', applicationConstants::getYesNoArr($this->adminLangId), applicationConstants::NO, array(), '');
        $frm->addIntegerField(Labels::getLabel('LBL_Display_Order', $this->adminLangId), 'emptycartitem_display_order');
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'emptycartitem_active', applicationConstants::getActiveInactiveArr($this->adminLangId), applicationConstants::ACTIVE, array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($lang_id = 0)
    {
        $frm = new Form('frmEmptyCartItemLang');
        $frm->addHiddenField('', 'emptycartitem_id');
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Empty_Cart_Item_Title', $this->adminLangId), 'emptycartitem_title');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    private function getSearchForm()
    {
        $frm = new Form('frmEmptyCartItemSearch', array('id'=>'frmEmptyCartItemSearch'));
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit =$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
