<?php
class BannersController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewBanners($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditBanners($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewBanners();
        /* $frmSearch = $this->getSearchForm();
        $this->set('frmSearch',$frmSearch);     */
        $this->_template->render();
    }

    public function layouts()
    {
        $this->_template->render(false, false);
    }

    public function search()
    {
        $this->objPrivilege->canViewBanners();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $post = FatApp::getPostedData();
        /* $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data); */

        $srch = BannerLocation::getSearchObject($this->adminLangId, false);
        $srch->addMultipleFields(array('blocation_banner_count','blocation_banner_width','blocation_banner_height','blocation_id','blocation_promotion_cost','blocation_active',"IFNULL(blocation_name,blocation_identifier) as blocation_name"));

        $srch->addOrder(Banner::DB_TBL_LOCATIONS_PREFIX . 'active', 'DESC');
        $srch->addOrder(Banner::DB_TBL_LOCATIONS_PREFIX . 'id', 'DESC');
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function bannerLocation($bLocationId = 0)
    {
        $this->objPrivilege->canViewBanners();
        $bLocationId = FatUtility::int($bLocationId);
        if (1 > $bLocationId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $frm = $this->getLocationForm();

        $data =  $this->getBannerLocationById($bLocationId);
        /* $srch = Banner::getBannerLocationSrchObj(false);
        $srch->addCondition('blocation_id','=',$bLocationId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $data = array();
        if($rs){
        $data = FatApp::getDb()->fetch($rs);
        } */

        if (empty($data)) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->fill($data);
        $this->set('languages', Language::getAllNames());
        $this->set('frm', $frm);
        $this->set('bLocationId', $bLocationId);
        $this->set('activeInactiveArr', $activeInactiveArr);
        $this->_template->render(false, false);
    }

    public function setupLocation()
    {
        $this->objPrivilege->canEditBanners();

        $frm = $this->getLocationForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $bLocationId = $post['blocation_id'];
        if (1 > $bLocationId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $bannerObj = new Banner();
        if (!$bannerObj->updateLocationData($post)) {
            Message::addErrorMessage($bannerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('bLocationId', $bLocationId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function listing($bLocationId)
    {
        $bLocationId = FatUtility::int($bLocationId);
        if (1 > $bLocationId) {
            FatUtility::dieJsonError($this->str_invalid_request);
        }

        $frmSearch = $this->getListingSearchForm();
        $frmSearch->fill(array('blocation_id'=>$bLocationId));

        $data = $this->getBannerLocationById($bLocationId);

        /* $srch = Banner::getBannerLocationSrchObj(false);
        $srch->addCondition('blocation_id','=',$bLocationId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $data = array();
        if($rs){
        $data = FatApp::getDb()->fetch($rs);
        } */
        $this->_template->addJs('js/responsive-img.min.js');
        $this->set('data', $data);
        $this->set('bLocationId', $bLocationId);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function listingSearch()
    {
        $this->objPrivilege->canViewBanners();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getListingSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $blocation_id = $post['blocation_id'];
        if (1 > $blocation_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $srch = new BannerSearch($this->adminLangId, false);
        $srch->joinLocations();
        $srch->joinPromotions($this->adminLangId, true);
        $srch->addPromotionTypeCondition();
        $srch->addMultipleFields(array('IFNULL(promotion_name,promotion_identifier) as promotion_name','banner_id','banner_type','banner_url','banner_target','banner_active','banner_blocation_id','banner_title','banner_img_updated_on'));
        $srch->addCondition('b.banner_blocation_id', '=', $blocation_id);

        $srch->addOrder('banner_active', 'DESC');
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('bannerTypeArr', Banner::getBannerTypesArr($this->adminLangId));
        $this->set('linkTargetsArr', applicationConstants::getLinkTargetsArr($this->adminLangId));
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function bannerForm($blocation_id, $banner_id)
    {
        $blocation_id = FatUtility::int($blocation_id);
        if (1 > $blocation_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $frm = $this->getBannerForm();
        $data = array('banner_blocation_id'=>$blocation_id,'banner_id'=>$banner_id);

        $banner_id = FatUtility::int($banner_id);
        if ($banner_id > 0) {
            $srch = Banner::getSearchObject($this->adminLangId, false);
            $srch->addCondition('banner_blocation_id', '=', $blocation_id);
            $srch->addCondition('banner_id', '=', $banner_id);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $data = array();
            if ($rs) {
                $data = FatApp::getDb()->fetch($rs);
            }
        }
        if ($banner_id==0) {
            $data['banner_type'] = Banner::TYPE_BANNER;
        }

        $frm->fill($data);
        $this->set('frm', $frm);
        $this->set('languages', Language::getAllNames());
        $this->set('blocation_id', $blocation_id);

        $this->set('banner_id', $banner_id);
        $this->set('frmTax', $frm);
        $this->_template->render(false, false);
    }

    public function setupBanner()
    {
        $this->objPrivilege->canEditBanners();

        $frm = $this->getBannerForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $banner_id = $post['banner_id'];

        $record = new Banner($banner_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($banner_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Banner::getAttributesByLangId($langId, $banner_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $banner_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        if ($newTabLangId == 0 && !$this->isMediaUploaded($banner_id)) {
            $this->set('openMediaForm', true);
        }
        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('banner_id', $banner_id);
        $this->set('langId', $newTabLangId);
        $this->set('blocation_id', $post['banner_blocation_id']);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function bannerLangForm($blocation_id, $banner_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewBanners();

        $blocation_id = FatUtility::int($blocation_id);
        $banner_id = FatUtility::int($banner_id);

        if (1 > $blocation_id || 1 > $banner_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $lang_id = FatUtility::int($lang_id);

        if ($banner_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $bannerLangFrm = $this->getBannerLangForm($blocation_id, $banner_id, $lang_id);

        $langData = Banner::getAttributesByLangId($lang_id, $banner_id);

        if ($langData) {
            $bannerLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('blocation_id', $blocation_id);
        $this->set('banner_id', $banner_id);
        $this->set('banner_lang_id', $lang_id);
        $this->set('bannerLangFrm', $bannerLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function bannerLocLangForm($blocationId, $lang_id = 0)
    {
        $this->objPrivilege->canViewBanners();

        $blocationId = FatUtility::int($blocationId);

        if (1 > $blocationId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $lang_id = FatUtility::int($lang_id);

        if ($lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $bannerLocLangFrm = $this->getBannerLocLangForm($blocationId, $lang_id);

        $langData = BannerLocation::getAttributesByLangId($lang_id, $blocationId);

        if ($langData) {
            $bannerLocLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('blocationId', $blocationId);
        $this->set('bannerLocaLangId', $lang_id);
        $this->set('bannerLocLangFrm', $bannerLocLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditBanners();
        $post = FatApp::getPostedData();

        $blocation_id = $post['blocation_id'];
        $banner_id = $post['banner_id'];
        $lang_id = $post['lang_id'];

        if ($banner_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getBannerLangForm($blocation_id, $banner_id, $lang_id);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $data = array(
        'bannerlang_banner_id'=>$banner_id,
        'bannerlang_lang_id'=>$lang_id,
        'banner_title'=>$post['banner_title'],
        );

        $bannerObj = new Banner($banner_id);
        if (!$bannerObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($taxObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = Banner::getAttributesByLangId($langId, $banner_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        if ($newTabLangId == 0 && !$this->isMediaUploaded($banner_id)) {
            $this->set('openMediaForm', true);
        }
        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('blocationId', $blocation_id);
        $this->set('bannerId', $banner_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langSetupLocation()
    {
        $this->objPrivilege->canEditBanners();
        $post = FatApp::getPostedData();

        $blocation_id = $post['blocation_id'];

        $lang_id = $post['lang_id'];

        if ($lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getBannerLocLangForm($blocation_id, $lang_id);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $data = array(
        'blocationlang_blocation_id'=>$blocation_id,
        'blocationlang_lang_id'=>$lang_id,
        'blocation_name'=>$post['blocation_name'],
        );

        $bannerObj = new BannerLocation($blocation_id);
        if (!$bannerObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($bannerObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = BannerLocation::getAttributesByLangId($langId, $blocation_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('blocationId', $blocation_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function mediaForm($blocation_id, $banner_id)
    {
        $blocation_id = FatUtility::int($blocation_id);
        $banner_id = FatUtility::int($banner_id);

        if (1 > $blocation_id || 1 > $banner_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $bannerDetail = Banner::getAttributesById($banner_id);
        if (!false == $bannerDetail && ($bannerDetail['banner_active'] != applicationConstants::ACTIVE)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request_Or_Inactive_Record', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $mediaFrm = $this->getMediaForm($blocation_id, $banner_id);
        if (!false == $bannerDetail) {
            $bannerImgArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $banner_id, 0, -1);
            $this->set('bannerImgArr', $bannerImgArr);
        }

        $blocationData = $this->getBannerLocationById($blocation_id);
        $bannerWidth = FatUtility::convertToType($blocationData['blocation_banner_width'], FatUtility::VAR_FLOAT);
        $bannerHeight = FatUtility::convertToType($blocationData['blocation_banner_height'], FatUtility::VAR_FLOAT);

        $this->set('bannerWidth', $bannerWidth);
        $this->set('bannerHeight', $bannerHeight);

        $this->set('mediaFrm', $mediaFrm);
        $this->set('languages', Language::getAllNames());
        $this->set('bannerTypeArr', $this->bannerTypeArr());
        $this->set('screenTypeArr', $this->getDisplayScreenName());
        $this->set('blocation_id', $blocation_id);
        $this->set('banner_id', $banner_id);
        $this->_template->render(false, false);
    }

    public function images($blocation_id, $banner_id, $lang_id=0, $screen=0)
    {
        $blocation_id = FatUtility::int($blocation_id);
        $banner_id = FatUtility::int($banner_id);

        if (1 > $blocation_id || 1 > $banner_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $bannerDetail = Banner::getAttributesById($banner_id);
        if (!false == $bannerDetail && ($bannerDetail['banner_active'] != applicationConstants::ACTIVE)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request_Or_Inactive_Record', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!false == $bannerDetail) {
            $bannerImgArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $banner_id, 0, $lang_id, false, $screen);
            $this->set('images', $bannerImgArr);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('screenTypeArr', $this->getDisplayScreenName());
        $this->set('blocation_id', $blocation_id);
        $this->set('banner_id', $banner_id);
        $this->_template->render(false, false);
    }

    public function upload($banner_id)
    {
        $this->objPrivilege->canEditBanners();

        $banner_id = FatUtility::int($banner_id);

        if (1 > $banner_id) {
            FatUtility::dieJsonError($this->str_invalid_request);
        }

        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
        }
        $blocation_id = FatUtility::int($post['blocation_id']);
        $lang_id = FatUtility::int($post['lang_id']);

        $banner_screen = FatUtility::int($post['banner_screen']);

        if (1 > $blocation_id) {
            FatUtility::dieJsonError($this->str_invalid_request);
        }
        /* if (!isset($post['file_type']) || FatUtility::int($post['file_type']) == 0 ) {
        Message::addErrorMessage($this->str_invalid_request);
        FatUtility::dieJsonError( Message::getHtml() );
        }

        $file_type = $post['file_type'];
        $allowedFileTypeArr = array(AttachedFile::FILETYPE_BANNER);

        if(!in_array($file_type,$allowedFileTypeArr)){
        Message::addErrorMessage($this->str_invalid_request);
        FatUtility::dieJsonError( Message::getHtml() );
        } */

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Please_Select_A_File', $this->adminLangId));
        }

        $fileHandlerObj = new AttachedFile();

        if (!$res = $fileHandlerObj->saveImage(
            $_FILES['file']['tmp_name'],
            AttachedFile::FILETYPE_BANNER,
            $banner_id,
            0,
            $_FILES['file']['name'],
            -1,
            true,
            $lang_id,
            '',
            $banner_screen
        )
        ) {
            FatUtility::dieJsonError($fileHandlerObj->getError());
        }

        Banner::setLastModified($banner_id);

        $this->set('bannerId', $banner_id);
        $this->set('blocationId', $blocation_id);
        $fileName = $_FILES['file']['name'];
        $this->set('file', $fileName);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileName = strlen($fileName) > 10 ? substr($fileName, 0, 10).'.'.$ext : $fileName;
        $this->set('msg', $fileName.' '.Labels::getLabel('MSG_File_uploaded_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeBanner($banner_id, $lang_id, $screen)
    {
        $banner_id = FatUtility::int($banner_id);
        $lang_id = FatUtility::int($lang_id);
        if (1 > $banner_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_BANNER, $banner_id, 0, 0, $lang_id, $screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditBanners();
        $banner_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($banner_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* if (!FatApp::getDb()->deleteRecords('tbl_banners', array('smt'=>'banner_id = ?', 'vals'=>array($banner_id)))) {
        Message::addErrorMessage(FatApp::getDb()->getError());
        FatUtility::dieJsonError( Message::getHtml() );
        } */

        $bannerObj = new Banner($banner_id);
        if (!$bannerObj->deleteRecord(true)) {
            Message::addErrorMessage($bannerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    private function isMediaUploaded($bannerId)
    {
        if ($attachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_BANNER, $bannerId, 0)) {
            return true;
        }
        return false;
    }

    private function getBannerLocationById($bLocationId)
    {
        $bLocationId = FatUtility::int($bLocationId);

        $srch = Banner::getBannerLocationSrchObj(false);
        $srch->addCondition('blocation_id', '=', $bLocationId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();

        $data = array();

        if ($rs) {
            $data = FatApp::getDb()->fetch($rs);
        }

        return $data;
    }

    /* private function getSearchForm(){
    $this->objPrivilege->canViewBanners();
    $frm = new Form('frmBannerSearch');
    return $frm;
    } */

    private function getLocationForm()
    {
        $this->objPrivilege->canViewBanners();
        $frm = new Form('frmBannerLocation');
        $frm->addHiddenField('', 'blocation_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Banner_Location_Identifier', $this->adminLangId), 'blocation_identifier');
        /* $frm->addFloatField('Preferred Width (in pixels)', 'blocation_banner_width')->requirements()->setRequired(true);
        $frm->addFloatField('Preferred Height (in pixels)', 'blocation_banner_height')->requirements()->setRequired(true); */

        $frm->addTextBox(Labels::getLabel('LBL_Promotion_Cost', $this->adminLangId), 'blocation_promotion_cost');
        /* $languages = Language::getAllNames();
        foreach($languages as $langId => $langName){
        $frm->addTextBox(Labels::getLabel('LBL_Location_Name',$langId).'('.$langName.')', 'blocation_name_'.$langId);
        } */

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'blocation_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getListingSearchForm()
    {
        $this->objPrivilege->canViewBanners();
        $frm = new Form('frmListingSearch');
        $frm->addTextBox('', 'keyword');
        $frm->addTextBox('', 'blocation_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        return $frm;
    }

    private function getBannerForm()
    {
        $this->objPrivilege->canViewBanners();

        $frm = new Form('frmBanner');
        $frm->addHiddenField('', 'banner_blocation_id');
        $frm->addHiddenField('', 'banner_id');
        $frm->addHiddenField('', 'banner_type');

        $frm->addTextBox(Labels::getLabel('LBL_Url', $this->adminLangId), 'banner_url')->requirements()->setRequired(true);

        $linkTargetsArr = applicationConstants::getLinkTargetsArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Open_In', $this->adminLangId), 'banner_target', $linkTargetsArr, '', array(), '');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'banner_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function getBannerLangForm($blocation_id, $banner_id, $lang_id)
    {
        $this->objPrivilege->canViewBanners();
        $frm = new Form('frmBannerLang');
        $frm->addHiddenField('', 'banner_id', $banner_id);
        $frm->addHiddenField('', 'blocation_id', $blocation_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Banner_Title', $this->adminLangId), 'banner_title');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    public function getBannerLocLangForm($blocation_id, $lang_id)
    {
        $this->objPrivilege->canViewBanners();
        $frm = new Form('frmBannerLocLang');

        $frm->addHiddenField('', 'blocation_id', $blocation_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Banner_Location_Title', $this->adminLangId), 'blocation_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    public function changeStatusBannerLocation()
    {
        $this->objPrivilege->canEditBanners();
        $blocationId = FatApp::getPostedData('blocationId', FatUtility::VAR_INT, 0);
        if (0 >= $blocationId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = BannerLocation::getAttributesById($blocationId, array('blocation_id', 'blocation_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['blocation_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateBannerLocationStatus($blocationId, $status);

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditBanners();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $blocationIdsArr = FatUtility::int(FatApp::getPostedData('blocation_ids'));
        if (empty($blocationIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($blocationIdsArr as $blocationId) {
            if (1 > $blocationId) {
                continue;
            }
            $this->updateBannerLocationStatus($blocationId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateBannerLocationStatus($blocationId, $status)
    {
        $status = FatUtility::int($status);
        $blocationId = FatUtility::int($blocationId);
        if (1 > $blocationId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $obj = new BannerLocation($blocationId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditBanners();
        $bannerId = FatApp::getPostedData('bannerId', FatUtility::VAR_INT, 0);
        if (0 >= $bannerId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = Banner::getAttributesById($bannerId, array('banner_id', 'banner_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($data['banner_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $obj = new Banner($bannerId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    private function getMediaForm($blocation_id, $banner_id = 0)
    {
        $frm = new Form('frmBannerMedia');
        $frm->addHiddenField('', 'banner_id', $banner_id);
        $frm->addHiddenField('', 'blocation_id', $blocation_id);
        $bannerTypeArr = $this->bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $screenArr = applicationConstants::getDisplaysArr($this->adminLangId);
        $displayFor = ($blocation_id == BannerLocation::HOME_PAGE_MIDDLE_BANNER) ? applicationConstants::SCREEN_MOBILE : '';
        $frm->addSelectBox(Labels::getLabel("LBL_Display_For", $this->adminLangId), 'banner_screen', $screenArr, $displayFor, array(), '');
        $fld =  $frm->addButton(Labels::getLabel('LBL_Banner_Image', $this->adminLangId), 'banner_image', Labels::getLabel('LBL_Upload_File', $this->adminLangId), array('class'=>'bannerFile-Js','id'=>'banner_image','data-banner_id'=>$banner_id,'data-blocation_id'=>$blocation_id));
        return $frm;
    }

    private function bannerTypeArr()
    {
        return applicationConstants::bannerTypeArr();
    }

    private function getDisplayScreenName()
    {
        $screenTypesArr = applicationConstants::getDisplaysArr($this->adminLangId);
        return array( 0 => '' ) + $screenTypesArr;
    }

    public function getBannerLocationDimensions($bannerLocationId, $deviceType)
    {            
        $bannerDimensions = BannerLocation::getDimensions($bannerLocationId, $deviceType);
        $this->set('bannerWidth', $bannerDimensions['blocation_banner_width']);
        $this->set('bannerHeight', $bannerDimensions['blocation_banner_height']);
        $this->_template->render(false, false, 'json-success.php');
    }
}
