<?php
class SlidesController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewSlides($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditSlides($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewSlides();
        /* $frmSearch = $this->getSearchForm();
        $this->set('frmSearch',$frmSearch); */
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewSlides();
        $post = FatApp::getPostedData();

        /* $searchForm = $this->getSearchForm();
        $post = $searchForm->getFormDataFromArray($post); */

        $srch = Slides::getSearchObject($this->adminLangId, false);
        $srch->addCondition('slide_type', '=', Slides::TYPE_SLIDE);
        $srch->addOrder(Slides::DB_TBL_PREFIX . 'active', 'DESC');
        $srch->addOrder('slide_display_order', 'ASC');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();

        $arrListing =array();
        if ($rs) {
            $arrListing = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arrListing", $arrListing);
        /* $this->set('languages', Language::getAllNames()); */
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function form($slide_id = 0)
    {
        $this->objPrivilege->canViewSlides();

        $slide_id = FatUtility::int($slide_id);
        $slideFrm = $this->getForm();

        if (0 < $slide_id) {
            $data = Slides::getAttributesById($slide_id);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $slideFrm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('slide_id', $slide_id);
        $this->set('slideFrm', $slideFrm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditSlides();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $slide_id = $post['slide_id'];
        $recordId = Slides::getAttributesByIdentifier($post['slide_identifier'], 'slide_id');
        if (!empty($recordId) && $recordId != $slide_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Slide_identifier_must_be_unique', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        unset($post['slide_id']);

        $recordObj = new Slides($slide_id);
        $recordObj->assignValues($post);

        if (!$recordObj->save()) {
            Message::addErrorMessage($recordObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($slide_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Slides::getAttributesByLangId($langId, $slide_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $slide_id = $recordObj->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        if ($newTabLangId == 0 && !$this->isMediaUploaded($slide_id)) {
            $this->set('openMediaForm', true);
        }
        $this->set('msg', $this->str_setup_successful);
        $this->set('slideId', $slide_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($slide_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewSlides();

        $slide_id = FatUtility::int($slide_id);
        $lang_id = FatUtility::int($lang_id);

        if ($slide_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $slideLangFrm = $this->getLangForm($lang_id);
        $langData = Slides::getAttributesByLangId($lang_id, $slide_id);

        $langData['slide_id'] = $slide_id;
        $slideLangFrm->fill($langData);

        $slideBanner = AttachedFile::getAttachment(AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slide_id, 0, $lang_id);
        $this->set('slideBanner', $slideBanner);

        $this->set('languages', Language::getAllNames());
        $this->set('slide_id', $slide_id);
        $this->set('slide_lang_id', $lang_id);
        $this->set('slideLangFrm', $slideLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditSlides();
        $post = FatApp::getPostedData();

        $slide_id = $post['slide_id'];
        $lang_id = $post['lang_id'];

        if ($slide_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['slide_id']);
        unset($post['lang_id']);
        $data = array(
        'slidelang_slide_id'=>$slide_id,
        'slidelang_lang_id'=>$lang_id,
        'slide_title'=>$post['slide_title']
        );

        $slideObj = new Slides($slide_id);
        if (!$slideObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($slideObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Slides::getAttributesByLangId($langId, $slide_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
        if ($newTabLangId == 0 && !$this->isMediaUploaded($slide_id)) {
            $this->set('openMediaForm', true);
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('slideId', $slide_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function mediaForm($slide_id)
    {
        $slide_id = FatUtility::int($slide_id);
        $slideDetail = Slides::getAttributesById($slide_id);
        $slideMediaFrm = $this->getMediaForm($slide_id);
        $screenTypeArr = applicationConstants::getDisplaysArr($this->adminLangId);
        /* CommonHelper::printArray(key($screenTypeArr)); die; */
        $this->set('slide_id', $slide_id);
        $this->set('slideMediaFrm', $slideMediaFrm);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function images($slide_id, $slide_screen = 0, $lang_id = 0)
    {
        $slide_id = FatUtility::int($slide_id);
        $slideDetail = Slides::getAttributesById($slide_id);
        if (false == $slideDetail) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        /* echo $slide_id.' '.$lang_id.' '.$slide_screen; die; */
        if (!false == $slideDetail) {
            $slideBanner = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slide_id, 0, $lang_id, false, $slide_screen);
            $this->set('images', $slideBanner);
        }

        $this->set('slide_id', $slide_id);
        $this->set('bannerTypeArr', $this->bannerTypeArr());
        $this->set('screenTypeArr', $this->getDisplayScreenName());
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditSlides();

        $slide_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($slide_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->markAsDeleted($slide_id);

        $fileHandlerObj = new AttachedFile();
        $fileHandlerObj->deleteFile(AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slide_id);
        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditSlides();
        $slideIdsArr = FatUtility::int(FatApp::getPostedData('slide_ids'));

        if (empty($slideIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($slideIdsArr as $slide_id) {
            if (1 > $slide_id) {
                continue;
            }
            $this->markAsDeleted($slide_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($slide_id)
    {
        $slide_id = FatUtility::int($slide_id);
        if (1 > $slide_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new Slides($slide_id);
        if (!$obj->deleteRecord(true)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function setUpImage($slide_id)
    {
        $this->objPrivilege->canEditSlides();

        $slide_id = FatUtility::int($slide_id);

        if (1 > $slide_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post = FatApp::getPostedData();
        $lang_id = FatUtility::int($post['lang_id']);
        $slide_screen = FatUtility::int($post['slide_screen']);
        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();

        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            AttachedFile::FILETYPE_HOME_PAGE_BANNER,
            $slide_id,
            0,
            $_FILES['file']['name'],
            -1,
            true,
            $lang_id,
            $slide_screen
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        Slides::setLastModified($slide_id);
        $this->set('slideId', $slide_id);
        $fileName = $_FILES['file']['name'];
        $this->set('file', $fileName);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileName = strlen($fileName) > 10 ? substr($fileName, 0, 10).'.'.$ext : $fileName;
        $this->set('msg', $fileName.' '.Labels::getLabel('MSG_File_uploaded_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function setUpImage( $slide_id, $lang_id ){
    $slide_id = FatUtility::int( $slide_id );
    $lang_id = FatUtility::int( $lang_id );
    if( !$slide_id || !$lang_id ){
    Message::addErrorMessage($this->str_invalid_request);
    FatUtility::dieJsonError( Message::getHtml() );
    }

    $post = FatApp::getPostedData();

    if ( !is_uploaded_file($_FILES['file']['tmp_name']) ) {
    Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File',$this->adminLangId));
    FatUtility::dieJsonError(Message::getHtml());
    }

    $fileHandlerObj = new AttachedFile();
    $fileHandlerObj->deleteFile( AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slide_id, 0, 0, $lang_id );
    if(!$res = $fileHandlerObj->saveImage($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slide_id, 0,
    $_FILES['file']['name'], -1, false, $lang_id)
    ){
    Message::addErrorMessage($fileHandlerObj->getError());
    FatUtility::dieJsonError( Message::getHtml() );
    }

    $this->set( 'file', $_FILES['file']['name'] );
    $this->set( 'slide_id', $slide_id );
    $this->set('msg', $_FILES['file']['name'].' '.Labels::getLabel('LBL_Uploaded_Successfully',$this->adminLangId));
    $this->_template->render(false, false, 'json-success.php');
    } */

    public function removeImage($slide_id, $lang_id, $screen)
    {
        $slide_id = FatUtility::int($slide_id);
        $lang_id = FatUtility::int($lang_id);
        if (1 > $slide_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slide_id, 0, 0, $lang_id, $screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateOrder()
    {
        $this->objPrivilege->canEditSlides();

        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $slideObj = new Slides();
            if (!$slideObj->updateOrder($post['slideList'])) {
                Message::addErrorMessage($slideObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Order_Updated_Successfully', $this->adminLangId));
        }
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditSlides();
        $slideId = FatApp::getPostedData('slideId', FatUtility::VAR_INT, 0);
        if (0 >= $slideId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = Slides::getAttributesById($slideId, array('slide_id', 'slide_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['slide_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateSlideStatus($slideId, $status);

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditSlides();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $slideIdsArr = FatUtility::int(FatApp::getPostedData('slide_ids'));
        if (empty($slideIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($slideIdsArr as $slideId) {
            if (1 > $slideId) {
                continue;
            }

            $this->updateSlideStatus($slideId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateSlideStatus($slideId, $status)
    {
        $status = FatUtility::int($status);
        $slideId = FatUtility::int($slideId);
        if (1 > $slideId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $obj = new Slides($slideId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    private function isMediaUploaded($slideId)
    {
        if ($attachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slideId, 0)) {
            return true;
        }
        return false;
    }

    private function getForm()
    {
        $this->objPrivilege->canViewSlides();
        $frm = new Form('frmSlide');
        $frm->addHiddenField('', 'slide_id');
        $frm->addHiddenField('', 'slide_type', Slides::TYPE_SLIDE);
        $frm->addRequiredField(Labels::getLabel('LBL_Slide_Identifier', $this->adminLangId), 'slide_identifier');
        
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Slide_URL', $this->adminLangId), 'slide_url');
        $fld->setFieldTagAttribute('placeholder', 'http://');

        $linkTargetsArr = applicationConstants::getLinkTargetsArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Open_In', $this->adminLangId), 'slide_target', $linkTargetsArr, '', array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'slide_active', applicationConstants::getActiveInactiveArr($this->adminLangId), applicationConstants::ACTIVE, array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm($lang_id = 0)
    {
        $frm = new Form('frmSlideLang');
        $frm->addHiddenField('', 'slide_id');
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Slide_Title', $this->adminLangId), 'slide_title');
        // $fld =  $frm->addButton(Labels::getLabel('LBL_Slide_slide_Image',$this->adminLangId),'slide_image',Labels::getLabel('LBL_Upload_File',$this->adminLangId),array('class'=>'slideFile-Js','id'=>'slide_image','data-slide_id'=>''));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    private function getMediaForm($slide_id = 0)
    {
        $frm = new Form('frmSlideMedia');
        $frm->addHiddenField('', 'slide_id', $slide_id);
        $bannerTypeArr = $this->bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $screenArr = applicationConstants::getDisplaysArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel("LBL_Display_For", $this->adminLangId), 'slide_screen', $screenArr, '', array(), '');
        $fld =  $frm->addButton(Labels::getLabel("LBL_Slide_Banner_Image", $this->adminLangId), 'slide_image', Labels::getLabel("LBL_Upload_File", $this->adminLangId), array('class'=>'slideFile-Js','id'=>'slide_image','data-slide_id'=>$slide_id));
        return $frm;
    }

    /* private function getSearchForm(){
    $frm = new Form('frmSlideSearch',array('id'=>'frmSlideSearch'));
    return $frm;
    } */

    private function bannerTypeArr()
    {
        return applicationConstants::bannerTypeArr();
    }

    private function getDisplayScreenName()
    {
        $screenTypesArr = applicationConstants::getDisplaysArr($this->adminLangId);
        return array( 0 => '' ) + $screenTypesArr;
    }
}
