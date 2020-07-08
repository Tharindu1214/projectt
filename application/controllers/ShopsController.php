<?php
class ShopsController extends MyAppController
{
    //use CommonServices;

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $searchForm = $this->getShopSearchForm($this->siteLangId);
        $this->set('searchForm', $searchForm);
        $this->_template->addJs('js/slick.min.js');
        $this->_template->addCss(array('css/slick.css','css/product-detail.css'));
        $this->_template->render();
    }

    public function featured()
    {
        $searchForm = $this->getShopSearchForm($this->siteLangId);
        $params['featured'] = 1;
        $searchForm->fill($params);
        $this->set('searchForm', $searchForm);
        $this->_template->addCss('css/slick.css');
        $this->_template->addJs('js/slick.js');
        $this->_template->render();
    }

    public function search()
    {
        $db = FatApp::getDb();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : FatUtility::int($data['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $searchForm = $this->getShopSearchForm($this->siteLangId);
        $post = $searchForm->getFormDataFromArray($data);

        /* SubQuery, Shop have products[ */
        $prodShopSrch = new ProductSearch($this->siteLangId);
        $prodShopSrch->setDefinedCriteria();
        $prodShopSrch->joinProductToCategory();
        $prodShopSrch->doNotCalculateRecords();
        $prodShopSrch->doNotLimitRecords();
        $prodShopSrch->joinSellerSubscription($this->siteLangId, true);
        $prodShopSrch->addSubscriptionValidCondition();
        $prodShopSrch->addMultipleFields(array('distinct(shop_id)'));
        //$rs = $prodShopSrch->getResultSet();
        /* $productRows = FatApp::getDb()->fetchAll($rs);
        $shopMainRootArr = array_unique(array_column($productRows,'shop_id')); */
        /* ] */

        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->joinSellerSubscription();
        $srch->joinTable('('. $prodShopSrch->getQuery() . ')', 'INNER JOIN', 'temp.shop_id = s.shop_id', 'temp');
        /* if($shopMainRootArr){
        $srch->addCondition('shop_id', 'in', $shopMainRootArr);
        } */
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        /* sub query to find out that logged user have marked shops as favorite or not[ */
        $favSrchObj = new UserFavoriteShopSearch();
        $favSrchObj->doNotCalculateRecords();
        $favSrchObj->doNotLimitRecords();
        $favSrchObj->addMultipleFields(array('ufs_shop_id','ufs_id'));
        $favSrchObj->addCondition('ufs_user_id', '=', $loggedUserId);
        $srch->joinTable('('. $favSrchObj->getQuery() . ')', 'LEFT OUTER JOIN', 'ufs_shop_id = s.shop_id', 'ufs');
        /* ] */

        $srch->addMultipleFields(
            array( 's.shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'IFNULL(shop_name, shop_identifier) as shop_name', 'shop_description',
            'shop_country_l.country_name as country_name', 'shop_state_l.state_name as state_name', 'shop_city',
            'IFNULL(ufs.ufs_id, 0) as is_favorite' )
        );

        $featured = FatApp::getPostedData('featured', FatUtility::VAR_INT, 0);
        if (0 < $featured) {
            $srch->addCondition('shop_featured', '=', $featured);
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addOrder('shop_created_on');
        $shopRs = $srch->getResultSet();
        $allShops = $db->fetchAll($shopRs, 'shop_id');

        $totalProdCountToDisplay = 4;

        $productSrchObj = new ProductSearch($this->siteLangId);
        $productSrchObj->joinProductToCategory($this->siteLangId);
        $productSrchObj->doNotCalculateRecords();
        /* $productSrchObj->setPageSize( 10 ); */
        $productSrchObj->setDefinedCriteria();
        $productSrchObj->joinSellerSubscription($this->siteLangId, true);
        $productSrchObj->addSubscriptionValidCondition();
        // $productSrchObj->joinProductRating();

        if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $productSrchObj->joinFavouriteProducts($loggedUserId);
            $productSrchObj->addFld('ufp_id');
        } else {
            $productSrchObj->joinUserWishListProducts($loggedUserId);
            $productSrchObj->addFld('IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }

        $productSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $productSrchObj->addMultipleFields(
            array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'product_image_updated_on',
            'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
            'theprice', 'selprod_price','selprod_stock', 'selprod_condition','prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','selprod_sold_count','IF(selprod_stock > 0, 1, 0) AS in_stock')
        );
        foreach ($allShops as $val) {
            $productShopSrchTempObj = clone $productSrchObj;
            $productShopSrchTempObj->addCondition('selprod_user_id', '=', $val['shop_user_id']);
            $productShopSrchTempObj->addOrder('in_stock', 'DESC');
            $productShopSrchTempObj->addGroupBy('selprod_product_id');
            $productShopSrchTempObj->setPageSize(4);
            $Prs = $productShopSrchTempObj->getResultSet();
            $allShops[$val['shop_id']]['products'] = $db->fetchAll($Prs);
            $allShops[$val['shop_id']]['totalProducts'] = $productShopSrchTempObj->recordCount();
            $allShops[$val['shop_id']]['shopRating'] = SelProdRating::getSellerRating($val['shop_user_id']);
            $allShops[$val['shop_id']]['shopTotalReviews'] = SelProdReview::getSellerTotalReviews($val['shop_user_id']);
        }
        /* CommonHelper::printArray($allShops[4]['products']); */
        $this->set('allShops', $allShops);
        $this->set('totalProdCountToDisplay', $totalProdCountToDisplay);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());

        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        $startRecord = ($page-1)* $pagesize + 1 ;
        $endRecord = $pagesize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }
        $json['totalRecords'] = $totalRecords;
        $json['startRecord'] = $startRecord;
        $json['endRecord'] = $endRecord;

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $json['html'] = $this->_template->render(false, false, 'shops/search.php', true, false);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, '_partial/load-more-btn.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    private function getShopSearchForm()
    {
        $frm = new Form('frmSearchShops');
        $frm->addHiddenField('', 'featured', 0);
        return $frm;
    }

    protected function getSearchForm()
    {
        return Shop::getFilterSearchForm();
    }

    public function view($shop_id)
    {
        $db = FatApp::getDb();

        $this->shopDetail($shop_id);

        if (true ===  MOBILE_APP_API_CALL) {
            $get = FatApp::getPostedData();
        } else {
            $get = FatApp::getParameters();
            $get = array_filter(Product::convertArrToSrchFiltersAssocArr($get));
        }
        // CommonHelper::printArray($get, true);
        if (array_key_exists('currency', $get)) {
            $get['currency_id'] = $get['currency'];
        }
        if (array_key_exists('sort', $get)) {
            $get['sortOrder'] = $get['sort'];
        }

        $includeShopData = true;
        if (array_key_exists('includeShopData', $get) && 1 > FatUtility::int($get['includeShopData'])) {
            $includeShopData = false;
        }
        //$get['join_price'] = 1;
        $get['shop_id'] = $shop_id;

        $data = $this->getListingData($get, $includeShopData);

        if (false ===  MOBILE_APP_API_CALL) {
            $frm = $this->getProductSearchForm();
            $frm->fill($get);

            $arr = array(
                'frmProductSearch'=>$frm,
                'canonicalUrl'=>CommonHelper::generateFullUrl('Shops', 'view', array($shop_id)),
                'productSearchPageType'=>SavedSearchProduct::PAGE_SHOP,
                'recordId'=>$shop_id,
                'bannerListigUrl'=>CommonHelper::generateFullUrl('Banner', 'categories'),
            );
            $data = array_merge($data, $arr);

            $this->includeProductPageJsCss();
            $this->_template->addJs('js/slick.min.js');
            $this->_template->addCss(array('css/slick.css','css/product-detail.css'));
            $this->_template->addJs('js/shop-nav.js');
            $this->_template->addJs('js/jquery.colourbrightness.min.js');
        }
        if (true ===  MOBILE_APP_API_CALL && true === $includeShopData) {
            $shopInfo = $this->shopPoliciesData($this->getShopInfo($shop_id));
            $data['shop'] = array_merge($data['shop'], $shopInfo);
            $data['shop']['rating'] = 0;
            if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                $data['shop']['rating'] = SelProdRating::getSellerRating($data['shop']['shop_user_id']);
            }
            $data['shop']['shop_logo'] = CommonHelper::generateFullUrl('image', 'shopLogo', array($data['shop']['shop_id'], $this->siteLangId));
            $data['shop']['shop_banner'] = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'shopBanner', array($data['shop']['shop_id'], $this->siteLangId, 'MOBILE', 0, applicationConstants::SCREEN_MOBILE)), CONF_IMG_CACHE_TIME, '.jpg');
        }

        $this->set('data', $data);

        if (false === MOBILE_APP_API_CALL) {
            $this->includeProductPageJsCss();
            $this->_template->addJs(array('js/slick.min.js', 'js/responsive-img.min.js', 'js/shop-nav.js', 'js/jquery.colourbrightness.min.js'));
            $this->_template->addCss(array('css/slick.css','css/product-detail.css'));
        }

        $this->_template->render();
    }

    public function showBackgroundImage($shop_id = 0, $lang_id = 0, $templateId = '')
    {
        $recordId = FatUtility::int($shop_id);
        $lang_id = FatUtility::int($lang_id);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE, $recordId, 0, $lang_id);
        if (!$file_row && !$this->getAllowedShowBg($templateId)) {
            return false;
        }

        return true;
    }

    public function shopDetail($shop_id, $policy = false)
    {
        $db = FatApp::getDb();

        $shop_id = FatUtility::int($shop_id);

        if ($shop_id <= 0) {
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'Shop'));
        }

        $shopDetails = Shop::getAttributesByid($shop_id);
        if (UserAuthentication::isUserLogged() && UserAuthentication::getLoggedUserId() == $shopDetails['shop_user_id'] && !UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'Packages'));
        }

        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->joinSellerSubscription();
        $srch->doNotCalculateRecords();
        $srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tu.user_id = shop_user_id', 'tu');
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        /* sub query to find out that logged user have marked current shop as favorite or not[ */
        $favSrchObj = new UserFavoriteShopSearch();
        $favSrchObj->doNotCalculateRecords();
        $favSrchObj->doNotLimitRecords();
        $favSrchObj->addMultipleFields(array('ufs_shop_id','ufs_id'));
        $favSrchObj->addCondition('ufs_user_id', '=', $loggedUserId);
        $favSrchObj->addCondition('ufs_shop_id', '=', $shop_id);
        $srch->joinTable('('. $favSrchObj->getQuery() . ')', 'LEFT OUTER JOIN', 'ufs_shop_id = shop_id', 'ufs');
        /* ] */

        $srch->addMultipleFields(
            array( 'shop_id','tu.user_name','tu.user_regdate','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city',
            'IFNULL(ufs.ufs_id, 0) as is_favorite' )
        );
        $srch->addCondition('shop_id', '=', $shop_id);
        if ($policy) {
            $srch->addMultipleFields(array('shop_payment_policy', 'shop_delivery_policy','shop_refund_policy','shop_additional_info','shop_seller_info'));
        }
        $shopRs = $srch->getResultSet();
        $shop = $db->fetch($shopRs);

        if (!$shop) {
            FatApp::redirectUser(FatUtility::exitWithErrorCode('404'));
        }

        $shopCategories = array();
        /* Switch($shop['shop_ltemplate_id']){
            case Shop::TEMPLATE_ONE:
            case Shop::TEMPLATE_THREE:
            case Shop::TEMPLATE_FOUR:
            case Shop::TEMPLATE_FIVE:
                $this->_template->addCss('shops/templates/page-css/'.$shop['shop_ltemplate_id'].'.css');
                break;
            case Shop::TEMPLATE_TWO:
                $this->_template->addJs('js/slick.min.js');
                $this->_template->addCss('shops/templates/page-css/'.$shop['shop_ltemplate_id'].'.css');
                break;
            default:
                $this->_template->addCss('shops/templates/page-css/'.SHOP::TEMPLATE_ONE.'.css');
            break;
        } */
        $this->_template->addCss('shops/templates/page-css/'.SHOP::TEMPLATE_ONE.'.css');
        $this->set('shop', $this->shopPoliciesData($shop));
        $this->set('shopRating', SelProdRating::getSellerRating($shop['shop_user_id']));
        $this->set('shopTotalReviews', SelProdReview::getSellerTotalReviews($shop['shop_user_id']));

        $description = trim(CommonHelper::subStringByWords(strip_tags(CommonHelper::renderHtml($shop['shop_description'], true)), 500));
        $description .= ' - '.Labels::getLabel('LBL_See_more_at', $this->siteLangId).": ".CommonHelper::getCurrUrl();

        if ($shop) {
            $socialShareContent = array(
            'title'=>$shop['shop_name'],
            'description'=>$description,
            'image'=>CommonHelper::generateUrl('image', 'shopBanner', array($shop['shop_id'], $this->siteLangId, 'wide')),
            );
            $this->set('socialShareContent', $socialShareContent);
        }

        $shopUserId = FatUtility::int($shop['shop_user_id']);
        if ($shopUserId !== 0) {
            $srchSplat = SocialPlatform::getSearchObject($this->siteLangId);
            $srchSplat->doNotCalculateRecords();
            $srchSplat->doNotLimitRecords();
            $srchSplat->addCondition('splatform_user_id', '=', $shopUserId);
            $db = FatApp::getDb();

            $rs = $srchSplat->getResultSet();

            $socialPlatforms = $db->fetchAll($rs);
            $this->set('socialPlatforms', $socialPlatforms);
        }

        $collection_data= ShopCollection::getShopCollectionsDetail($shop_id, $this->siteLangId);
        $this->set('collectionData', $collection_data);
        $this->set('layoutTemplate', 'shop');
        // $this->set('template_id', ($shop['shop_ltemplate_id']==0)?SHOP::TEMPLATE_ONE:$shop['shop_ltemplate_id']);
        $this->set('template_id', SHOP::TEMPLATE_ONE);
        $this->set('layoutRecordId', $shop['shop_id']);
        $showBgImage = $this->showBackgroundImage($shop_id, $this->siteLangId, SHOP::TEMPLATE_ONE);
        $this->set('showBgImage', $showBgImage);
    }

    public function getAllowedShowBg($templateId = '')
    {
        switch ($templateId) {
            case Shop::TEMPLATE_ONE:
            case Shop::TEMPLATE_TWO:
            case Shop::TEMPLATE_THREE:
                return false;
                break;
            case Shop::TEMPLATE_FOUR:
            case Shop::TEMPLATE_FIVE:
                return true;
                    break;
            default:
                return false;
                 break;
        }
    }

    public function topProducts($shop_id)
    {
        $db = FatApp::getDb();

        $this->shopDetail($shop_id);

        $frm = $this->getProductSearchForm();

        $get = FatApp::getParameters();
        $get = Product::convertArrToSrchFiltersAssocArr($get);

        if (array_key_exists('currency', $get)) {
            $get['currency_id'] = $get['currency'];
        }
        if (array_key_exists('sort', $get)) {
            $get['sortOrder'] = $get['sort'];
        }

        $get['top_products'] = 1;
        $get['shop_id'] = $shop_id;

        $frm->fill($get);

        $data = $this->getListingData($get);

        $arr = array(
            'frmProductSearch'=>$frm,
            'canonicalUrl'=>CommonHelper::generateFullUrl('Shops', 'topProducts', array($shop_id)),
            'productSearchPageType'=>SavedSearchProduct::PAGE_SHOP,
            'recordId'=>$shop_id,
            'bannerListigUrl'=>CommonHelper::generateFullUrl('Banner', 'categories'),
        );

        $data = array_merge($data, $arr);
        $this->set('data', $data);

        $this->includeProductPageJsCss();
        $this->_template->addJs('js/slick.min.js');
        $this->_template->addCss(array('css/slick.css','css/product-detail.css'));
        $this->_template->addJs('js/shop-nav.js');
        $this->_template->addJs('js/jquery.colourbrightness.min.js');
        $this->_template->render(true, true, 'shops/view.php');
    }

    public function policy($shop_id)
    {
        $this->shopDetail($shop_id, true);

        $frm = $this->getProductSearchForm();
        $searchFrm = $this->getSearchForm();
        $frmData = array('shop_id' => $shop_id);
        $frm->fill($frmData);
        $searchFrm->fill($frmData);
        $this->set('frmProductSearch', $frm);
        $this->set('searchFrm', $searchFrm);
        $this->set('shopId', $shop_id);
        $this->_template->addJs('js/slick.js');
        $this->_template->addCss('css/slick.css');
        $this->_template->addJs('js/shop-nav.js');
        $this->_template->addJs('js/jquery.colourbrightness.min.js');

        $this->_template->render();
    }

    public function collection($shop_id, $scollectionId)
    {
        $db = FatApp::getDb();
        $shop_id = FatUtility::int($shop_id);
        $scollectionId = FatUtility::int($scollectionId);
        if (1 > $scollectionId) {
            FatApp::redirectUser(CommonHelper::generateUrl(''));
        }
        $this->shopDetail($shop_id);

        $shopcolDetails = ShopCollection::getCollectionGeneralDetail($shop_id, $scollectionId);

        $frm = $this->getProductSearchForm();

        $get = FatApp::getParameters();
        $get = Product::convertArrToSrchFiltersAssocArr($get);

        if (array_key_exists('currency', $get)) {
            $get['currency_id'] = $get['currency'];
        }
        if (array_key_exists('sort', $get)) {
            $get['sortOrder'] = $get['sort'];
        }
        //$get['join_price'] = 1;
        $get['shop_id'] = $shop_id;
        $get['collection_id'] = $scollectionId;

        $fld = $frm->getField('sortBy');
        $fld->value='popularity_desc';
        $fld->fldType ='hidden';
        $frm->fill($get);

        $data = $this->getListingData($get);

        $arr = array(
            'frmProductSearch'=>$frm,
            'canonicalUrl'=>CommonHelper::generateFullUrl('Shops', 'collection', array($shop_id, $scollectionId)),
            'productSearchPageType'=>SavedSearchProduct::PAGE_SHOP,
            'recordId'=>$shop_id,
            'bannerListigUrl'=>CommonHelper::generateFullUrl('Banner', 'categories'),
        );

        $data = array_merge($data, $arr);
        $this->set('data', $data);

        $this->includeProductPageJsCss();
        $this->_template->addJs('js/slick.min.js');
        $this->_template->addCss(array('css/slick.css','css/product-detail.css'));
        $this->_template->addJs('js/shop-nav.js');
        $this->_template->addJs('js/jquery.colourbrightness.min.js');
        $this->_template->render(true, true, 'shops/view.php');
    }

    public function sendMessage($shop_id, $selprod_id = 0)
    {
        UserAuthentication::checkLogin();
        $shop_id = FatUtility::int($shop_id);
        $selprod_id = FatUtility::int($selprod_id);
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $db = FatApp::getDb();

        $shop = $this->getShopInfo($shop_id);
        if (!$shop) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Home'));
        }

        $frm = $this->getSendMessageForm($this->siteLangId);
        $userObj = new User($loggedUserId);
        $loggedUserData = $userObj->getUserInfo(array('user_id', 'user_name', 'credential_username'));
        $frmData = array( 'shop_id' => $shop_id  );

        if ($selprod_id > 0) {
            $frmData['product_id'] = $selprod_id;
            $srch = SellerProduct::getSearchObject($this->siteLangId);
            $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
            $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.$this->siteLangId, 'p_l');
            $srch->addMultipleFields(array('IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title'));
            $srch->addCondition('selprod_id', '=', $selprod_id);
            $db = FatApp::getDb();
            $rs = $srch->getResultSet();
            $products = $db->fetch($rs);
            $this->set('product', $products);
        }

        $frm->fill($frmData);
        $this->set('frm', $frm);
        $this->set('loggedUserData', $loggedUserData);
        $this->set('shop', $shop);
        $this->_template->render();
    }

    public function setUpSendMessage()
    {
        UserAuthentication::checkLogin();
        $frm = $this->getSendMessageForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $loggedUserId = UserAuthentication::getLoggedUserId();
        if (false == $post) {
            LibHelper::dieJsonError(current($frm->getValidationErrors()));
        }

        $shop_id = FatUtility::int($post['shop_id']);
        $shopData = $this->getShopInfo($shop_id);
        if (!$shopData) {
            $message = Labels::getLabel('LBL_Invalid_Shop', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        if ($shopData['shop_user_id'] == $loggedUserId) {
            $message = Labels::getLabel('LBL_You_are_not_allowed_to_send_message', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $threadObj = new Thread();
        $threadDataToSave = array(
        'thread_subject'    =>    $post['thread_subject'],
        'thread_started_by' =>    $loggedUserId,
        'thread_start_date'    =>    date('Y-m-d H:i:s')
        );

        if (isset($post['product_id']) && $post['product_id']>0) {
            $product_id = FatUtility::int($post['product_id']);
            $threadDataToSave['thread_type'] = Thread::THREAD_TYPE_PRODUCT;
            $threadDataToSave['thread_record_id'] = $product_id;
        } else {
            $threadDataToSave['thread_type'] =    Thread::THREAD_TYPE_SHOP;
            $threadDataToSave['thread_record_id'] =    $shop_id;
        }

        $threadObj->assignValues($threadDataToSave);

        if (!$threadObj->save()) {
            $message = Labels::getLabel($threadObj->getError(), $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $thread_id = $threadObj->getMainTableRecordId();

        $threadMsgDataToSave = array(
        'message_thread_id'    =>    $thread_id,
        'message_from'        =>    $loggedUserId,
        'message_to'        =>    $shopData['shop_user_id'],
        'message_text'        =>    $post['message_text'],
        'message_date'        =>    date('Y-m-d H:i:s'),
        'message_is_unread'    =>    1,
        'message_deleted'    =>    0
        );
        if (!$message_id = $threadObj->addThreadMessages($threadMsgDataToSave)) {
            $message = Labels::getLabel($threadObj->getError(), $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($message_id) {
            $emailObj = new EmailHandler();
            if (!$emailObj->SendMessageNotification($message_id, $this->siteLangId)) {
                LibHelper::dieJsonError($emailObj->getError());
            }
        }
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function reportSpam($shop_id)
    {
        UserAuthentication::checkLogin();
        $db = FatApp::getDb();
        $shop_id = FatUtility::int($shop_id);

        $shop = $this->getShopInfo($shop_id);
        if (!$shop) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Home'));
        }
        $frm = $this->getReportSpamForm($this->siteLangId);
        $frm->fill(array( 'shop_id' => $shop_id ));
        $this->set('frm', $frm);
        $this->set('shop', $shop);
        $this->_template->render();
    }

    public function setUpShopSpam()
    {
        UserAuthentication::checkLogin();
        $frm = $this->getReportSpamForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $loggedUserId = UserAuthentication::getLoggedUserId();

        if (false == $post) {
            LibHelper::dieJsonError(current($frm->getValidationErrors()));
        }

        $shop_id = FatUtility::int($post['shop_id']);
        if (1 > $shop_id) {
            LibHelper::dieJsonError(Labels::getLabel('LBL_Invalid_Shop', $this->siteLangId));
        }

        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->joinSellerSubscription();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array( 'shop_id', 'shop_user_id'));
        $srch->addCondition('shop_id', '=', $shop_id);
        $shopRs = $srch->getResultSet();
        $shopData = FatApp::getDb()->fetch($shopRs);

        if (!$shopData) {
            LibHelper::dieJsonError(Labels::getLabel('LBL_Invalid_Shop', $this->siteLangId));
        }

        $sReportObj = new ShopReport();
        $dataToSave = array(
        'sreport_shop_id'            =>    $shop_id,
        'sreport_reportreason_id'    =>    $post['sreport_reportreason_id'],
        'sreport_message'            =>    $post['sreport_message'],
        'sreport_user_id'            =>    $loggedUserId,
        'sreport_added_on'            =>    date('Y-m-d H:i:s'),
        );

        $sReportObj->assignValues($dataToSave);
        if (!$sReportObj->save()) {
            FatUtility::dieWithError(strip_tags(Labels::getLabel($sReportObj->getError(), $this->siteLangId)));
        }

        $sreport_id = $sReportObj->getMainTableRecordId();

        if (!$sreport_id) {
            FatUtility::dieWithError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        /* email notification[ */
        if ($sreport_id) {
            $emailObj = new EmailHandler();
            $emailObj->sendShopReportNotification($sreport_id, $this->siteLangId);

            //send notification to admin
            $notificationData = array(
            'notification_record_type' => Notification::TYPE_SHOP,
            'notification_record_id' => $shop_id,
            'notification_user_id' => $loggedUserId,
            'notification_label_key' => Notification::REPORT_SHOP_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
            );

            if (!Notification::saveNotifications($notificationData)) {
                FatUtility::dieWithError(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            }
        }
        /* ] */

        $sucessMsg = Labels::getLabel('MSG_Reported_Successfully!', $this->siteLangId);
        Message::addMessage($sucessMsg);
        $this->set('msg', $sucessMsg);
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function searchWhoFavouriteShop(){
    $db = FatApp::getDb();
    $data = FatApp::getPostedData();
    $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : FatUtility::int($data['page']);
    $pagesize = FatApp::getConfig('CONF_PAGE_SIZE',FatUtility::VAR_INT, 10);

    $searchForm = $this->getWhoFavouriteSearchForm($this->siteLangId);
    $post = $searchForm->getFormDataFromArray($data);


    $shop_id = FatUtility::int($post['shop_id']);
    if(1 > $shop_id){
    FatUtility::dieWithError( Labels::getLabel('LBL_Invalid_Access_ID',$this->siteLangId));
    }

    $srch = new UserFavoriteShopSearch($this->siteLangId);
    $srch->joinWhosFavouriteUser();
    $srch->joinFavouriteUserShopsCount();
    $srch->addMultipleFields(array( 'ufs_shop_id as shop_id','ufs_user_id','user_name','userFavShopcount'));
    $srch->addCondition('ufs_shop_id','=',$shop_id);

    $page = (empty($page) || $page <= 0)?1:$page;
    $page = FatUtility::int($page);
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);

    $rs = $srch->getResultSet();
    $userFavorite = $db->fetchAll( $rs );

    $totalShopToShow = 4;
    $prodSrchObj = new ProductSearch( $this->siteLangId );
    $prodSrchObj->setDefinedCriteria();
    $prodSrchObj->setPageSize(1);
    $shops = array();
    foreach($userFavorite as $val){
    $fsrch = new UserFavoriteShopSearch($this->siteLangId);
    $fsrch->joinWhosFavouriteUser();
    $fsrch->addCondition('ufs_user_id','=',$val['ufs_user_id']);
    $fsrch->addMultipleFields(array( 'ufs_shop_id as shop_id'));
    $fsrch->setPageSize($totalShopToShow);
    $frs = $fsrch->getResultSet();
    $shops[$val['ufs_user_id']]['shop'] = $db->fetchAll( $frs,'shop_id');
    if( $shops[$val['ufs_user_id']]['shop'] ){
                foreach( $shops[$val['ufs_user_id']]['shop'] as $res ){
                    $prodSrch = clone $prodSrchObj;
                    $prodSrch->addShopIdCondition( $res['shop_id'] );
                    $prodSrch->addMultipleFields( array( 'selprod_id', 'product_id', 'shop_id','IFNULL(shop_name, shop_identifier) as shop_name',
                    'IFNULL(product_name, product_identifier) as product_name',
                    'IF(selprod_stock > 0, 1, 0) AS in_stock') );
                    $prodRs = $prodSrch->getResultSet();
                    $shops[$val['ufs_user_id']]['products'][] = $db->fetch( $prodRs);
                    $shops[$val['ufs_user_id']]['totalProducts'] =     $prodSrch->recordCount();
                }
    }
    }

    $this->set( 'shops', $shops );
    $this->set( 'totalShopToShow', $totalShopToShow );
    $this->set( 'userFavorite', $userFavorite );
    $this->set('pageCount',$srch->pages());
    $this->set('recordCount',$srch->recordCount());
    $this->set('page', $page);
    $this->set('pageSize', $pagesize);
    $this->set('postedData', $post);

    $startRecord = ($page-1)* $pagesize + 1 ;
    $endRecord = $pagesize;
    $totalRecords = $srch->recordCount();
    if ($totalRecords < $endRecord) { $endRecord = $totalRecords; }
    $json['totalRecords'] = $totalRecords;
    $json['startRecord'] = $startRecord;
    $json['endRecord'] = $endRecord;
    $json['html'] = $this->_template->render( false, false, '', true, false);
    $json['loadMoreBtnHtml'] = $this->_template->render( false, false, '_partial/load-more-btn.php', true, false);
    FatUtility::dieJsonSuccess($json);
    }

    public function whoFavoritesShop($shop_id){
    $db = FatApp::getDb();
    $shop_id = FatUtility::int($shop_id);

    $searchForm = $this->getWhoFavouriteSearchForm($this->siteLangId);
    $searchForm->fill(array('shop_id'=>$shop_id));

    $shopData = $this->getShopInfo($shop_id);
    if( !$shopData ){
    Message::addErrorMessage( Labels::getLabel('LBL_Invalid_Request', $this->siteLangId) );
    FatApp::redirectUser(CommonHelper::generateUrl('Home'));
    }

    $srch = new UserFavoriteShopSearch($this->siteLangId);
    $srch->joinWhosFavouriteUser();
    $srch->joinFavouriteUserShopsCount();
    $srch->addMultipleFields(array( 'ufs_shop_id as shop_id','ufs_user_id','user_name','userFavShopcount'));
    $srch->addCondition('ufs_shop_id','=',$shop_id);

    $rs = $srch->getResultSet();
    $userFavorite = $db->fetchAll( $rs );

    $this->set( 'shopData', $shopData );
    $this->set( 'searchForm', $searchForm );
    $this->set( 'userFavoriteCount', $srch->recordCount() );
    $this->_template->render( );
    }*/

    public function policies($shop_id)
    {
        $shop = $this->getShopInfo($shop_id);
        if (!$shop) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Home'));
        }

        $this->set('shop', $shop);
        $this->_template->render();
    }
    private function shopPoliciesData($shop)
    {
        $shop['shop_payment_policy'] = empty($shop['shop_payment_policy']) ? (object) array() : array(
            'title' => Labels::getLabel('LBL_PAYMENT_POLICY', $this->siteLangId),
            'description' => $shop['shop_payment_policy'],
        );
        $shop['shop_delivery_policy'] =  empty($shop['shop_delivery_policy']) ? (object) array() : array(
            'title' => Labels::getLabel('LBL_DELIVERY_POLICY', $this->siteLangId),
            'description' => $shop['shop_delivery_policy'],
        );
        $shop['shop_refund_policy'] =  empty($shop['shop_refund_policy']) ? (object) array() : array(
            'title' => Labels::getLabel('LBL_REFUND_POLICY', $this->siteLangId),
            'description' => $shop['shop_refund_policy'],
        );
        $shop['shop_additional_info'] =  empty($shop['shop_additional_info']) ? (object) array() : array(
            'title' => Labels::getLabel('LBL_ADDITIONAL_INFO', $this->siteLangId),
            'description' => $shop['shop_additional_info'],
        );
        $shop['shop_seller_info'] =  empty($shop['shop_seller_info']) ? (object) array() : array(
            'title' => Labels::getLabel('LBL_SELLER_INFO', $this->siteLangId),
            'description' => $shop['shop_seller_info'],
        );
        return $shop;
    }

    public function banner($shopId, $sizeType = '', $prodCatId = 0, $lang_id = 0)
    {
        $shopId = FatUtility::int($shopId);
        $prodCatId = FatUtility::int($prodCatId);
        $file_row = false;

        if ($prodCatId > 0) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_BANNER_SELLER, $shopId, $prodCatId, $lang_id);
            /* if(false == $file_row){
            $file_row = AttachedFile::getAttachment( AttachedFile::FILETYPE_SHOP_BANNER, $shopId );
            } */
        }

        if (false == $file_row) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_BANNER, $shopId, 0, $lang_id);
        }

        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 250;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'WIDE':
                $w = 1320;
                $h = 320;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    private function getShopInfo($shop_id)
    {
        $db = FatApp::getDb();
        $shop_id = FatUtility::int($shop_id);
        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->joinSellerSubscription();
        $srch->addMultipleFields(
            array( 'shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_payment_policy', 'shop_delivery_policy', 'shop_refund_policy', 'shop_additional_info', 'shop_seller_info',
            'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city','u.user_name as shop_owner_name', 'u.user_regdate', 'u_cred.credential_username as shop_owner_username' )
        );

        $srch->addCondition('shop_id', '=', $shop_id);
        $shopRs = $srch->getResultSet();
        return $shop = $db->fetch($shopRs);
    }

    private function getReportSpamForm($langId)
    {
        $frm = new Form('frmShopReportSpam');
        $frm->addHiddenField('', 'shop_id');
        $frm->addSelectBox(Labels::getLabel('LBL_Select_Reason', $langId), 'sreport_reportreason_id', ShopReportReason::getReportReasonArr($langId), '', array(), Labels::getLabel('LBL_Select', $langId))->requirements()->setRequired();
        $frm->addTextArea(Labels::getLabel('LBL_Message', $langId), 'sreport_message')->requirements()->setRequired();
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit_Report', $langId));
        return $frm;
    }

    private function getSendMessageForm($langId)
    {
        $frm = new Form('frmSendMessage');
        //$frm->addHiddenField('', 'user_id');
        $frm->addHiddenField('', 'shop_id');

        $fld = $frm->addHtml(Labels::getLabel('LBL_From', $langId), 'send_message_from', '');
        $frm->addHtml(Labels::getLabel('LBL_To', $langId), 'send_message_to', '');
        $frm->addHtml(Labels::getLabel('LBL_About_Product', $langId), 'about_product', '');
        $frm->addRequiredField(Labels::getLabel('LBL_Subject', $langId), 'thread_subject');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Your_Message', $langId), 'message_text');
        $fld->requirements()->setRequired();
        $frm->addHiddenField('', 'product_id');
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send', $langId));
        return $frm;
    }

    private function getWhoFavouriteSearchForm($langId)
    {
        $frm = new Form('frmsearchWhoFavouriteShop');
        $frm->addHiddenField('', 'shop_id');
        return $frm;
    }

    public function track($shopId = 0, $redirectType, $recordId)
    {
        $shopId = FatUtility::int($shopId);
        if (1 > $shopId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl(''));
        }

        /* Track Click */
        $srch = new PromotionSearch($this->siteLangId, true);
        $srch->joinActiveUser();
        $srch->joinShops();
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->addPromotionTypeCondition(Promotion::TYPE_SHOP);
        $srch->addShopActiveExpiredCondition();
        $srch->joinUserWallet();
        $srch->joinBudget();
        $srch->addBudgetCondition();
        $srch->addCondition('shop_id', '=', $shopId);
        $srch->addMultipleFields(array( 'shop_id','shop_user_id','shop_name','country_name','state_name','promotion_id','promotion_cpc'));
        $srch->addOrder('', 'rand()');
        $srch->setPageSize(1);
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if ($row == false) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl(''));
        }

        if ($redirectType == PROMOTION::REDIRECT_PRODUCT) {
            $url =  CommonHelper::generateFullUrl('products', 'view', array($recordId));
        } elseif ($redirectType == PROMOTION::REDIRECT_CATEGORY) {
            $url =  CommonHelper::generateFullUrl('category', 'view', array($recordId));
        } else {
            $url  = CommonHelper::generateFullUrl('shops', 'view', array($recordId));
        }

        $userId = 0;

        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        if (Promotion::isUserClickCountable($userId, $row['promotion_id'], $_SERVER['REMOTE_ADDR'], session_id())) {
            $promotionClickData = array(
            'pclick_promotion_id' => $row['promotion_id'],
            'pclick_user_id' => $userId,
            'pclick_datetime' => date('Y-m-d H:i:s'),
            'pclick_ip' => $_SERVER['REMOTE_ADDR'],
            'pclick_cost' => $row['promotion_cpc'],
            'pclick_session_id' => session_id(),
            );
            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_CLICKS, $promotionClickData, false, '', $promotionClickData);
            $clickId= FatApp::getDb()->getInsertId();

            $promotionClickChargesData = array(

            'picharge_pclick_id' => $clickId,
            'picharge_datetime'  => date('Y-m-d H:i:s'),
            'picharge_cost'  => $row['promotion_cpc'],

            );

            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_ITEM_CHARGES, $promotionClickChargesData, false);

            $promotionLogData = array(
            'plog_promotion_id' => $row['promotion_id'],
            'plog_date' =>  date('Y-m-d'),
            'plog_clicks' =>  1,
            );


            $onDuplicatePromotionLogData = array_merge($promotionLogData, array('plog_clicks'=>'mysql_func_plog_clicks+1'));
            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_LOGS, $promotionLogData, true, array(), $onDuplicatePromotionLogData);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            FatApp::redirectUser(CommonHelper::processURLString($url));
        }

        FatApp::redirectUser(CommonHelper::generateUrl(''));
    }

    /* private function getProductSearchForm(){
    $sortByArr = array( 'price_asc' => Labels::getLabel('LBL_Price_(Low_to_High)', $this->siteLangId), 'price_desc' => Labels::getLabel('LBL_Price_(High_to_Low)', $this->siteLangId) );

    $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
    $itemsTxt = Labels::getLabel('LBL_Items', $this->siteLangId);
    $pageSizeArr[$pageSize] = $pageSize.' '.$itemsTxt;
    $pageSizeArr[25] = 25 . ' '.$itemsTxt;
    $pageSizeArr[50] = 50 . ' '.$itemsTxt;
    $frm = new Form('frmProductSearch');
    $frm->addTextBox('','keyword');
    $frm->addSelectBox( '', 'sortBy', $sortByArr, 'price_asc', array(), '');
    $frm->addSelectBox( '', 'pageSize', $pageSizeArr, $pageSize, array(), '' );
    $frm->addHiddenField('', 'page', 1);
    $frm->addHiddenField('', 'sortOrder', 'asc');
    $frm->addHiddenField('', 'category', 0);
    $frm->addHiddenField('', 'shop_id', 0);
    $frm->addSubmitButton('','btnProductSrchSubmit','');
    return $frm;
    } */

    private function getListingData($get, $includeShopData = true)
    {
        $db = FatApp::getDb();

        $shop_id = 0;
        if (array_key_exists('shop_id', $get)) {
            $shop_id = FatUtility::int($get['shop_id']);
        }

        $userId = 0;
        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();
        }
        $shop = array();

        if (true == $includeShopData) {
            $srch = new ShopSearch($this->siteLangId);
            $srch->setDefinedCriteria($this->siteLangId);
            $srch->joinSellerSubscription();
            $srch->doNotCalculateRecords();
            $srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tu.user_id = shop_user_id', 'tu');

            /* sub query to find out that logged user have marked current shop as favorite or not[ */
            $favSrchObj = new UserFavoriteShopSearch();
            $favSrchObj->doNotCalculateRecords();
            $favSrchObj->doNotLimitRecords();
            $favSrchObj->addMultipleFields(array('ufs_shop_id','ufs_id'));
            $favSrchObj->addCondition('ufs_user_id', '=', $userId);
            $favSrchObj->addCondition('ufs_shop_id', '=', $shop_id);
            $srch->joinTable('('. $favSrchObj->getQuery() . ')', 'LEFT OUTER JOIN', 'ufs_shop_id = shop_id', 'ufs');
            /* ] */

            $srch->addMultipleFields(
                array( 'shop_id', 'tu.user_name', 'tu.user_regdate', 'shop_user_id', 'shop_ltemplate_id', 'shop_created_on', 'IFNULL(shop_name, shop_identifier) as shop_name', 'shop_description',
                'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city',
                'IFNULL(ufs.ufs_id, 0) as is_favorite' )
            );
            $srch->addCondition('shop_id', '=', $shop_id);
            /* if($policy) {
                $srch->addMultipleFields(array('shop_payment_policy', 'shop_delivery_policy','shop_refund_policy','shop_additional_info','shop_seller_info'));
            } */
            //echo $srch->getQuery();
            $shopRs = $srch->getResultSet();
            $shop = $db->fetch($shopRs);
        }

        $page = 1;
        if (array_key_exists('page', $get)) {
            $page = FatUtility::int($get['page']);
            if ($page < 2) {
                $page = 1;
            }
        }

        $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
        if (array_key_exists('pageSize', $get)) {
            $pageSize = FatUtility::int($get['pageSize']);
            if (0 >= $pageSize) {
                $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
            }
        }

        $srch = Product::getListingObj($get, $this->siteLangId, $userId);

        $srch->setPageNumber($page);
        if ($pageSize) {
            $srch->setPageSize($pageSize);
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $products = $db->fetchAll($rs);

        $data = array(
            'products'=>$products,
            'shop'=>$shop,
            'page'=>$page,
            'pageSize'=>$pageSize,
            'shopId'=>$shop_id,
            'pageCount'=>$srch->pages(),
            'postedData'=>$get,
            'recordCount'=>$srch->recordCount(),
            'siteLangId'=>$this->siteLangId
        );
        return $data;
    }

    public function shopReportReasons()
    {
        $srch = ShopReportReason::getReportReasonArr($this->siteLangId, true);
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetchAll($rs);
        $this->set('data', array('reportReasons' => $data));
        $this->_template->render();
    }
}
