<?php
class OptionValuesController extends LoggedUserController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->_template->addJs('js/jscolor.js');
    }

    public function search()
    {
        $post = FatApp::getPostedData();
        $option_id=FatUtility::int($post['option_id']);
        if ($option_id <= 0) {
            FatUtility::dieWithError($this->str_invalid_request_id);
        }

        $srch = OptionValue::getSearchObject();
        $srch->addFld('ov.*');
        $srch->addCondition('ov.optionvalue_option_id', '=', $option_id);

        $srch->joinTable(
            OptionValue::DB_TBL . '_lang',
            'LEFT OUTER JOIN',
            'ovl.optionvaluelang_optionvalue_id = ov.optionvalue_id
		AND ovl.optionvaluelang_lang_id = ' . $this->siteLangId,
            'ovl'
        );

        $srch->addMultipleFields(array("ovl.optionvalue_name"));
        $srch->addOrder('ov.optionvalue_id', 'ASC');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set("langId", $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $frm = $this->getForm();
        //$post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $option_id = FatUtility::int($post['optionvalue_option_id']);
        if ($option_id>0) {
            UserPrivilege::canSellerEditOption($option_id, $this->siteLangId);
        }
        $optionvalue_id = FatUtility::int($post['optionvalue_id']);
        unset($post['optionvalue_id']);

        if (0 < $optionvalue_id) {
            $optionValueObj= new OptionValue();
            $data = $optionValueObj->getAtttibutesByIdAndOptionId($option_id, $optionvalue_id, array('optionvalue_id'));

            if ($data === false) {
                Message::addErrorMessage(
                    Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId)
                );
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $optionValueObj = new OptionValue($optionvalue_id);
        $optionValueObj->assignValues($post);

        if (!$optionValueObj->save()) {
            Message::addErrorMessage($optionValueObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $languages=Language::getAllNames();
        foreach ($languages as $langId=>$langName) {
            $data=array(
            'optionvaluelang_optionvalue_id'=>$optionvalue_id,
            'optionvaluelang_lang_id'=>$langId,
            'optionvalue_name'=>$post['optionvalue_name'.$langId],
            );
            if (!$optionValueObj->updateLangData($langId, $data)) {
                Message::addErrorMessage($optionValueObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $this->set('msg', Labels::getLabel('MSG_SETUP_SUCCESSFULLY', $this->siteLangId));
        $this->set('optionId', $option_id);
        $this->set('optionValueId', $optionvalue_id);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function form($option_id, $optionvalue_id = 0)
    {
        $option_id = FatUtility::int($option_id);
        if ($option_id <= 0) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId)
            );
        } else {
            if (!$row = UserPrivilege::canSellerEditOption($option_id, $this->siteLangId)) {
                Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        $optionvalue_id = FatUtility::int($optionvalue_id);
        $optionValueFrm = $this->getForm($option_id, $optionvalue_id);


        if (0 < $optionvalue_id) {
            $optionValueObj= new OptionValue($optionvalue_id);
            $data = $optionValueObj->getOptionValue($option_id);

            if ($data === false) {
                FatUtility::dieWithError(
                    Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
                );
            }
            $optionValueFrm->fill($data);
        }

        $this->set('optionValueFrm', $optionValueFrm);
        $this->set('langId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    private function getForm($option_id=0, $optionvalue_id=0)
    {
        $option_id = FatUtility::int($option_id);
        $optionvalue_id = FatUtility::int($optionvalue_id);

        $lang_id = $this->siteLangId;

        $frm = new Form('frmOptionValues', array('id'=>'frmOptionValues'));
        $frm->addHiddenField('', 'optionvalue_id', $optionvalue_id);
        $frm->addHiddenField('', 'optionvalue_option_id', $option_id);
        $frm->addRequiredField(Labels::getLabel('LBL_OPTION_VALUE_IDENTIFIER', $lang_id), 'optionvalue_identifier');

        $languages = Language::getAllNames();
        foreach ($languages as $langId=>$langName) {
            $frm->addRequiredField(Labels::getLabel('LBL_OPTION_VALUE_NAME', $lang_id).' '.$langName, 'optionvalue_name'.$langId);
        }

        $optionRow = Option::getAttributesById($option_id);
        if ($optionRow && $optionRow['option_is_color']) {
            $fld = $frm->addTextBox(Labels::getLabel('LBL_Option_Value_Color', $this->siteLangId), 'optionvalue_color_code');
            $fld->addFieldTagAttribute('class', 'jscolor');
        }

        $fld = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $lang_id));
        $fld_cancel = $frm->addButton(
            "",
            "btn_clear",
            Labels::getLabel('LBL_CANCEL', $lang_id),
            array('onclick'=>'optionForm('.$option_id.');')
        );

        $fld->attachField($fld_cancel);
        return $frm;
    }

    public function deleteRecord()
    {
        $optionvalue_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        $option_id = FatApp::getPostedData('option_id', FatUtility::VAR_INT, 0);

        if ($optionvalue_id < 1 || $option_id < 1) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        } else {
            UserPrivilege::canSellerEditOption($option_id, $this->siteLangId);
        }
        $optionValueObj = new OptionValue($optionvalue_id);
        if (!$optionValueObj->canEditRecord($option_id)) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($optionValueObj->isLinkedWithInventory($optionvalue_id)) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_This_option_value_is_linked_with_inventory', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if (!$optionValueObj->deleteRecord()) {
            Message::addErrorMessage($optionValueObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess(
            Labels::getLabel('MSG_RECORD_DELETED', $this->siteLangId)
        );
    }

    public function setOptionsOrder()
    {
        $post=FatApp::getPostedData();
        if (!empty($post)) {
            $obj = new OptionValue();
            if (!$obj->updateOrder($post['optionvalues'])) {
                Message::addErrorMessage($obj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            /* FatUtility::dieJsonSuccess(
            Labels::getLabel('LBL_Order_Updated_Successfully',$this->siteLangId)
            );     */
            $this->set('msg', Labels::getLabel('LBL_Order_Updated_Successfully', $this->siteLangId));
            $this->_template->render(false, false, 'json-success.php');
        }
    }
}
