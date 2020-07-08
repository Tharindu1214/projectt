<?php
class OptionsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        /* $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup');
        if(!FatUtility::isAjaxCall() && in_array($action,$ajaxCallArray)){
        die($this->str_invalid_Action);
        }  */
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewOptions($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditOptions($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->_template->addJs('js/jscolor.js');
        $this->_template->addJs('js/import-export.js');
        $this->objPrivilege->canViewOptions();
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmOptionSearch', array('id'=>'frmOptionSearch'));
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit =$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
        $this->objPrivilege->canViewOptions();

        $pagesize=FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $frmSearch = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $post = $frmSearch->getFormDataFromArray($data);

        $srch = Option::getSearchObject($this->adminLangId);
        $srch->joinTable(User::DB_TBL, 'LEFT JOIN', 'u.user_id = option_seller_id', 'u');
        if (!empty($post['keyword'])) {
            $condition=$srch->addCondition('o.option_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('ol.option_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(array( "o.*", "IFNULL( ol.option_name, o.option_identifier ) as option_name, u.user_name"));
        $srch->addOrder('option_id', 'DESC');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("ignoreOptionValues", Option::ignoreOptionValues());
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditOptions();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $option_id = FatUtility::int($post['option_id']);
        unset($post['option_id']);

        $optionObj = new Option($option_id);
        /* if($option_id == 0){
        $displayOrder = $optionObj->getMaxOrder();
        $post['option_display_order'] = $displayOrder;
        } */

        $optionObj->assignValues($post);
        if (!$optionObj->save()) {
            Message::addErrorMessage($optionObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $option_id = ($option_id > 0)?$option_id:$optionObj->getMainTableRecordId();

        $option_type=FatUtility::int($post['option_type']);

        if (in_array($option_type, Option::ignoreOptionValues())) {
            $optionValueObj = new OptionValue();
            $arr=$optionValueObj->getAtttibutesByOptionId($option_id, array('optionvalue_id'));
            foreach ($arr as $val) {
                $optionValueObj = new OptionValue($val['optionvalue_id']);
                $optionValueObj->deleteRecord(true);
            }
        }

        $languages = Language::getAllNames();
        foreach ($languages as $langId=>$langName) {
            $data=array(
            'optionlang_lang_id' => $langId,
            'optionlang_option_id' => $option_id,
            'option_name' => $post['option_name'.$langId],
            );

            if (!$optionObj->updateLangData($langId, $data)) {
                Message::addErrorMessage($optionObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        if ($option_id>0) {
            $msg = Labels::getLabel('MSG_UPDATED_SUCCESSFULLY', $this->adminLangId);
        } else {
            $msg = Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->adminLangId);
        }
        Product::updateMinPrices();
        $this->set('msg', $msg);
        $this->set('optionId', $option_id);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function form($option_id = 0)
    {
        $this->objPrivilege->canEditOptions();

        $option_id = FatUtility::int($option_id);
        $hideListBox = false;

        if (0 < $option_id) {
            $optionObj = new Option();
            $data = $optionObj->getOption($option_id);

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            if (in_array($data['option_type'], Option::ignoreOptionValues())) {
                $hideListBox = true;
            }
        }

        $this->set('option_id', $option_id);
        $this->set('hideListBox', $hideListBox);
        $this->set('langId', $this->adminLangId);
        $this->_template->render(false, false);
    }

    public function addForm($option_id=0)
    {
        $this->objPrivilege->canEditOptions();

        $option_id = FatUtility::int($option_id);
        $frmOptions = $this->getForm($option_id);


        if (0 < $option_id) {
            $optionObj = new Option();
            $data = $optionObj->getOption($option_id);

            if ($data === false) {
                FatUtility::dieWithError(
                    Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
                );
            }

            $frmOptions->fill($data);
        }

        $this->set('frmOptions', $frmOptions);
        $this->_template->render(false, false);
    }

    private function getForm($option_id=0)
    {
        $this->objPrivilege->canEditOptions();

        /*Used when option created from product form */
        $post = FatApp::getPostedData();
        if (isset($post['product_id']) && $post['product_id']!='') {
            $product_id = FatUtility::int($post['product_id']);
        }

        $option_id = FatUtility::int($option_id);
        $adminLangId = $this->adminLangId;

        $optionObj = new Option();
        $frm = new Form('frmOptions', array('id'=>'frmOptions'));
        $frm->addHiddenField('', 'option_id', $option_id);

        $frm->addRequiredField(
            Labels::getLabel('LBL_OPTION_IDENTIFIER', $adminLangId),
            'option_identifier'
        );

        $languages = Language::getAllNames();
        foreach ($languages as $langId=>$langName) {
            $fld = $frm->addRequiredField(
                Labels::getLabel('LBL_OPTION_NAME', $adminLangId).' '.$langName,
                'option_name'.$langId
            );
            $fld->setWrapperAttribute('class', 'layout--'.Language::getLayoutDirection($langId));
        }

        $frm->addHiddenField('', 'option_type', Option::OPTION_TYPE_SELECT);

        $yesNoArr = applicationConstants::getYesNoArr($adminLangId);
        $frm->addSelectBox(
            Labels::getLabel('LBL_OPTION_HAVE_SEPARATE_IMAGE', $adminLangId),
            'option_is_separate_images',
            $yesNoArr,
            0,
            array(),
            ''
        )->requirements()->setRequired();

        $frm->addSelectBox(Labels::getLabel('LBL_Option_is_Color', $adminLangId), 'option_is_color', $yesNoArr, 0, array(), '')->requirements()->setRequired();

        $frm->addSelectBox(Labels::getLabel('LBL_Option_display_in_filters', $adminLangId), 'option_display_in_filter', $yesNoArr, 0, array(), '')->requirements()->setRequired();

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('BTN_SAVE_CHANGES', $adminLangId));
        if (isset($product_id) && $product_id > 0) {
            $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('BTN_CANCEL', $adminLangId), array('onClick'=>'productOptionsForm('.$product_id.')'));
            $fld_submit->attachField($fld_cancel);
        }

        return $frm;
    }

    public function canSetValue()
    {
        $hideBox = false;
        $post = FatApp::getPostedData();
        $option_type = FatUtility::int($post['optionType']);
        if (in_array($option_type, Option::ignoreOptionValues())) {
            $hideBox = true;
        }
        $this->set('hideBox', $hideBox);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditOptions();

        $option_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($option_id < 1) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->adminLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($option_id);
        Product::updateMinPrices();
        $this->set('msg', Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditOptions();
        $optionIdsArr = FatUtility::int(FatApp::getPostedData('option_ids'));

        if (empty($optionIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($optionIdsArr as $option_id) {
            if (1 > $option_id) {
                continue;
            }
            $this->markAsDeleted($option_id);
        }
        Product::updateMinPrices();
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($option_id)
    {
        $optionObj = new Option($option_id);
        if (!$optionObj->canRecordMarkDelete($option_id)) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->adminLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($optionObj->isLinkedWithProduct($option_id)) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_This_option_is_linked_with_product', $this->adminLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }

        $optionObj->assignValues(array(Option::tblFld('deleted') => 1));
        if (!$optionObj->save()) {
            Message::addErrorMessage($optionObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function autoComplete()
    {
        /* $pagesize = 10; */
        $post = FatApp::getPostedData();
        $this->objPrivilege->canViewOptions();

        $srch = Option::getSearchObject($this->adminLangId);
        $srch->addOrder('option_identifier');
        $srch->addMultipleFields(array('option_id, option_name, option_identifier'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('option_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('option_identifier', 'LIKE', '%'. $post['keyword'] . '%', 'OR');
        }

        /* $srch->setPageSize($pagesize); */
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $options = $db->fetchAll($rs, 'option_id');

        $json = array();
        foreach ($options as $key => $option) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($option['option_name'], ENT_QUOTES, 'UTF-8')),
            'option_identifier'    => strip_tags(html_entity_decode($option['option_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }
}
