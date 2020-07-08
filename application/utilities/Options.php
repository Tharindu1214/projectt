<?php
trait Options
{
    public function options()
    {
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->addJs('js/jscolor.js');
        $this->_template->addJs('js/jquery.tablednd.js');
        $this->_template->render(true, true);
    }

    private function getSearchForm()
    {
        $frm = new Form('frmOptionSearch', array('id'=>'frmOptionSearch'));
        $frm->addTextBox('', 'keyword');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick'=>'clearOptionSearch();'));
        return $frm;
    }

    public function searchOptions()
    {
        $pagesize=FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $frmSearch = $this->getSearchForm();

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $post = $frmSearch->getFormDataFromArray($data);
        $userId = UserAuthentication::getLoggedUserId();
        $srch = Option::getSearchObject($this->siteLangId);
        if (!empty($post['keyword'])) {
            $condition=$srch->addCondition('o.option_identifier', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('ol.option_name', 'like', '%'.$post['keyword'].'%', 'OR');
        }
        $srch->addCondition('o.option_seller_id', '=', $userId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(array( "o.*", "IFNULL( ol.option_name, o.option_identifier ) as option_name"));

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("ignoreOptionValues", Option::ignoreOptionValues());
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set("frmSearch", $frmSearch);
        $this->_template->render(false, false);
    }

    public function setupOptions()
    {
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $option_id = FatUtility::int($post['option_id']);
        if ($option_id>0) {
            UserPrivilege::canSellerEditOption($option_id, $this->siteLangId);
        }
        unset($post['option_id']);

        $optionObj = new Option($option_id);
        /* if($option_id == 0){
        $displayOrder = $optionObj->getMaxOrder();
        $post['option_display_order'] = $displayOrder;
        } */
        $userId = UserAuthentication::getLoggedUserId();
        $post['option_seller_id'] = $userId;
        $optionObj->assignValues($post);
        if (!$optionObj->save()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Option_Identifier_already_exists', $this->siteLangId));
            /* Message::addErrorMessage($optionObj->getError()); */
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

        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->set('optionId', $option_id);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function optionForm($option_id = 0)
    {
        $option_id = FatUtility::int($option_id);
        if ($option_id>0) {
            UserPrivilege::canSellerEditOption($option_id, $this->siteLangId);
        }
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
        $this->set('langId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function addOptionForm($option_id=0)
    {
        $option_id = FatUtility::int($option_id);
        $frmOptions = $this->getForm($option_id);


        if (0 < $option_id) {
            $optionObj = new Option();
            if ($option_id>0) {
                UserPrivilege::canSellerEditOption($option_id, $this->siteLangId);
            }
            $data = $optionObj->getOption($option_id);

            if ($data === false) {
                FatUtility::dieWithError(
                    Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
                );
            }

            $frmOptions->fill($data);
        }

        $this->set('frmOptions', $frmOptions);
        $this->_template->render(false, false);
    }

    private function getForm($option_id=0)
    {

        /*Used when option created from product form */
        $post = FatApp::getPostedData();
        if (isset($post['product_id']) && $post['product_id']!='') {
            $product_id = FatUtility::int($post['product_id']);
        }

        $option_id = FatUtility::int($option_id);
        if ($option_id>0) {
            UserPrivilege::canSellerEditOption($option_id, $this->siteLangId);
        }

        $optionObj = new Option();
        $frm = new Form('frmOptions', array('id'=>'frmOptions'));
        $frm->addHiddenField('', 'option_id', $option_id);
        $frm->developerTags['colClassPrefix'] = 'col-md-';
        $frm->developerTags['fld_default_col'] = 6;
        $frm->addRequiredField(
            Labels::getLabel('LBL_OPTION_IDENTIFIER', $this->siteLangId),
            'option_identifier'
        );

        $languages = Language::getAllNames();
        foreach ($languages as $langId=>$langName) {
            $fld = $frm->addRequiredField(
                Labels::getLabel('LBL_OPTION_NAME', $this->siteLangId).' '.$langName,
                'option_name'.$langId
            );
            $fld->setWrapperAttribute('class', 'layout--'.Language::getLayoutDirection($langId));
        }

        /* $optionTypeArr = Option::getOptionTypes($this->siteLangId );
        $frm->addSelectBox(Labels::getLabel('LBL_OPTION_TYPE',$this->siteLangId),'option_type',
        $optionTypeArr,'',array('onChange'=>'showHideValues(this)'),'')->requirements()->setRequired();
        */

        $frm->addHiddenField('', 'option_type', Option::OPTION_TYPE_SELECT);

        $yesNoArr = applicationConstants::getYesNoArr($this->siteLangId);
        $frm->addSelectBox(
            Labels::getLabel('LBL_OPTION_HAVE_SEPARATE_IMAGE', $this->siteLangId),
            'option_is_separate_images',
            $yesNoArr,
            0,
            array(),
            ''
        )->requirements()->setRequired();

        $frm->addSelectBox(Labels::getLabel('LBL_Option_is_Color', $this->siteLangId), 'option_is_color', $yesNoArr, 0, array(), '')->requirements()->setRequired();

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('BTN_SAVE_CHANGES', $this->siteLangId));
        if (isset($product_id) && $product_id > 0) {
            $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('BTN_CANCEL', $this->siteLangId), array('onClick'=>'productOptionsForm('.$product_id.')'));
            $fld_submit->attachField($fld_cancel);
        }

        return $frm;
    }

    public function canSetValue()
    {
        $hideBox = false;
        $post = FatApp::getPostedData();
        // var_dump($post);exit;
        $option_type = FatUtility::int($post['optionType']);
        if (in_array($option_type, Option::ignoreOptionValues())) {
            $hideBox = true;
        }
        $this->set('hideBox', $hideBox);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function bulkOptionsDelete()
    {
        $optionId_arr = FatApp::getPostedData('option_id');
        if (is_array($optionId_arr) && count($optionId_arr)) {
            foreach ($optionId_arr as $option_id) {
                $this->deleteOption(FatUtility::int($option_id));
            }
            FatUtility::dieJsonSuccess(
                Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY', $this->siteLangId)
            );
        }
        FatUtility::dieWithError(
            Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
        );
    }

    public function deleteSellerOption()
    {
        $option_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        $this->deleteOption($option_id);

        FatUtility::dieJsonSuccess(
            Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY', $this->siteLangId)
        );
    }

    private function deleteOption($option_id)
    {
        if ($option_id < 1 || empty($option_id)) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }
        if ($option_id > 0) {
            UserPrivilege::canSellerEditOption($option_id, $this->siteLangId);
        }

        $optionObj = new Option($option_id);
        if (!$optionObj->canRecordMarkDelete($option_id)) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($optionObj->isLinkedWithProduct($option_id)) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_This_option_is_linked_with_product', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }

        $optionObj->assignValues(array(Option::tblFld('deleted') => 1));
        if (!$optionObj->save()) {
            Message::addErrorMessage($optionObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function autoCompleteOptions()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId();
        $srch = Option::getSearchObject($this->siteLangId);
        $srch->addOrder('option_identifier');

        $cnd = $srch->addCondition('option_seller_id', '=', $userId);
        $cnd->attachCondition('option_seller_id', '=', 0, 'OR');


        /* $srch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN',
        'optionlang_option_id = option_id AND optionlang_lang_id = ' . FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 1)); */
        $srch->addMultipleFields(array('option_id, option_name, option_identifier'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('option_name', 'LIKE', '%' . $post['keyword']. '%');
            $cnd->attachCondition('option_identifier', 'LIKE', '%'. $post['keyword'] . '%', 'OR');
        }

        $srch->setPageSize($pagesize);
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
