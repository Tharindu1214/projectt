<?php
class LabelsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('form','search','setup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewLanguageLabels($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditLanguageLabels($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewLanguageLabels();
        $frmSearch = $this->getSearchForm();
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewLanguageLabels();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        //$page = ( empty($data['page']) || $data['page'] <= 0 ) ? 1 : $data['page'];
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $post = $searchForm->getFormDataFromArray($data);

        $srch = Labels::getSearchObject();
        $srch->joinTable('tbl_languages', 'inner join', 'label_lang_id = language_id and language_active = ' .applicationConstants::ACTIVE);
        $srch->addOrder('lbl.' . Labels::DB_TBL_PREFIX . 'lang_id', 'ASC');
        $srch->addGroupBy('lbl.' . Labels::DB_TBL_PREFIX . 'key');
        $srch->addGroupBy('lbl.' . Labels::DB_TBL_PREFIX . 'id', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $type = FatApp::getPostedData('label_type', FatUtility::VAR_INT, -1);
        if ($type > -1) {
            $srch->addCondition('label_type', '=', $type);
        }

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('lbl.label_key', 'like', '%'.$post['keyword'].'%', 'AND');
            $cond->attachCondition('lbl.label_caption', 'like', '%'.$post['keyword'].'%', 'OR');
        }
        $srch->addCondition('lbl.label_lang_id', '=', $this->adminLangId);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function form($label_id, $labelType = Labels::TYPE_WEB)
    {
        $this->objPrivilege->canViewLanguageLabels();

        $labelTypeArr = Labels::getTypeArr($this->adminLangId);

        if (!array_key_exists($labelType, $labelTypeArr)) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $label_id = FatUtility::int($label_id);

        if ($label_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $data =  Labels::getAttributesById($label_id, array('label_key'));
        if ($data ==  false) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $labelKey = $data['label_key'];

        $frm = $this->getForm($labelKey, $labelType);

        $srch = Labels::getSearchObject();
        $srch->addCondition('lbl.label_key', '=', $labelKey);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $record = array();

        if ($rs) {
            $record = FatApp::getDb()->fetchAll($rs, 'label_lang_id');
        }

        if ($record ==  false) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $arr = array();

        foreach ($record as $k => $v) {
            $arr['label_key'] = $v['label_key'];
            $arr['label_caption'.$k] = $v['label_caption'];
        }

        $arr['label_type'] = $labelType;
        $frm->fill($arr);

        $this->set('labelKey', $labelKey);
        $this->set('frm', $frm);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditLanguageLabels();
        $data = FatApp::getPostedData();

        $frm = $this->getForm($data['label_key'], $data['label_type']);
        $post = $frm->getFormDataFromArray($data);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $labelKey = $post['label_key'];
        $labelType = FatApp::getPostedData('label_type', FatUtility::VAR_INT, Labels::TYPE_WEB);
        $labelTypeArr = Labels::getTypeArr($this->adminLangId);

        if (!array_key_exists($labelType, $labelTypeArr)) {
            FatUtility::dieJsonError($this->str_invalid_request);
        }

        $srch = Labels::getSearchObject();
        $srch->addCondition('lbl.label_key', '=', $labelKey);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();

        $record = FatApp::getDb()->fetchAll($rs, 'label_lang_id');
        if ($record == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            $keyValue = strip_tags(trim($post['label_caption'.$langId]));
            $data = array(
                'label_lang_id'=>$langId,
                'label_key'=>$labelKey,
                'label_caption'=>$keyValue,
                'label_type'=>$labelType,
            );
            $obj = new Labels();
            if (!$obj->addUpdateData($data)) {
                Message::addErrorMessage($obj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }

            if (Labels::isAPCUcacheAvailable()) {
                $cacheKey = Labels::getAPCUcacheKey($labelKey, $langId);
                apcu_store($cacheKey, $keyValue);
            }
        }
        $this->updateJsonFile(Labels::TYPE_WEB);
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function export()
    {
        $adminLangId = $this->adminLangId;
        $this->objPrivilege->canViewLanguageLabels();
        $searchForm = $this->getSearchForm();
        $post = $searchForm->getFormDataFromArray(FatApp::getPostedData());

        $srch = new SearchBase(Labels::DB_TBL, 'lbl');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->joinTable(Language::DB_TBL, 'INNER JOIN', 'label_lang_id = language_id AND language_active = '. applicationConstants::ACTIVE);
        $srch->addOrder('label_key', 'DESC');
        $srch->addOrder('label_lang_id', 'ASC');
        $srch->addMultipleFields(array( 'label_id', 'label_key', 'label_lang_id', 'label_caption' ));

        $rs = $srch->getResultSet();

        $langSrch = Language::getSearchObject();
        $langSrch->doNotCalculateRecords();
        $langSrch->addMultipleFields(array( 'language_id', 'language_code', 'language_name' ));
        $langSrch->addOrder('language_id', 'ASC');
        $langRs = $langSrch->getResultSet();
        $languages = FatApp::getDb()->fetchAll($langRs);
        $sheetData = array();

        /* Sheet Heading Row[ */
        $arr = array( Labels::getLabel('LBL_Key', $adminLangId) );
        if ($languages) {
            foreach ($languages as $lang) {
                array_push($arr, $lang['language_code']);
            }
        }
        array_push($sheetData, $arr);
        /* ] */

        $db = FatApp::getDb();

        $key = '';
        $counter = 0;
        $arr = array();
        $langArr = array();

        while ($row = $db->fetch($rs)) {
            if ($key != $row['label_key']) {
                if (!empty($langArr)) {
                    $arr[$counter] = array('label_key' => $key );
                    foreach ($langArr as $k=>$val) {
                        if (is_array($val)) {
                            foreach ($val as $key=>$v) {
                                $val[$key] = htmlentities($v);
                            }
                        }
                        $arr[$counter]['data'] = $val;
                    }
                    $counter++;
                }
                $key = $row['label_key'];
                $langArr = array();
                foreach ($languages as $lang) {
                    $langArr[$key][$lang['language_id']]  = '';
                }
                $langArr[$key][$row['label_lang_id']] = $row['label_caption'] ;
            } else {
                $langArr[$key][$row['label_lang_id']] = $row['label_caption'] ;
            }
        }

        foreach ($arr as $a) {
            $sheetArr = array();
            $sheetArr = array( $a['label_key'] );
            if (!empty($a['data'])) {
                foreach ($a['data'] as $langId=>$caption) {
                    array_push($sheetArr, html_entity_decode($caption));
                }
            }
            array_push($sheetData, $sheetArr);
        }

        CommonHelper::convertToCsv($sheetData, 'Labels_'.date("d-M-Y").'.csv', ',');
        exit;
    }

    public function importLabelsForm()
    {
        $this->objPrivilege->canEditLanguageLabels();
        $frm = $this->getImportLabelsForm();
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function uploadLabelsImportedFile()
    {
        $this->objPrivilege->canEditLanguageLabels();

        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $post = FatApp::getPostedData();

        if (!in_array($_FILES['import_file']['type'], CommonHelper::isCsvValidMimes())) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();

        /* All Languages[  */
        $langSrch = Language::getSearchObject();
        $langSrch->doNotCalculateRecords();
        $langSrch->addMultipleFields(array( 'language_id', 'language_code', 'language_name' ));
        $langSrch->addOrder('language_id', 'ASC');
        $langRs = $langSrch->getResultSet();
        $languages = $db->fetchAll($langRs, 'language_code');
        /* ] */

        $csvFilePointer = fopen($_FILES['import_file']['tmp_name'], 'r');

        $firstLine = fgetcsv($csvFilePointer);
        array_shift($firstLine);
        $firstLineLangArr = $firstLine;
        $langIndexLangIds = array();
        foreach ($firstLineLangArr as $key => $langCode) {
            if (!array_key_exists($langCode, $languages)) {
                Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Coloum_CSV_File", $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }

            $langIndexLangIds[$key] = $languages[$langCode]['language_id'];
        }
        /* CommonHelper::printArray($langIndexLangIds); die(); */
        while (($line = fgetcsv($csvFilePointer)) !== false) {
            if ($line[0] != '') {
                $labelKey = array_shift($line);
                foreach ($line as $key => $caption) {
                    $sql = "SELECT label_key FROM ". Labels::DB_TBL ." WHERE label_key = " . $db->quoteVariable($labelKey). " AND label_lang_id = " .  $langIndexLangIds[$key];
                    $rs = $db->query($sql);
                    if ($row = $db->fetch($rs)) {
                        $db->updateFromArray(Labels::DB_TBL, array( 'label_caption' => $caption ), array('smt' => 'label_key = ? AND label_lang_id = ?', 'vals' => array( $labelKey, $langIndexLangIds[$key] ) ));
                    } else {
                        $dataToSaveArr = array(
                        'label_key'        =>    $labelKey,
                        'label_lang_id'    =>    $langIndexLangIds[$key],
                        'label_caption'    =>    $caption,
                        );
                        $db->insertFromArray(Labels::DB_TBL, $dataToSaveArr);
                    }
                }
            }
        }

        $labelsUpdatedAt = array('conf_name'=>'CONF_LANG_LABELS_UPDATED_AT','conf_val'=>time());
        FatApp::getDb()->insertFromArray('tbl_configurations', $labelsUpdatedAt, false, array(), $labelsUpdatedAt);

        Message::addMessage(Labels::getLabel('LBL_Labels_data_imported/updated_Successfully', $this->adminLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    private function getImportLabelsForm()
    {
        $frm = new Form('frmImportLabels', array('id' => 'frmImportLabels'));
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_File_to_be_uploaded:', $this->adminLangId), 'import_file', array('id' => 'import_file'));
        $fldImg->setFieldTagAttribute('onChange', '$(\'#importFileName\').html(this.value)');
        $fldImg->htmlBeforeField='<div class="filefield"><span class="filename" id="importFileName"></span>';
        $fldImg->htmlAfterField='<label class="filelabel">'.Labels::getLabel('LBL_Browse_File', $this->adminLangId).'</label></div><br/><small>'.nl2br(Labels::getLabel('LBL_Import_Labels_Instructions', $this->adminLangId)).'</small>';

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Import', $this->adminLangId));

        return $frm;
    }

    private function getSearchForm()
    {
        $this->objPrivilege->canViewLanguageLabels();
        $frm = new Form('frmLabelsSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->adminLangId), 'label_type', array('-1'=>Labels::getLabel('LBL_Does_Not_Matter', $this->adminLangId)) + Labels::getTypeArr($this->adminLangId), -1, array(), '');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getForm($label_key, $label_type)
    {
        $this->objPrivilege->canViewLanguageLabels();
        $frm = new Form('frmLabels');
        $frm->addHiddenField('', 'label_key', $label_key);
        $frm->addHiddenField('', 'label_type', $label_type);
        $languages = Language::getAllNames();
        $frm->addTextbox(Labels::getLabel('LBL_Key', $this->adminLangId), 'key', $label_key);
        foreach ($languages as $langId => $langName) {
            //$frm->addRequiredField($langName,'label_caption'.$langId);
            $fld = null;
            $fld = $frm->addTextArea($langName, 'label_caption'.$langId);
            $fld->requirements()->setRequired();
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function updateJsonFile($labelType = Labels::TYPE_WEB)
    {
        $languages = Language::getAllCodesAssoc();
        foreach ($languages as $langId => $langCode) {
            $resp = Labels::updateDataToFile($langId, $langCode, $labelType, true);
            if ($resp === false) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Unable_to_update_file', $this->adminLangId));
            }
        }
        $message = Labels::getLabel('MSG_File_successfully_updated', $this->adminLangId);
        FatUtility::dieJsonSuccess($message);
    }
}
