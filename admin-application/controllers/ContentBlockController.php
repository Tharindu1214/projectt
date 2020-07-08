<?php
class ContentBlockController extends AdminBaseController
{
    const IMPORT_INSTRUCTIONS = 1;

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->rewriteUrl = Extrapage::REWRITE_URL_PREFIX;
    }


    public function getBreadcrumbNodes($action)
    {
        $nodes = array();

        switch ($action) {
            case 'importInstructions':
                $nodes[] = array('title'=>Labels::getLabel('LBL_Import_instructions', $this->adminLangId));
                break;
            case 'index':
                $className = get_class($this);
                $arr = explode('-', FatUtility::camel2dashed($className));
                array_pop($arr);
                $urlController = implode('-', $arr);
                $className = ucwords(implode(' ', $arr));
                $nodes[] = array('title'=>$className);
                break;
            default:
                $nodes[] = array('title' => $action);
                break;
        }
        return $nodes;
    }

    public function index($epage_id = 0)
    {
        $epage_id = FatUtility::int($epage_id);
        $this->objPrivilege->canViewContentBlocks();
        $this->canEdit = $this->objPrivilege->canEditContentBlocks($this->admin_id, true);

        $this->set("canEdit", $this->canEdit);
        $this->set('epage_id', $epage_id);
        $this->set('includeEditor', true);
        $this->_template->render();
    }

    public function importInstructions()
    {
        $this->objPrivilege->canViewImportInstructions();
        $this->set('includeEditor', true);
        $this->_template->render();
    }

    public function search($importInstructions = 0)
    {
        $this->objPrivilege->canViewContentBlocks();

        $srch = Extrapage::getSearchObject($this->adminLangId, false);

        $importInstructions = FatUtility::int($importInstructions);
        $srch->addCondition('epage_content_for', '=', $importInstructions);

        $srch->addOrder('epage_active', 'DESC');
        $srch->addOrder('epage_id', 'DESC');
        $rs = $srch->getResultSet();

        $records = FatApp::getDb()->fetchAll($rs);

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $this->set("activeInactiveArr", $activeInactiveArr);
        $this->set("arr_listing", $records);

        $this->canView = $this->objPrivilege->canViewContentBlocks($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditContentBlocks($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
        $this->set("importInstructions", $importInstructions);

        $this->_template->render(false, false);
    }

    public function form($epage_id = 0)
    {
        $this->objPrivilege->canViewContentBlocks();

        $epage_id = FatUtility::int($epage_id);
        $blockFrm = $this->getForm($epage_id, $this->adminLangId);

        if (0 < $epage_id) {
            $data = Extrapage::getAttributesById($epage_id, array('epage_id','epage_identifier','epage_active'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            /* url data[ */
            $urlRow = UrlRewrite::getDataByOriginalUrl($this->rewriteUrl.$epage_id);
            if (!empty($urlRow)) {
                $data['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }
            /*]*/
            $blockFrm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('epage_id', $epage_id);
        $this->set('blockFrm', $blockFrm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditContentBlocks();

        $frm = $this->getForm(0, $this->adminLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $epage_id = $post['epage_id'];
        if (1 > $epage_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = Extrapage::getAttributesById($epage_id, array('epage_id','epage_identifier'));
        if ($data === false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $record = new Extrapage($epage_id);
        $urlrewrite_custom = $post['urlrewrite_custom'];
        unset($post['urlrewrite_custom']);
        if (!$record->updatePageContent($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* url data[ */
        $originalUrl = $this->rewriteUrl.$epage_id;
        if ($urlrewrite_custom == '') {
            UrlRewrite::remove($originalUrl);
        } else {
            $record->rewriteUrl($urlrewrite_custom);
        }
        /* ] */

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Extrapage::getAttributesByLangId($langId, $epage_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->adminLangId));
        $this->set('epageId', $epage_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($epage_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewContentBlocks();

        $epage_id = FatUtility::int($epage_id);
        $lang_id = FatUtility::int($lang_id);

        if ($epage_id==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $epageData = Extrapage::getAttributesById($epage_id);
        $blockLangFrm = $this->getLangForm($epage_id, $lang_id);
        $langData = Extrapage::getAttributesByLangId($lang_id, $epage_id);

        if ($langData) {
            $blockLangFrm->fill($langData);
        }

        if ($epage_id==Extrapage::SELLER_BANNER_SLOGAN) {
            $fileType = AttachedFile::FILETYPE_SELLER_PAGE_SLOGAN_BG_IMAGE;
        } elseif ($epage_id==Extrapage::ADVERTISER_BANNER_SLOGAN) {
            $fileType = AttachedFile::FILETYPE_ADVERTISER_PAGE_SLOGAN_BG_IMAGE;
        } else {
            $fileType = AttachedFile::FILETYPE_AFFILIATE_PAGE_SLOGAN_BG_IMAGE;
        }

        $bgImages = AttachedFile::getMultipleAttachments($fileType, $epage_id, 0, $lang_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $this->set('bgImages', $bgImages);
        $this->set('bannerTypeArr', $bannerTypeArr);
        $this->set('languages', Language::getAllNames());
        $this->set('epage_id', $epage_id);
        $this->set('epage_lang_id', $lang_id);
        $this->set('blockLangFrm', $blockLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->set('epageData', $epageData);
        $this->set('contentBlockArrWithBg', Extrapage::getContentBlockArrWithBg($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditContentBlocks();
        $post=FatApp::getPostedData();
        $epage_id = FatUtility::int($post['epage_id']);
        $lang_id = FatUtility::int($post['lang_id']);
        if ($epage_id==0 || $lang_id==0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = $this->getLangForm($epage_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJSONError(Message::getHtml());
        }

        unset($post['epage_id']);
        unset($post['lang_id']);
        $data=array(
        'epagelang_lang_id'=>$lang_id,
        'epagelang_epage_id'=>$epage_id,
        'epage_label'=>$post['epage_label'],
        'epage_content'=>$post['epage_content'],
        );

        $epageObj = new Extrapage($epage_id);
        if (!$epageObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($epageObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Extrapage::getAttributesByLangId($langId, $epage_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->adminLangId));
        $this->set('epageId', $epage_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function updateStatusForm($epage_id){
    $this->objPrivilege->canViewContentBlocks();
    $epage_id = FatUtility::int($epage_id);
    if(1 > $epage_id){
    FatUtility::dieWithError($this->str_invalid_request);
    }

    $frm = $this->getUpdateStatusForm();

    $data = Extrapage::getAttributesById($epage_id);

    if (empty($data)) {
    FatUtility::dieWithError($this->str_invalid_request);
    }

    $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);

    $frm->fill($data);
    $this->set('frm',$frm);
    $this->set('activeInactiveArr',$activeInactiveArr);
    $this->_template->render(false, false);
    }

    public function updateStatus(){
    $this->objPrivilege->canEditContentBlocks();

    $frm = $this->getUpdateStatusForm();
    $post = $frm->getFormDataFromArray(FatApp::getPostedData());

    if (false === $post) {
    Message::addErrorMessage(current($frm->getValidationErrors()));
    FatUtility::dieJsonError( Message::getHtml() );
    }

    $epage_id = $post['epage_id'];
    if(1 > $epage_id){
    Message::addErrorMessage($this->str_invalid_request);
    FatUtility::dieJsonError( Message::getHtml() );
    }

    $data = Extrapage::getAttributesById($epage_id);
    if($data === false || $data['epage_default'] == 1){
    Message::addErrorMessage($this->str_invalid_request);
    FatUtility::dieJsonError( Message::getHtml() );
    }

    $obj = new Extrapage($epage_id);
    $assignValues = array(
    'epage_id' =>$epage_id,
    'epage_active' =>$post['epage_active'],
    );

    if(!$obj->updatePageContent($assignValues)){
    Message::addErrorMessage($obj->getError());
    FatUtility::dieJsonError( Message::getHtml() );
    }

    $this->set('msg', Labels::getLabel('LBL_Setup_Successful',$this->adminLangId));
    $this->set('epageId', $epage_id);
    $this->_template->render(false, false, 'json-success.php');
    } */

    public function changeStatus()
    {
        $this->objPrivilege->canEditContentBlocks();
        $epageId = FatApp::getPostedData('epageId', FatUtility::VAR_INT, 0);
        if (0 == $epageId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $contentBlockData = Extrapage::getAttributesById($epageId, array('epage_active'));

        if (!$contentBlockData) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($contentBlockData['epage_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateEPageStatus($epageId, $status);

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditContentBlocks();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $epageIdsArr = FatUtility::int(FatApp::getPostedData('epage_ids'));
        if (empty($epageIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($epageIdsArr as $epageId) {
            if (1 > $epageId) {
                continue;
            }

            $this->updateEPageStatus($epageId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateEPageStatus($epageId, $status)
    {
        $status = FatUtility::int($status);
        $epageId = FatUtility::int($epageId);
        if (1 > $epageId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $EPageObj = new Extrapage($epageId);

        if (!$EPageObj->changeStatus($status)) {
            Message::addErrorMessage($EPageObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    /* private function getUpdateStatusForm(){
    $frm = new Form('frmContentBlock');
    $frm->addHiddenField('', 'epage_id');

    $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
    $frm->addSelectBox(Labels::getLabel('LBL_Status',$this->adminLangId), 'epage_active', $activeInactiveArr, '',array(),'');
    $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes',$this->adminLangId));
    return $frm;
    } */

    private function getForm($epage_id = 0, $langId = 0)
    {
        $this->objPrivilege->canViewContentBlocks();
        $epage_id = FatUtility::int($epage_id);

        $frm = new Form('frmBlock');
        $frm->addHiddenField('', 'epage_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Page_Identifier', $this->adminLangId), 'epage_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_SEO_Friendly_URL', $this->adminLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'epage_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($epage_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmBlockLang');
        $frm->addHiddenField('', 'epage_id', $epage_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Page_Title', $this->adminLangId), 'epage_label');

        if (array_key_exists($epage_id, Extrapage::getContentBlockArrWithBg($this->adminLangId))) {
            if ($epage_id==Extrapage::SELLER_BANNER_SLOGAN) {
                $fileType = AttachedFile::FILETYPE_SELLER_PAGE_SLOGAN_BG_IMAGE;
            } elseif ($epage_id==Extrapage::ADVERTISER_BANNER_SLOGAN) {
                $fileType = AttachedFile::FILETYPE_ADVERTISER_PAGE_SLOGAN_BG_IMAGE;
            } else {
                $fileType = AttachedFile::FILETYPE_AFFILIATE_PAGE_SLOGAN_BG_IMAGE;
            }
            $fld = $frm->addButton(
                Labels::getLabel('LBL_Backgroud_Image', $this->adminLangId),
                'cblock_bg_image',
                Labels::getLabel('LBL_Upload_Image', $this->adminLangId),
                array('class'=>'bgImageFile-Js','id'=>'cblock_bg_image','data-file_type'=>$fileType,'data-frm'=>'frmBlock')
            );
        }

        $frm->addHtmlEditor(Labels::getLabel('LBL_Page_Content', $this->adminLangId), 'epage_content');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    public function setUpBgImage()
    {
        $post = FatApp::getPostedData();
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);
        $epage_id = FatApp::getPostedData('epage_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        if (!$file_type || !$epage_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $allowedFileTypeArr = array(AttachedFile::FILETYPE_SELLER_PAGE_SLOGAN_BG_IMAGE,AttachedFile::FILETYPE_ADVERTISER_PAGE_SLOGAN_BG_IMAGE,AttachedFile::FILETYPE_AFFILIATE_PAGE_SLOGAN_BG_IMAGE);

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage(
            $_FILES['file']['tmp_name'],
            $file_type,
            $epage_id,
            0,
            $_FILES['file']['name'],
            -1,
            $unique_record = true,
            $lang_id,
            $_FILES['file']['type']
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
            // FatUtility::dieJsonError($fileHandlerObj->getError());
        }

        $this->set('file', $_FILES['file']['name']);
        $this->set('epage_id', $epage_id);
        $this->set('file_type', $file_type);
        $this->set('lang_id', $lang_id);
        $this->set('msg', $_FILES['file']['name'].' '.Labels::getLabel('LBL_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeBgImage($epage_id = 0, $langId = 0, $file_type)
    {
        $epage_id = FatUtility::int($epage_id);
        $langId = FatUtility::int($langId);
        if (!$epage_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile($file_type, $epage_id, 0, 0, $langId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
}
