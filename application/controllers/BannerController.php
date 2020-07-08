<?php
class BannerController extends MyAppController
{
    public function index()
    {
    }

    public function url($bannerId = 0)
    {
        $bannerId = FatUtility::int($bannerId);
        if (1 > $bannerId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('home'));
        }
        $srch = new BannerSearch($this->siteLangId, true);
        $srch->joinLocations($this->siteLangId, true);
        $srch->joinPromotions();
        $srch->addSkipExpiredPromotionAndBannerCondition();
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition('banner_id', '=', $bannerId);
        $srch->addMultipleFields(array('banner_id','banner_url','banner_type','banner_blocation_id','banner_record_id','blocation_promotion_cost','promotion_cpc'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if ($row == false) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('home'));
        }

        $url  = str_replace('{SITEURL}', CommonHelper::generateFullUrl(), $row['banner_url']);

        $userId = 0;
        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();
        }
        if (Promotion::isUserClickCountable($userId, $row['banner_record_id'], $_SERVER['REMOTE_ADDR'], session_id())) {
            switch ($row['banner_type']) {
                case Banner::TYPE_BANNER:

                    break;

                case Banner::TYPE_PPC:
                    $promotionClickData = array(
                    'pclick_promotion_id' => $row['banner_record_id'],
                    'pclick_user_id' => $userId,
                    'pclick_datetime' => date('Y-m-d H:i:s'),
                    'pclick_ip' => $_SERVER['REMOTE_ADDR'],
                    /* 'pclick_cost' => $row['blocation_promotion_cost'], */
                    'pclick_cost' => $row['promotion_cpc'],
                    'pclick_session_id' => session_id(),
                    );
                    FatApp::getDb()->insertFromArray(Promotion::DB_TBL_CLICKS, $promotionClickData, false, '', $promotionClickData);

                    $clickId= FatApp::getDb()->getInsertId();

                    $promotionClickChargesData = array(

                    'picharge_pclick_id' => $clickId,
                    'picharge_datetime'  => date('Y-m-d H:i:s'),
                    /* 'picharge_cost'  => $row['blocation_promotion_cost'], */
                    'picharge_cost'  => $row['promotion_cpc'],

                    );

                    FatApp::getDb()->insertFromArray(Promotion::DB_TBL_ITEM_CHARGES, $promotionClickChargesData, false);


                    $promotionLogData = array(
                    'plog_promotion_id' => $row['banner_record_id'],
                    'plog_date' =>  date('Y-m-d'),
                    'plog_clicks' =>  1,
                    );

                    $onDuplicatePromotionLogData = array_merge($promotionLogData, array('plog_clicks'=>'mysql_func_plog_clicks+1'));
                    FatApp::getDb()->insertFromArray(Promotion::DB_TBL_LOGS, $promotionLogData, true, array(), $onDuplicatePromotionLogData);
                    break;
            }
        }
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            FatApp::redirectUser($url);
        }

        FatApp::redirectUser(CommonHelper::generateUrl(''));
    }

    public function HomePageBannerTopLayout($bannerId, $langId = 0, $screen = 0)
    {
        $bannerDimensions = BannerLocation::getDimensions(BannerLocation::HOME_PAGE_TOP_BANNER, $screen);
        $w = 1350;
        $h = 405;
        /*Desktop default value need to update in DB*/
        if (array_key_exists('blocation_banner_width', $bannerDimensions)) {
            $w = $bannerDimensions['blocation_banner_width'];
        }
        if (array_key_exists('blocation_banner_height', $bannerDimensions)) {
            $h = $bannerDimensions['blocation_banner_height'];
        }
        $this->showBanner($bannerId, $langId, $w, $h, $screen);
    }

    // For Mobile API
    public function HomePageBannerMiddleLayout($bannerId, $langId = 0, $screen = 0)
    {
        $bannerDimensions = BannerLocation::getDimensions(BannerLocation::HOME_PAGE_MIDDLE_BANNER, $screen);
        $w = 600;
        $h = 338;
         /*Desktop default value need to update in DB*/
        if (array_key_exists('blocation_banner_width', $bannerDimensions)) {
            $w = $bannerDimensions['blocation_banner_width'];
        }
        if (array_key_exists('blocation_banner_height', $bannerDimensions)) {
            $h = $bannerDimensions['blocation_banner_height'];
        }
        $this->showBanner($bannerId, $langId, $w, $h, $screen);
    }

    public function HomePageBannerBottomLayout($bannerId, $langId = 0, $screen = 0)
    {
        $bannerDimensions = BannerLocation::getDimensions(BannerLocation::HOME_PAGE_BOTTOM_BANNER, $screen);
        $w = 600;
        $h = 198;
         /*Desktop default value need to update in DB*/
        if (array_key_exists('blocation_banner_width', $bannerDimensions)) {
            $w = $bannerDimensions['blocation_banner_width'];
        }
        if (array_key_exists('blocation_banner_height', $bannerDimensions)) {
            $h = $bannerDimensions['blocation_banner_height'];
        }
        $this->showBanner($bannerId, $langId, $w, $h, $screen);
    }

    public function productDetailPageBanner($bannerId, $langId = 0, $screen = 0)
    {
        $bannerDimensions = BannerLocation::getDimensions(BannerLocation::PRODUCT_DETAIL_PAGE_BANNER, $screen);
        $w = 600;
        $h = 198;
         /*Desktop default value need to update in DB*/
        if (array_key_exists('blocation_banner_width', $bannerDimensions)) {
            $w = $bannerDimensions['blocation_banner_width'];
        }
        if (array_key_exists('blocation_banner_height', $bannerDimensions)) {
            $h = $bannerDimensions['blocation_banner_height'];
        }
        $this->showBanner($bannerId, $langId, $w, $h, $screen);
    }

    public function Thumb($bannerId, $langId = 0, $screen = 0)
    {
        $this->showBanner($bannerId, $langId, 100, 100, $screen);
    }

    public function showBanner($bannerId, $langId, $w = '200', $h = '200', $screen = 0)
    {
        $bannerId = FatUtility::int($bannerId);
        $langId = FatUtility::int($langId);

        $fileRow = AttachedFile::getAttachment(AttachedFile::FILETYPE_BANNER, $bannerId, 0, $langId, true, $screen);
        $image_name = isset($fileRow['afile_physical_path']) ?  $fileRow['afile_physical_path'] : '';
        AttachedFile::displayImage($image_name, $w, $h, '', '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false, true, false);
    }

    public function showOriginalBanner($bannerId, $langId, $screen = 0)
    {
        $bannerId = FatUtility::int($bannerId);
        $langId = FatUtility::int($langId);

        $fileRow = AttachedFile::getAttachment(AttachedFile::FILETYPE_BANNER, $bannerId, 0, $langId, true, $screen);
        $image_name = isset($fileRow['afile_physical_path']) ?  $fileRow['afile_physical_path'] : '';
        AttachedFile::displayOriginalImage($image_name, '', '', true);
    }

    public function categories()
    {
        $bannerListing = $this->getBanners('Category_Page_Left', $this->siteLangId);
        $this->set('bannerListing', $bannerListing);
        $this->_template->render(false, false);
    }

    public function Products()
    {
        $bannerListing = $this->getBanners('Product_Page_Right', $this->siteLangId);
        $this->set('bannerListing', $bannerListing);
        $this->_template->render(false, false);
    }

    public function allProducts()
    {
        $bannerListing = $this->getBanners('All_Products_Left', $this->siteLangId);
        $this->set('bannerListing', $bannerListing);
        $this->_template->render(false, false);
    }

    public function blogPage()
    {
        $bannerListing = $this->getBanners('Blog_Section_Right', $this->siteLangId);
        $this->set('bannerListing', $bannerListing);
        $this->_template->render(false, false);
    }

    public function Brands()
    {
        $bannerListing = $this->getBanners('Brand_Page_Left', $this->siteLangId);
        $this->set('bannerListing', $bannerListing);
        $this->_template->render(false, false);
    }

    public function searchListing()
    {
        $bannerListing = $this->getBanners('Search_Page_Left', $this->siteLangId);

        $this->set('bannerListing', $bannerListing);
        $this->_template->render(false, false);
    }

    private function getBanners($type, $langId)
    {
        if ($type == '') {
            return;
        }

        $bannerDataCache =  FatCache::get('bannersCache'.$type.'_'.$langId, CONF_IMG_CACHE_TIME, '.txt');
        if ($bannerDataCache) {
            return unserialize($bannerDataCache);
        }

        $db = FatApp::getDb();
        $bannerSrch = Banner::getBannerLocationSrchObj(true);
        $bannerSrch->addCondition('blocation_key', '=', $type);
        $rs = $bannerSrch->getResultSet();
        $bannerLocation = $db->fetch($rs);

        if (empty($bannerLocation)) {
            return;
        }

        $srch = Banner::getSearchObject($langId, true);
        $srch->doNotCalculateRecords();

        if ($bannerLocation['blocation_banner_count'] > 0) {
            $srch->setPageSize($bannerLocation['blocation_banner_count']);
        }

        $srch->addCondition('banner_blocation_id', '=', $bannerLocation['blocation_id']);
        $rs = $srch->getResultSet();

        return $bannerListing = $db->fetchAll($rs, 'banner_id');
        FatCache::set('bannersCache'.$type.'_'.$langId, serialize($bannerListing), '.txt');
    }

    public function locationFrames($frameId, $sizeType='')
    {
        $frameId = FatUtility::int($frameId);
        if (1>$frameId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('frameId', $frameId);
        $this->_template->render(false, false);
    }
}
