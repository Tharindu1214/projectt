<?php
class PromotionsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewPromotions($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditPromotions($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewPromotions();
        $frmSearch = $this->getSearchForm();
        $data = FatApp::getPostedData();
        if ($data) {
            $data['promotion_id'] = $data['id'];
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->_template->addJs(array('js/jquery.datetimepicker.js'), false);
        $this->_template->addCss(array('css/jquery.datetimepicker.css'), false);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewPromotions();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = new PromotionSearch($this->adminLangId);
        $srch->joinBannersAndLocation($this->adminLangId, Promotion::TYPE_BANNER, 'b');
        $srch->joinPromotionsLogForCount();
        $srch->joinActiveUser(false);
        $srch->joinShops($this->adminLangId);
        $srch->addOrder('promotion_id', 'DESC');
        $srch->addMultipleFields(array('pr.promotion_id','ifnull(pr_l.promotion_name,pr.promotion_identifier)as promotion_name','user_name','credential_username','credential_email','credential_email','pr.promotion_type','pr.promotion_budget','pr.promotion_duration','promotion_approved','bbl.blocation_promotion_cost','pri.impressions','pri.clicks','pri.orders','bbl.blocation_id','shop_id','IFNULL(shop_name, shop_identifier) as shop_name'));
        $srch->addCondition('pr.promotion_deleted', '=', applicationConstants::NO);

        $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        if (!empty($date_from)) {
            $srch->addCondition('pr.promotion_start_date', '>=', $date_from. ' 00:00:00');
        }

        $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
        if (!empty($date_to)) {
            $srch->addCondition('pr.promotion_end_date', '<=', $date_to. ' 23:59:59');
        }

        $active = FatApp::getPostedData('active', FatUtility::VAR_INT, -1);
        if ($active >= 0) {
            $srch->addCondition('pr.promotion_active', '=', $active);
        }
        $promotion_id = FatApp::getPostedData('promotion_id', FatUtility::VAR_INT, -1);
        if (!empty($promotion_id) and $promotion_id >= 0) {
            $srch->addCondition('pr.promotion_id', '=', $promotion_id);
        }

        $approved = FatApp::getPostedData('approve', FatUtility::VAR_INT, -1);
        if ($approved >= 0) {
            $srch->addCondition('pr.promotion_approved', '=', $approved);
        }

        $impressionFrom = FatApp::getPostedData('impression_from', FatUtility::VAR_INT, 0);
        $impressionTo = FatApp::getPostedData('impression_to', FatUtility::VAR_INT, 0);
        if ($impressionFrom > 0) {
            $srch->addCondition('pri.impressions', '>=', $impressionFrom);
        }
        if ($impressionTo > 0) {
            $srch->addCondition('pri.impressions', '<=', $impressionTo);
        }


        $clickFrom = FatApp::getPostedData('click_from', FatUtility::VAR_INT, 0);
        $clickTo = FatApp::getPostedData('click_to', FatUtility::VAR_INT, 0);
        if ($clickFrom > 0) {
            $srch->addCondition('pri.clicks', '>=', $clickFrom);
        }
        if ($clickTo > 0) {
            $srch->addCondition('pri.clicks', '<=', $clickTo);
        }

        $type = FatApp::getPostedData('type', FatUtility::VAR_INT, '-1');
        if ($type != '-1') {
            $srch->addCondition('promotion_type', '=', $type);
        }
        $srch->addGroupBy('pr.promotion_id');
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
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->adminLangId));
        $this->set('typeArr', Promotion::getTypeArr($this->adminLangId));
        $this->set('canViewShops', $this->objPrivilege->canViewShops($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function getTypeData($promotionId, $promotionType = 0)
    {
        $promotionType = FatUtility::int($promotionType);
        $promotionId = FatUtility::int($promotionId);

        $promotionDetails = Promotion::getAttributesById($promotionId);

        $userId = $promotionDetails['promotion_user_id'];

        if (1 > $promotionType) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $label = '';
        $value = 0;
        switch ($promotionType) {
        case Promotion::TYPE_SHOP:
            $srch = Shop::getSearchObject(true, $this->adminLangId);
            $srch->addCondition('shop_user_id', '=', $userId);
            $srch->setPageSize(1);
            $srch->doNotCalculateRecords();
            $srch->addMultipleFields(array('ifnull(shop_name,shop_identifier) as shop_name','shop_id'));
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs);
            if (empty($row)) {
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            $label = $row['shop_name'];
            $value = $row['shop_id'];
            break;

        case Promotion::TYPE_PRODUCT:
            if ($promotionId > 0) {
                $srch = new PromotionSearch($this->adminLangId);
                $srch->joinProducts();
                $srch->addCondition('selprod_user_id', '=', $userId);
                $srch->setPageSize(1);
                $srch->doNotCalculateRecords();
                $srch->addMultipleFields(array('selprod_id','selprod_title','ifnull(product_name,product_identifier)as product_name'));
                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);
                if (!empty($row)) {
                    $label = $row['selprod_title'] .' ('.$row['product_name'].')';
                    $value = $row['selprod_id'];
                }
            }
            break;

        }

        $this->set('promotionType', $promotionType);
        $this->set('label', $label);
        $this->set('value', $value);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupPromotion()
    {
        $this->objPrivilege->canEditPromotions();
        $promotionId = FatApp::getPostedData('promotion_id');
        $frm = $this->getForm($promotionId);
        $userId = FatApp::getPostedData('promotion_user_id');
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $promotionDetails = Promotion::getAttributesById($promotionId);
        $oldApprovalStatus = applicationConstants::INACTIVE;
        if ($promotionDetails) {
            $oldApprovalStatus  = $promotionDetails['promotion_approved'];
        }
        $promotion_record_id = 0;
        $bannerData = array();
        $slidesData = array();

        $minBudget = 0;

        switch ($post['promotion_type']) {
        case Promotion::TYPE_SHOP:
            $srch = Shop::getSearchObject(true, $this->adminLangId);
            $srch->addCondition('shop_user_id', '=', $userId);
            $srch->setPageSize(1);
            $srch->doNotCalculateRecords();
            $srch->addMultipleFields(array('ifnull(shop_name,shop_identifier) as shop_name','shop_id'));
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs);
            if (empty($row)) {
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            $promotion_record_id = $row['shop_id'];
            $minBudget = FatApp::getConfig('CONF_CPC_SHOP', FatUtility::VAR_FLOAT, 0);
            break;

        case Promotion::TYPE_PRODUCT:
            $selProdId = $post['promotion_record_id'];

            $srch = new ProductSearch($this->adminLangId);
            $srch->joinSellerProducts();
            $srch->setPageSize(1);
            $srch->doNotCalculateRecords();
            $srch->addCondition('selprod_id', '=', $selProdId);
            $srch->addCondition('selprod_user_id', '=', $userId);
            $srch->addMultipleFields(array('selprod_id'));

            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs);

            if (empty($row)) {
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            $promotion_record_id = $row['selprod_id'];
            $minBudget = FatApp::getConfig('CONF_CPC_PRODUCT', FatUtility::VAR_FLOAT, 0);
            break;

        case Promotion::TYPE_BANNER:
            $promotion_record_id = 0;
            $bannerData = array(
            'banner_blocation_id' => $post['banner_blocation_id'],
            'banner_url' => $post['banner_url'],
            'banner_target' => applicationConstants::LINK_TARGET_BLANK_WINDOW,
            'banner_type' => Banner::TYPE_PPC,
            'banner_active' => applicationConstants::ACTIVE,
            );

            $bannerLocationId = Fatutility::int($post['banner_blocation_id']);
            $srch = BannerLocation::getSearchObject($this->adminLangId);
            $srch->addMultipleFields(array('blocation_promotion_cost'));
            $srch->addCondition('blocation_id', '=', $bannerLocationId);
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs, 'blocation_id');
            if (!empty($row)) {
                $minBudget = $row['blocation_promotion_cost'];
            }
            break;

        case Promotion::TYPE_SLIDES:
            $promotion_record_id = 0;
            $slidesData = array(
            'slide_url' => $post['slide_url'],
            'slide_target' => applicationConstants::LINK_TARGET_BLANK_WINDOW,
            'slide_type' => Slides::TYPE_PPC,
            'slide_active' => applicationConstants::ACTIVE,
            );
            $minBudget = FatApp::getConfig('CONF_CPC_SLIDES', FatUtility::VAR_FLOAT, 0);
            break;

        default:
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
            break;
        }

        $promotionBudget = Fatutility::float($post['promotion_budget']);
        if ($minBudget > $promotionBudget) {
            Message::addErrorMessage(Labels::getLabel("MSG_Budget_should_be_greater_than_CPC", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* $bannerData = array(
        'banner_blocation_id' => $post['banner_blocation_id'],
        'banner_url' => $post['banner_url'],
        'banner_target' => $post['banner_target'],
        'banner_type' => Banner::TYPE_PPC,
        'banner_active' => applicationConstants::ACTIVE,
        ); */

        $promotionId = $post['promotion_id'];

        unset($post['banner_id']);
        unset($post['promotion_id']);
        unset($post['banner_blocation_id']);
        unset($post['banner_url']);
        /* unset($post['banner_target']); */
        unset($post['promotion_record_id']);

        $record = new Promotion($promotionId);
        $data = array(
        'promotion_user_id' => $userId,
        'promotion_added_on' => date('Y-m-d H:i:s'),
        'promotion_active' => applicationConstants::ACTIVE,
        'promotion_record_id' => $promotion_record_id,
        );

        $data = array_merge($data, $post);
        $record->assignValues($data);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($promotionId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = Promotion::getAttributesByLangId($langId, $promotionId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $promotionId = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        switch ($post['promotion_type']) {
        case Promotion::TYPE_BANNER:
            $bannerId = 0;
            $srch = Banner::getSearchObject();
            $srch->addCondition('banner_type', '=', Banner::TYPE_PPC);
            $srch->addCondition('banner_record_id', '=', $promotionId);
            $srch->addMultipleFields(array('banner_id'));
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs);

            if ($row) {
                $bannerId = $row['banner_id'];
            }

            $bannerRecord = new Banner($bannerId);
            $bannerData['banner_record_id'] = $promotionId;
            $bannerRecord->assignValues($bannerData);

            if (!$bannerRecord->save()) {
                Message::addErrorMessage($bannerRecord->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            break;

        case Promotion::TYPE_SLIDES:
            $slideId = 0;
            $srch = Slides::getSearchObject();
            $srch->addCondition('slide_type', '=', Slides::TYPE_PPC);
            $srch->addCondition('slide_record_id', '=', $promotionId);
            $srch->addMultipleFields(array('slide_id'));
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs);
            if ($row) {
                $slideId = $row['slide_id'];
            }

            $slideRecord = new Slides($slideId);
            $slidesData['slide_record_id'] = $promotionId;
            $slideRecord->assignValues($slidesData);

            if (!$slideRecord->save()) {
                Message::addErrorMessage($slideRecord->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            break;
        }

        $promotionDetails = Promotion::getAttributesById($promotionId);
        $currentApprovalStatus  = $promotionDetails['promotion_approved'];
        if ($oldApprovalStatus == applicationConstants::INACTIVE && $currentApprovalStatus == applicationConstants::ACTIVE) {
            EmailHandler::sendPromotionStatusChangeNotification($this->adminLangId, $userId, $promotionDetails);
        }

        $this->set('promotionId', $promotionId);
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function promotionLangForm($promotionId = 0, $langId = 0)
    {
        $this->objPrivilege->canEditPromotions();
        $promotionId = FatUtility::int($promotionId);
        $langId = FatUtility::int($langId);
        $promotionDetails = Promotion::getAttributesById($promotionId);

        $userId = $promotionDetails['promotion_user_id'];
        if ($promotionId == 0 || $langId == 0) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->adminLangId));
        }

        $langFrm = $this->getPromotionLangForm($promotionId, $langId);
        $langData = Promotion::getAttributesByLangId($langId, $promotionId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $promotionType = 0;
        $row = Promotion::getAttributesById($promotionId, array('promotion_type'));
        if (!empty($row)) {
            $promotionType = $row['promotion_type'];
        }

        $this->set('language', Language::getAllNames());
        $this->set('promotionId', $promotionId);
        $this->set('promotion_lang_id', $langId);
        $this->set('promotionType', $promotionType);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->_template->render(false, false);
    }

    public function removePromotionBanner()
    {
        $this->objPrivilege->canEditPromotions();
        $promotionId = FatApp::getPostedData('promotionId', FatUtility::VAR_INT, 0);
        $bannerId = FatApp::getPostedData('bannerId', FatUtility::VAR_INT, 0);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $screen = FatApp::getPostedData('screen', FatUtility::VAR_INT, 0);

        $data = Promotion::getAttributesById($promotionId, array('promotion_id','promotion_type','promotion_user_id'));
        if (!$data) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        $attachedFileType = 0;
        switch ($data['promotion_type']) {
        case Promotion::TYPE_BANNER:
            $attachedFileType = AttachedFile::FILETYPE_BANNER;
            break;

        case Promotion::TYPE_SLIDES:
            $attachedFileType = AttachedFile::FILETYPE_HOME_PAGE_BANNER;
            break;
        }

        if (1 > $attachedFileType) {
            Message::addErrorMessage(Labels::getLabel('Lbl_Invalid_request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile($attachedFileType, $bannerId, 0, 0, $langId, $screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function promotionUpload()
    {
        $this->objPrivilege->canEditPromotions();
        $post = FatApp::getPostedData();
        /* CommonHelper::printArray($post); */
        $promotionId = FatUtility::int($post['promotion_id']);
        $langId = FatUtility::int($post['lang_id']);
        $promotionType = FatUtility::int($post['promotion_type']);
        $bannerScreen = FatUtility::int($post['banner_screen']);

        $promotionDetails = Promotion::getAttributesById($promotionId);
        $userId = $promotionDetails['promotion_user_id'];

        $allowedTypeArr = array(Promotion::TYPE_BANNER,Promotion::TYPE_SLIDES);

        if (1 > $promotionId || !in_array($promotionType, $allowedTypeArr)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $recordId = 0;
        $attachedFileType =    0;

        $srch = new PromotionSearch($this->adminLangId);
        $srch->addCondition('promotion_id', '=', $promotionId);
        $srch->addCondition('promotion_user_id', '=', $userId);

        switch ($promotionType) {
        case Promotion::TYPE_BANNER:
            $srch->joinBannersAndLocation($this->adminLangId, Promotion::TYPE_BANNER, 'b');
            $rs = $srch->getResultSet();
            $promotionDetails = FatApp::getDb()->fetch($rs);
            $recordId = $promotionDetails['banner_id'];
            $attachedFileType = AttachedFile::FILETYPE_BANNER;
            break;
        case Promotion::TYPE_SLIDES:
            $srch->joinSlides();
            $rs = $srch->getResultSet();
            $promotionDetails = FatApp::getDb()->fetch($rs);
            $recordId = $promotionDetails['slide_id'];
            $attachedFileType = AttachedFile::FILETYPE_HOME_PAGE_BANNER;
            break;
        }

        if (1 > $recordId || 1 > $attachedFileType) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();

        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            $attachedFileType,
            $recordId,
            0,
            $_FILES['file']['name'],
            -1,
            true,
            $langId,
            $bannerScreen
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('promotionId', $promotionId);
        $this->set('file', $_FILES['file']['name']);
        $this->set('msg', $_FILES['file']['name']. Labels::getLabel('MSG_File_uploaded_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupPromotionLang()
    {
        $this->objPrivilege->canEditPromotions();
        $post = FatApp::getPostedData();

        $promotionId = $post['promotion_id'];
        $langId = $post['lang_id'];

        if ($promotionId == 0 || $langId == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getPromotionLangForm($promotionId, $langId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['promotion_id']);
        unset($post['lang_id']);
        $data=array(
        'promotionlang_lang_id'=>$langId,
        'promotionlang_promotion_id'=>$promotionId,
        'promotion_name'=>$post['promotion_name']
        );

        $obj = new Promotion($promotionId);
        if (!$obj->updateLangData($langId, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = Promotion::getAttributesByLangId($langId, $promotionId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('promotionId', $promotionId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function form($promotionId = 0)
    {
        $this->objPrivilege->canEditPromotions();

        $promotionId = FatUtility::int($promotionId);
        $frmPromotion = $this->getForm($promotionId);
        if (0 < $promotionId) {
            $promotionObj = new Promotion();
            $srch = new PromotionSearch($this->adminLangId);
            $srch->joinBannersAndLocation($this->adminLangId, Promotion::TYPE_BANNER, 'b');
            $srch->joinSlides();
            $srch->joinShops(0, false, false);
            $srch->addCondition('promotion_id', '=', $promotionId);

            $srch->addMultipleFields(array('promotion_id','promotion_identifier','promotion_user_id','promotion_type','promotion_budget','promotion_duration','promotion_start_date','promotion_end_date','promotion_start_time','promotion_end_time','promotion_active','promotion_approved','ifnull(shop_identifier,shop_name) as promotion_shop','banner_url','banner_target','banner_blocation_id','slide_url','slide_target'));
            $rs = $srch->getResultSet();
            $promotionDetails = FatApp::getDb()->fetch($rs);

            if ($promotionDetails === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $promotionType = $promotionDetails['promotion_type'];
            $frmPromotion->fill($promotionDetails);
        }

        $this->set('promotionType', $promotionType);
        $this->set('promotionId', $promotionId);
        $this->set('frmPromotion', $frmPromotion);
        $this->set('language', Language::getAllNames());
        $this->set('activeTab', 'GENERAL');
        $this->_template->render(false, false);
    }

    public function promotionMediaForm($promotionId = 0)
    {
        $this->objPrivilege->canEditPromotions();

        $promotionId = FatUtility::int($promotionId);

        if (1 > $promotionId) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->adminLangId));
        }

        $promotionType = 0 ;

        $srch = new PromotionSearch($this->adminLangId);
        $srch->joinBannersAndLocation($this->adminLangId, Promotion::TYPE_BANNER, 'b');
        $srch->joinSlides();
        $srch->addCondition('promotion_id', '=', $promotionId);
        $srch->addMultipleFields(array('promotion_id','promotion_type','banner_id','blocation_banner_width','blocation_banner_height','slide_id'));
        $rs = $srch->getResultSet();
        $promotionDetails = FatApp::getDb()->fetch($rs);
        if (empty($promotionDetails)) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->adminLangId));
        }
        $promotionType = $promotionDetails['promotion_type'];

        $recordId = 0;
        $attachedFileType = 0;

        switch ($promotionType) {
        case Promotion::TYPE_BANNER:
            $imgDetail = Banner::getAttributesById($promotionDetails['banner_id']);
            if (!false == $imgDetail && ($imgDetail['banner_active'] != applicationConstants::ACTIVE)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request_Or_Inactive_Record', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $attachedFileType = AttachedFile::FILETYPE_BANNER;
            $recordId = $promotionDetails['banner_id'];
            break;
        case Promotion::TYPE_SLIDES:
            $imgDetail = Slides::getAttributesById($promotionDetails['slide_id']);
            if (!false == $imgDetail && ($imgDetail['slide_active'] != applicationConstants::ACTIVE)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request_Or_Inactive_Record', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $attachedFileType = AttachedFile::FILETYPE_HOME_PAGE_BANNER;
            $recordId = $promotionDetails['slide_id'];
            break;
        }

        $mediaFrm = $this->getPromotionMediaForm($promotionId, $promotionType);
        $bannerWidth = '1200';
        $bannerHeight = '360';
        if ($promotionType == Promotion::TYPE_BANNER) {
            $bannerWidth = FatUtility::convertToType($promotionDetails['blocation_banner_width'], FatUtility::VAR_FLOAT);
            $bannerHeight = FatUtility::convertToType($promotionDetails['blocation_banner_height'], FatUtility::VAR_FLOAT);
        }

        $this->set('bannerWidth', $bannerWidth);
        $this->set('bannerHeight', $bannerHeight);
        $this->set('promotionType', $promotionType);
        $this->set('promotionId', $promotionId);
        $this->set('language', Language::getAllNames());
        $this->set('mediaFrm', $mediaFrm);
        $this->set('screen', applicationConstants::SCREEN_DESKTOP);
        $this->_template->render(false, false);
    }

    public function images($promotionId = 0, $lang_id=0, $screen=0)
    {
        $this->objPrivilege->canEditPromotions();
        $promotionId = FatUtility::int($promotionId);
        if (1 > $promotionId) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->adminLangId));
        }
        $promotionType = 0 ;
        $srch = new PromotionSearch($this->adminLangId);
        $srch->joinBannersAndLocation($this->adminLangId, Promotion::TYPE_BANNER, 'b');
        $srch->joinSlides();
        $srch->addCondition('promotion_id', '=', $promotionId);
        $srch->addMultipleFields(array('promotion_id','promotion_type','banner_id','blocation_banner_width','blocation_banner_height','slide_id'));
        $rs = $srch->getResultSet();
        $promotionDetails = FatApp::getDb()->fetch($rs);
        if (empty($promotionDetails)) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->adminLangId));
        }
        $promotionType = $promotionDetails['promotion_type'];

        $recordId = 0;
        $attachedFileType = 0;

        switch ($promotionType) {
        case Promotion::TYPE_BANNER:
            $imgDetail = Banner::getAttributesById($promotionDetails['banner_id']);
            if (!false == $imgDetail && ($imgDetail['banner_active'] != applicationConstants::ACTIVE)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request_Or_Inactive_Record', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $attachedFileType = AttachedFile::FILETYPE_BANNER;
            $recordId = $promotionDetails['banner_id'];
            break;
        case Promotion::TYPE_SLIDES:
            $imgDetail = Slides::getAttributesById($promotionDetails['slide_id']);
            if (!false == $imgDetail && ($imgDetail['slide_active'] != applicationConstants::ACTIVE)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request_Or_Inactive_Record', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $attachedFileType = AttachedFile::FILETYPE_HOME_PAGE_BANNER;
            $recordId = $promotionDetails['slide_id'];
            break;
        }

        if (!false == $imgDetail) {
            $bannerImgArr = AttachedFile::getMultipleAttachments($attachedFileType, $recordId, 0, $lang_id, false, $screen);
            $this->set('bannerImgArr', $bannerImgArr);
        }

        $this->set('promotionType', $promotionType);
        $this->set('promotionId', $promotionId);
        $this->set('bannerTypeArr', applicationConstants::bannerTypeArr());
        $this->set('screenTypeArr', array( 0 => '' )+applicationConstants::getDisplaysArr($this->adminLangId));
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function autoCompleteSelprods($userId = 0)
    {
        $db = FatApp::getDb();
        $srch = new ProductSearch($this->adminLangId);
        $srch->joinSellerProducts();
        if (!empty($post['keyword'])) {
            $srch->addCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $post = FatApp::getPostedData();
        $srch->addCondition('selprod_id', '>', 0);
        $srch->addCondition('selprod_user_id', '=', $userId);
        if (!empty($post['keyword'])) {
            /* $srch->addCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%');
            $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%','OR');
            $srch->addCondition('product_identifier', 'LIKE', '%' . $post['keyword'] . '%','OR'); */
            $srch->addDirectCondition("(selprod_title like " . $db->quoteVariable('%'.$post['keyword'].'%') . " or product_name LIKE " . $db->quoteVariable('%'.$post['keyword'].'%') . " or product_identifier LIKE " . $db->quoteVariable('%'.$post['keyword'].'%') . " )", 'and');
        }

        $srch->setPageSize(FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));

        $srch->addMultipleFields(array('selprod_id','IFNULL(product_name,product_identifier) as product_name, IFNULL(selprod_title,product_identifier) as selprod_title'));
        $rs = $srch->getResultSet();

        $products = $db->fetchAll($rs, 'selprod_id');
        $json = array();
        foreach ($products as $key => $product) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode(($product['selprod_title']!='')?$product['selprod_title']:$product['product_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function deletePromotionRecord()
    {
        $this->objPrivilege->canEditPromotions();

        $promotionId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);

        if (1 > $promotionId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = Promotion::getAttributesById($promotionId, array('promotion_id','promotion_user_id'));
        if (!$data) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($promotionId);

        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY', $this->adminLangId));
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditPromotions();
        $promotionIdsArr = FatUtility::int(FatApp::getPostedData('promotion_ids'));

        if (empty($promotionIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($promotionIdsArr as $promotionId) {
            if (1 > $promotionId) {
                continue;
            }
            $this->markAsDeleted($promotionId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($promotionId)
    {
        $promotionId = FatUtility::int($promotionId);
        if (1 > $promotionId) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new Promotion($promotionId);
        $obj->assignValues(array(Promotion::tblFld('deleted') => 1));
        if (!$obj->save()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    private function getPromotionLangForm($promotionId, $langId)
    {
        $frm = new Form('frmPromotionLang');
        $frm->addHiddenField('', 'promotion_id', $promotionId);
        $frm->addHiddenField('', 'lang_id', $langId);
        $frm->addRequiredField(Labels::getLabel('LBL_promotion_name', $this->adminLangId), 'promotion_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getSearchForm()
    {
        $this->objPrivilege->canViewPromotions();
        $frm = new Form('frmPromotionSearch');

        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender' ));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Active', $this->adminLangId), 'active', array( -1 =>'Does not Matter' ) + $activeInactiveArr, '', array(), '');

        $yesNoArr = applicationConstants::getYesNoArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Approved', $this->adminLangId), 'approve', array( -1 =>'Does not Matter' ) + $yesNoArr, '', array(), '');

        $frm->addTextBox(Labels::getLabel('LBL_Impression_from_(number)', $this->adminLangId), 'impression_from');
        $frm->addTextBox(Labels::getLabel('LBL_Impression_to_(number)', $this->adminLangId), 'impression_to');

        $frm->addTextBox(Labels::getLabel('LBL_Clicks_from_(number)', $this->adminLangId), 'click_from');
        $frm->addTextBox(Labels::getLabel('LBL_Clicks_to_(number)', $this->adminLangId), 'click_to');
        $frm->addHiddenField('', 'promotion_id');
        $frm->addSelectBox('', 'type', array('-1'=>Labels::getLabel('LBL_All_Type', $this->adminLangId))+Promotion::getTypeArr($this->adminLangId), '', array(), '');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearPromotionSearch();'));
        $fld_submit->attachField($fld_cancel);

        return $frm;
    }

    private function getForm($promotionId)
    {
        $frm = new Form('frmPromotion');
        $frm->addHiddenField('', 'promotion_id', $promotionId);
        $frm->addHiddenField('', 'promotion_record_id', '');
        $frm->addRequiredField(Labels::getLabel('Lbl_Identifier', $this->adminLangId), 'promotion_identifier');

        $linkTargetsArr = applicationConstants::getLinkTargetsArr($this->adminLangId);
        if ($promotionId > 0) {
            $srch = new PromotionSearch($this->adminLangId);
            $srch->addCondition('promotion_id', '=', $promotionId);
            $srch->addMultipleFields(array('promotion_type'));
            $rs = $srch->getResultSet();
            $promotioType = FatApp::getDb()->fetch($rs);
            $promotionTypeArr = Promotion::getTypeArr($this->adminLangId);
            $promotioTypeValue = $promotionTypeArr[$promotioType['promotion_type']];
            $promotioTypeArr = array($promotioType['promotion_type'] => $promotioTypeValue);
        } else {
            $promotioTypeArr = Promotion::getTypeArr($this->adminLangId);
        }
        $pTypeFld = $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->adminLangId), 'promotion_type', $promotioTypeArr, '', array(), '');

        /* Shop [ */
        $frm->addTextBox(Labels::getLabel('LBL_Shop', $this->adminLangId), 'promotion_shop', '', array('readonly'=>true))->requirements()->setRequired(true);
        ;
        $shopUnReqObj = new FormFieldRequirement('promotion_shop', Labels::getLabel('LBL_Shop', $this->adminLangId));
        $shopUnReqObj->setRequired(false);

        $shopReqObj = new FormFieldRequirement('promotion_shop', Labels::getLabel('LBL_Shop', $this->adminLangId));
        $shopReqObj->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_CPC', $this->adminLangId), 'promotion_shop_cpc', FatApp::getConfig('CONF_CPC_SHOP', FatUtility::VAR_FLOAT, 0), array('readonly'=>true));
        /*]*/

        /* Product [ */
        $frm->addTextBox(Labels::getLabel('LBL_Product', $this->adminLangId), 'promotion_product')->requirements()->setRequired(true);
        ;
        $prodUnReqObj = new FormFieldRequirement('promotion_product', Labels::getLabel('LBL_Product', $this->adminLangId));
        $prodUnReqObj->setRequired(false);

        $prodReqObj = new FormFieldRequirement('promotion_product', Labels::getLabel('LBL_Product', $this->adminLangId));
        $prodReqObj->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_CPC', $this->adminLangId), 'promotion_product_cpc', FatApp::getConfig('CONF_CPC_PRODUCT', FatUtility::VAR_FLOAT, 0), array('readonly'=>true));
        /* ]*/

        /* Banner Url [*/
        $frm->addTextBox(Labels::getLabel('LBL_Url', $this->adminLangId), 'banner_url')->requirements()->setRequired(true);
        ;
        $urlUnReqObj = new FormFieldRequirement('banner_url', Labels::getLabel('LBL_Url', $this->adminLangId));
        $urlUnReqObj->setRequired(false);

        $urlReqObj = new FormFieldRequirement('banner_url', Labels::getLabel('LBL_Url', $this->adminLangId));
        $urlReqObj->setRequired(true);
        /*]*/

        /* Slide Url [*/
        $frm->addTextBox(Labels::getLabel('LBL_Url', $this->adminLangId), 'slide_url')->requirements()->setRequired(true);
        ;
        $urlSlideUnReqObj = new FormFieldRequirement('slide_url', Labels::getLabel('LBL_Url', $this->adminLangId));
        $urlSlideUnReqObj->setRequired(false);

        $urlSlideReqObj = new FormFieldRequirement('slide_url', Labels::getLabel('LBL_Url', $this->adminLangId));
        $urlSlideReqObj->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_CPC', $this->adminLangId), 'promotion_slides_cpc', FatApp::getConfig('CONF_CPC_SLIDES', FatUtility::VAR_FLOAT, 0), array('readonly'=>true));

        /* $frm->addSelectBox(Labels::getLabel('LBL_Open_In',$this->adminLangId), 'slide_target', $linkTargetsArr, '',array(),'');	 */
        /*]*/

        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'banner_url', $urlReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'banner_url', $urlUnReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'banner_url', $urlUnReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'banner_url', $urlUnReqObj);

        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'promotion_product', $prodUnReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'promotion_product', $prodUnReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'promotion_product', $prodReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'promotion_product', $prodUnReqObj);

        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'promotion_shop', $shopUnReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'promotion_shop', $shopReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'promotion_shop', $shopUnReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'promotion_shop', $shopUnReqObj);

        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'slide_url', $urlSlideUnReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'slide_url', $urlSlideUnReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'slide_url', $urlSlideUnReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'slide_url', $urlSlideReqObj);

        //$frm->addTextBox(Labels::getLabel('LBL_Url',$this->adminLangId), 'banner_url')->requirements()->setRequired(true);

        $linkTargetsArr = applicationConstants::getLinkTargetsArr($this->adminLangId);
        /* $frm->addSelectBox(Labels::getLabel('LBL_Open_In',$this->adminLangId), 'banner_target', $linkTargetsArr, '',array(),''); */

        $srch = BannerLocation::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array('blocation_id','blocation_promotion_cost','ifnull(blocation_name,blocation_identifier) as blocation_name'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs, 'blocation_id');
        $locationArr = array();
        if (!empty($row)) {
            foreach ($row as $key=>$val) {
                $locationArr[$key] = $val['blocation_name'] .' ( '.CommonHelper::displayMoneyFormat($val['blocation_promotion_cost']).' )';
            }
        }
        $frm->addSelectBox(Labels::getLabel('LBL_Location', $this->adminLangId), 'banner_blocation_id', $locationArr, '', array(), '');


        $fld = $frm->addTextBox(Labels::getLabel('Lbl_Budget', $this->adminLangId), 'promotion_budget');
        $fld->requirements()->setRequired();
        $fld->requirements()->setFloatPositive(true);

        $fldDuration = $frm->addSelectBox(Labels::getLabel('LBL_Duration', $this->adminLangId), 'promotion_duration', Promotion::getPromotionBudgetDurationArr($this->adminLangId), '', array('id'=>'promotion_duration'))->requirements()->setRequired();

        $frm->addDateField(Labels::getLabel('LBL_Start_Date', $this->adminLangId), 'promotion_start_date', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $this->adminLangId) ,'readonly'=>'readonly' ))->requirements()->setRequired();
        $frm->addDateField(Labels::getLabel('LBL_End_Date', $this->adminLangId), 'promotion_end_date', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $this->adminLangId)  ,'readonly'=>'readonly'))->requirements()->setRequired();

        $fld = $frm->addRequiredField(Labels::getLabel('LBL_promotion_start_ime', $this->adminLangId), 'promotion_start_time', '', array('class'=>'time' ,'readonly'=>'readonly' ));
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_promotion_end_time', $this->adminLangId), 'promotion_end_time', '', array('class'=>'time' ,'readonly'=>'readonly' ));
        $yesNoArr = applicationConstants::getYesNoArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Approved', $this->adminLangId), 'promotion_approved', $yesNoArr, '', array(), '');
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'promotion_active', $activeInactiveArr, '', array(), '');
        $frm->addHiddenField('', 'promotion_user_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getPromotionMediaForm($promotionId = 0, $promotionType = 0)
    {
        $promotionId = FatUtility::int($promotionId);
        $frm = new Form('frmPromotionMedia');

        $frm->addHiddenField('', 'promotion_id', $promotionId);
        $frm->addHiddenField('', 'promotion_type', $promotionType);

        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $screenArr = applicationConstants::getDisplaysArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel("LBL_Display_For", $this->adminLangId), 'banner_screen', $screenArr, '', array(), '');
        $fld =  $frm->addButton(Labels::getLabel('LBL_Banner_Image', $this->adminLangId), 'banner_image', Labels::getLabel('LBL_Upload_File', $this->adminLangId), array('class'=>'bannerFile-Js','id'=>'banner_image'));

        return $frm;
    }

    public function checkValidPromotionBudget()
    {
        $post = FatApp::getPostedData();
        $promotionType = Fatutility::int($post['promotion_type']);
        $promotionBudget = Fatutility::float($post['promotion_budget']);

        $minBudget = 0;

        switch ($promotionType) {
        case Promotion::TYPE_SHOP:
            $minBudget = FatApp::getConfig('CONF_CPC_SHOP', FatUtility::VAR_FLOAT, 0);
            break;
        case Promotion::TYPE_PRODUCT:
            $minBudget = FatApp::getConfig('CONF_CPC_PRODUCT', FatUtility::VAR_FLOAT, 0);
            break;
        case Promotion::TYPE_BANNER:
            $bannerLocationId = Fatutility::int($post['banner_blocation_id']);
            $srch = BannerLocation::getSearchObject($this->adminLangId);
            $srch->addMultipleFields(array('blocation_promotion_cost'));
            $srch->addCondition('blocation_id', '=', $bannerLocationId);
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs, 'blocation_id');
            if (!empty($row)) {
                $minBudget = $row['blocation_promotion_cost'];
            }
            break;
        case Promotion::TYPE_SLIDES:
            $minBudget = FatApp::getConfig('CONF_CPC_SLIDES', FatUtility::VAR_FLOAT, 0);
            break;
        }

        if ($minBudget > $promotionBudget) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_Budget_should_be_greater_than_CPC", $this->adminLangId));
        }
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function getBannerLocationDimensions($promotionId, $deviceType)
    {
        $srch = new PromotionSearch($this->adminLangId);
        $srch->joinBannersAndLocation($this->adminLangId, Promotion::TYPE_BANNER, 'b', $deviceType);
        $srch->addCondition('promotion_id', '=', $promotionId);
        $srch->addMultipleFields(array('blocation_banner_width','blocation_banner_height'));
        $rs = $srch->getResultSet();
        $bannerDimensions = FatApp::getDb()->fetch($rs);
        $this->set('bannerWidth', $bannerDimensions['blocation_banner_width']);
        $this->set('bannerHeight', $bannerDimensions['blocation_banner_height']);
        $this->_template->render(false, false, 'json-success.php');
    }
}
