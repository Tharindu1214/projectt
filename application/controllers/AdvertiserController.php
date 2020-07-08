<?php
class AdvertiserController extends AdvertiserBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $user   = new User($userId);

        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'Ad';

        $walletBalance = User::getUserBalance($userId);

        $lowBalWarning = '';
        $errorSet      = false;
        /* foreach($promotionList as $promotion){
        if ($promotion["promotion_start_date"]<=date("Y-m-d") && $promotion["promotion_end_date"]>=date("Y-m-d") && ($walletBalance<FatApp::getConfig('CONF_PPC_MIN_WALLET_BALANCE', FatUtility::VAR_INT, 0) && $errorSet==false)) {
        $errorSet = true;
        Message::addInfo(sprintf(Labels::getLabel('L_Please_maintain_minimum_balance_to_%s', $this->siteLangId), CommonHelper::displaymoneyformat(FatApp::getConfig('CONF_PPC_MIN_WALLET_BALANCE'))));
        }
        } */

        /* Transactions Listing [ */
        $srch = Transactions::getUserTransactionsObj($userId);
        $srch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs           = $srch->getResultSet();
        $transactions = FatApp::getDb()->fetchAll($rs, 'utxn_id');
        /* ] */

        /* Active Promotions [ */
        $activePSrch = $this->getPromotionsSearch(true);
        $rs = $activePSrch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'promotion_id');
        /* ] */

        /* Total Promotions [ */
        $totalPSrch = $this->getPromotionsSearch();
        $rs = $totalPSrch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'promotion_id');
        /* ] */

        $txnObj = new Transactions();
        $txnsSummary = $txnObj->getTransactionSummary($userId, date('Y-m-d'));
        $this->set('txnsSummary', $txnsSummary);

        $this->set('totChargedAmount', Promotion::getTotalChargedAmount($userId));
        $this->set('activePromotionChargedAmount', Promotion::getTotalChargedAmount($userId, true));
        $this->set('transactions', $transactions);
        $this->set('txnStatusArr', Transactions::getStatusArr($this->siteLangId));
        $this->set('activePromotions', $records);
        $this->set('totPromotions', $totalPSrch->recordCount());
        $this->set('totActivePromotions', $activePSrch->recordCount());
        $this->set('lowBalWarning', $lowBalWarning);
        // $this->set('frmRechargeWallet', $this->getRechargeWalletForm($this->siteLangId));
        $this->set('walletBalance', $walletBalance);
        $typeArr = Promotion::getTypeArr($this->siteLangId);
        $this->set('typeArr', $typeArr);
        // $this->set('promotionList', $promotionList);
        // $this->set('promotionCount', $srch->recordCount());
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render(true, true);
    }

    public function getPromotionsSearch($active = false)
    {
        $pSrch = $this->searchPromotionsObj();
        $pSrch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b');
        $pSrch->joinPromotionsLogForCount();
        $pSrch->addMultipleFields(array(
            'pr.promotion_id',
            'ifnull(pr_l.promotion_name,pr.promotion_identifier)as promotion_name',
            'pr.promotion_type',
            'pr.promotion_cpc',
            'pr.promotion_budget',
            'pr.promotion_duration',
            'pr.promotion_start_date',
            'pr.promotion_end_date',
            'pr.promotion_approved',
            'bbl.blocation_promotion_cost',
            'pri.impressions',
            'pri.clicks',
            'pri.orders'
        ));


        if ($active) {
            $pSrch->setDefinedCriteria();
            $pSrch->addCondition('promotion_end_date', '>', date("Y-m-d"));
            $pSrch->addCondition('promotion_approved', '=', applicationConstants::YES);
        } else {
            // $pSrch->addCondition('promotion_deleted', '=', applicationConstants::NO);
        }

        $pSrch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        return $pSrch;
    }

    public function setupPromotion()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $frm    = $this->getPromotionForm();
        $post   = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $promotion_record_id = 0;
        $promotionApproved   = applicationConstants::NO;
        $bannerData          = array();
        $slidesData          = array();

        $minBudget = 0;

        switch ($post['promotion_type']) {
            case Promotion::TYPE_SHOP:
                $srch = Shop::getSearchObject(true, $this->siteLangId);
                $srch->addCondition('shop_user_id', '=', $userId);
                $srch->setPageSize(1);
                $srch->doNotCalculateRecords();
                $srch->addMultipleFields(array('ifnull(shop_name,shop_identifier) as shop_name','shop_id'));
                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);
                if (empty($row)) {
                    Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                $promotion_record_id = $row['shop_id'];
                $promotionApproved = applicationConstants::YES;
                $minBudget = FatApp::getConfig('CONF_CPC_SHOP', FatUtility::VAR_FLOAT, 0);
                break;
            case Promotion::TYPE_PRODUCT:
                $selProdId = $post['promotion_record_id'];

                $srch = new ProductSearch($this->siteLangId);
                $srch->joinSellerProducts();
                $srch->joinProductToCategory();
                $srch->joinSellerSubscription($this->siteLangId, true);
                $srch->addSubscriptionValidCondition();
                $srch->joinBrands();
                $srch->setPageSize(1);
                $srch->doNotCalculateRecords();
                $srch->addCondition('selprod_id', '=', $selProdId);
                $srch->addCondition('selprod_user_id', '=', $userId);
                $srch->addMultipleFields(array('selprod_id'));

                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);

                if (empty($row)) {
                    Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                $promotion_record_id = $row['selprod_id'];
                $promotionApproved = applicationConstants::YES;
                $minBudget = FatApp::getConfig('CONF_CPC_PRODUCT', FatUtility::VAR_FLOAT, 0);
                break;

            case Promotion::TYPE_BANNER:
                $promotion_record_id = 0;
                $bannerData          = array(
                    'banner_blocation_id' => $post['banner_blocation_id'],
                    'banner_url' => $post['banner_url'],
                    'banner_target' => applicationConstants::LINK_TARGET_BLANK_WINDOW,
                    'banner_type' => Banner::TYPE_PPC,
                    'banner_active' => applicationConstants::ACTIVE
                );

                $bannerLocationId = Fatutility::int($post['banner_blocation_id']);
                $srch             = BannerLocation::getSearchObject($this->siteLangId);
                $srch->addMultipleFields(array(
                    'blocation_promotion_cost'
                ));
                $srch->addCondition('blocation_id', '=', $bannerLocationId);
                $rs  = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs, 'blocation_id');
                if (!empty($row)) {
                    $minBudget = $row['blocation_promotion_cost'];
                }
                break;

            case Promotion::TYPE_SLIDES:
                $promotion_record_id = 0;
                $slidesData          = array(
                    'slide_url' => $post['slide_url'],
                    'slide_target' => applicationConstants::LINK_TARGET_BLANK_WINDOW,
                    'slide_type' => Slides::TYPE_PPC,
                    'slide_active' => applicationConstants::ACTIVE
                );
                $minBudget           = FatApp::getConfig('CONF_CPC_SLIDES', FatUtility::VAR_FLOAT, 0);
                break;

            default:
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
                break;
        }

        $promotionBudget = Fatutility::float($post['promotion_budget']);
        if ($minBudget > $promotionBudget) {
            Message::addErrorMessage(Labels::getLabel("MSG_Budget_should_be_greater_than_CPC", $this->siteLangId));
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
        if (Promotion::TYPE_PRODUCT == $post['promotion_type'] || $post['promotion_type'] == Promotion::TYPE_SHOP) {
            $srch = Promotion::getSearchObject(0, false);
            $srch->addCondition('promotion_user_id', '=', $userId);
            $srch->addCondition('promotion_record_id', '=', $promotion_record_id);
            $srch->addCondition('promotion_type', '=', $post['promotion_type']);
            $srch->addCondition('promotion_duration', '=', $post['promotion_duration']);
            $srch->addCondition('promotion_start_date', '<=', $post['promotion_start_date']);
            $srch->addCondition('promotion_end_date', '>=', $post['promotion_end_date']);
            /* $srch->addCondition('promotion_end_time','=',$post['promotion_end_time']); */
            $srch->addCondition('promotion_id', '!=', $promotionId);
            $rs  = $srch->getResultSet();
            /* echo $srch->getQuery();die;  */
            $row = FatApp::getDb()->fetch($rs);
            if (!empty($row)) {
                Message::addErrorMessage(Labels::getLabel('LBL_Promotion_record_with_same_period_already_exists', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        unset($post['banner_id']);
        unset($post['promotion_id']);
        /* unset($post['banner_blocation_id']); */
        unset($post['banner_url']);
        /* unset($post['banner_target']); */
        unset($post['promotion_record_id']);

        $record = new Promotion($promotionId);
        $data   = array(
            'promotion_user_id' => UserAuthentication::getLoggedUserId(),
            'promotion_added_on' => date('Y-m-d H:i:s'),
            'promotion_active' => applicationConstants::ACTIVE,
            'promotion_record_id' => $promotion_record_id
        );

        if (!$promotionId) {
            $data['promotion_approved'] = $promotionApproved;
        }

        if ($post['promotion_type']==Promotion::TYPE_SHOP) {
            $data['promotion_cpc'] = $post['promotion_shop_cpc'];
        } elseif ($post['promotion_type']==Promotion::TYPE_PRODUCT) {
            $data['promotion_cpc'] = $post['promotion_product_cpc'];
        } elseif ($post['promotion_type']==Promotion::TYPE_SLIDES) {
            $data['promotion_cpc'] = $post['promotion_slides_cpc'];
        } else {
            $srch = BannerLocation::getSearchObject($this->siteLangId);
            $srch->addMultipleFields(array(
                'blocation_id',
                'blocation_promotion_cost',
                'ifnull(blocation_name,blocation_identifier) as blocation_name'
            ));
            $rs                    = $srch->getResultSet();
            $row                   = FatApp::getDb()->fetchAll($rs, 'blocation_id');
            $data['promotion_cpc'] = $row[$post['banner_blocation_id']]['blocation_promotion_cost'];
        }
        $data = array_merge($data, $post);
        $record->assignValues($data);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($promotionId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langIdKey => $langName) {
                if ($langIdKey > $newTabLangId) {
                    $newTabLangId = $langIdKey;
                    break;
                }
                /* if(!$row = Promotion::getAttributesByLangId($langId,$promotionId)){
                $newTabLangId = $langId;
                break;
                } */
            }
        } else {
            $promotionId  = $record->getMainTableRecordId();
            $newTabLangId = $this->siteLangId;
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

                $bannerRecord                   = new Banner($bannerId);
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

                $slideRecord                   = new Slides($slideId);
                $slidesData['slide_record_id'] = $promotionId;
                $slideRecord->assignValues($slidesData);

                if (!$slideRecord->save()) {
                    Message::addErrorMessage($slideRecord->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
                break;
        }

        $notificationData = array(
            'notification_record_type' => Notification::TYPE_PROMOTION,
            'notification_record_id' => $promotionId,
            'notification_user_id' => UserAuthentication::getLoggedUserId(),
            'notification_label_key' => Notification::PROMOTION_APPROVAL_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s')
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('promotionId', $promotionId);
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupPromotionLang()
    {
        $post   = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId();

        $promotionId = $post['promotion_id'];
        $langId      = $post['lang_id'];

        if ($promotionId == 0 || $langId == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $promotionData = Promotion::getAttributesById($promotionId, array('promotion_user_id'));
        if (!$promotionData || ($promotionData && $promotionData['promotion_user_id']!=$userId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm  = $this->getPromotionLangForm($promotionId, $langId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['promotion_id']);
        unset($post['lang_id']);
        $data = array(
            'promotionlang_lang_id' => $langId,
            'promotionlang_promotion_id' => $promotionId,
            'promotion_name' => $post['promotion_name']
        );

        $obj = new Promotion($promotionId);
        if (!$obj->updateLangData($langId, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $promotionType = Promotion::getAttributesById($promotionId, array('promotion_type'));
        if ($promotionType['promotion_type'] == Promotion::TYPE_SHOP || $promotionType['promotion_type'] == Promotion::TYPE_PRODUCT) {
            $this->set('noMediaTab', 'noMediaTab');
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langIdKey => $langName) {
            if ($langIdKey>$langId) {
                $newTabLangId = $langIdKey;
                break;
            }
            /* if(!$row = Promotion::getAttributesByLangId($langIdKey,$promotionId)){
            $newTabLangId = $langId;
            break;
            } */
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->siteLangId));
        $this->set('promotionId', $promotionId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function promotionUpload()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $post   = FatApp::getPostedData();

        $promotionId   = FatUtility::int($post['promotion_id']);
        $promotionType = FatUtility::int($post['promotion_type']);
        $langId        = FatUtility::int($post['lang_id']);
        $bannerScreen  = FatUtility::int($post['banner_screen']);

        $allowedTypeArr = array(
            Promotion::TYPE_BANNER,
            Promotion::TYPE_SLIDES
        );


        if (1 > $promotionId || !in_array($promotionType, $allowedTypeArr)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_File', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $recordId         = 0;
        $attachedFileType = 0;

        $srch = new PromotionSearch($this->siteLangId);
        $srch->addCondition('promotion_id', '=', $promotionId);
        $srch->addCondition('promotion_user_id', '=', $userId);

        switch ($promotionType) {
            case Promotion::TYPE_BANNER:
                $srch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b');
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
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $db = FatApp::getDb();
        $db->startTransaction();
        $fileHandlerObj = new AttachedFile();

        if (!$res = $fileHandlerObj->saveImage($_FILES['file']['tmp_name'], $attachedFileType, $recordId, 0, $_FILES['file']['name'], -1, true, $langId, '', $bannerScreen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }


        /* if($promotionDetails['promotion_approved']==applicationConstants::YES){ */
        $dataToUpdate = array(
            'promotion_approved' => applicationConstants::NO
        );
        $record       = new Promotion($promotionId);
        $record->assignValues($dataToUpdate);

        if (!$record->save()) {
            $db->rollbackTransaction();
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $objEmailHandler = new EmailHandler();
        $objEmailHandler->sendPromotionApprovalRequestAdmin($this->siteLangId, $userId, $promotionDetails);

        $notificationData = array(
            'notification_record_type' => Notification::TYPE_PROMOTION,
            'notification_record_id' => $promotionId,
            'notification_user_id' => UserAuthentication::getLoggedUserId(),
            'notification_label_key' => Notification::PROMOTION_APPROVAL_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s')
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* } */
        $db->commitTransaction();

        $fileName = $_FILES['file']['name'];
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileName = strlen($fileName) > 10 ? substr($fileName, 0, 10).'.'.$ext : $fileName;
        Message::addMessage($fileName . " " . Labels::getLabel('MSG_File_uploaded_successfully_and_send_it_for_admin_approval', $this->siteLangId));

        $this->set('promotionId', $promotionId);
        $this->set('file', $_FILES['file']['name']);
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function searchPromotions()
    {
        $userId    = UserAuthentication::getLoggedUserId();
        $pagesize  = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $frmSearch = $this->getPromotionSearchForm($this->siteLangId);

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $frmSearch->getFormDataFromArray($data);
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);

        $srch = $this->searchPromotionsObj();

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('pr.promotion_identifier', 'like', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('pr_l.promotion_name', 'like', '%' . $post['keyword'] . '%');
        }

        $type = FatApp::getPostedData('type', FatUtility::VAR_INT, '-1');
        if ($type != '-1') {
            $srch->addCondition('promotion_type', '=', $type);
        }

        $active_promotion = FatApp::getPostedData('active_promotion', FatUtility::VAR_INT, '-1');
        if ($active_promotion != '-1') {
            $srch->addCondition('promotion_active', '=', applicationConstants::YES);
            $srch->addCondition('promotion_deleted', '=', applicationConstants::NO);
            $srch->addCondition('promotion_end_date', '>', date("Y-m-d"));
            $srch->addCondition('promotion_approved', '=', applicationConstants::YES);
        }

        $dateFrom = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        $dateTo   = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');

        if (!empty($dateFrom) || (!empty($dateTo))) {
            $srch->addDateCondition($dateFrom, $dateTo);
        }

        /* if( !empty($dateTo) ) {
        $srch->addDateToCondition($dateTo, $dateFrom);
        } */
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs                         = $srch->getResultSet();
        $records                    = FatApp::getDb()->fetchAll($rs, 'promotion_id');
        $promotionBudgetDurationArr = Promotion::getPromotionBudgetDurationArr($this->siteLangId);
        /* CommonHelper::printArray($records); die; */
        $this->set('promotionBudgetDurationArr', $promotionBudgetDurationArr);
        $this->set('arr_listing', $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('userId', $userId);
        $this->set('typeArr', Promotion::getTypeArr($this->siteLangId));
        $this->_template->render(false, false);
    }

    public function searchPromotionsObj()
    {
        $srch = new PromotionSearch($this->siteLangId);
        $srch->addMultipleFields(array(
            'promotion_id',
            'promotion_budget',
            'promotion_duration',
            'promotion_type',
            'IFNULL(promotion_name,promotion_identifier) as promotion_name',
            'promotion_start_date',
            'promotion_end_date',
            'promotion_start_time',
            'promotion_end_time',
            'promotion_active',
            'promotion_approved',
            'promotion_active'
        ));
        $srch->addCondition('promotion_deleted', '=', applicationConstants::NO);
        $srch->addCondition('promotion_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addOrder('promotion_id', 'DESC');
        return $srch;
    }

    public function getTypeData($promotionId, $promotionType = 0)
    {
        $promotionType = FatUtility::int($promotionType);
        $promotionId   = FatUtility::int($promotionId);

        $userId = UserAuthentication::getLoggedUserId();

        if (1 > $promotionType) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $label = '';
        $value = 0;
        switch ($promotionType) {
            case Promotion::TYPE_SHOP:
                $srch = Shop::getSearchObject(true, $this->siteLangId);
                $srch->addCondition('shop_user_id', '=', $userId);
                $srch->setPageSize(1);
                $srch->doNotCalculateRecords();
                $srch->addMultipleFields(array('ifnull(shop_name,shop_identifier) as shop_name','shop_id'));
                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);
                if (empty($row)) {
                    Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                $label = $row['shop_name'];
                $value = $row['shop_id'];
                break;

            case Promotion::TYPE_PRODUCT:
                if ($promotionId > 0) {
                    $row = Promotion::getAttributesById($promotionId, array(
                        'promotion_record_id'
                    ));

                    $srch = new PromotionSearch($this->siteLangId);
                    $srch->joinProducts();
                    $srch->addCondition('selprod_user_id', '=', $userId);
                    $srch->addCondition('selprod_id', '=', $row['promotion_record_id']);
                    $srch->setPageSize(1);
                    $srch->doNotCalculateRecords();
                    $srch->addMultipleFields(array(
                        'selprod_id',
                        'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
                        'ifnull(product_name,product_identifier)as product_name'
                    ));
                    $rs  = $srch->getResultSet();
                    $row = FatApp::getDb()->fetch($rs);
                    if (!empty($row)) {
                        $label = $row['selprod_title'] . ' (' . $row['product_name'] . ')';
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

    public function promotions()
    {
        $data = FatApp::getPostedData();
        $frmSearchPromotions = $this->getPromotionSearchForm($this->siteLangId);
        if ($data) {
            $frmSearchPromotions->fill($data);
        }
        $userId = UserAuthentication::getLoggedUserId();
        $srch   = new PromotionSearch($this->siteLangId);
        $srch->addMultipleFields(array(
            'promotion_id'
        ));
        $srch->addCondition('promotion_user_id', '=', $userId);
        $srch->addCondition('promotion_deleted', '=', applicationConstants::NO);
        $srch->addCondition('promotion_active', '=', applicationConstants::YES);
        $srch->addCondition('promotion_end_date', '>=', date("Y-m-d"));
        $srch->addOrder('promotion_id', 'DESC');

        $rs      = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'promotion_id');

        $this->_template->addJs(array('js/jquery.datetimepicker.js'), false);
        $this->_template->addCss(array('css/jquery.datetimepicker.css'), false);

        $this->set("frmSearchPromotions", $frmSearchPromotions);
        $this->set("records", $records);
        $this->_template->render(true, true);
    }

    public function promotionCharges()
    {
        $this->_template->render(true, true);
    }

    public function searchPromotionCharges()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $data     = FatApp::getPostedData();
        $page     = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $prmSrch = new SearchBase(Promotion::DB_TBL_CHARGES, 'tpc');
        $prmSrch->joinTable(Promotion::DB_TBL, 'INNER JOIN', 'pr.' . Promotion::DB_TBL_PREFIX . 'id = tpc.' . Promotion::DB_TBL_CHARGES_PREFIX . 'promotion_id', 'pr');
        $prmSrch->addCondition('pr.promotion_user_id', '=', $userId);
        $prmSrch->addMultipleFields(array(
            'promotion_id',
            'promotion_type',
            'promotion_identifier',
            'sum(pcharge_charged_amount) as totChargedAmount',
            'sum(pcharge_clicks) as totClicks',
            'pcharge_date'
        ));
        $prmSrch->addGroupBy('promotion_id');
        $prmSrch->addOrder('tpc.' . Promotion::DB_TBL_CHARGES_PREFIX . 'id', 'desc');
        $prmSrch->setPageNumber($page);
        $prmSrch->setPageSize($pagesize);
        $rs      = $prmSrch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $prmSrch->pages());
        $this->set("recordCount", $prmSrch->recordCount());
        $typeArr = Promotion::getTypeArr($this->siteLangId);
        $this->set('typeArr', $typeArr);
        $this->set("pageSize", $pagesize);
        $this->set("page", $page);
        $this->_template->render(false, false);
    }

    public function promotionForm($promotionId = 0)
    {
        $userId      = UserAuthentication::getLoggedUserId();
        $promotionId = FatUtility::int($promotionId);

        $promotionDetails = array();
        $promotionType = 0;
        if ($promotionId) {
            $srch = new PromotionSearch($this->siteLangId);
            $srch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b');
            $srch->joinSlides($this->siteLangId);
            if (User::isSeller()) {
                $srch->joinShops($this->siteLangId, false, false);
                $srch->addFld(array(
                    'ifnull(shop_name,shop_identifier) as promotion_shop'
                ));
            }
            $srch->addCondition('promotion_id', '=', $promotionId);
            $srch->addCondition('promotion_user_id', '=', $userId);
            $srch->addMultipleFields(array(
                'promotion_id',
                'promotion_identifier',
                'promotion_user_id',
                'promotion_type',
                'promotion_budget',
                'promotion_cpc',
                'promotion_duration',
                'promotion_start_date',
                'promotion_end_date',
                'promotion_start_time',
                'promotion_end_time',
                'promotion_active',
                'promotion_approved',
                'banner_url',
                'banner_target',
                'banner_blocation_id',
                'slide_url',
                'slide_target'
            ));
            $rs               = $srch->getResultSet();
            $promotionDetails = FatApp::getDb()->fetch($rs);
            $promotionType    = $promotionDetails['promotion_type'];
            if ($promotionDetails) {
                $promotionDetails['promotion_start_time'] = date('H:i', strtotime($promotionDetails['promotion_start_time']));
                $promotionDetails['promotion_end_time']   = date('H:i', strtotime($promotionDetails['promotion_end_time']));
                if ($promotionDetails['promotion_type'] == Promotion::TYPE_SHOP) {
                    $promotionDetails['promotion_shop_cpc'] = $promotionDetails['promotion_cpc'];
                } elseif ($promotionDetails['promotion_type'] == Promotion::TYPE_PRODUCT) {
                    $promotionDetails['promotion_product_cpc'] = $promotionDetails['promotion_cpc'];
                } elseif ($promotionDetails['promotion_type'] == Promotion::TYPE_SLIDES) {
                    $promotionDetails['promotion_slides_cpc'] = $promotionDetails['promotion_cpc'];
                }
            }
        }

        $frm = $this->getPromotionForm($promotionId);
        $frm->fill($promotionDetails);

        $this->set('frm', $frm);
        $this->set('promotionId', $promotionId);
        $this->set('promotionType', $promotionType);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function promotionLangForm($promotionId = 0, $langId = 0)
    {
        $promotionId = FatUtility::int($promotionId);
        $langId      = FatUtility::int($langId);

        if ($promotionId == 0 || $langId == 0) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
        }

        $langFrm  = $this->getPromotionLangForm($promotionId, $langId);
        $langData = Promotion::getAttributesByLangId($langId, $promotionId);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $promotionType = 0;
        $row = Promotion::getAttributesById($promotionId, array('promotion_type'));
        if (!empty($row)) {
            $promotionType = $row['promotion_type'];
        }

        $this->set('languages', Language::getAllNames());
        $this->set('promotionId', $promotionId);
        $this->set('promotion_lang_id', $langId);
        $this->set('promotionType', $promotionType);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->_template->render(false, false);
    }

    public function promotionMediaForm($promotionId = 0)
    {
        $userId      = UserAuthentication::getLoggedUserId();
        $promotionId = FatUtility::int($promotionId);

        if (1 > $promotionId) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
        }

        $promotionType = 0;

        $srch = new PromotionSearch($this->siteLangId);
        $srch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b');
        $srch->joinSlides();
        $srch->addCondition('promotion_id', '=', $promotionId);
        $srch->addCondition('promotion_user_id', '=', $userId);
        $srch->addMultipleFields(array(
            'promotion_id',
            'promotion_type',
            'banner_id',
            'blocation_banner_width',
            'blocation_banner_height',
            'slide_id'
        ));
        $rs               = $srch->getResultSet();
        $promotionDetails = FatApp::getDb()->fetch($rs);
        if (empty($promotionDetails)) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
        }
        $promotionType = $promotionDetails['promotion_type'];

        $recordId         = 0;
        $attachedFileType = 0;

        switch ($promotionType) {
        case Promotion::TYPE_BANNER:
            $imgDetail = Banner::getAttributesById($promotionDetails['banner_id']);
            $attachedFileType = AttachedFile::FILETYPE_BANNER;
            $recordId = $promotionDetails['banner_id'];
            break;
        case Promotion::TYPE_SLIDES:
            $imgDetail = Slides::getAttributesById($promotionDetails['slide_id']);
            $attachedFileType = AttachedFile::FILETYPE_HOME_PAGE_BANNER;
            $recordId = $promotionDetails['slide_id'];
            break;
        }

        $mediaFrm     = $this->getPromotionMediaForm($promotionId, $promotionType);
        $bannerWidth  = '1200';
        $bannerHeight = '360';
        if ($promotionType == Promotion::TYPE_BANNER) {
            $bannerWidth = FatUtility::convertToType($promotionDetails['blocation_banner_width'], FatUtility::VAR_FLOAT);
            $bannerHeight = FatUtility::convertToType($promotionDetails['blocation_banner_height'], FatUtility::VAR_FLOAT);
        }

        $this->set('bannerWidth', $bannerWidth);
        $this->set('bannerHeight', $bannerHeight);
        $this->set('promotionType', $promotionType);
        $this->set('bannerTypeArr', applicationConstants::bannerTypeArr());
        $this->set('screenTypeArr', array(
            0 => ''
        ) + applicationConstants::getDisplaysArr($this->siteLangId));
        $this->set('promotionId', $promotionId);
        $this->set('languages', Language::getAllNames());
        $this->set('mediaFrm', $mediaFrm);
        $this->_template->render(false, false);
    }

    public function images($promotionId = 0, $langId = 0, $screen = 0)
    {
        $userId      = UserAuthentication::getLoggedUserId();
        $promotionId = FatUtility::int($promotionId);

        if (1 > $promotionId) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
        }

        $promotionType = 0;

        $srch = new PromotionSearch($this->siteLangId);
        $srch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b');
        $srch->joinSlides();
        $srch->addCondition('promotion_id', '=', $promotionId);
        $srch->addCondition('promotion_user_id', '=', $userId);
        $srch->addMultipleFields(array(
            'promotion_id',
            'promotion_type',
            'banner_id',
            'blocation_banner_width',
            'blocation_banner_height',
            'slide_id'
        ));
        $rs               = $srch->getResultSet();
        $promotionDetails = FatApp::getDb()->fetch($rs);
        if (empty($promotionDetails)) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
        }
        $promotionType = $promotionDetails['promotion_type'];

        $recordId         = 0;
        $attachedFileType = 0;

        switch ($promotionType) {
            case Promotion::TYPE_BANNER:
                $imgDetail = Banner::getAttributesById($promotionDetails['banner_id']);
                $attachedFileType = AttachedFile::FILETYPE_BANNER;
                $recordId = $promotionDetails['banner_id'];
                break;
            case Promotion::TYPE_SLIDES:
                $imgDetail = Slides::getAttributesById($promotionDetails['slide_id']);
                $attachedFileType = AttachedFile::FILETYPE_HOME_PAGE_BANNER;
                $recordId = $promotionDetails['slide_id'];
                break;
        }

        if (!false == $imgDetail) {
            $bannerImgArr = AttachedFile::getMultipleAttachments($attachedFileType, $recordId, 0, $langId, false, $screen);
            /* CommonHelper::printArray($bannerImgArr);die; */
            $this->set('images', $bannerImgArr);
        }

        $this->set('promotionType', $promotionType);
        $this->set('bannerTypeArr', applicationConstants::bannerTypeArr());
        $this->set('screenTypeArr', array(
            0 => ''
        ) + applicationConstants::getDisplaysArr($this->siteLangId));
        $this->set('promotionId', $promotionId);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function removePromotionBanner()
    {
        $promotionId = FatApp::getPostedData('promotionId', FatUtility::VAR_INT, 0);
        $bannerId    = FatApp::getPostedData('bannerId', FatUtility::VAR_INT, 0);
        $langId      = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $screen      = FatApp::getPostedData('screen', FatUtility::VAR_INT, 0);

        $data = Promotion::getAttributesById($promotionId, array(
            'promotion_id',
            'promotion_type',
            'promotion_user_id'
        ));
        if (!$data || $data['promotion_user_id'] != UserAuthentication::getLoggedUserId()) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj   = new AttachedFile();
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
            Message::addErrorMessage(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$fileHandlerObj->deleteFile($attachedFileType, $bannerId, 0, 0, $langId, $screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function deletePromotionRecord(){
    $promotionId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);

    if(1 > $promotionId){
    Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID',$this->siteLangId));
    FatUtility::dieJsonError( Message::getHtml() );
    }

    $data = Promotion::getAttributesById($promotionId,array('promotion_id','promotion_user_id'));
    if(!$data || $data['promotion_user_id']!= UserAuthentication::getLoggedUserId()){
    Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID',$this->siteLangId));
    FatUtility::dieJsonError( Message::getHtml() );
    }

    $obj = new Promotion($promotionId);
    $obj->assignValues(array(Promotion::tblFld('deleted') => 1));
    if(!$obj->save()){
    Message::addErrorMessage($obj->getError());
    FatUtility::dieJsonError( Message::getHtml() );
    }

    FatUtility::dieJsonSuccess(Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY',$this->siteLangId));
    } */

    public function autoCompleteSelprods()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $db     = FatApp::getDb();

        $srch = new ProductSearch($this->siteLangId);
        $srch->joinSellerProducts();
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription($this->siteLangId, true);
        $srch->addSubscriptionValidCondition();
        /* if (!empty($post['keyword'])) {
        $srch->addCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%');
        } */

        $post = FatApp::getPostedData();
        $srch->addCondition('selprod_id', '>', 0);
        $srch->addCondition('selprod_user_id', '=', $userId);
        if (!empty($post['keyword'])) {
            /* $srch->addCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%');
            $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%','OR');
            $srch->addCondition('product_identifier', 'LIKE', '%' . $post['keyword'] . '%','OR'); */
            $srch->addDirectCondition("(selprod_title like " . $db->quoteVariable($post['keyword']) . " or selprod_title like " . $db->quoteVariable('%' . $post['keyword'] . '%') . " or product_name LIKE " . $db->quoteVariable('%' . $post['keyword'] . '%') . " or product_identifier LIKE " . $db->quoteVariable('%' . $post['keyword'] . '%') . ")", 'and');
        }
        //echo $srch->getQuery();
        $srch->setPageSize(FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));

        $srch->addMultipleFields(array(
            'selprod_id',
            'IFNULL(product_name,product_identifier) as product_name, IFNULL(selprod_title,product_identifier) as selprod_title'
        ));
        //echo $srch->getQuery();
        $rs = $srch->getResultSet();

        $products = $db->fetchAll($rs, 'selprod_id');
        $json = array();
        foreach ($products as $key => $product) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode(($product['selprod_title'] != '') ? $product['selprod_title'] : $product['product_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function analytics($promotionId = 0)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $searchForm = $this->getPPCAnalyticsSearchForm($this->siteLangId);
        $searchForm->fill(array(
            'promotion_id' => $promotionId
        ));

        $srch = new PromotionSearch($this->siteLangId);
        $srch->addCondition('promotion_id', '=', $promotionId);
        $srch->addCondition('promotion_user_id', '=', $userId);
        $srch->addMultipleFields(array(
            'promotion_id',
            'promotion_type',
            'ifnull(promotion_name,promotion_identifier)as promotion_name'
        ));
        $rs               = $srch->getResultSet();
        $promotionDetails = FatApp::getDb()->fetch($rs);

        if (empty($promotionDetails)) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $this->set('searchForm', $searchForm);
        $this->set('promotionDetails', $promotionDetails);

        $this->_template->render(true, true);
    }

    public function searchAnalyticsData()
    {
        $userId   = UserAuthentication::getLoggedUserId();
        $data     = FatApp::getPostedData();
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $promotionId = FatUtility::int($data['promotion_id']);

        if ($promotionId < 1) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('advertiser'));
        }
        $promotionDetails = Promotion::getAttributesById($promotionId);
        if ($promotionDetails['promotion_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $promotionType = 0;

        $frmSearch = $this->getPPCAnalyticsSearchForm($this->siteLangId);
        $page      = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post      = $frmSearch->getFormDataFromArray($data);
        $page      = (empty($page) || $page <= 0) ? 1 : $page;
        $page      = FatUtility::int($page);

        $fromDate = $post['date_from'];
        $toDate   = $post['date_to'];

        $srch = new SearchBase(Promotion::DB_TBL_LOGS, 'i');
        $srch->addMultipleFields(array(
            'i.plog_promotion_id',
            'sum(i.plog_impressions) as impressions',
            'sum(i.plog_clicks) as clicks',
            'sum(i.plog_orders) as orders',
            'plog_date'
        ));

        $srch->addGroupBy('plog_date');
        $srch->addOrder('plog_date', 'DESC');
        if ($fromDate != '') {
            $srch->addCondition('i.plog_date', '>=', $fromDate.' 00:00:00');
        }
        if ($toDate != '') {
            $srch->addCondition('i.plog_date', '<=', $toDate.' 23:59:59');
        }
        if ($promotionId != '') {
            $srch->addCondition('i.plog_promotion_id', '=', $promotionId);
        }


        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        $rs               = $srch->getResultSet();
        $promotionDetails = FatApp::getDb()->fetchAll($rs);

        $this->set('pageSize', $pageSize);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('arr_listing', $promotionDetails);
        $this->set('promotion_id', $promotionId);
        $this->set('page', $page);
        $this->_template->render(false, false);
    }

    private function getPromotionForm($promotionId = 0)
    {
        $frm = new Form('frmPromotion');
        $frm->addHiddenField('', 'promotion_id', $promotionId);
        $frm->addHiddenField('', 'promotion_record_id', '');
        $frm->addRequiredField(Labels::getLabel('Lbl_Identifier', $this->siteLangId), 'promotion_identifier');

        $linkTargetsArr = applicationConstants::getLinkTargetsArr($this->siteLangId);

        $userId   = UserAuthentication::getLoggedUserId();
        $shopSrch = Shop::getSearchObject(true, $this->siteLangId);
        $shopSrch->addCondition('shop_user_id', '=', $userId);
        $shopSrch->setPageSize(1);
        $shopSrch->doNotCalculateRecords();
        $shopSrch->addMultipleFields(array(
            'shop_id'
        ));
        $rs                    = $shopSrch->getResultSet();
        $row                   = FatApp::getDb()->fetch($rs);
        $displayAdvertiserOnly = false;
        if (empty($row)) {
            $displayAdvertiserOnly = true;
        }

        if ($promotionId > 0) {
            $srch = new PromotionSearch($this->siteLangId);
            $srch->addCondition('promotion_id', '=', $promotionId);
            $srch->addMultipleFields(array(
                'promotion_type'
            ));
            $rs                = $srch->getResultSet();
            $promotioType      = FatApp::getDb()->fetch($rs);
            $promotionTypeArr  = Promotion::getTypeArr($this->siteLangId, $displayAdvertiserOnly);
            $promotioTypeValue = $promotionTypeArr[$promotioType['promotion_type']];
            $promotioTypeArr   = array(
                $promotioType['promotion_type'] => $promotioTypeValue
            );
        } else {
            $promotioTypeArr = Promotion::getTypeArr($this->siteLangId, $displayAdvertiserOnly);
            if (!User::isSeller()) {
                unset($promotioTypeArr[Promotion::TYPE_SHOP]);
                unset($promotioTypeArr[Promotion::TYPE_PRODUCT]);
            }
        }

        $pTypeFld = $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->siteLangId), 'promotion_type', $promotioTypeArr, '', array(), '');

        if (User::isSeller()) {
            /* Shop [ */
            $frm->addTextBox(Labels::getLabel('LBL_Shop', $this->siteLangId), 'promotion_shop', '', array(
                'readonly' => true
            ))->requirements()->setRequired(true);
            $shopUnReqObj = new FormFieldRequirement('promotion_shop', Labels::getLabel('LBL_Shop', $this->siteLangId));
            $shopUnReqObj->setRequired(false);

            $shopReqObj = new FormFieldRequirement('promotion_shop', Labels::getLabel('LBL_Shop', $this->siteLangId));
            $shopReqObj->setRequired(true);

            $frm->addTextBox(Labels::getLabel('LBL_CPC' . '_[' . commonHelper::getDefaultCurrencySymbol() . ']', $this->siteLangId), 'promotion_shop_cpc', FatApp::getConfig('CONF_CPC_SHOP', FatUtility::VAR_FLOAT, 0), array(
                'readonly' => true
            ));
            /*]*/

            /* Product [ */
            $frm->addTextBox(Labels::getLabel('LBL_Product', $this->siteLangId), 'promotion_product')->requirements()->setRequired(true);
            $prodUnReqObj = new FormFieldRequirement('promotion_product', Labels::getLabel('LBL_Product', $this->siteLangId));
            $prodUnReqObj->setRequired(false);

            $prodReqObj = new FormFieldRequirement('promotion_product', Labels::getLabel('LBL_Product', $this->siteLangId));
            $prodReqObj->setRequired(true);

            $frm->addTextBox(Labels::getLabel('LBL_CPC'.'_['.CommonHelper::getDefaultCurrencySymbol().']', $this->siteLangId), 'promotion_product_cpc', FatApp::getConfig('CONF_CPC_PRODUCT', FatUtility::VAR_FLOAT, 0), array(
                'readonly' => true
            ));
            /* ]*/

            /* Banner Url [*/
            $frm->addTextBox(Labels::getLabel('LBL_Url', $this->siteLangId), 'banner_url')->requirements()->setRequired(true);
            $urlUnReqObj = new FormFieldRequirement('banner_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlUnReqObj->setRequired(false);

            $urlReqObj = new FormFieldRequirement('banner_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlReqObj->setRequired(true);
            /*]*/

            /* Slide Url [*/
            $frm->addTextBox(Labels::getLabel('LBL_Url', $this->siteLangId), 'slide_url')->requirements()->setRequired(true);
            $urlSlideUnReqObj = new FormFieldRequirement('slide_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlSlideUnReqObj->setRequired(false);

            $urlSlideReqObj = new FormFieldRequirement('slide_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlSlideReqObj->setRequired(true);

            $frm->addTextBox(Labels::getLabel('LBL_CPC', $this->siteLangId), 'promotion_slides_cpc', FatApp::getConfig('CONF_CPC_SLIDES', FatUtility::VAR_FLOAT, 0), array(
                'readonly' => true
            ));

            /* $frm->addSelectBox(Labels::getLabel('LBL_Open_In',$this->siteLangId), 'slide_target', $linkTargetsArr, '',array(),'');     */
            /*]*/

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'banner_url', $urlUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'banner_url', $urlUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'banner_url', $urlReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'banner_url', $urlUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'promotion_product', $prodUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'promotion_product', $prodUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'promotion_product', $prodReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'promotion_product', $prodUnReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'promotion_shop', $shopUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'promotion_shop', $shopReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'promotion_shop', $shopUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'promotion_shop', $shopUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'slide_url', $urlSlideUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'slide_url', $urlSlideUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'slide_url', $urlSlideUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'slide_url', $urlSlideReqObj);
        } else {
            /* $frm->addHiddenField('','promotion_type',Promotion::TYPE_BANNER);
            $frm->addTextBox(Labels::getLabel('LBL_Url',$this->siteLangId), 'banner_url')->requirements()->setRequired(true); */

            /* Banner Url [*/
            $frm->addTextBox(Labels::getLabel('LBL_Url', $this->siteLangId), 'banner_url')->requirements()->setRequired(true);
            $urlUnReqObj = new FormFieldRequirement('banner_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlUnReqObj->setRequired(false);

            $urlReqObj = new FormFieldRequirement('banner_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlReqObj->setRequired(true);
            /*]*/

            /* Slide Url [*/
            $frm->addTextBox(Labels::getLabel('LBL_Url', $this->siteLangId), 'slide_url')->requirements()->setRequired(true);
            $urlSlideUnReqObj = new FormFieldRequirement('slide_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlSlideUnReqObj->setRequired(false);

            $urlSlideReqObj = new FormFieldRequirement('slide_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlSlideReqObj->setRequired(true);

            $frm->addTextBox(Labels::getLabel('LBL_CPC' . '_[' . commonHelper::getDefaultCurrencySymbol() . ']', $this->siteLangId), 'promotion_slides_cpc', FatApp::getConfig('CONF_CPC_SLIDES', FatUtility::VAR_FLOAT, 0), array(
                'readonly' => true
            ));

            /* $frm->addSelectBox(Labels::getLabel('LBL_Open_In',$this->siteLangId), 'slide_target', $linkTargetsArr, '',array(),''); */
            /*]*/

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'banner_url', $urlReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'banner_url', $urlUnReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'slide_url', $urlSlideUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'slide_url', $urlSlideReqObj);
        }

        //$frm->addTextBox(Labels::getLabel('LBL_Url',$this->siteLangId), 'banner_url')->requirements()->setRequired(true);


        /* $frm->addSelectBox(Labels::getLabel('LBL_Open_In',$this->siteLangId), 'banner_target', $linkTargetsArr, '',array(),'');
         */

        $srch = BannerLocation::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(array(
            'blocation_id',
            'blocation_promotion_cost',
            'ifnull(blocation_name,blocation_identifier) as blocation_name'
        ));
        $rs          = $srch->getResultSet();
        $row         = FatApp::getDb()->fetchAll($rs, 'blocation_id');
        $locationArr = array();
        if (!empty($row)) {
            foreach ($row as $key => $val) {
                $locationArr[$key] = $val['blocation_name'] . ' ( ' . CommonHelper::displayMoneyFormat($val['blocation_promotion_cost']) . ' )';
            }
        }

        $fld = $frm->addTextBox(Labels::getLabel('LbL_Budget'.'_[' . commonHelper::getDefaultCurrencySymbol() .']', $this->siteLangId), 'promotion_budget');
        $fld->requirements()->setRequired();
        $fld->requirements()->setFloatPositive(true);

        $locIdFld         = $frm->addSelectBox(Labels::getLabel('LBL_Location', $this->siteLangId), 'banner_blocation_id', $locationArr, '', array(), Labels::getLabel('LBL_Select', $this->siteLangId))->requirements()->setRequired(true);
        $locIdFldUnReqObj = new FormFieldRequirement('banner_blocation_id', Labels::getLabel('LBL_Location', $this->siteLangId));
        $locIdFldUnReqObj->setRequired(false);

        $locIdFldReqObj = new FormFieldRequirement('banner_blocation_id', Labels::getLabel('LBL_Location', $this->siteLangId));
        $locIdFldReqObj->setRequired(true);

        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'banner_blocation_id', $locIdFldReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'banner_blocation_id', $locIdFldUnReqObj);

        if (User::isSeller()) {
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'banner_blocation_id', $locIdFldUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'banner_blocation_id', $locIdFldUnReqObj);
        }


        $fldDuration = $frm->addSelectBox(Labels::getLabel('LBL_Duration', $this->siteLangId), 'promotion_duration', Promotion::getPromotionBudgetDurationArr($this->siteLangId), '', array(
            'id' => 'promotion_duration'
        ))->requirements()->setRequired();

        $frm->addDateField(Labels::getLabel('LBL_Start_Date', $this->siteLangId), 'promotion_start_date', '', array(
            'placeholder' => Labels::getLabel('LBL_Date_From', $this->siteLangId),
            'readonly' => 'readonly'
        ))->requirements()->setRequired();
        $frm->addDateField(Labels::getLabel('LBL_End_Date', $this->siteLangId), 'promotion_end_date', '', array(
            'placeholder' => Labels::getLabel('LBL_Date_To', $this->siteLangId),
            'readonly' => 'readonly'
        ))->requirements()->setRequired();

        $fld               = $frm->addRequiredField(Labels::getLabel('LBL_promotion_start_time', $this->siteLangId), 'promotion_start_time', '', array(
            'class' => 'time',
            'readonly' => 'readonly'
        ));
        $fld               = $frm->addRequiredField(Labels::getLabel('LBL_promotion_end_time', $this->siteLangId), 'promotion_end_time', '', array(
            'class' => 'time',
            'readonly' => 'readonly'
        ));
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->siteLangId), 'promotion_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function getPromotionLangForm($promotionId, $langId)
    {
        $frm = new Form('frmPromotionLang');
        $frm->addHiddenField('', 'promotion_id', $promotionId);
        $frm->addHiddenField('', 'lang_id', $langId);
        $frm->addRequiredField(Labels::getLabel('LBL_promotion_name', $langId), 'promotion_name');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    private function getPromotionMediaForm($promotionId = 0, $promotionType = 0)
    {
        $promotionId = FatUtility::int($promotionId);
        $frm         = new Form('frmPromotionMedia');

        $frm->addHiddenField('', 'promotion_id', $promotionId);
        $frm->addHiddenField('', 'promotion_type', $promotionType);

        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->siteLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $screenArr = applicationConstants::getDisplaysArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel("LBL_Display_For", $this->siteLangId), 'banner_screen', $screenArr, '', array(), '');
        $fld = $frm->addButton(Labels::getLabel('LBL_Banner_Image', $this->siteLangId), 'banner_image', Labels::getLabel('LBL_Upload_File', $this->siteLangId), array(
            'class' => 'bannerFile-Js',
            'id' => 'banner_image'
        ));

        return $frm;
    }

    private function getPromotionSearchForm($langId)
    {
        $langId = FatUtility::int($langId);

        $frm = new Form('frmPromotionSearch');
        $frm->addTextBox('', 'keyword', '', array(
            'placeholder' => Labels::getLabel('LBL_keyword', $langId)
        ));

        $typeArr = Promotion::getTypeArr($langId);
        if (!User::isSeller()) {
            unset($typeArr[Promotion::TYPE_SHOP]);
            unset($typeArr[Promotion::TYPE_PRODUCT]);
        }
        $frm->addSelectBox('', 'active_promotion', array(
            '-1' => Labels::getLabel('LBL_All', $langId),
            '1' => Labels::getLabel('LBL_Active_Promotions', $langId)
        ), '', array(), '');
        $frm->addSelectBox('', 'type', array(
            '-1' => Labels::getLabel('LBL_All_Type', $langId)
        ) + $typeArr, '', array(), '');

        $frm->addDateField('', 'date_from', '', array(
            'readonly' => 'readonly',
            'class' => 'field--calender',
            'placeholder' => Labels::getLabel('LBL_Date_From', $langId)
        ));
        $frm->addDateField('', 'date_to', '', array(
            'readonly' => 'readonly',
            'class' => 'field--calender',
            'placeholder' => Labels::getLabel('LBL_Date_To', $langId)
        ));

        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldClear  = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array(
            'onclick' => 'clearPromotionSearch();'
        ));
        /* $fldSubmit->attachField($fldClear); */
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function getPPCAnalyticsSearchForm($langId)
    {
        $langId = FatUtility::int($langId);

        $frm = new Form('frmPromotionAnalyticsSearch');


        $frm->addDateField('', 'date_from', '', array(
            'readonly' => 'readonly',
            'class' => 'field--calender',
            'placeholder' => Labels::getLabel('LBL_Date_From', $langId)
        ));
        $frm->addDateField('', 'date_to', '', array(
            'readonly' => 'readonly',
            'class' => 'field--calender',
            'placeholder' => Labels::getLabel('LBL_Date_To', $langId)
        ));

        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldClear  = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array(
            'onclick' => 'clearPromotionSearch();'
        ));

        $frm->addHiddenField('', 'page');

        $frm->addHiddenField('', 'promotion_id');
        return $frm;
    }

    private function getRechargeWalletForm($langId)
    {
        $frm = new Form('frmRechargeWallet');
        $fld = $frm->addFloatField('', 'amount');
        //$fld->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Add_Money_to_account', $langId));
        return $frm;
    }

    public function checkValidPromotionBudget()
    {
        $post            = FatApp::getPostedData();
        $promotionType   = Fatutility::int($post['promotion_type']);
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
                $srch = BannerLocation::getSearchObject($this->siteLangId);
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
            Message::addErrorMessage(Labels::getLabel("MSG_Budget_should_be_greater_than_CPC", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function getBannerLocationDimensions($promotionId, $deviceType)
    {
        $srch = new PromotionSearch($this->siteLangId);
        $srch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b', $deviceType);
        $srch->addCondition('promotion_id', '=', $promotionId);
        $srch->addMultipleFields(array(
            'blocation_banner_width',
            'blocation_banner_height'
        ));
        $rs               = $srch->getResultSet();
        $bannerDimensions = FatApp::getDb()->fetch($rs);
        $this->set('bannerWidth', $bannerDimensions['blocation_banner_width']);
        $this->set('bannerHeight', $bannerDimensions['blocation_banner_height']);
        $this->_template->render(false, false, 'json-success.php');
    }
}
