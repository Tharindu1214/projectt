<?php
class MobileAppApiController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->db = FatApp::getDb();
        $this->pagesize = 10;
        $post = FatApp::getPostedData();

        $this->appToken = '';

        if (array_key_exists('HTTP_X_TOKEN', $_SERVER) && !empty($_SERVER['HTTP_X_TOKEN'])) {
            $this->appToken = ($_SERVER['HTTP_X_TOKEN'] != '')?$_SERVER['HTTP_X_TOKEN']:'';
        } elseif (('1.0' == MOBILE_APP_API_VERSION || $action == 'send_to_web' || empty($this->appToken)) && array_key_exists('_token', $post)) {
            $this->appToken = ($post['_token']!='')?$post['_token']:'';
        }

        $this->app_user['temp_user_id'] = 0;
        if (!empty($_SERVER['HTTP_X_TEMP_USER_ID'])) {
            $this->app_user['temp_user_id'] = $_SERVER['HTTP_X_TEMP_USER_ID'];
        }

        if ($this->appToken) {
            if (!UserAuthentication::isUserLogged('', $this->appToken)) {
                $arr = array('status'=>-1,'msg'=>Labels::getLabel('L_Invalid_Token', $this->siteLangId));
                die(FatUtility::convertToJson($arr, JSON_UNESCAPED_UNICODE));
            }

            $userId = UserAuthentication::getLoggedUserId();
            $userObj = new User($userId);
            if (!$row = $userObj->getProfileData()) {
                $arr = array('status'=>-1,'msg'=>Labels::getLabel('L_Invalid_Token', $this->siteLangId));
                die(FatUtility::convertToJson($arr, JSON_UNESCAPED_UNICODE));
            }
            $this->app_user = $row;
            $this->app_user['temp_user_id'] = 0;
        }

        if (array_key_exists('language', $post)) {
            $this->siteLangId = FatUtility::int($post['language']);
            $_COOKIE['defaultSiteLang'] = $this->siteLangId;
        }

        if (array_key_exists('currency', $post)) {
            $this->siteCurrencyId = FatUtility::int($post['currency']);
            $_COOKIE['defaultSiteCurrency'] = $this->siteCurrencyId;
        }

        $currencyRow = Currency::getAttributesById($this->siteCurrencyId);
        $this->currencySymbol = !empty($currencyRow['currency_symbol_left'])?$currencyRow['currency_symbol_left']:$currencyRow['currency_symbol_right'];
        CommonHelper::initCommonVariables();

        $public_api_requests = array(
                                        'home',
                                        'category',
                                        'get_products',
                                        'get_image',
                                        'product_details',
                                        'product_description',
                                        'product_reviews',
                                        'signup',
                                        'login',
                                        'forgot_password',
                                        'login_facebook',
                                        'login_gplus',
                                        'save_address',
                                        'languages',
                                        'currencies',
                                        'get_category_structure',
                                        'shop_detail',
                                        'get_shops',
                                        'get_brands',
                                        'countries',
                                        'get_states',
                                        'shop_reviews',
                                        'searchProductSuggestionsAutocomplete',
                                        'faq',
                                        'contactus',
                                        'contactsubmit',
                                        'brand',
                                        'report_shop_spam_reasons',
                                        'shopinfo',
                                        'privacy_policy',
                                        'terms_conditions',
                                        'about_us',
                                        'language_labels'
                                    );
        if (MOBILE_APP_API_VERSION > '1.1') {
            $public_api_requests = array_merge($public_api_requests, array('add_to_cart','remove_cart_item','update_cart_qty','get_cart_details'));
        }

        if (!in_array($action, $public_api_requests)) {
            if (!isset($this->app_user["user_id"]) || (!$this->app_user["user_id"]>0)) {
                FatUtility::dieJsonError(Labels::getLabel('L_MOBILE_Please_login_or_login_again', $this->siteLangId));
            }
        }

        $user_id = $this->getAppLoggedUserId();
        $userObj = new User($user_id);
        $srch = $userObj->getUserSearchObj();
        $srch->addMultipleFields(array('u.*'));
        $rs = $srch->getResultSet();
        $this->user_details = $this->db->fetch($rs, 'user_id');

        $cObj = new Cart($user_id, 0, $this->app_user['temp_user_id']);
        $this->cart_items = $cObj->countProducts();
        $this->totalFavouriteItems = UserFavorite::getUserFavouriteItemCount($user_id);

        $threadObj = new Thread();
        $this->totalUnreadMessageCount = $threadObj->getMessageCount($user_id);

        $notificationObj = new Notifications();
        $this->totalUnreadNotificationCount = $notificationObj->getUnreadNotificationCount($user_id);
    }

    public function cleanArray($arr)
    {
        $arrStr = array();
        foreach ($arr as $key => $val) {
            if (!is_array($val)) {
                if (!is_object($val)) {
                    //$arrStr[$key] = preg_replace('/[\x00-\x1F\x7F]/u', '', $val);
                    //Commented as \n /new line not working with messages
                    //$arrStr[$key] = preg_replace('/[\x1F\x7F]/u', '', $val);
                    $arrStr[$key] = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $val);
                } else {
                    $arrStr[$key] =  $val;
                }
            } else {
                $arrStr[$key]= $this->cleanArray($val);
            }
        }
        return $arrStr;
    }

    public function json_encode_unicode($data, $convertToType = false)
    {
        die(FatUtility::convertToJson($data, JSON_UNESCAPED_UNICODE));
        /*
        if($convertToType){
        die(json_encode($data));
        }
        $data = $this->cleanArray($data);
        FatUtility::dieJsonSuccess($data); */
        //die (json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function languages()
    {
        $languages = Language::getAllNames(false);
        if ($languages) {
            foreach ($languages as &$language) {
                $arrLanguage[] = $language;
            }
        }
        die($this->json_encode_unicode(array('status'=>1, 'languages'=>$arrLanguage)));
    }

    public function currencies()
    {
        $cObj = Currency::getSearchObject($this->siteLangId, true);
        $cObj->addMultipleFields(
            array(
            'currency_id','currency_code','IFNULL(curr_l.currency_name,curr.currency_code) as currency_name'
            )
        );
        $rs = $cObj->getResultSet();
        $currencies = $this->db->fetchAll($rs);
        die($this->json_encode_unicode(array('status'=>1, 'currencies'=>$currencies)));
    }

    public function language_labels()
    {
        $srch = Labels::getSearchObject();
        $srch->joinTable('tbl_languages', 'inner join', 'label_lang_id = language_id and language_active = ' .applicationConstants::ACTIVE);
        $srch->addOrder('lbl.' . Labels::DB_TBL_PREFIX . 'lang_id', 'ASC');
        $srch->addGroupBy('lbl.' . Labels::DB_TBL_PREFIX . 'key');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('lbl.label_lang_id', '=', $this->siteLangId);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        die($this->json_encode_unicode(array('status'=>1, 'records'=>$records)));
    }

    public function home()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $productSrchObj = new ProductSearch($this->siteLangId);
        $productSrchObj->joinProductToCategory($this->siteLangId);
        $productSrchObj->doNotCalculateRecords();
        /* $productSrchObj->setPageSize( 10 ); */
        $productSrchObj->setDefinedCriteria();
        $productSrchObj->joinSellerSubscription($this->siteLangId, true);
        $productSrchObj->addSubscriptionValidCondition();
        $productSrchObj->joinFavouriteProducts($loggedUserId);
        $productSrchObj->joinProductRating();
        $productSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $productSrchObj->addGroupBy('selprod_id');

        $productSrchObj->addMultipleFields(
            array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
            'theprice', 'selprod_price','selprod_stock', 'selprod_condition','prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','ifnull(sq_sprating.prod_rating,0) prod_rating ','ifnull(sq_sprating.totReviews,0) totReviews','selprod_sold_count','ufp_id','IF(ufp_id > 0, 1, 0) as isfavorite','selprod_min_order_qty')
        );
        $orderBy = 'ASC';

        /* Collections fetching/processing [ */

        //$collectionCache =  FatCache::get('collectionCache',CONF_HOME_PAGE_CACHE_TIME,'.txt');
        $collectionCache = false;
        if ($collectionCache) {
            $collections  = unserialize($collectionCache);
        } else {
            $srch = new CollectionSearch($this->siteLangId);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addOrder('collection_display_order', 'ASC');
            $srch->addMultipleFields(
                array('collection_id', 'IFNULL(collection_name, collection_identifier) as collection_name',
                'IFNULL( collection_description, "" ) as collection_description', 'IFNULL(collection_link_caption, "") as collection_link_caption',
                'collection_link_url', 'collection_layout_type', 'collection_type', 'collection_criteria','collection_child_records','collection_primary_records' )
            );
            $rs = $srch->getResultSet();
            $collectionsDbArr = $this->db->fetchAll($rs, 'collection_id');

            $productCatSrchObj = ProductCategory::getSearchObject(false, $this->siteLangId);
            $productCatSrchObj->doNotCalculateRecords();
            /* $productCatSrchObj->setPageSize(4); */
            $productCatSrchObj->addMultipleFields(array('prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','prodcat_description'));

            $collections = array();
            if (MOBILE_APP_API_VERSION < '1.3') {
                $collections = new ArrayObject();
            }

            /* [ */

            if (!empty($collectionsDbArr)) {
                $collectionObj = new CollectionSearch();
                $collectionObj->doNotCalculateRecords();
                //$collectionObj->doNotLimitRecords();

                $shopSearchObj = new ShopSearch($this->siteLangId);
                $shopSearchObj ->setDefinedCriteria($this->siteLangId);

                $i = 0;
                foreach ($collectionsDbArr as $collection_id => $collection) {
                    if (!$collection['collection_primary_records']) {
                        continue;
                    }
                    switch ($collection['collection_type']) {
                        case Collections::COLLECTION_TYPE_PRODUCT:
                            $tempObj = clone $collectionObj;
                            $tempObj->joinCollectionProducts();
                            $tempObj->addCondition('collection_id', '=', $collection_id);
                            $tempObj->setPageSize($collection['collection_primary_records']);
                            $tempObj->addMultipleFields(array( 'ctsp_selprod_id' ));
                            $tempObj->addCondition('ctsp_selprod_id', '!=', 'NULL');
                            $rs = $tempObj->getResultSet();

                            if (!$productIds = $this->db->fetchAll($rs, 'ctsp_selprod_id')) {
                                continue 2;
                            }

                            /* fetch Products data[ */

                            if ($collection['collection_criteria'] == Collections::COLLECTION_CRITERIA_PRICE_LOW_TO_HIGH) {
                                $orderBy = 'ASC';
                            }
                            if ($collection['collection_criteria'] == Collections::COLLECTION_CRITERIA_PRICE_HIGH_TO_LOW) {
                                $orderBy = 'DESC';
                            }
                            $productSrchTempObj = clone $productSrchObj;
                            $productSrchTempObj->addCondition('selprod_id', 'IN', array_keys($productIds));
                            $productSrchTempObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
                            $productSrchTempObj->addOrder('theprice', $orderBy);
                            $productSrchTempObj->joinSellers();
                            $productSrchTempObj->joinSellerSubscription($this->siteLangId);
                            $productSrchTempObj->addGroupBy('selprod_id');
                            $productSrchTempObj->setPageSize($collection['collection_primary_records']);
                            $rs = $productSrchTempObj->getResultSet();
                            if (MOBILE_APP_API_VERSION < '1.3') {
                                $collections[$collection['collection_layout_type']][$collection['collection_id']] = $collection;
                            } else {
                                $collections[$i] = $collection;
                            }
                            $collection_products = $this->db->fetchAll($rs, 'selprod_id');
                            $home_collection_products = array();
                            foreach ($collection_products as $skey => $sval) {
                                $arr_product_val = array(
                                "discounted_text"=>CommonHelper::showProductDiscountedText($sval, $this->siteLangId),
                                "image_url"=>CommonHelper::generateFullUrl('image', 'product', array($sval['product_id'], "MEDIUM", $sval['selprod_id'], 0, $this->siteLangId)),
                                "currency_selprod_price"=>CommonHelper::displayMoneyFormat($sval['selprod_price'], true, false, false),
                                "currency_theprice"=>CommonHelper::displayMoneyFormat($sval['theprice'], true, false, false),
                                );
                                $home_collection_products[] = array_merge($sval, $arr_product_val);
                                //$home_collection_products[] = array_merge($sval, array("image_url"=>CommonHelper::generateFullUrl('image','product', array($sval['product_id'], "MEDIUM", $sval['selprod_id'], 0, $this->siteLangId))));
                            }
                            if (MOBILE_APP_API_VERSION < '1.3') {
                                $collections[$collection['collection_layout_type']][$collection['collection_id']]['products'] = $home_collection_products;
                            } else {
                                $collections[$i]['products'] = $home_collection_products;
                            }
                            //commonHelper::printArray($collections); die;
                            /* ] */
                            unset($tempObj);
                            unset($productSrchTempObj);
                            break;

                        case Collections::COLLECTION_TYPE_CATEGORY:
                            if ($collection['collection_layout_type'] == Collections::TYPE_CATEGORY_LAYOUT1) {
                                continue 2;
                            }
                            $tempObj = clone $collectionObj;
                            $tempObj->addCondition('collection_id', '=', $collection_id);
                            $tempObj->joinCollectionCategories($this->siteLangId);
                            $tempObj->addMultipleFields(array( 'ctpc_prodcat_id'));
                            $tempObj->addCondition('ctpc_prodcat_id', '!=', 'NULL');
                            $tempObj->setPageSize($collection['collection_primary_records']);
                            $rs = $tempObj->getResultSet();

                            if (!$categoryIds = $this->db->fetchAll($rs, 'ctpc_prodcat_id')) {
                                continue 2;
                            }

                            /* fetch Categories data[ */
                            $productCatSrchTempObj = clone $productCatSrchObj;
                            $productCatSrchTempObj->addCondition('prodcat_id', 'IN', array_keys($categoryIds));
                            $rs = $productCatSrchTempObj->getResultSet();
                            /* ] */

                            if (MOBILE_APP_API_VERSION < '1.3') {
                                $collections[$collection['collection_layout_type']][$collection['collection_id']] = $collection;
                            } else {
                                $collections[$i] = $collection;
                            }

                            $collection_categories = $this->db->fetchAll($rs);
                            $home_collection_categories = array();
                            foreach ($collection_categories as $skey => $sval) {
                                $home_collection_categories[] = array_merge($sval, array("image_url"=>CommonHelper::generateFullUrl('category', 'icon', array($sval['prodcat_id'],$this->siteLangId))));
                            }

                            if (MOBILE_APP_API_VERSION < '1.3') {
                                $collections[$collection['collection_layout_type']][$collection['collection_id']]['categories'] = $home_collection_categories;
                            } else {
                                $collections[$i]['categories'] = $home_collection_categories;
                            }

                            unset($tempObj);
                            break;
                        case Collections::COLLECTION_TYPE_SHOP:
                            $tempObj = clone $collectionObj;
                            $tempObj->addCondition('collection_id', '=', $collection_id);
                            $tempObj->joinCollectionShops();
                            $tempObj->addMultipleFields(array( 'ctps_shop_id' ));
                            $tempObj->addCondition('ctps_shop_id', '!=', 'NULL');
                            // $tempObj->setPageSize( $collection['collection_primary_records'] );
                            $rs = $tempObj->getResultSet();
                            /* echo $tempObj->getQuery(); die; */
                            if (!$shopIds = $this->db->fetchAll($rs, 'ctps_shop_id')) {
                                continue 2;
                            }
                            $shopObj = clone $shopSearchObj;
                            $shopObj->joinSellerSubscription();
                            $shopObj->addCondition('shop_id', 'IN', array_keys($shopIds));
                            $shopObj->addMultipleFields(array( 'shop_id','shop_user_id','shop_name','country_name','state_name'));
                            $rs = $shopObj->getResultSet();
                            if (MOBILE_APP_API_VERSION < '1.3') {
                                $collections[$collection['collection_layout_type']][$collection['collection_id']] = $collection;
                            } else {
                                $collections[$i] = $collection;
                            }
                            while ($shopsData = $this->db->fetch($rs)) {
                                $pageSize = 3;
                                if (MOBILE_APP_API_VERSION < '1.3') {
                                    if (!$collection['collection_child_records']) {
                                        continue;
                                    }
                                    $pageSize = $collection['collection_child_records'];
                                }
                                /* fetch Shop data[ */
                                $productShopSrchTempObj = clone $productSrchObj;
                                $productShopSrchTempObj->addCondition('selprod_user_id', '=', $shopsData['shop_user_id']);
                                $productShopSrchTempObj->addGroupBy('selprod_product_id');

                                $productShopSrchTempObj->setPageSize($pageSize);

                                $Prs = $productShopSrchTempObj->getResultSet();

                                if (!FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                                    $rating = 0;
                                } else {
                                    $rating = SelProdRating::getSellerRating($shopsData['shop_user_id']);
                                }
                                $shopsData['rating'] = $rating;
                                $shopsData['shop_logo']=CommonHelper::generateFullUrl('image', 'shopLogo', array($shopsData['shop_id'], $this->siteLangId));
                                $shopsData['shop_banner']=CommonHelper::generateFullUrl('image', 'shopBanner', array($shopsData['shop_id'], $this->siteLangId));

                                if (MOBILE_APP_API_VERSION < '1.3') {
                                    $collections[$collection['collection_layout_type']][$collection['collection_id']]['shops'][$shopsData['shop_id']]['shopData']=$shopsData;
                                } else {
                                    $collections[$i]['shops'][$shopsData['shop_id']]['shopData']=$shopsData;
                                }



                                $collectionProds = $this->db->fetchAll($Prs);
                                $home_collectionProds = array();
                                foreach ($collectionProds as $pkey => $pval) {
                                    $arr_product_val = array(
                                    "discounted_text"=>CommonHelper::showProductDiscountedText($pval, $this->siteLangId),
                                    "image_url"=>CommonHelper::generateFullUrl('image', 'product', array($pval['product_id'], "MEDIUM", $pval['selprod_id'], 0, $this->siteLangId)),
                                    "currency_selprod_price"=>CommonHelper::displayMoneyFormat($pval['selprod_price'], true, false, false),
                                    "currency_theprice"=>CommonHelper::displayMoneyFormat($pval['theprice'], true, false, false),
                                    );
                                    $home_collectionProds[] = array_merge($pval, $arr_product_val);

                                    //$home_collectionProds[] = array_merge($pval, array("image_url"=>CommonHelper::generateFullUrl('image','product', array($pval['product_id'], "MEDIUM", $pval['selprod_id'], 0, $this->siteLangId))));
                                }


                                if (MOBILE_APP_API_VERSION < '1.3') {
                                    $collections[$collection['collection_layout_type']][$collection['collection_id']]['shops'][$shopsData['shop_id']]['products'] = $home_collectionProds;
                                } else {
                                    $collections[$i]['shops'][$shopsData['shop_id']]['products'] = $home_collectionProds;
                                }

                                //$collections[$collection['collection_layout_type']][$collection['collection_id']]['rating'][$shopsData['shop_id']] =  $rating;


                                /* ] */
                            }
                            $rs = $tempObj->getResultSet();
                            unset($tempObj);
                            break;
                    }
                    $i++;
                }
            }

            if (MOBILE_APP_API_VERSION > '1.2') {
                $collections = array_values($collections);
            }

            FatCache::set('collectionCache', serialize($collections), '.txt');
        }
        /* ] */

        /* [ Sponsored Items */


        /* Main Slides[ */
        $srchSlide = new SlideSearch($this->siteLangId);
        $srchSlide->doNotCalculateRecords();
        $srchSlide->joinPromotions($this->siteLangId, true, true, true);
        $srchSlide->addPromotionTypeCondition();
        $srchSlide->joinUserWallet();
        //$srchSlide->joinActiveUser();
        $srchSlide->addMinimiumWalletbalanceCondition();
        $srchSlide->addSkipExpiredPromotionAndSlideCondition();
        $srchSlide->joinBudget();
        $srchSlide->joinAttachedFile();
        $srchSlide->addMultipleFields(
            array('slide_id','slide_record_id','slide_type','IFNULL(promotion_name, promotion_identifier) as promotion_name,IFNULL(slide_title, slide_identifier) as slide_title',
            'slide_target', 'slide_url','promotion_id' ,'daily_cost','weekly_cost','monthly_cost','total_cost', )
        );

        $slidesPageSize = FatApp::getConfig('CONF_PPC_SLIDES_HOME_PAGE', FatUtility::VAR_INT, 2);
        $slides = array();
        if ($slidesPageSize) {
            $srch = new SearchBase('('.$srchSlide->getQuery().') as t');
            $srch->addDirectCondition(
                '((CASE
					WHEN promotion_duration='.Promotion::DAILY.' THEN promotion_budget > COALESCE(daily_cost,0)
					WHEN promotion_duration='.Promotion::WEEKLY.' THEN promotion_budget > COALESCE(weekly_cost,0)
					WHEN promotion_duration='.Promotion::MONTHLY.' THEN promotion_budget > COALESCE(monthly_cost,0)
					WHEN promotion_duration='.Promotion::DURATION_NOT_AVAILABALE.' THEN promotion_budget = -1
				  END ) )'
            );
            $srch->addMultipleFields(array('slide_id','slide_type','slide_record_id','slide_url','slide_target','slide_title','promotion_id'));
            $srch->setPageSize($slidesPageSize);
            //$srchSlide->addOrder( Slides::DB_TBL_PREFIX . 'display_order');
            $srch->addOrder('', 'rand()');

            $rs = $srch->getResultSet();
            $slides = $this->db->fetchAll($rs, 'slide_id');
            $home_slides = array();
            foreach ($slides as $key => $val) {
                $home_slides[] = array_merge($val, array("image_url"=>CommonHelper::generateFullUrl('Image', 'slide', array($val['slide_id'],applicationConstants::SCREEN_MOBILE,$this->siteLangId))));
            }
        }
        /* ] */
        $banners = new ArrayObject();
        $bannerSrch = Banner::getBannerLocationSrchObj(true, applicationConstants::SCREEN_MOBILE);
        $bannerSrch->addCondition('blocation_id', '<=', BannerLocation::HOME_PAGE_BOTTOM_BANNER);
        $rs = $bannerSrch->getResultSet();
        $bannerLocation = $this->db->fetchAll($rs, 'blocation_key');
        if (!empty($bannerLocation)) {
            $banners = $bannerLocation;
            foreach ($bannerLocation as $val) {
                $srch = new BannerSearch($this->siteLangId, true);
                $srch->joinPromotions($this->siteLangId, true, true, true);
                $srch->addPromotionTypeCondition();
                //$srch->joinActiveUser();

                $srch->joinUserWallet();
                $srch->addMinimiumWalletbalanceCondition();
                $srch->addSkipExpiredPromotionAndBannerCondition();
                $srch->joinBudget();
                $srch->addMultipleFields(array('banner_id','banner_blocation_id','banner_type','banner_record_id','banner_url','banner_target','banner_title','promotion_id' ,'daily_cost','weekly_cost','monthly_cost','total_cost', ));
                $srch->doNotCalculateRecords();
                $srch->joinAttachedFile();
                $srch->addCondition('banner_blocation_id', '=', $val['blocation_id']);

                $srch = new SearchBase('('.$srch->getQuery().') as t');
                $srch->doNotCalculateRecords();
                $srch->addDirectCondition(
                    '((CASE
					WHEN promotion_duration='.Promotion::DAILY.' THEN promotion_budget > COALESCE(daily_cost,0)
					WHEN promotion_duration='.Promotion::WEEKLY.' THEN promotion_budget > COALESCE(weekly_cost,0)
					WHEN promotion_duration='.Promotion::MONTHLY.' THEN promotion_budget > COALESCE(monthly_cost,0)
					WHEN promotion_duration='.Promotion::DURATION_NOT_AVAILABALE.' THEN promotion_budget = -1
				  END ) )'
                );
                $srch->addMultipleFields(array('banner_id','banner_blocation_id','banner_type','banner_record_id','banner_url','banner_target','banner_title','promotion_id'));
                //die($srch->getquery());
                if ($val['blocation_banner_count'] > 0) {
                    $srch->setPageSize($val['blocation_banner_count']);
                }
                $srch->addOrder('', 'rand()');
                $rs = $srch->getResultSet();
                $bannerListing = $this->db->fetchAll($rs, 'banner_id');

                $home_banners = array();
                foreach ($bannerListing as $bkey => $bval) {
                    $home_banners[] = array_merge($bval, array("image_url"=>CommonHelper::generateFullUrl('Banner', 'HomePageAfterFirstLayout', array($bval['banner_id'], $this->siteLangId))));
                }
                $banners[$val['blocation_key']]['banners'] = $home_banners;
                //commonhelper::printarray($banners[$val['blocation_key']]['banners']);
                //die();
            }
        }


        $promotionObj = new PromotionSearch($this->siteLangId);
        $sponsoredShops = array();
        $shopPageSize = FatApp::getConfig('CONF_PPC_SHOPS_HOME_PAGE', FatUtility::VAR_INT, 2);
        if ($shopPageSize) {
            /* For Shops */
            $shopObj  = clone $promotionObj;
            $shopObj->setDefinedCriteria();
            $shopObj->joinActiveUser();
            $shopObj->joinShops();
            $shopObj->joinShopCountry();
            $shopObj->joinShopState();
            $shopObj->addPromotionTypeCondition(Promotion::TYPE_SHOP);

            $shopObj->addShopActiveExpiredCondition();
            $shopObj->joinUserWallet();
            $shopObj->joinBudget();
            $shopObj->addBudgetCondition();
            $shopObj->addOrder('', 'rand()');
            //echo $shopObj->getQuery(); die;
            $shopObj->setPageSize($shopPageSize);

            $rs = $shopObj->getResultSet();

            while ($shops = $this->db->fetch($rs)) {
                /* fetch Shop data[ */
                $productShopSrchTempObj = clone $productSrchObj;
                $productShopSrchTempObj->addCondition('selprod_user_id', '=', $shops['shop_user_id']);
                $productShopSrchTempObj->addGroupBy('selprod_product_id');
                $productShopSrchTempObj->setPageSize(2);
                $Prs = $productShopSrchTempObj->getResultSet();
                $shops['shop_logo']=CommonHelper::generateFullUrl('image', 'shopLogo', array($shops['shop_id'], $this->siteLangId));
                $shops['shop_banner']=CommonHelper::generateFullUrl('image', 'shopBanner', array($shops['shop_id'], $this->siteLangId));
                $sponsoredShops['shops'][$shops['shop_id']]['shopData']=$shops;
                $sponsoredShops['shops'][$shops['shop_id']]['shopData']['promotion_id']=$shops['promotion_id'];
                if (!FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                    $rating = 0;
                } else {
                    $rating = SelProdRating::getSellerRating($shops['shop_user_id']);
                }
                $sponsoredShops['rating'][$shops['shop_id']] =  $rating;
                $sponsoredShops_products = $this->db->fetchAll($Prs);
                $home_sponsoredShops_products = array();
                foreach ($sponsoredShops_products as $skey => $sval) {
                    $arr_product_val = array(
                      "discounted_text"=>CommonHelper::showProductDiscountedText($sval, $this->siteLangId),
                      "image_url"=>CommonHelper::generateFullUrl('image', 'product', array($sval['product_id'], "MEDIUM", $sval['selprod_id'], 0, $this->siteLangId)),
                      "currency_selprod_price"=>CommonHelper::displayMoneyFormat($sval['selprod_price'], true, false, false),
                      "currency_theprice"=>CommonHelper::displayMoneyFormat($sval['theprice'], true, false, false),
                                        );
                    $home_sponsoredShops_products[] = array_merge($sval, $arr_product_val);
                }
                $sponsoredShops['shops'][$shops['shop_id']]['products'] = $home_sponsoredShops_products;
                /* ] */
            }
            /* End For Shops */
        }

        /* For Products */

        $prodObj  = clone $promotionObj;
        $prodObj->joinProducts();
        $prodObj->joinShops();
        $prodObj->addPromotionTypeCondition(Promotion::TYPE_PRODUCT);
        $prodObj->joinActiveUser();
        $prodObj->setDefinedCriteria();
        $prodObj->addShopActiveExpiredCondition();
        $prodObj->joinUserWallet();
        $prodObj->joinBudget();
        $prodObj->addBudgetCondition();
        $prodObj->doNotCalculateRecords();
        $prodObj->addMultipleFields(array('selprod_id as proSelProdId','promotion_id','promotion_record_id'));
        $productPageSize = FatApp::getConfig('CONF_PPC_PRODUCTS_HOME_PAGE', FatUtility::VAR_INT, 4);
        $sponsoredProds =  array();
        if ($productPageSize) {
            $productSrchSponObj = clone $productSrchObj;
            $productSrchSponObj->joinTable('(' . $prodObj->getQuery().') ', 'INNER JOIN', 'selprod_id = ppr.proSelProdId ', 'ppr');
            $productSrchSponObj->addFld(array('promotion_id','promotion_record_id'));
            $productSrchSponObj->addOrder('theprice', $orderBy);
            $productSrchSponObj->joinSellers();
            $productSrchSponObj->joinSellerSubscription($this->siteLangId);
            $productSrchSponObj->addGroupBy('selprod_id');
            $productSrchSponObj->setPageSize($productPageSize);
            $productSrchSponObj->addOrder('', 'rand()');
            $rs = $productSrchSponObj->getResultSet();
            $sponsoredProds = $this->db->fetchAll($rs);
            $home_sponsoredProds = array();
            foreach ($sponsoredProds as $skey => $sval) {
                $arr_product_val = array(
                "discounted_text"=>CommonHelper::showProductDiscountedText($sval, $this->siteLangId),
                "image_url"=>CommonHelper::generateFullUrl('image', 'product', array($sval['product_id'], "MEDIUM", $sval['selprod_id'], 0, $this->siteLangId)),
                "currency_selprod_price"=>CommonHelper::displayMoneyFormat($sval['selprod_price'], true, false, false),
                "currency_theprice"=>CommonHelper::displayMoneyFormat($sval['theprice'], true, false, false),
                                        );

                $home_sponsoredProds[] = array_merge($sval, $arr_product_val);
            }
        }
        /* End For Products */

        /* Sponsored Items ] */
        $api_home_page_elements['sponsored_products'] = $home_sponsoredProds;
        $api_home_page_elements['sponsored_shops'] = $sponsoredShops;
        $api_home_page_elements['slides'] = $home_slides;
        $api_home_page_elements['banners'] = $banners;
        $api_home_page_elements['collections'] = $collections;

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_home_page_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function get_category_structure()
    {
        $productCategory = new productCategory;
        $prodSrchObj = new ProductCategorySearch($this->siteLangId);
        $categoriesArr = ProductCategory::getProdCatParentChildWiseArr($this->siteLangId, 0, true, false, false, $prodSrchObj, true);
        //echo "<prE>";print_r($categoriesArr);
        $categoriesDataArr = $productCategory ->getCategoryTreeArr($this->siteLangId, $categoriesArr, array( 'prodcat_id', 'IFNULL(prodcat_name,prodcat_identifier ) as prodcat_name','substr(GETCATCODE(prodcat_id),1,6) AS prodrootcat_code', 'prodcat_content_block','prodcat_active','prodcat_parent','GETCATCODE(prodcat_id) as prodcat_code'));

        //$categoriesDataArr = ProductCategory::getProdCatParentChildWiseArr( $this->siteLangId,0, true, false, false, false,true );

        $categoriesDataArr = $this->resetKeyValues(array_values($categoriesDataArr));
        if (empty($categoriesDataArr)) {
            $categoriesDataArr =  array();
        }

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$categoriesDataArr,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    private function resetKeyValues($arr)
    {
        $result = array();
        foreach ($arr as $key => $val) {
            if (!array_key_exists('prodcat_id', $val)) {
                continue;
            }
            $result[$key] = $val;
            $childernArr = array();
            if (!empty($val['children'])) {
                $array = array_values($val['children']);
                $childernArr = $this->resetKeyValues($array);
            }
            $result[$key]['children'] = $childernArr;
        }
        return array_values($result);
    }

    public function category($id)
    {
        $id = intVal($id);
        if ($id == 0) {
            if (isset($_REQUEST['category'])) {
                $id = $_REQUEST['category'];
            }
        }

        $category_id = FatUtility::int($id);


        $catSrch = new ProductCategorySearch($this->siteLangId);
        $catSrch->addCondition('prodcat_id', '=', $category_id);


        /* to show searched category data[ */
        $catSrch->addMultipleFields(array('prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','prodcat_description','GETCATCODE(prodcat_id) AS prodcat_code'));
        $catSrchRs = $catSrch->getResultSet();
        $categoryData = $this->db->fetch($catSrchRs);



        if (!$categoryData) {
            FatUtility::exitWithErrorCode(404);
        }
        $catBanner = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_BANNER, $categoryData['prodcat_id']);
        $categoryData['catBanner'] = $catBanner;

        /* ] */


        $prodSrchObj = new ProductSearch($this->siteLangId);
        $prodSrchObj->setDefinedCriteria(1);
        $prodSrchObj->joinProductToCategory();
        $prodSrchObj->joinSellerSubscription($this->siteLangId, true);
        $prodSrchObj->addSubscriptionValidCondition();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->doNotLimitRecords();
        $prodSrchObj->addCategoryCondition($category_id);
        /* $prodSrchObj->addCondition('selprod_deleted' ,'=' , applicationConstants::NO);
        $prodSrchObj->addGroupBy('selprod_id'); */

        //$prodSrchObj->addMultipleFields(array('selprod_id','prodcat_id'));
        $rs = $prodSrchObj->getResultSet();
        $record = $this->db->fetchAll($rs);


        $brandsArr = array();
        $conditionsArr  = array();
        $priceArr  = array();

        /* Categories Data[ */
        $catSrch = clone $prodSrchObj;
        $catSrch->addGroupBy('prodcat_id');
        $categoriesDataArr = ProductCategory::getProdCatParentChildWiseArr($this->siteLangId, $category_id, false, false, false, $catSrch, false);

        //var_dump($categoriesDataArr); die;

        $productCategory = new ProductCategory;
        $categoriesArr = $productCategory ->getCategoryTreeArr($this->siteLangId, $categoriesDataArr);


        /* ] */

        /* Brand Filters Data[ */
        $brandSrch = clone $prodSrchObj;
        $brandSrch->addGroupBy('brand_id');
        $brandSrch->addOrder('brand_name');
        $brandSrch->removGroupBy('selprod_id');
        $brandSrch->addMultipleFields(array( 'brand_id', 'ifNull(brand_name,brand_identifier) as brand_name'));
        /* if needs to show product counts under brands[ */
        //$brandSrch->addFld('count(selprod_id) as totalProducts');
        /* ] */

        $brandRs = $brandSrch->getResultSet();
        $brandsArr = $this->db->fetchAll($brandRs);

        /* ] */

        /* {Can modify the logic fetch data directly from query . will implement later}
        Option Filters Data[ */
        $options = array();
        if ($category_id && ProductCategory::isLastChildCategory($category_id)) {
            $selProdCodeSrch = clone $prodSrchObj;
            $selProdCodeSrch->addGroupBy('selprod_code');
            $selProdCodeSrch->addMultipleFields(array('product_id','selprod_code'));
            $selProdCodeRs = $selProdCodeSrch->getResultSet();
            $selProdCodeArr = $this->db->fetchAll($selProdCodeRs);

            if (!empty($selProdCodeArr)) {
                foreach ($selProdCodeArr as $val) {
                    $optionsVal = SellerProduct::getSellerProductOptionsBySelProdCode($val['selprod_code'], $this->siteLangId, true);
                    $options = $options+$optionsVal;
                }
            }
        }

        usort(
            $options,
            function ($a, $b) {
                if ($a['optionvalue_id']==$b['optionvalue_id']) {
                    return 0;
                }
                return ($a['optionvalue_id']<$b['optionvalue_id'])?-1:1;
            }
        );

        /* $optionSrch->joinSellerProductOptionsWithSelProdCode();
        $optionSrch->addGroupBy('optionvalue_id'); */
        /*]*/


        /* Condition filters data[ */
        $conditionSrch = clone $prodSrchObj;
        $conditionSrch->removGroupBy('selprod_id');
        $conditionSrch->addGroupBy('selprod_condition');
        $conditionSrch->addOrder('selprod_condition');
        $conditionSrch->addMultipleFields(array('selprod_condition'));

        /* if needs to show product counts under any condition[ */
        //$conditionSrch->addFld('count(selprod_condition) as totalProducts');
        /* ] */
        $conditionRs = $conditionSrch->getResultSet();
        $conditionsArr = $this->db->fetchAll($conditionRs);
        /* ] */


        /* Price Filters[ */
        $priceSrch = new ProductSearch($this->siteLangId);
        $priceSrch->setDefinedCriteria(1);
        $priceSrch->joinProductToCategory();
        $priceSrch->joinSellerSubscription();
        $priceSrch->addSubscriptionValidCondition();
        $priceSrch->doNotCalculateRecords();
        $priceSrch->doNotLimitRecords();
        $priceSrch->addCategoryCondition($category_id);
        $priceSrch->addMultipleFields(array('MIN(theprice) as minPrice', 'MAX(theprice) as maxPrice'));


        $qry = $priceSrch->getQuery();
        $qry .= ' having minPrice IS NOT NULL AND maxPrice IS NOT NULL';
        //$priceRs = $priceSrch->getResultSet();
        $priceRs = $this->db->query($qry);
        $priceArr = $this->db->fetch($priceRs);
        /* ] */

        //commonhelper::printArray($priceArr);
        if (!empty($priceArr)) {
            /* $priceArrCurrency = array_map( function( $item ){ return CommonHelper::displayMoneyFormat( $item, true, false ,false ); } , $priceArr );
            $priceArrCurrency['minPrice']=floor($priceArrCurrency['minPrice']);
            $priceArrCurrency['maxPrice']=ceil($priceArrCurrency['maxPrice']);  */
            $priceArrCurrency['minPrice']= floor(CommonHelper::displayMoneyFormat($priceArr['minPrice'], false, false, false));
            $priceArrCurrency['maxPrice']= ceil(CommonHelper::displayMoneyFormat($priceArr['maxPrice'], false, false, false));
        }


        $productFiltersArr = array(
        'categoriesArr'            =>    $categoriesArr,
        //    'categoryDataArr'        =>    $categoryFilterData,
        'brandsArr'                =>    $brandsArr,
        'conditionsArr'            =>    $conditionsArr,
        'priceArr'                =>    $priceArr,
        'priceArrCurrency'        =>    $priceArrCurrency,
        'options'                =>    $options,
        'siteLangId'            =>    $this->siteLangId,
        );


        /*commonhelper::printarray($categoryData);
        die();*/
        //CommonHelper::printArray(array('status'=>1 ,'data'=>$api_home_page_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->user_details['favItems'],'unread_messages'=>$this->user_details['unreadMessages']));
        $api_category_page_elements = array('categoryData'=>$categoryData,'product_filters'=>$productFiltersArr);
        //commonhelper::printarray($api_category_page_elements);
        //die();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_category_page_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function get_products()
    {
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        //$pagesize = 2;
        if ($page < 2) {
            $page = 1;
        }
        $arr_products = array();

        $srch = new ProductSearch($this->siteLangId);

        $collection_product_id = FatApp::getPostedData('collection_product_id', FatUtility::VAR_INT, 0);
        $criteria = array('collection_product_id'=>$collection_product_id);

        $keyword = FatApp::getPostedData('keyword', null, '');
        $criteria['keyword'] = $keyword;

        $shop_id = FatApp::getPostedData('shop_id', null, '');
        if ($shop_id > 0) {
            $srch->setDefinedCriteria(false, 0, $criteria);
        } else {
            $srch->setDefinedCriteria(true, 0, $criteria);
        }

        $srch->joinProductToCategory();
        $srch->joinSellerSubscription();
        $srch->addSubscriptionValidCondition();


        /* to check current product is in wish list or not[ */
        $loggedUserId = $this->getAppLoggedUserId();
        $srch->joinFavouriteProducts($loggedUserId);

        $wislistPSrchObj = new UserWishListProductSearch();
        //    $wislistPSrchObj->joinFavouriteProducts();
        $wislistPSrchObj->joinWishLists();
        $wislistPSrchObj->doNotCalculateRecords();
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $loggedUserId);
        $wishListSubQuery = $wislistPSrchObj->getQuery();

        $srch->joinTable('(' . $wishListSubQuery . ')', 'LEFT OUTER JOIN', 'uwlp.uwlp_selprod_id = selprod_id', 'uwlp');
        /* ] */
        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating","count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $srch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating');

        $srch->setPageNumber($page);
        $srch->addMultipleFields(
            array('GETCATCODE(`prodcat_id`)',
            'product_id', 'prodcat_id', 'ufp_id', 'IFNULL(product_name, product_identifier) as product_name', 'product_model', 'product_short_description',
            'substring_index(group_concat(IFNULL(prodcat_name, prodcat_identifier) ORDER BY IFNULL(prodcat_name, prodcat_identifier) ASC SEPARATOR "," ) , ",", 1) as prodcat_name',
            'selprod_id', 'selprod_user_id',  'selprod_code', 'selprod_stock', 'selprod_condition', 'selprod_price', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type', 'splprice_start_date', 'splprice_end_date',
            'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name', 'IF(selprod_stock > 0, 1, 0) AS in_stock',
            'selprod_sold_count','selprod_return_policy', 'IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist','ifnull(prod_rating,0) prod_rating','ifnull(sq_sprating.totReviews,0) totReviews','IF(ufp_id > 0, 1, 0) as isfavorite','selprod_min_order_qty'
            )
        );

        if ($pagesize) {
            $srch->setPageSize($pagesize);
        }

        $category_id = FatApp::getPostedData('category', null, '');
        if ($category_id) {
            $srch->addCategoryCondition($category_id);
        }


        if ($shop_id) {
            $shop_id = FatUtility::int($shop_id);
            $srch->addShopIdCondition($shop_id);
        }

        /* Shop collection added by seller it's self for shop[*/
        $collection_id = FatApp::getPostedData('collection_id', null, '');
        //$collection_id = 1;
        if ($collection_id) {
            $collection_id = FatUtility::int($collection_id);
            $srch->addCollectionIdCondition($collection_id);
        }
        /*]*/

        if (!empty($keyword)) {
            $srch->addKeywordSearch($keyword);
            $srch->addFld('if(selprod_title LIKE '.FatApp::getDb()->quoteVariable('%'.$keyword.'%').',  1,   0  ) as keywordmatched');
            $srch->addFld('if(selprod_title LIKE '.FatApp::getDb()->quoteVariable('%'.$keyword.'%').',  IFNULL(splprice_price, selprod_price),   theprice ) as theprice');
            $srch->addFld(
                'if(selprod_title LIKE '.FatApp::getDb()->quoteVariable('%'.$keyword.'%').',  CASE WHEN splprice_selprod_id IS NULL THEN 0 ELSE 1
                END,   special_price_found ) as special_price_found'
            );
        } else {
            $srch->addFld('theprice');
            $srch->addFld('special_price_found');
        }

        $brand = FatApp::getPostedData('brand', null, '');
        if ($brand) {
            $srch->addBrandCondition($brand);
        }

        $optionvalue = FatApp::getPostedData('optionvalue', null, '');
        if ($optionvalue) {
            $srch->addOptionCondition($optionvalue);
        }

        $condition = FatApp::getPostedData('condition', null, '');
        if (!empty($condition)) {
            $srch->addConditionCondition($condition);
        }

        $out_of_stock = FatApp::getPostedData('out_of_stock', null, '');
        if (!empty($out_of_stock) && $out_of_stock == 1) {
            $srch->excludeOutOfStockProducts();
        }

        $min_price_range = FatApp::getPostedData('min_price_range', null, '');
        if (!empty($min_price_range)) {
            $min_price_range_default_currency =  CommonHelper::getDefaultCurrencyValue($min_price_range, false, false);
            $srch->addCondition('theprice', '>=', $min_price_range_default_currency);
        }

        $max_price_range = FatApp::getPostedData('max_price_range', null, '');
        if (!empty($max_price_range)) {
            $max_price_range_default_currency =  CommonHelper::getDefaultCurrencyValue($max_price_range, false, false);
            $srch->addCondition('theprice', '<=', $max_price_range_default_currency);
        }

        $featured = FatApp::getPostedData('featured', null, '');
        if (!empty($featured)) {
            $srch->addCondition('product_featured', '=', $featured);
        }

        $srch->addOrder('in_stock', 'DESC');
        $sortBy = FatApp::getPostedData('sort_by', null, 'popularity');
        $sortOrder = FatApp::getPostedData('sort_order', null, 'asc');
        if (!in_array($sortOrder, array('asc','desc'))) {
            $sortOrder = 'asc';
        }

        if (!empty($sortBy)) {
            $sortByArr = explode("_", $sortBy);
            $sortBy = isset($sortByArr[0]) ? $sortByArr[0] : $sortBy;
            $sortOrder = isset($sortByArr[1]) ? $sortByArr[1] : $sortOrder;
            switch ($sortBy) {
                case 'price':
                    $srch->addOrder('theprice', $sortOrder);
                    break;
                case 'keyword_relevancy':
                    $srch->addOrder('keyword_relevancy', $sortOrder);
                    break;
                case 'popularity':
                    $srch->addOrder('selprod_sold_count', $sortOrder);
                    break;
                case 'rating':
                    $srch->addOrder('prod_rating', $sortOrder);
                    break;
            }
        } elseif (!empty($keyword)) {
            $srch->addOrder('keyword_relevancy', 'DESC');
        }
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        /* groupby added, because if same product is linked with multiple categories, then showing in repeat for each category[ */
        if ($collection_product_id) {
            $srch->addGroupBy('selprod_id');
        } else {
            $srch->addGroupBy('product_id');
            if (!empty($keyword)) {
                $srch->addGroupBy('keywordmatched');
                $srch->addOrder('keywordmatched', 'desc');
            }
        }
        /* ] */

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $productsList = $db->fetchAll($rs);
        $prodSrchObj = new ProductSearch();
        if ($productsList) {
            foreach ($productsList as &$product) {
                $moreSellerSrch = clone $prodSrchObj;
                $moreSellerSrch->addMoreSellerCriteria($product['selprod_code'], $product['selprod_user_id']);
                $moreSellerSrch->addMultipleFields(array('count(selprod_id) as totalSellersCount','MIN(theprice) as theprice','special_price_found'));
                $moreSellerSrch->addGroupBy('selprod_code');
                $moreSellerRs = $moreSellerSrch->getResultSet();
                $moreSellerRow = $db->fetch($moreSellerRs);
                $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($product['product_id'], "MEDIUM", $product['selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $product['discounted_text'] =  CommonHelper::showProductDiscountedText($product, $this->siteLangId);

                $product['product_image'] =  $mainImgUrl;
                $product['moreSellerData'] =  ($moreSellerRow) ? $moreSellerRow : array();
                $product['selprod_return_policies'] = SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_RETURN, $this->siteLangId, $limit = 2);
                $product['selprod_warranty_policies'] =  SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_WARRANTY, $this->siteLangId);
                $product['currency_selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], true, false, false);
                $product['currency_theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], true, false, false);
            }
        }

        $sortByArr = array( 'price_asc' => Labels::getLabel('LBL_Price_(Low_to_High)', $this->siteLangId), 'price_desc' => Labels::getLabel('LBL_Price_(High_to_Low)', $this->siteLangId), 'popularity_desc' => Labels::getLabel('LBL_Sort_by_Popularity', $this->siteLangId), 'rating_desc' => Labels::getLabel('LBL_Sort_by_Rating', $this->siteLangId) );
        $count = 0;
        foreach ($sortByArr as $key => $val) {
            $getSortArr[$count]['key']= $key;
            $getSortArr[$count]['value']= $val;
            $count++;
        }
        $api_products_elements = array('products'=>$productsList,'total_pages'=>$srch->pages(),'page'=>$page,'total_records'=>$srch->recordCount(),'sortByArr'=>$getSortArr);

        //commonhelper::printarray($api_products_elements);
        //die();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_products_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function get_image()
    {
        $type = FatApp::getPostedData('type', null, '');
        $image_url = "";
        switch (strtoupper($type)) {
            case 'PRODUCT_PRIMARY':
                $product_id = FatApp::getPostedData('product_id', null, '');
                $seller_product_id = FatApp::getPostedData('seller_product_id', null, '');
                $image_url = CommonHelper::generateFullUrl('image', 'product', array($product_id, "MEDIUM", $seller_product_id, 0, $this->siteLangId));
                break;
            case 'SLIDE':
                $slide_id = FatApp::getPostedData('slide_id', null, '');
                $image_url = CommonHelper::generateFullUrl('Image', 'slide', array($slide_id,0,$this->siteLangId));
                break;
            case 'BANNER':
                $banner_id = FatApp::getPostedData('banner_id', null, '');
                $image_url = CommonHelper::generateFullUrl('Banner', 'HomePageAfterFirstLayout', array($banner_id, $this->siteLangId));
                break;
        }
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$image_url)));
    }

    public function relatedProductsById($ids = array())
    {
        $loggedUserId = $this->getAppLoggedUserId();
        if (isset($ids) && is_array($ids) && count($ids)) {
            $prodSrch = new ProductSearch($this->siteLangId);
            $prodSrch->setDefinedCriteria();
            $prodSrch->joinProductToCategory();
            $prodSrch->doNotCalculateRecords();
            $prodSrch->doNotLimitRecords();
            $prodSrch->joinFavouriteProducts($loggedUserId);
            $prodSrch->joinProductRating();
            $prodSrch->addCondition('selprod_id', 'IN', $ids);
            $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);

            $prodSrch->addMultipleFields(
                array(
                'product_id', 'IFNULL(product_name, product_identifier) as product_name', 'prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name', 'ifnull(sq_sprating.prod_rating,0) prod_rating ', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
                'selprod_id', 'selprod_condition', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'theprice',
                'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type','selprod_sold_count','ufp_id','selprod_price')
            );
            $productRs = $prodSrch->getResultSet();
            $Products = FatApp::getDb()->fetchAll($productRs, 'selprod_id');

            uksort(
                $Products,
                function ($key1, $key2) use ($ids) {
                    return (array_search($key1, $ids) > array_search($key2, $ids));
                }
            );
            return $Products;
        }
    }

    public function product_details($selprod_id)
    {
        $productImagesArr = array();
        $loggedUserId = $this->getAppLoggedUserId();
        $prodSrchObj = new ProductSearch($this->siteLangId);

        /* fetch requested product[ */
        $prodSrch = clone $prodSrchObj;
        $prodSrch->setDefinedCriteria();

        $prodSrch->joinProductToCategory();
        $prodSrch->    joinSellerSubscription();
        $prodSrch->    addSubscriptionValidCondition();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrch->doNotLimitRecords();

        /* sub query to find out that logged user have marked current product as in wishlist or not[ */
        $prodSrch->joinFavouriteProducts($loggedUserId);

        $wislistPSrchObj = new UserWishListProductSearch();
        $wislistPSrchObj->joinWishLists();

        $wislistPSrchObj->doNotCalculateRecords();
        //$wislistPSrchObj->setPageNumber(1);
        $wislistPSrchObj->setPageSize(1);
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $loggedUserId);
        $wislistPSrchObj->addCondition('uwlp_selprod_id', '=', $selprod_id);

        $wishListSubQuery = $wislistPSrchObj->getQuery();
        $prodSrch->joinTable('(' . $wishListSubQuery . ')', 'LEFT OUTER JOIN', 'uwlp_selprod_id = selprod_id', 'uwlp');
        /* ] */

        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id','spr.spreview_product_id',"ROUND(AVG(sprating_rating),2) as prod_rating","count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $prodSrch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_product_id = product_id', 'sq_sprating');

        $prodSrch->addMultipleFields(
            array(
            'product_id','product_identifier', 'IFNULL(product_name,product_identifier) as product_name', 'product_seller_id', 'ufp_id', 'product_model','product_type', 'IFNULL(prodcat_name,prodcat_identifier) as prodcat_name', 'product_upc', 'product_isbn', 'product_short_description', 'product_description',
            'selprod_id', 'selprod_user_id', 'selprod_code', 'selprod_condition', 'selprod_price', 'special_price_found','splprice_start_date', 'splprice_end_date', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'selprod_warranty', 'selprod_return_policy','selprodComments',
            'theprice', 'selprod_stock' , 'selprod_threshold_stock_level', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name',
            'shop_id', 'shop_name', 'IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist','ifnull(sq_sprating.prod_rating,0) prod_rating ','ifnull(sq_sprating.totReviews,0) totReviews',
            'splprice_display_dis_type', 'splprice_display_dis_val', 'splprice_display_list_price', 'product_attrgrp_id', 'product_youtube_video', 'product_cod_enabled', 'selprod_cod_enabled','IF(ufp_id > 0, 1, 0) as isfavorite','selprod_min_order_qty')
        );


        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);


        /* ] */

        if (!$product) {
            FatUtility::exitWithErrorCode(404);
            /* Message::addErrorMessage("Invalid Request");
            FatApp::redirectUser(CommonHelper::generateUrl('Products')); */
        }

        /* over all catalog product reviews */
        $selProdReviewObj->addCondition('spreview_product_id', '=', $product['product_id']);
        $selProdReviewObj->addMultipleFields(array('count(spreview_postedby_user_id) totReviews','sum(if(sprating_rating=1,1,0)) rated_1','sum(if(sprating_rating=2,1,0)) rated_2','sum(if(sprating_rating=3,1,0)) rated_3','sum(if(sprating_rating=4,1,0)) rated_4','sum(if(sprating_rating=5,1,0)) rated_5'));
        $reviews = FatApp::getDb()->fetch($selProdReviewObj->getResultSet());
        $this->set('reviews', $reviews);
        $subscription = false;
        $allowed_images =-1;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $allowed_images = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $product['selprod_user_id'], array('ossubs_images_allowed'));
            $subscription = true;
        }

        /* Product::recordProductWeightage($product['selprod_code'],SmartWeightageSettings::PRODUCT_VIEW);
        Product::addUpdateProductBrowsingHistory($product['selprod_code'],SmartWeightageSettings::PRODUCT_VIEW); */

        /* Current Product option Values[ */
        $options = SellerProduct::getSellerProductOptions($selprod_id, false);
        $productSelectedOptionValues = array();
        $productGroupImages= array();
        if ($options) {
            foreach ($options as $op) {
                $images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], $op['selprodoption_optionvalue_id'], $this->siteLangId, true, '', $allowed_images);
                if ($images) {
                    $productImagesArr += $images;
                }
                $productSelectedOptionValues[$op['selprodoption_option_id']] = $op['selprodoption_optionvalue_id'];
            }
        }

        if ($productImagesArr) {
            foreach ($productImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }

        $product['selectedOptionValues'] = $productSelectedOptionValues;
        /* ] */

        if (isset($allowed_images) && $allowed_images >0) {
            $universal_allowed_images_count = $allowed_images  - count($productImagesArr);
        }

        $productUniversalImagesArr = array();
        if (empty($productGroupImages) ||  !$subscription || isset($universal_allowed_images_count)) {
            $universalImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], -1, $this->siteLangId, true, '');
            /* CommonHelper::printArray($universalImages); die; */
            if ($universalImages) {
                if (isset($universal_allowed_images_count)) {
                    $images = array_slice($universalImages, 0, $universal_allowed_images_count);

                    $productUniversalImagesArr = $images;

                    $universal_allowed_images_count = $universal_allowed_images_count  - count($productUniversalImagesArr);
                } elseif (!$subscription) {
                    $productUniversalImagesArr = $universalImages;
                }
            }
        }


        if ($productUniversalImagesArr) {
            foreach ($productUniversalImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }
        $productGalleryImagesArr = array();
        foreach ($productGroupImages as $image) {
            $mainImgUrl = CommonHelper::generateFullUrl('Image', 'product', array($image['afile_record_id'], 'MEDIUM', 0, $image['afile_id'] ));
            $productGalleryImagesArr[] = array_merge($image, array('image_url'=>$mainImgUrl));
        }
        /*commonhelper::printarray($productGalleryImagesArr);
        die();*/
        //$universalImages = AttachedFile::getMultipleAttachments( AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], -1, $this->siteLangId, true,'',$allowed_images_count );
        //if( $universalImages ){
        //$productImagesArr += $universalImages;
        //}

        /*[ Check COD enabled and Get Shipping Rates*/
        $codEnabled = false;
        if (Product::isProductShippedBySeller($product['product_id'], $product['product_seller_id'], $product['selprod_user_id'])) {
            $walletBalance = User::getUserBalance($product['selprod_user_id']);
            if ($product['selprod_cod_enabled']) {
                $codEnabled = true;
            }
            $codMinWalletBalance = -1;
            $shop_cod_min_wallet_balance = Shop::getAttributesByUserId($product['selprod_user_id'], 'shop_cod_min_wallet_balance');
            if ($shop_cod_min_wallet_balance > -1) {
                $codMinWalletBalance = $shop_cod_min_wallet_balance;
            } elseif (FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1) > -1) {
                $codMinWalletBalance = FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1);
            }
            if ($codMinWalletBalance > -1 && $codMinWalletBalance > $walletBalance) {
                $codEnabled = false;
            }
            $shippingRates = Product::getProductShippingRates($product['product_id'], $this->siteLangId, 0, $product['selprod_user_id']);
            $shippingDetails = Product::getProductShippingDetails($product['product_id'], $this->siteLangId, $product['selprod_user_id']);
        } else {
            if ($product['product_cod_enabled']) {
                $codEnabled = true;
            }
            $shippingRates = Product::getProductShippingRates($product['product_id'], $this->siteLangId, 0, 0);
            $shippingDetails = Product::getProductShippingDetails($product['product_id'], $this->siteLangId, 0);
        }

        if ($product['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $shippingRates = array();
            $shippingDetails  = array();
        }

        if ($shippingRates) {
            foreach ($shippingRates as &$shippingRate) {
                $shippingRate['pship_charges_currency'] = CommonHelper::displayMoneyFormat($shippingRate['pship_charges'], true, false, false);
                $shippingRate['pship_additional_charges_currency'] = CommonHelper::displayMoneyFormat($shippingRate['pship_additional_charges'], true, false, false);
            }
        }

        /*]*/


        /*[ Product shipping cost */
        $shippingCost = 0;
        /*]*/

        /* more sellers[ */
        $moreSellerSrch = clone $prodSrchObj;
        //$moreSellerSrch->setDefinedCriteria();
        $moreSellerSrch->addMoreSellerCriteria($product['selprod_code']);
        $moreSellerSrch->addMultipleFields(array( 'selprod_id', 'selprod_user_id', 'selprod_price', 'special_price_found', 'theprice', 'shop_id', 'shop_name' ,'IF(selprod_stock > 0, 1, 0) AS in_stock'));
        $moreSellerSrch->addHaving('in_stock', '>', 0);
        $moreSellerSrch->addOrder('theprice');
        $moreSellerSrch->addGroupBy('shop_id');
        $moreSellerRs = $moreSellerSrch->getResultSet();
        $moreSellersArr = FatApp::getDb()->fetchAll($moreSellerRs);
        if (!empty($moreSellersArr)) {
            foreach ($moreSellersArr as $key => $prod) {
                $moreSellersArr[$key]['discounted_text'] = CommonHelper::showProductDiscountedText($prod, $this->siteLangId);
                $moreSellersArr[$key]['currency_selprod_price'] = CommonHelper::displayMoneyFormat($prod['selprod_price'], true, false, false);
                $moreSellersArr[$key]['currency_theprice'] = CommonHelper::displayMoneyFormat($prod['theprice'], true, false, false);
            }
        }

        $product['moreSellersArr'] = $moreSellersArr;
        /* ] */

        $product['selprod_return_policies'] =  SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_RETURN, $this->siteLangId);
        $product['selprod_warranty_policies'] =  SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_WARRANTY, $this->siteLangId);
        /* $productImagesArr = array(); */


        /* ] */
        //CommonHelper::printArray($productImagesArr);die;
        /* Form buy product[ */

        /* ] */

        $optionSrchObj = clone $prodSrchObj;
        $optionSrchObj->setDefinedCriteria();
        $optionSrchObj->doNotCalculateRecords();
        $optionSrchObj->doNotLimitRecords();
        $optionSrchObj->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = tspo.selprodoption_selprod_id', 'tspo');
        $optionSrchObj->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
        $optionSrchObj->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');
        $optionSrchObj->addCondition('product_id', '=', $product['product_id']);

        $optionSrch = clone $optionSrchObj;
        $optionSrch->joinTable(Option::DB_TBL.'_lang', 'LEFT OUTER JOIN', 'op.option_id = op_l.optionlang_option_id AND op_l.optionlang_lang_id = '. $this->siteLangId, 'op_l');
        $optionSrch->addMultipleFields(array(  'option_id', 'option_is_color', 'ifNULL(option_name,option_identifier) as option_name' ));
        $optionSrch->addCondition('option_id', '!=', 'NULL');
        $optionSrch->addGroupBy('option_id');

        $optionRs = $optionSrch->getResultSet();
        $optionRows = FatApp::getDb()->fetchAll($optionRs, 'option_id');

        if ($optionRows) {
            foreach ($optionRows as &$option) {
                $optionValueSrch = clone $optionSrchObj;
                $optionValueSrch->joinTable(OptionValue::DB_TBL.'_lang', 'LEFT OUTER JOIN', 'opval.optionvalue_id = opval_l.optionvaluelang_optionvalue_id AND opval_l.optionvaluelang_lang_id = '. $this->siteLangId, 'opval_l');
                $optionValueSrch->addCondition('product_id', '=', $product['product_id']);
                $optionValueSrch->addCondition('option_id', '=', $option['option_id']);
                $optionValueSrch->addMultipleFields(array( 'IFNULL(product_name, product_identifier) as product_name','selprod_id','selprod_user_id','selprod_code','option_id','ifNULL(optionvalue_name,optionvalue_identifier) as optionvalue_name ', 'theprice', 'optionvalue_id','optionvalue_color_code'));
                $optionValueSrch->addGroupBy('optionvalue_id');
                $optionValueRs = $optionValueSrch->getResultSet();
                $optionValueRows = FatApp::getDb()->fetchAll($optionValueRs, 'optionvalue_id');
                $option['values'] = $optionValueRows;
            }
        }

        /* Get Product Specifications */
        $specSrchObj = clone $prodSrchObj;
        $specSrchObj->setDefinedCriteria();
        $specSrchObj->doNotCalculateRecords();
        $specSrchObj->doNotLimitRecords();
        $specSrchObj->joinTable(Product::DB_PRODUCT_SPECIFICATION, 'LEFT OUTER JOIN', 'product_id = tcps.prodspec_product_id', 'tcps');
        $specSrchObj->joinTable(Product::DB_PRODUCT_LANG_SPECIFICATION, 'INNER JOIN', 'tcps.prodspec_id = tcpsl.prodspeclang_prodspec_id and   prodspeclang_lang_id  = '.$this->siteLangId, 'tcpsl');
        $specSrchObj->addMultipleFields(array('prodspec_id','prodspec_name','prodspec_value'));
        $specSrchObj->addGroupBy('prodspec_id');
        $specSrchObj->addCondition('prodspec_product_id', '=', $product['product_id']);
        $specSrchObjRs = $specSrchObj->getResultSet();
        $productSpecifications = FatApp::getDb()->fetchAll($specSrchObjRs);

        /* End of Product Specifications */


        if ($product) {
            $product['discounted_text'] =  CommonHelper::showProductDiscountedText($product, $this->siteLangId);
            $product['currency_selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], true, false, false);
            $product['currency_theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], true, false, false);
            $title  = $product['product_name'];

            if ($product['selprod_title']) {
                $title = $product['selprod_title'];
            }

            $product_description = trim(CommonHelper::subStringByWords(strip_tags(CommonHelper::renderHtml($product["product_description"], true)), 500));
            $product_description .= ' - '.Labels::getLabel('LBL_See_more_at', $this->siteLangId).": ".CommonHelper::getCurrUrl();

            $productImageUrl = '';
            /* $productImageUrl = FatUtility::generateFullUrl('Image','product', array($product['product_id'],'', $product['selprod_id'],0,$this->siteLangId )); */
            if ($productImagesArr) {
                $afile_id = array_keys($productImagesArr)[0];
                $productImageUrl = FatUtility::generateFullUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $afile_id ));
            }
        }




        /* Recommnended Products [ */
        $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id', false);

        $srch = new ProductSearch($this->siteLangId);
        $join_price = 1;
        $srch->setDefinedCriteria($join_price);
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription();
        $srch->addSubscriptionValidCondition();
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addMultipleFields(
            array(
            'product_id','prodcat_id','ufp_id','substring_index(group_concat(IFNULL(prodcat_name, prodcat_identifier) ORDER BY IFNULL(prodcat_name, prodcat_identifier) ASC SEPARATOR "," ) , ",", 1) as prodcat_name', 'IFNULL(product_name, product_identifier) as product_name', 'product_model', 'product_short_description',
            'selprod_id', 'selprod_user_id',  'selprod_code', 'selprod_stock', 'selprod_condition', 'selprod_price', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'special_price_found','splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
            'theprice', 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name',
            'IF(selprod_stock > 0, 1, 0) AS in_stock','selprod_sold_count','selprod_return_policy', 'IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist','ifnull(prod_rating,0) prod_rating','IF(ufp_id > 0, 1, 0) as isfavorite','selprod_min_order_qty'
                 )
        );

        $dateToEquate = date('Y-m-d');

        $loggedUserId = $this->getAppLoggedUserId();
        $recommendedProductsQuery = "(select rec_product_id , weightage from
							(
								SELECT ppr_recommended_product_id as rec_product_id , ppr_weightage as weightage from tbl_product_product_recommendation
								where ppr_viewing_product_id = $productId order by ppr_weightage desc limit 5
							) as set1
							union
							select rec_product_id , weightage from
							(
								select tpr_product_id  as rec_product_id , if(tpr_custom_weightage_valid_till <= '$dateToEquate' , tpr_custom_weightage+tpr_weightage , tpr_weightage) as weightage from
								(
									select * from tbl_product_to_tags where ptt_product_id = $productId
								) innerSet1 inner JOIN tbl_tag_product_recommendation on tpr_tag_id = ptt_tag_id
								order by if(tpr_custom_weightage_valid_till <= '$dateToEquate' , tpr_custom_weightage+tpr_weightage , tpr_weightage) desc limit 5
							) as set2
							";
        if ($loggedUserId) {
            $recommendedProductsQuery.= " union
							select rec_product_id , weightage from
							(
								SELECT upr_product_id as rec_product_id , upr_weightage as weightage from tbl_user_product_recommendation
								where upr_user_id = $loggedUserId order by upr_weightage desc limit 5
							) as set3 " ;
        }

        $recommendedProductsQuery.= ")";

        //    $srch->joinTable("$recommendedProductsQuery" , 'inner join' , 'rs1.rec_product_id = product_id' , 'rs1' );
        $srch->addGroupBy('product_id');
        //$srch->addOrder('rs1.weightage' , 'desc');


        $srch->joinFavouriteProducts($loggedUserId);
        $wislistPSrchObj = new UserWishListProductSearch();
        //    $wislistPSrchObj->joinFavouriteProducts();
        $wislistPSrchObj->joinWishLists();
        $wislistPSrchObj->doNotCalculateRecords();
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $loggedUserId);
        $wishListSubQuery = $wislistPSrchObj->getQuery();

        $srch->joinTable('(' . $wishListSubQuery . ')', 'LEFT OUTER JOIN', 'uwlp.uwlp_selprod_id = selprod_id', 'uwlp');
        /* ] */
        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $srch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating');
        $srch->setPageSize(5);
        $srch->doNotCalculateRecords();
        //echo $srch->getQuery();exit;
        $recommendedProducts = FatApp::getDb()->fetchAll($srch->getResultSet());
        $pd_recommendedProducts = array();
        foreach ($recommendedProducts as $pkey=>$pval) {
            $arr = array(
            "currency_selprod_price"=>CommonHelper::displayMoneyFormat($pval['selprod_price'], true, false, false),
            "currency_theprice"=>CommonHelper::displayMoneyFormat($pval['theprice'], true, false, false),
            "discounted_text"=>CommonHelper::showProductDiscountedText($pval, $this->siteLangId),
            "image_url"=>CommonHelper::generateFullUrl('image', 'product', array($pval['product_id'], "MEDIUM", $pval['selprod_id'], 0, $this->siteLangId)));
            $pd_recommendedProducts[] = array_merge($pval, $arr);
        }


        $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($product['product_id'], "MEDIUM", $product['selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
        $product['product_image'] =  $mainImgUrl;

        /* ]  */




        /* product combo/batch[ */
        $sellerProductObj = new SellerProduct($selprod_id);
        $productGroups = $sellerProductObj->getGroupsToProduct($this->siteLangId);
        if ($productGroups) {
            foreach ($productGroups as $key => &$pg) {
                $srch = new ProductSearch($this->siteLangId, ProductGroup::DB_PRODUCT_TO_GROUP, ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'product_id');
                $srch->setBatchProductsCriteria();
                $srch->addCondition(ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'prodgroup_id', '=', $pg['ptg_prodgroup_id']);
                $srch->addMultipleFields(array( 'selprod_id', 'product_id', 'selprod_stock', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'IFNULL(splprice_price, selprod_price) AS theprice', 'CASE WHEN splprice_selprod_id IS NULL THEN 0 ELSE 1 END AS special_price_found' ));
                $rs = $srch->getResultSet();
                $pg_products = FatApp::getDb()->fetchAll($rs);
                //$pg_products = $sellerProductObj->getProductsToGroup( $pg['ptg_prodgroup_id'], $this->siteLangId );
                if ($pg_products) {
                    foreach ($pg_products as $pg_product) {
                        if (!$pg_product['in_stock']) {
                            unset($productGroups[$key]);
                            continue 2;
                        }
                    }
                }
                $pg['products'] = $pg_products;
            }
        }
        $criteria='selprod_id';
        $sellerObj=new SellerProduct();


        $productCustomSrchObj = new ProductSearch($this->siteLangId);
        $productCustomSrchObj->joinProductToCategory($this->siteLangId);
        $productCustomSrchObj->setDefinedCriteria();
        $productCustomSrchObj->joinSellerSubscription($this->siteLangId, true);
        $productCustomSrchObj->addSubscriptionValidCondition();
        $productCustomSrchObj->joinFavouriteProducts($loggedUserId);
        $productCustomSrchObj->joinProductRating();
        $productCustomSrchObj->doNotCalculateRecords();
        $productCustomSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $productCustomSrchObj->addGroupBy('selprod_id');

        /* $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type','=',SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating","count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $productCustomSrchObj->joinTable( '(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating' ); */

        $productCustomSrchObj->addMultipleFields(
            array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
            'theprice', 'selprod_price','selprod_stock', 'selprod_condition','prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','ifnull(sq_sprating.prod_rating,0) prod_rating ','ifnull(sq_sprating.totReviews,0) totReviews','selprod_sold_count','ufp_id','IF(ufp_id > 0, 1, 0) as isfavorite','selprod_min_order_qty')
        );

        $srch = new SearchBase(SellerProduct::DB_TBL_UPSELL_PRODUCTS);
        $srch->joinTable('(' . $productCustomSrchObj->getQuery() . ')', 'INNER JOIN', SellerProduct::DB_TBL_UPSELL_PRODUCTS_PREFIX.'recommend_sellerproduct_id = selprod_id', 'pCust');
        $srch->addCondition(SellerProduct::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'sellerproduct_id', '=', $product['selprod_id']);
        //die($srch->getquery());
        $rs = $srch->getResultSet();
        $upsellProducts = FatApp::getDb()->fetchAll($rs);

        if ($upsellProducts) {
            foreach ($upsellProducts as &$upsellProduct) {
                $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($upsellProduct['product_id'], "MEDIUM", $upsellProduct['selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $upsellProduct['discounted_text'] =  CommonHelper::showProductDiscountedText($upsellProduct, $this->siteLangId);
                $upsellProduct['product_image'] =  $mainImgUrl;
                $upsellProduct['currency_selprod_price'] = CommonHelper::displayMoneyFormat($upsellProduct['selprod_price'], true, false, false);
                $upsellProduct['currency_theprice'] = CommonHelper::displayMoneyFormat($upsellProduct['theprice'], true, false, false);
            }
        }

        //$upsellProducts=$sellerObj->getUpsellProducts($product['selprod_id'],$this->siteLangId);
        //commonhelper::printArray($upsellProducts);
        //die();
        //$relatedProducts=$sellerObj->getRelatedProducts($product['selprod_id'],$this->siteLangId,$criteria);

        $srch = new SearchBase(SellerProduct::DB_TBL_RELATED_PRODUCTS);
        $srch->joinTable('(' . $productCustomSrchObj->getQuery() . ')', 'INNER JOIN', SellerProduct::DB_TBL_RELATED_PRODUCTS_PREFIX.'recommend_sellerproduct_id = selprod_id', 'pCust');
        $srch->addCondition(SellerProduct::DB_TBL_RELATED_PRODUCTS_PREFIX . 'sellerproduct_id', '=', $product['selprod_id']);
        $rs = $srch->getResultSet();
        $relatedProducts = FatApp::getDb()->fetchAll($rs);
        if ($relatedProducts) {
            foreach ($relatedProducts as &$relatedProduct) {
                $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($relatedProduct['product_id'], "MEDIUM", $relatedProduct['selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $relatedProduct['discounted_text'] =  CommonHelper::showProductDiscountedText($relatedProduct, $this->siteLangId);
                $relatedProduct['product_image'] =  $mainImgUrl;
                $relatedProduct['currency_selprod_price'] = CommonHelper::displayMoneyFormat($relatedProduct['selprod_price'], true, false, false);
                $relatedProduct['currency_theprice'] = CommonHelper::displayMoneyFormat($relatedProduct['theprice'], true, false, false);
            }
        }
        //$relatedProductsRs=$this->relatedProductsById(array_keys($relatedProducts));
        // CommonHelper::printArray($relatedProductsRs);die;

        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(
            array( 'shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'ifNULL(shop_name,shop_identifier)as shop_name', 'shop_description',
            'ifNULL(shop_country_l.country_name,shop_country.country_code) as shop_country_name', 'ifNULL(shop_state_l.state_name,state_identifier) as shop_state_name', 'shop_city' )
        );
        $srch->addCondition('shop_id', '=', $product['shop_id']);
        $shopRs = $srch->getResultSet();
        $shop = FatApp::getDb()->fetch($shopRs);
        $shop['shop_logo']=CommonHelper::generateFullUrl('image', 'shopLogo', array($shop['shop_id'], $this->siteLangId));
        $shop['shop_banner']=CommonHelper::generateFullUrl('image', 'shopBanner', array($shop['shop_id'], $this->siteLangId));


        if (!FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
            $shop_rating = 0;
        } else {
            $shop_rating = SelProdRating::getSellerRating($shop['shop_user_id']);
        }


        /*   [ Promotional Banner   */

        $bannerSrch = Banner::getBannerLocationSrchObj(true, applicationConstants::SCREEN_MOBILE);
        $bannerSrch->addCondition('blocation_id', '=', 3);
        $rs = $bannerSrch->getResultSet();
        $bannerLocation = FatApp::getDb()->fetchAll($rs, 'blocation_key');

        $banners = $bannerLocation;
        $product_bannerListing = array();
        foreach ($bannerLocation as $val) {
            $srch = new BannerSearch($this->siteLangId, true);
            $srch->joinPromotions($this->siteLangId, true, true, true);
            $srch->addPromotionTypeCondition();
            $srch->joinUserWallet();
            $srch->addMinimiumWalletbalanceCondition();
            $srch->addSkipExpiredPromotionAndBannerCondition();
            $srch->joinBudget();
            $srch->addMultipleFields(array('banner_id','banner_blocation_id','banner_type','banner_record_id','banner_url','banner_target','banner_title','promotion_id' ,'daily_cost','weekly_cost','monthly_cost','total_cost', ));
            $srch->addOrder('', 'rand()');
            $srch->doNotCalculateRecords();

            if ($val['blocation_banner_count'] > 0) {
                $srch->setPageSize($val['blocation_banner_count']);
            }
            $srch->addCondition('banner_blocation_id', '=', $val['blocation_id']);


            $srch = new SearchBase('('.$srch->getQuery().') as t');
            $srch->doNotCalculateRecords();
            $srch->addDirectCondition(
                '((CASE
					WHEN promotion_duration='.Promotion::DAILY.' THEN promotion_budget > COALESCE(daily_cost,0)
					WHEN promotion_duration='.Promotion::WEEKLY.' THEN promotion_budget > COALESCE(weekly_cost,0)
					WHEN promotion_duration='.Promotion::MONTHLY.' THEN promotion_budget > COALESCE(monthly_cost,0)
					WHEN promotion_duration='.Promotion::DURATION_NOT_AVAILABALE.' THEN promotion_budget = -1
				  END ) )'
            );
            $srch->addMultipleFields(array('banner_id','banner_blocation_id','banner_type','banner_record_id','banner_url','banner_target','banner_title','promotion_id' ,'userBalance','daily_cost','weekly_cost','monthly_cost','total_cost','promotion_budget' ,'promotion_duration'));
            $rs = $srch->getResultSet();
            $bannerListing = FatApp::getDb()->fetchAll($rs, 'banner_id');

            foreach ($bannerListing as $bkey=>$bval) {
                $product_bannerListing[] = array_merge($val, $bval, array("image_url"=>CommonHelper::generateFullUrl('Banner', 'HomePageAfterFirstLayout', array($bval['banner_id'], $this->siteLangId))));
            }

            $banners[$val['blocation_key']]['banners'] = $product_bannerListing;
        }

        /* End of Prmotional Banner  ]*/

        // CommonHelper::printArray($productImagesArr); die;



        /* Get product Polls [ */
        $pollQuest = Polling::getProductPoll($product['product_id'], $this->siteLangId);
        $this->set('pollQuest', $pollQuest);
        /* ] */
        /* Get Product Volume Discount (if any)[ */
        $sellerProduct = new SellerProduct($product['selprod_id']);
        $volumeDiscountRows = $sellerProduct->getVolumeDiscounts();
        foreach ($volumeDiscountRows as &$volumeDiscountRow) {
            $volumeDiscount = $product['theprice'] * ($volumeDiscountRow['voldiscount_percentage'] / 100);
            $price = ($product['theprice'] - $volumeDiscount);
            $volumeDiscountRow['price'] = $price;
            $volumeDiscountRow['currency_price'] = CommonHelper::displayMoneyFormat($price, true, false, false);
        }

        $api_product_detail_elements['codEnabled'] = $codEnabled;
        $api_product_detail_elements['shippingRates'] = $shippingRates;
        $api_product_detail_elements['shippingDetails'] = $shippingDetails;
        $api_product_detail_elements['optionRows'] = $optionRows;
        $api_product_detail_elements['productSpecifications'] = $productSpecifications;

        $api_product_detail_elements['recommendedProducts'] = $pd_recommendedProducts;
        $api_product_detail_elements['upsellProducts'] = $upsellProducts;
        $api_product_detail_elements['relatedProductsRs'] = $relatedProducts;
        $api_product_detail_elements['banners'] = $product_bannerListing;
        $api_product_detail_elements['product'] = $product;
        $api_product_detail_elements['shop_rating'] = $shop_rating;
        $api_product_detail_elements['shop'] = $shop;

        $api_product_detail_elements['productImagesArr'] = $productGalleryImagesArr;
        $api_product_detail_elements['productGroups'] = $productGroups;
        $api_product_detail_elements['volumeDiscountRows'] = $volumeDiscountRows;
        //commonhelper::printarray($api_product_detail_elements);
        //die();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_product_detail_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function product_description($seller_product_id)
    {
        $prodSrchObj = new ProductSearch($this->siteLangId);
        $prodSrchObj->setDefinedCriteria();
        $prodSrchObj->joinProductToCategory();
        $prodSrchObj->joinSellerSubscription();
        $prodSrchObj->addSubscriptionValidCondition();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->addCondition('selprod_id', '=', $seller_product_id);
        $prodSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrchObj->doNotLimitRecords();
        $prodSrchObj->addFld('product_description');
        //die($prodSrchObj->getquery());
        $productRs = $prodSrchObj->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        echo str_replace('/editor/editor-image/', FatUtility::generateFullUrl().'editor/editor-image/', $product["product_description"]);
    }

    public function product_reviews($selprod_id)
    {
        $productImagesArr = array();
        $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id', false);
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $orderBy = FatApp::getPostedData('orderBy', FatUtility::VAR_STRING, 'most_recent');
        $page = ($page)? $page : 1;
        $pageSize = FatApp::getPostedData('pageSize', FatUtility::VAR_INT, FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10));
        $srch = new SelProdReviewSearch();
        $srch->joinSelProdRating();
        $srch->joinUser();
        $srch->joinSelProdReviewHelpful();
        $srch->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $srch->addCondition('spr.spreview_product_id', '=', $productId);
        $srch->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $srch->addMultipleFields(array('spreview_id','spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating" ,'spreview_title','spreview_description','spreview_posted_on','spreview_postedby_user_id','user_name','group_concat(case when sprh_helpful = 1 then concat(sprh_user_id,"~",1) else concat(sprh_user_id,"~",0) end ) usersMarked' ,'sum(if(sprh_helpful = 1 , 1 ,0)) as helpful' ,'sum(if(sprh_helpful = 0 , 1 ,0)) as notHelpful','count(sprh_spreview_id) as countUsersMarked' ));
        $srch->addGroupBy('spr.spreview_id');

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        switch ($orderBy) {
            case 'most_helpful':
                $srch->addOrder('helpful', 'desc');
                break;
            default:
                $srch->addOrder('spr.spreview_posted_on', 'desc');
                break;
        }
        $records = FatApp::getDb()->fetchAll($srch->getResultSet());


        $api_products_reviews_elements = array('reviews'=>$records,'total_pages'=>$srch->pages(),'page'=>$page,'total_records'=>$srch->recordCount());
        //CommonHelper::printArray($api_products_reviews_elements);

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_products_reviews_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function change_password()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $new_password = FatApp::getPostedData('new_password', null, '');
        $confirm_new_password = FatApp::getPostedData('confirm_new_password', null, '');
        $current_password = FatApp::getPostedData('current_password', null, '');

        if ($new_password != $confirm_new_password) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_New_Password_Confirm_Password_does_not_match', $this->siteLangId));
        }

        if (! ValidateElement::password($new_password)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));
        }

        $userObj = new User($loggedUserId);
        $srch = $userObj->getUserSearchObj(array('user_id','credential_password'));
        $rs = $srch->getResultSet();

        if (!$rs) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $data = FatApp::getDb()->fetch($rs, 'user_id');

        if ($data === false) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        if ($data['credential_password'] != UserAuthentication::encryptPassword($current_password)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_YOUR_CURRENT_PASSWORD_MIS_MATCHED', $this->siteLangId));
        }

        if (!$userObj->setLoginPassword($new_password)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Password_could_not_be_set', $this->siteLangId));
        }

        $res=array('status'=>1,'msg'=>Labels::getLabel('MSG_Password_changed_successfully', $this->siteLangId));
        die($this->json_encode_unicode($res));
    }

    public function profile_info()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $userObj = new User($loggedUserId);
        $srch = $userObj->getUserSearchObj();

        $countrySearchObj = Countries::getSearchObject(false, $this->siteLangId);
        $countrySearchObj->doNotCalculateRecords();
        $countrySearchObj->doNotLimitRecords();
        $countriesDbView = $countrySearchObj->getQuery();

        $srch->joinTable(
            "($countriesDbView)",
            'LEFT OUTER JOIN',
            'u.'.User::DB_TBL_PREFIX.'country_id = c.'.Countries::tblFld('id'),
            'c'
        );

        $stateSearchObj = States::getSearchObject(false, $this->siteLangId);
        $stateSearchObj->doNotCalculateRecords();
        $stateSearchObj->doNotLimitRecords();
        $stateDbView = $stateSearchObj->getQuery();

        $srch->joinTable(
            "($stateDbView)",
            'LEFT OUTER JOIN',
            'u.'.User::DB_TBL_PREFIX.'state_id = st.'.States::tblFld('id'),
            'st'
        );


        $srch->addMultipleFields(array('u.*','c.country_name','st.state_name'));
        $rs = $srch->getResultSet();
        $user = FatApp::getDb()->fetch($rs, 'user_id');
        //commonhelper::printarray($user);
        //die();
        $arr=array();
        if (!empty($user)) {
            $arr = array(
            'user_id'=>$user['user_id'],
            'user_image'=>CommonHelper::generateFullUrl('Image', 'user', array($user['user_id'],'ORIGINAL')),
            'name'=>$user['user_name'],
            'email'=>$user['credential_email'],
            'username'=>$user['credential_username'],
            'phone'=>FatUtility::convertToType($user['user_phone'], FatUtility::VAR_STRING),
            'dob'=>$user['user_dob'],
            'city'=>$user['user_city'],
            'country_id'=>$user['user_country_id'],
            'country'=>$user['country_name'],
            'state_id'=>$user['user_state_id'],
            'state'=>$user['state_name'],
            'company'=>$user['user_company'],
            'profile_info'=>$user['user_profile_info'],
            'address_1'=>$user['user_address1'],
            'address_2'=>$user['user_address2'],
            'postcode'=>$user['user_zip'],
            'is_buyer'=>$user['user_is_buyer'],
            'is_supplier'=>$user['user_is_supplier'],
            'is_advertiser'=>$user['user_is_advertiser'],
            'is_affiliate'=>$user['user_is_affiliate'],
            'reg_date'=>$user['user_regdate'],
            'products_services'=>$user['user_products_services'],
            );
        }

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$arr,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount), true));
    }

    public function remove_profile_image()
    {
        $userId = $this->getAppLoggedUserId();
        $userId = FatUtility::int($userId);

        if (1 > $userId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $userId)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
        }

        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE, $userId)) {
            FatUtility::dieJsonError($fileHandlerObj->getError());
        }
        $res=array('status'=>1,'msg'=>Labels::getLabel('MSG_File_deleted_successfully', $this->siteLangId));
        die($this->json_encode_unicode($res));
    }

    public function update_profile_image()
    {
        $userId = $this->getAppLoggedUserId();

        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
        }
        $imageUrl = '';
        if ($post['action'] == "demo_avatar") {
            if (!is_uploaded_file($_FILES['user_profile_image']['tmp_name'])) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
            }

            $fileHandlerObj = new AttachedFile();

            if (!$res = $fileHandlerObj->saveImage($_FILES['user_profile_image']['tmp_name'], AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $userId, 0, $_FILES['user_profile_image']['name'], -1, true)
            ) {
                FatUtility::dieJsonError($fileHandlerObj->getError());
            }

            FatApp::getDb()->deleteRecords(
                AttachedFile::DB_TBL,
                array(
                'smt' => 'afile_type = ? AND afile_record_id = ?',
                'vals' => array(AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE, $userId)
                )
            );

            $imageUrl = CommonHelper::generateFullUrl('Image', 'user', array($userId,'ORIGINAL'));
        }

        if ($post['action'] == "avatar") {
            if (!is_uploaded_file($_FILES['user_profile_image']['tmp_name'])) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
            }
            $fileHandlerObj = new AttachedFile();
            if (!$res = $fileHandlerObj->saveImage($_FILES['user_profile_image']['tmp_name'], AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE, $userId, 0, $_FILES['user_profile_image']['name'], -1, true)
            ) {
                FatUtility::dieJsonError($fileHandlerObj->getError());
            }

            $data = json_decode(stripslashes($post['img_data']));
            CommonHelper::crop($data, CONF_UPLOADS_PATH .$res, $this->siteLangId);
            $imageUrl = FatUtility::generateFullUrl('Image', 'user', array($userId,'croped',true));
        }

        $res=array('status'=>1,'msg'=>Labels::getLabel('MSG_File_uploaded_successfully', $this->siteLangId),'image'=>$imageUrl);
        die($this->json_encode_unicode($res));
    }

    public function update_profile_info()
    {
        $userId = $this->getAppLoggedUserId();
        $user_is_affiliate = isset($this->app_user["user_is_affiliate"])?$this->app_user["user_is_affiliate"]:0;

        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $user_state_id = FatUtility::int($post['user_state_id']);
        $post['user_state_id'] = $user_state_id;
        if (isset($post['user_id'])) {
            unset($post['user_id']);
        }

        if (isset($post['user_dob']) && ($post['user_dob'] == "0000-00-00" || $post['user_dob'] == "" || strtotime($post['user_dob']) == 0)) {
            unset($post['user_dob']);
        }
        unset($post['credential_username']);
        unset($post['credential_email']);


        /* saving user extras[ */
        if ($user_is_affiliate) {
            $dataToSave = array(
            'uextra_user_id'        =>    $userId,
            'uextra_company_name'    =>    $post['uextra_company_name'],
            'uextra_website'        =>    CommonHelper::processUrlString($post['uextra_website'])
            );
            $dataToUpdateOnDuplicate = $dataToSave;
            unset($dataToUpdateOnDuplicate['uextra_user_id']);
            if (!FatApp::getDb()->insertFromArray(User::DB_TBL_USR_EXTRAS, $dataToSave, false, array(), $dataToUpdateOnDuplicate)) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Details_could_not_be_saved', $this->siteLangId));
            }
        }
        /* ] */


        $userObj = new User($userId);
        $userObj->assignValues($post);
        if (!$userObj->save()) {
            FatUtility::dieJsonError($userObj->getError());
        }
        $res=array('status'=>1,'msg'=>Labels::getLabel('MSG_Setup_successful', $this->siteLangId));
        die($this->json_encode_unicode($res));
    }

    public function signup()
    {
        $post = FatApp::getPostedData();
        $db = FatApp::getDb();
        //$post = array('user_name'=>'Keith Anderson','user_username'=>'keith.anderson','user_email'=>'keith.anderson@dummyid.com','user_password'=>'welcome1','user_newsletter_signup'=>1);
        if ($post == false) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if (!ValidateElement::password($post['user_password'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->siteLangId));
        }

        $userObj = new User();
        $srch = $userObj->getUserSearchObj(array('user_id','credential_email','credential_username'));
        $condition=$srch->addCondition('credential_username', '=', $post['user_username']);
        $condition->attachCondition('credential_email', '=', $post['user_email'], 'OR');
        //die($srch->getquery());
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if ($row) {
            if ($row['credential_username']==$post['user_username']) {
                FatUtility::dieJsonError(sprintf(Labels::getLabel('M_ERROR_DUPLICATE_USERNAME', $this->siteLangId), $row['credential_username']));
            } elseif ($row['credential_email']==$post['user_email']) {
                FatUtility::dieJsonError(sprintf(Labels::getLabel('M_ERROR_DUPLICATE_EMAIL', $this->siteLangId), $row['credential_email']));
            }
        }


        $db->startTransaction();

        $post['user_is_buyer'] = 1;
        $post['user_is_supplier'] = (FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) ? 0 : 1;
        $post['user_is_advertiser'] = (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1) || FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) ? 0 : 1;
        //$post['user_is_supplier'] = 0;
        $post['user_preferred_dashboard'] = User::USER_BUYER_DASHBOARD;
        $post['user_registered_initially_for'] = User::USER_TYPE_BUYER;

        $userObj->assignValues($post);
        if (!$userObj->save()) {
            $db->rollbackTransaction();
            FatUtility::dieJsonError(Labels::getLabel('MSG_USER_COULD_NOT_BE_SET', $this->siteLangId). $userObj->getError());
        }

        if (!$userObj->setMobileAppToken()) {
            $db->rollbackTransaction();
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $active = FatApp::getConfig('CONF_ADMIN_APPROVAL_REGISTRATION', FatUtility::VAR_INT, 1) ? 0: 1;
        $verify = FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION', FatUtility::VAR_INT, 1) ? 0 : 1;


        /* ] */

        if (!$userObj->setLoginCredentials($post['user_username'], $post['user_email'], $post['user_password'], $active, $verify)) {
            $db->rollbackTransaction();
            FatUtility::dieJsonError(Labels::getLabel('MSG_LOGIN_CREDENTIALS_COULD_NOT_BE_SET', $this->siteLangId). $userObj->getError());
        }

        $userObj->setUpRewardEntry($userObj->getMainTableRecordId(), $this->siteLangId);

        //$userObj->setUpAffiliateRewarding( $userObj->getMainTableRecordId() );

        if (FatApp::getPostedData('user_newsletter_signup')) {
            include_once CONF_INSTALLATION_PATH . 'library/Mailchimp.php';
            $api_key = FatApp::getConfig("CONF_MAILCHIMP_KEY");
            $list_id = FatApp::getConfig("CONF_MAILCHIMP_LIST_ID");
            if ($api_key == '' || $list_id == '') {
                FatUtility::dieJsonError(Labels::getLabel("LBL_Newsletter_is_not_configured_yet,_Please_contact_admin", $this->siteLangId). $userObj->getError());
            }

            $MailchimpObj = new Mailchimp($api_key);
            $Mailchimp_ListsObj = new Mailchimp_Lists($MailchimpObj);
            try {
                $subscriber = $Mailchimp_ListsObj->subscribe($list_id, array( 'email' => htmlentities($post['user_email'])));
            } catch (Mailchimp_Error $e) {
            }
        }

        if (FatApp::getConfig('CONF_NOTIFY_ADMIN_REGISTRATION', FatUtility::VAR_INT, 1)) {
            if (!$userObj->notifyAdminRegistration($post, $this->siteLangId)) {
                $db->rollbackTransaction();
                FatUtility::dieJsonError(Labels::getLabel('MSG_NOTIFICATION_EMAIL_COULD_NOT_BE_SENT', $this->siteLangId));
            }
        }

        $confAutoLoginRegisteration = FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION')?0:FatApp::getConfig('CONF_AUTO_LOGIN_REGISTRATION', FatUtility::VAR_INT, 1);

        $userInfo = array();
        $resultArr = array();
        $userId = $userObj->getMainTableRecordId();
        $emailNotArr = array_merge($post, array("user_id"=>$userId));
        if (FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION', FatUtility::VAR_INT, 1) /* && !$isCheckOutPage */) {
            if (!$this->userEmailVerification($userObj, $emailNotArr)) {
                $db->rollbackTransaction();
                FatUtility::dieJsonError(Labels::getLabel('MSG_VERIFICATION_EMAIL_COULD_NOT_BE_SENT', $this->siteLangId));
            }
        } else {
            if (FatApp::getConfig('CONF_WELCOME_EMAIL_REGISTRATION', FatUtility::VAR_INT, 1)) {
                if (!$this->userWelcomeEmailRegistration($userObj, $emailNotArr)) {
                    $db->rollbackTransaction();
                    FatUtility::dieJsonError(Labels::getLabel('MSG_WELCOME_EMAIL_COULD_NOT_BE_SENT', $this->siteLangId));
                }
            }

            if ($confAutoLoginRegisteration) {
                $db->commitTransaction();
                $authentication = new UserAuthentication();
                if (!$authentication->login(FatApp::getPostedData('user_username'), FatApp::getPostedData('user_password'), $_SERVER['REMOTE_ADDR'])) {
                    FatUtility::dieJsonError(Labels::getLabel($authentication->getError(), $this->siteLangId));
                }
                $userInfo = $userObj->getUserInfo(array('user_app_access_token','user_id','user_name'), true, true);
                $userInfoArr = array('token'=>$userInfo["user_app_access_token"],'user_id'=>$userInfo["user_id"], 'user_name'=>$userInfo["user_name"]);
            }
        }
        //CommonHelper::printArray($userObj);
        //die();

        $db->commitTransaction();

        $arr = array('status'=>1,'msg'=>Labels::getLabel('LBL_Registeration_Successfull', $this->siteLangId),'auto_login'=>$confAutoLoginRegisteration);
        if (!empty($userInfoArr)) {
            $arr = array_merge($arr, $userInfoArr);
        }
        die($this->json_encode_unicode($arr));
    }

    public function login()
    {
        $post = FatApp::getPostedData();
        if (empty($post['username']) || empty($post['password'])) {
            LibHelper::dieJsonError(Labels::getLabel('ERR_USERNAME_AND_PASSWORD_BOTH_ARE_REQUIRED', $this->siteLangId));
        }

        $authentication = new UserAuthentication();
        if (!$authentication->login($post['username'], $post['password'], $_SERVER['REMOTE_ADDR'], true, false, $this->app_user['temp_user_id'])) {
            LibHelper::dieJsonError(Labels::getLabel($authentication->getError(), $this->siteLangId));
        }
        $this->app_user['temp_user_id'] = 0;
        $userId = UserAuthentication::getLoggedUserId();
        $uObj = new User($userId);
        if (!$generatedToken = $uObj->setMobileAppToken()) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $userInfo = $uObj->getUserInfo(array('user_name','user_id'), true, true);

        $arr = array(
        'status'=>1,
        'token'=>$generatedToken,
        'user_name'=>$userInfo["user_name"],
        'user_id'=>$userInfo["user_id"],
        'user_image'=>CommonHelper::generateFullUrl('image', 'user', array($userInfo['user_id'],'thumb',1))
        );
        die($this->json_encode_unicode($arr));
    }

    public function logout()
    {
        UserAuthentication::logout();
        $arr = array(
        'status'=>1,
        );
        die($this->json_encode_unicode($arr));
    }

    public function forgot_password()
    {
        $post = FatApp::getPostedData();
        //$post['user_email_username']="keith.anderson";
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $user = $post['user_email_username'];
        $userAuthObj = new UserAuthentication();
        if (!$row = $userAuthObj->getUserByEmailOrUserName($user, '', false)) {
            FatUtility::dieJsonError(Labels::getLabel($userAuthObj->getError(), $this->siteLangId));
        }
        if ($userAuthObj->checkUserPwdResetRequest($row['user_id'])) {
            FatUtility::dieJsonError(Labels::getLabel($userAuthObj->getError(), $this->siteLangId));
        }
        $token = UserAuthentication::encryptPassword(FatUtility::getRandomString(20));
        $row['token'] = $token;
        $userAuthObj->deleteOldPasswordResetRequest();
        $db = FatApp::getDb();
        $db->startTransaction();
        if (!$userAuthObj->addPasswordResetRequest($row)) {
            FatUtility::dieJsonError(Labels::getLabel($userAuthObj->getError(), $this->siteLangId));
        }
        $row['link'] = FatUtility::generateFullUrl('GuestUser', 'resetPassword', array($row['user_id'], $token));
        $email = new EmailHandler();
        if (!$email->sendForgotPasswordLinkEmail($this->siteLangId, $row)) {
            $db->rollbackTransaction();
            FatUtility::dieJsonError(Labels::getLabel("MSG_ERROR_IN_SENDING_PASSWORD_RESET_LINK_EMAIL", $this->siteLangId));
        }

        $db->commitTransaction();
        $arr=array('status'=>1,'msg'=>Labels::getLabel("MSG_YOUR_PASSWORD_RESET_INSTRUCTIONS_TO_YOUR_EMAIL", $this->siteLangId));
        die($this->json_encode_unicode($arr));
    }

    public function login_facebook()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        include_once CONF_INSTALLATION_PATH . 'library/facebook/facebook.php';
        $facebook = new Facebook(
            array(
            'appId' => FatApp::getConfig("CONF_FACEBOOK_APP_ID", FatUtility::VAR_STRING, ''),
            'secret' => FatApp::getConfig("CONF_FACEBOOK_APP_SECRET", FatUtility::VAR_STRING, ''),
            )
        );
        $facebook->setAccessToken($post['fb_token']);
        $user = $facebook->getUser();
        if (!$user) {
            $arr=array('status'=>0, 'msg'=>"Invalid Token");
        }
        try {
            // Proceed knowing you have a logged in user who's authenticated.
            $userProfile = $facebook->api('/me?fields=id,name,email');
        } catch (FacebookApiException $e) {
            FatUtility::dieJsonError($e->getMessage());
            $user = null;
        }

        if (empty($userProfile)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_ERROR_INVALID_REQUEST', $this->siteLangId));
        }

        // User info ok? Let's print it (Here we will be adding the login and registering routines)
        $facebookName = $userProfile['name'];
        $userFacebookId = $userProfile['id'];
        $facebookEmail = $userProfile['email'];

        $db = FatApp::getDb();
        $userObj = new User();
        $srch = $userObj->getUserSearchObj(array('user_id','user_facebook_id','credential_email','credential_active'));
        if (!empty($facebookEmail)) {
            $srch->addCondition('credential_email', '=', $facebookEmail);
        } else {
            FatUtility::dieJsonError(Labels::getLabel("MSG_THERE_WAS_SOME_PROBLEM_IN_AUTHENTICATING_YOUR_ACCOUNT_WITH_FACEBOOK,_PLEASE_TRY_WITH_DIFFERENT_LOGIN_OPTIONS", $this->siteLangId));
        }
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if ($row) {
            if ($row['credential_active'] != applicationConstants::ACTIVE) {
                FatUtility::dieJsonError(Labels::getLabel("ERR_YOUR_ACCOUNT_HAS_BEEN_DEACTIVATED", $this->siteLangId));
            }
            $userObj->setMainTableRecordId($row['user_id']);
            $arr = array('user_facebook_id' => $userFacebookId);
            if (!$userObj->setUserInfo($arr)) {
                FatUtility::dieJsonError(Labels::getLabel($userObj->getError(), $this->siteLangId));
            }
        } else {
            $user_is_supplier = (FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1))?0:1;
            $user_is_advertiser = (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1) || FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1))?0:1;

            $db->startTransaction();

            $userData = array(
            'user_name' => $facebookName,
            'user_is_buyer' => 1,
            'user_is_supplier' => $user_is_supplier,
            'user_is_advertiser' => $user_is_advertiser,
            'user_facebook_id' => $userFacebookId,
            );
            $post['user_registered_initially_for'] = User::USER_TYPE_BUYER;
            $userObj->assignValues($userData);
            if (!$userObj->save()) {
                FatUtility::dieJsonError(Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
            }

            $username = str_replace(" ", "", $facebookName).$userFacebookId;

            if (!$userObj->setLoginCredentials($username, $facebookEmail, uniqid(), 1, 1)) {
                $db->rollbackTransaction();
                FatUtility::dieJsonError(Labels::getLabel("MSG_LOGIN_CREDENTIALS_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
            }
            if (FatApp::getConfig('CONF_WELCOME_EMAIL_REGISTRATION', FatUtility::VAR_INT, 1) && $facebookEmail) {
                $userId = $userObj->getMainTableRecordId();
                $data['user_email'] = $facebookEmail;
                $data['user_name'] = $facebookName;
                $data['user_id'] = $userId;

                //ToDO::Change login link to contact us link
                $data['link'] = FatUtility::generateFullUrl('GuestUser', 'loginForm');
                $userEmailObj = new User($userId);
                if (!$this->userWelcomeEmailRegistration($userEmailObj, $data)) {
                    $db->rollbackTransaction();
                    FatUtility::dieJsonError(Labels::getLabel("MSG_WELCOME_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
                }
            }
            $db->commitTransaction();
            $userObj->setUpRewardEntry($userObj->getMainTableRecordId(), $this->siteLangId);
        }
        $userInfo = $userObj->getUserInfo(array('user_facebook_id','user_preferred_dashboard','credential_username','credential_password'));
        if (!$userInfo || ($userInfo && $userInfo['user_facebook_id']!= $userFacebookId)) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId));
        }
        $authentication = new UserAuthentication();
        if (!$authentication->login($userInfo['credential_username'], $userInfo['credential_password'], $_SERVER['REMOTE_ADDR'], false, false, $this->app_user['temp_user_id'])) {
            FatUtility::dieJsonError(Labels::getLabel($authentication->getError(), $this->siteLangId));
        }
        $this->app_user['temp_user_id'] = 0;
        if (!$token = $userObj->setMobileAppToken()) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $userInfo = $userObj->getUserInfo(array('user_id','user_name'), true, true);
        $arr=array('status'=>1,'token'=>$token, 'user_name'=>$userInfo["user_name"],'user_id'=>$userInfo["user_id"],
        'user_image'=>CommonHelper::generateFullUrl('image', 'user', array($userInfo['user_id'],'ORIGINAL')));
        die($this->json_encode_unicode($arr));
    }

    public function login_gplus()
    {
        $db = FatApp::getDb();
        $post = FatApp::getPostedData();
        //$post['gp_token']="TT";
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $userObj=new User();
        if (isset($post['gp_token'])) {
            $content=@file_get_contents($gplus_url = "https://www.googleapis.com/oauth2/v1/userinfo?access_token=".$post['gp_token']);
            $me=json_decode($content);
            if ($me!=null) {
                $userGoogleplusEmail = filter_var($me->email, FILTER_SANITIZE_EMAIL); // get the USER EMAIL ADDRESS using OAuth2
                $userGoogleplusId = $me->id;
                $userGoogleplusName = $me->name;
                if (isset($userGoogleplusEmail) && (!empty($userGoogleplusEmail))) {
                    $userObj = new User();
                    $srch = $userObj->getUserSearchObj(array('user_id','credential_email','credential_active'));
                    $srch->addCondition('credential_email', '=', $userGoogleplusEmail);
                    $rs = $srch->getResultSet();
                    $row = $db->fetch($rs);

                    if ($row) {
                        if ($row['credential_active'] != applicationConstants::ACTIVE) {
                            FatUtility::dieJsonError(Labels::getLabel('ERR_YOUR_ACCOUNT_HAS_BEEN_DEACTIVATED', $this->siteLangId));
                        }
                        $userObj->setMainTableRecordId($row['user_id']);

                        $arr = array('user_googleplus_id' => $userGoogleplusId);

                        if (!$userObj->setUserInfo($arr)) {
                            FatUtility::dieJsonError(Labels::getLabel($userObj->getError(), $this->siteLangId));
                        }
                    } else {
                        $user_is_supplier = (FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) ? 0: 1;
                        $user_is_advertiser = (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1) || FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) ? 0: 1;

                        $db->startTransaction();

                        $userData = array(
                        'user_name' => $userGoogleplusName,
                        'user_is_buyer' => 1,
                        'user_is_supplier' => $user_is_supplier,
                        'user_is_advertiser' => $user_is_advertiser,
                        'user_googleplus_id' => $userGoogleplusId,
                        );
                        $post['user_registered_initially_for'] = User::USER_TYPE_BUYER;
                        $userObj->assignValues($userData);
                        if (!$userObj->save()) {
                            $db->rollbackTransaction();
                            FatUtility::dieJsonError(Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
                        }
                        $username = str_replace(" ", "", $userGoogleplusName).$userGoogleplusId;
                        if (!$userObj->setLoginCredentials($username, $userGoogleplusEmail, uniqid(), 1, 1)) {
                            $db->rollbackTransaction();
                        }
                        $db->commitTransaction();
                        $userObj->setUpRewardEntry($userObj->getMainTableRecordId(), $this->siteLangId);
                    }
                    $userInfo = $userObj->getUserInfo(array('user_googleplus_id','user_preferred_dashboard','credential_username','credential_password'));
                    if (!$userInfo || ($userInfo && $userInfo['user_googleplus_id']!= $userGoogleplusId)) {
                        FatUtility::dieJsonError(Labels::getLabel("MSG_USER_COULD_NOT_BE_SET", $this->siteLangId));
                    }
                    $authentication = new UserAuthentication();
                    if (!$authentication->login($userInfo['credential_username'], $userInfo['credential_password'], $_SERVER['REMOTE_ADDR'], false, false, $this->app_user['temp_user_id'])) {
                        FatUtility::dieJsonError(Labels::getLabel($authentication->getError(), $this->siteLangId));
                    }
                    $this->app_user['temp_user_id'] = 0;
                    if (!$token = $userObj->setMobileAppToken()) {
                        FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
                    }
                    $userInfo = $userObj->getUserInfo(array('user_id','user_name'), true, true);
                    $arr=array('status'=>1,'token'=>$token, 'user_name'=>$userInfo["user_name"],'user_id'=>$userInfo["user_id"],
                    'user_image'=>CommonHelper::generateFullUrl('image', 'user', array($userInfo['user_id'],'ORIGINAL')));
                    die($this->json_encode_unicode($arr));
                }
            } else {
                $arr=array('status'=>0, 'msg'=>"Something wrong with this token, not returning user's email.");
            }
        } else {
            $arr=array('status'=>0, 'msg'=>"Invalid Token");
        }
        die($this->json_encode_unicode($arr));
    }

    public function messages()
    {
        $userId = $this->getAppLoggedUserId();
        $db = FatApp::getDb();
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        $srch = new MessageSearch();
        $srch->joinThreadLastMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->joinThreadStartedByUser();
        $srch->addMultipleFields(array('tth.*','ttm.message_id','ttm.message_text','ttm.message_date','ttm.message_is_unread','ttm.message_to'));
        $srch->addCondition('ttm.message_deleted', '=', 0);
        $cnd = $srch->addCondition('ttm.message_from', '=', $userId);
        $cnd->attachCondition('ttm.message_to', '=', $userId, 'OR');
        $srch->addOrder('message_id', 'DESC');
        $srch->addGroupBy('ttm.message_thread_id');
        /* die($srch->getQuery()); */
        if (isset($post['keyword']) && $post['keyword']!='') {
            $cnd = $srch->addCondition('tth.thread_subject', 'like', "%".$post['keyword']."%");
            $cnd->attachCondition('tfr.user_name', 'like', "%".$post['keyword']."%", 'OR');
            $cnd->attachCondition('tfr_c.credential_username', 'like', "%".$post['keyword']."%", 'OR');
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        //die($srch->getquery());
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $message_records = array();
        foreach ($records as $mkey=>$mval) {
            $profile_images_arr=  array(
             "message_from_profile_url"=>CommonHelper::generateFullUrl('image', 'user', array($mval['message_from_user_id'],'thumb',1)),
             "message_to_profile_url"=>CommonHelper::generateFullUrl('image', 'user', array($mval['message_to_user_id'],'thumb',1)),
             "message_timestamp"=>strtotime($mval['message_date'])
                                        );
            $message_records[] = array_merge($mval, $profile_images_arr);
        }

        $api_message_elements = array('messages'=>$message_records,'total_pages'=>$srch->pages(),'page'=>$page,'total_records'=>$srch->recordCount());
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_message_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function view_thread_messages()
    {
        $userId = $this->getAppLoggedUserId();
        $db = FatApp::getDb();
        $post = FatApp::getPostedData();
        //$post['thread_id']=3;
        $arr=array();
        $page = 1;
        $pagesize=$this->pagesize;
        if ((empty($post)) || (empty($post['thread_id']))) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $threadId = $post['thread_id'];
        $messageId = FatApp::getPostedData('message_id', null, '');
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);

        $srch = new MessageSearch();
        $srch->joinThreadMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->joinThreadStartedByUser();
        $srch->joinShops();
        $srch->joinOrderProducts();
        $srch->joinOrderProductStatus();
        $srch->addMultipleFields(array('tth.*','ttm.message_id','top.op_invoice_number','message_text','message_date'));
        //$srch->addMultipleFields(array('tth.*','ttm.message_id','ttm.message_text','ttm.message_date','ttm.message_is_unread'));
        $srch->addCondition('ttm.message_deleted', '=', 0);
        $srch->addCondition('tth.thread_id', '=', $threadId);
        if ($messageId) {
            $srch->addCondition('ttm.message_id', '=', $messageId);
        }
        $cnd = $srch->addCondition('ttm.message_from', '=', $userId);
        $cnd->attachCondition('ttm.message_to', '=', $userId, 'OR');
        $srch->addOrder('message_date', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        //die($srch->getquery());
        $rs = $srch->getResultSet();
        $threadDetails = FatApp::getDb()->fetchAll($rs);

        /* if($threadDetails == false){
        FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_ACCESS',$this->siteLangId));
        } */

        $threadObj = new Thread($threadId);
        if (!$threadObj->markUserMessageRead($threadId, $userId)) {
            FatUtility::dieJsonError($threadObj->getError());
        }
        $thread_message_records = array();
        if (!empty($threadDetails)) {
            foreach ($threadDetails as $mkey=>$mval) {
                $profile_images_arr =  array(
                 "message_from_profile_url"=>CommonHelper::generateFullUrl('image', 'user', array($mval['message_from_user_id'],'ORIGINAL')),
                 "message_to_profile_url"=>CommonHelper::generateFullUrl('image', 'user', array($mval['message_to_user_id'],'ORIGINAL')),
                 "message_timestamp"=>strtotime($mval['message_date'])
                                );
                $thread_message_records[] = array_merge($mval, $profile_images_arr);
            }
        }

        $api_thread_elements = array('thread_details'=>$thread_message_records,'thread_types'=>Thread::getThreadTypeArr($this->siteLangId));
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_thread_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function send_thread_message()
    {
        $userId = $this->getAppLoggedUserId();
        $db = FatApp::getDb();
        $post = FatApp::getPostedData();
        //$post = array('message_thread_id'=>1,"message_text"=>"Hello Lorem Ipsum 2");
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $threadId =  FatUtility::int($post['message_thread_id']);
        /*
        $messageId =  FatUtility::int($post['message_id']);

        if(1 > $threadId || 1 > $messageId){
        FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST',$this->siteLangId));
        }
        */

        if (1 > $threadId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $srch = new MessageSearch();
        $srch->joinThreadMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->joinThreadStartedByUser();
        $srch->addMultipleFields(array('tth.*'));
        $srch->addCondition('ttm.message_deleted', '=', 0);
        $srch->addCondition('tth.thread_id', '=', $threadId);
        //$srch->addCondition('ttm.message_id','=',$messageId);
        $cnd = $srch->addCondition('ttm.message_from', '=', $userId);
        $cnd->attachCondition('ttm.message_to', '=', $userId, 'OR');
        $rs = $srch->getResultSet();

        $threadDetails = FatApp::getDb()->fetch($rs);
        if (empty($threadDetails)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $messageSendTo = ($threadDetails['message_from_user_id'] == $userId)?$threadDetails['message_to_user_id']:$threadDetails['message_from_user_id'];

        $data = array(
        'message_thread_id'=>$threadId,
        'message_from'=>$userId,
        'message_to'=>$messageSendTo,
        'message_text'=>$post['message_text'],
        'message_date'=>date('Y-m-d H:i:s'),
        'message_is_unread'=>1
        );

        $tObj = new Thread();

        if (!$insertId = $tObj->addThreadMessages($data)) {
            FatUtility::dieJsonError(Labels::getLabel($tObj->getError(), $this->siteLangId));
        }

        if ($insertId) {
            $emailObj = new EmailHandler();
            $emailObj->SendMessageNotification($insertId, $this->siteLangId);
        }
        unset($data['message_is_unread']);
        $userObj = new User($userId);
        $userInfo = $userObj->getUserInfo(array('user_id','user_name'), true, true);
        $data['message_from_name'] =  $userInfo['user_name'];
        $arr = array('status'=>1,'msg'=>Labels::getLabel("MSG_Message_Submitted_Successfully", $this->siteLangId),'data'=>$data);
        die($this->json_encode_unicode($arr));
    }

    public function addresses()
    {
        $userId = $this->getAppLoggedUserId();
        $addresses = UserAddress::getUserAddresses($userId, $this->siteLangId);
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$addresses,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function save_address()
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $ua_id = 0;
        if (array_key_exists('ua_id', $post)) {
            $ua_id = FatUtility::int($post['ua_id']);
            unset($post['ua_id']);
        }
        $ua_state_id = FatUtility::int($post['ua_state_id']);
        $post['ua_state_id'] = $ua_state_id;

        $addressObj = new UserAddress($ua_id);
        $data_to_be_save = $post;
        $data_to_be_save['ua_user_id'] = $userId;
        $addressObj->assignValues($data_to_be_save, true);
        if (!$addressObj->save()) {
            FatUtility::dieJsonError($addressObj->getError());
        }
        if (0<=$ua_id) {
            $ua_id = $addressObj->getMainTableRecordId();
        }
        $arr=array('status'=>1,'msg'=>Labels::getLabel("LBL_Setup_Successful", $this->siteLangId));
        die($this->json_encode_unicode($arr));
    }

    public function delete_address()
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $ua_id = FatUtility::int($post['id']);
        if (1 > $ua_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $data =  UserAddress::getUserAddresses($userId, $this->siteLangId, 0, $ua_id);
        if ($data === false) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $addressObj = new UserAddress($ua_id);
        if (!$addressObj->deleteRecord()) {
            FatUtility::dieJsonError($addressObj->getError());
        }
        $arr=array('status'=>1,'msg'=>Labels::getLabel("MSG_Deleted_successfully", $this->siteLangId));
        die($this->json_encode_unicode($arr));
    }

    public function primary_address()
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            //FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST',$this->siteLangId));
        }
        $ua_id = FatUtility::int($post['id']);
        if (1 > $ua_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $data =  UserAddress::getUserAddresses($userId, $this->siteLangId, 0, $ua_id);
        if ($data === false) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $updateArray = array( 'ua_is_default'=>0);
        $whr = array('smt'=>'ua_user_id = ?', 'vals'=>array($userId));

        if (!FatApp::getDb()->updateFromArray(UserAddress::DB_TBL, $updateArray, $whr)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        //die('TT');
        $addressObj = new UserAddress($ua_id);
        $data = array(
        'ua_id'=>$ua_id,
        'ua_is_default'=>1,
        'ua_user_id'=>$userId,
        );

        $addressObj->assignValues($data, true);
        if (!$addressObj->save()) {
            FatUtility::dieJsonError($addressObj->getError());
        }
        $arr=array('status'=>1,'msg'=>Labels::getLabel("MSG_Address_Updated_Successfully", $this->siteLangId));
        die($this->json_encode_unicode($arr));
    }

    public function favorite_products()
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }

        $productSrchObj = new ProductSearch($this->siteLangId);
        $productSrchObj->joinProductToCategory($this->siteLangId);
        $productSrchObj->setDefinedCriteria();
        $productSrchObj->joinSellerSubscription($this->siteLangId, true);
        $productSrchObj->addSubscriptionValidCondition();
        $productSrchObj->joinFavouriteProducts($userId);
        $productSrchObj->joinProductRating();
        $productSrchObj->doNotCalculateRecords();
        $productSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $productSrchObj->addGroupBy('product_id');


        /* $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type','=',SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating","count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $productSrchObj->joinTable( '(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating' ); */

        $productSrchObj->addMultipleFields(
            array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
            'theprice', 'selprod_price','selprod_stock', 'selprod_condition','prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','ifnull(sq_sprating.prod_rating,0) prod_rating ','ifnull(sq_sprating.totReviews,0) totReviews','selprod_sold_count','ufp_id','IF(ufp_id > 0, 1, 0) as isfavorite','selprod_min_order_qty')
        );

        $srch = new UserFavoriteProductSearch();
        $srch->joinTable('(' . $productSrchObj->getQuery() . ')', 'INNER JOIN', 'ufp_selprod_id = selprod_id', 'pCust');
        $srch->addCondition('ufp_user_id', '=', $userId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addGroupBy('selprod_id');
        $rs = $srch->getResultSet();
        $favoriteProducts = FatApp::getDb()->fetchAll($rs);

        if ($favoriteProducts) {
            foreach ($favoriteProducts as &$favoriteProduct) {
                $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($favoriteProduct['product_id'], "MEDIUM", $favoriteProduct['selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $favoriteProduct['discounted_text'] =  CommonHelper::showProductDiscountedText($favoriteProduct, $this->siteLangId);
                $favoriteProduct['product_image'] =  $mainImgUrl;
                $favoriteProduct['currency_selprod_price'] = CommonHelper::displayMoneyFormat($favoriteProduct['selprod_price'], true, false, false);
                $favoriteProduct['currency_theprice'] = CommonHelper::displayMoneyFormat($favoriteProduct['theprice'], true, false, false);
            }
        }
        $arr=array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$favoriteProducts);
        die($this->json_encode_unicode($arr));
    }

    public function wishlists()
    {
        $userId = $this->getAppLoggedUserId();
        $wishLists = UserWishList::getUserWishLists($userId, false);
        $arr=array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$wishLists);
        die($this->json_encode_unicode($arr));
    }

    public function wishlist_products($wishlist_id)
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }

        $db = FatApp::getDb();
        $productSrchObj = new ProductSearch($this->siteLangId);
        $productSrchObj->joinProductToCategory($this->siteLangId);
        $productSrchObj->setDefinedCriteria();
        $productSrchObj->joinSellerSubscription($this->siteLangId, true);
        $productSrchObj->addSubscriptionValidCondition();
        $productSrchObj->joinFavouriteProducts($userId);
        $productSrchObj->joinProductRating();
        $productSrchObj->doNotCalculateRecords();
        $productSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $productSrchObj->addGroupBy('selprod_id');


        /* $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type','=',SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating","count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $productSrchObj->joinTable( '(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating' ); */

        $productSrchObj->addMultipleFields(
            array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
            'theprice', 'selprod_price','selprod_stock', 'selprod_condition','prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','ifnull(sq_sprating.prod_rating,0) prod_rating ','ifnull(sq_sprating.totReviews,0) totReviews','selprod_sold_count','ufp_id','IF(ufp_id > 0, 1, 0) as isfavorite','selprod_min_order_qty')
        );

        $srch = new UserWishListProductSearch($this->siteLangId);
        $srch->joinTable('(' . $productSrchObj->getQuery() . ')', 'INNER JOIN', 'uwlp_selprod_id = selprod_id', 'pCust');
        $srch->addCondition('uwlp_uwlist_id', '=', $wishlist_id);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('uwlp_added_on');
        $srch->addGroupBy('uwlp_selprod_id');

        $rs = $srch->getResultSet();
        $wishListProducts = FatApp::getDb()->fetchAll($rs);

        if ($wishListProducts) {
            foreach ($wishListProducts as &$wishlistProduct) {
                $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($wishlistProduct['product_id'], "MEDIUM", $wishlistProduct['selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $wishlistProduct['discounted_text'] =  CommonHelper::showProductDiscountedText($wishlistProduct, $this->siteLangId);
                $wishlistProduct['product_image'] =  $mainImgUrl;
                $wishlistProduct['currency_selprod_price'] = CommonHelper::displayMoneyFormat($wishlistProduct['selprod_price'], true, false, false);
                $wishlistProduct['currency_theprice'] = CommonHelper::displayMoneyFormat($wishlistProduct['theprice'], true, false, false);
            }
        }

        $wishlist['products'] = $wishListProducts;
        $wishlist['total_records'] = $srch->recordCount();
        $wishlist['total_pages'] = $srch->pages();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$wishlist,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function add_to_cart()
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        //$addons = array("41"=>2,"38"=>3);
        //$post = array("selprod_id"=>"14","quantity"=>"1");
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if ($userId>0) {
            $user_is_buyer = User::getAttributesById($userId, 'user_is_buyer');
            if (!$user_is_buyer) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Please_login_with_buyer_account_to_add_products_to_cart', $this->siteLangId));
            }
            $user_id = $userId;
        }

        $json = array();
        $selprod_id = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, ''); //FatUtility::int($post['selprod_id']);
        $quantity = FatApp::getPostedData('quantity', FatUtility::VAR_INT, '1'); //FatUtility::int($post['quantity']);

        $productsToAdd  = isset($post['addons'])?$post['addons']:array();
        $productsToAdd[$selprod_id] = $quantity;
        //print_r($productsToAdd);
        //die();
        $ProductAdded = false;
        foreach ($productsToAdd as $productId =>$quantity) {
            if ($productId <= 0) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            }
            $srch = new ProductSearch($this->siteLangId);
            $srch->joinSellerProducts();
            $srch->addCondition('pricetbl.selprod_id', '=', $productId);
            $srch->addMultipleFields(
                array(
                'selprod_id','selprod_code', 'selprod_min_order_qty', 'selprod_stock', 'product_name' )
            );
            //die($srch->getquery());
            $rs = $srch->getResultSet();
            $db = FatApp::getDb();
            $sellerProductRow = $db->fetch($rs);
            if (!$sellerProductRow || $sellerProductRow['selprod_id'] != $productId) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            }
            $productId = $sellerProductRow['selprod_id'];
            $selprod_code = $sellerProductRow['selprod_code'];
            $productAdd = true;
            /* cannot add, out of stock products in cart[ */

            if ($sellerProductRow['selprod_stock'] <= 0) {
                if ($productId!=$selprod_id) {
                    FatUtility::dieJsonError(sprintf(Labels::getLabel('LBL_Out_of_Stock_Products_cannot_be_added_to_cart_%s', $this->siteLangId), FatUtility::decodeHtmlEntities($sellerProductRow['product_name'])));
                } else {
                    FatUtility::dieJsonError(sprintf(Labels::getLabel('LBL_Out_of_Stock_Products_cannot_be_added_to_cart_%s', $this->siteLangId), FatUtility::decodeHtmlEntities($sellerProductRow['product_name'])));
                }
            }
            /* ] */

            /* minimum quantity check[ */
            $minimum_quantity = ($sellerProductRow['selprod_min_order_qty']) ? $sellerProductRow['selprod_min_order_qty'] : 1;
            if ($quantity < $minimum_quantity) {
                $productAdd = false;
                $str = Labels::getLabel('LBL_Please_add_minimum_{minimumquantity}', $this->siteLangId);
                $str = str_replace("{minimumquantity}", $minimum_quantity, $str);
                FatUtility::dieJsonError($str." ".FatUtility::decodeHtmlEntities($sellerProductRow['product_name']));
            }
            /* ] */

            /* product availability date check covered in product search model[ ] */
            $this->app_user['temp_user_id'] = $this->getAppTempUserId();
            $cartObj = new Cart($userId, 0, $this->app_user['temp_user_id']);
            /* cannot add quantity more than stock of the product[ */
            $selprod_stock = $sellerProductRow['selprod_stock'] - Product::tempHoldStockCount($productId);
            if ($quantity > $selprod_stock) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Requested_quantity_more_than_stock_available', $this->siteLangId)." ". $selprod_stock." " .FatUtility::decodeHtmlEntities($sellerProductRow['product_name']));
            }
            /* ] */
            if ($productAdd) {
                //die($productId."#".$quantity);
                $cartObj->add($productId, $quantity);
                $ProductAdded = true;
            }
        }

        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("MSG_Added_to_cart", $this->siteLangId), 'cart_count'=>$cartObj->countProducts(),'temp_user_id'=>$this->app_user['temp_user_id'])));
    }

    public function remove_cart_item()
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if (!isset($post['key'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $cartObj = new Cart($userId, 0, $this->app_user['temp_user_id']);
        if (!$cartObj->remove(md5($post['key']))) {
            FatUtility::dieJsonError($cartObj->getError());
        }
        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("MSG_Item_removed_successfully", $this->siteLangId), 'cart_count'=>$cartObj->countProducts())));
    }

    public function update_cart_qty()
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if (!isset($post['key'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $key = $post['key'];
        $quantity = isset($post['quantity']) ? FatUtility::int($post['quantity']) : 1;
        //$key = "czo1OiJTUF8xMyI7";
        //$quantity = 5;
        $cartObj = new Cart($userId, 0, $this->app_user['temp_user_id']);
        if (!$cartObj->update(md5($key), $quantity)) {
            FatUtility::dieJsonError($cartObj->getError());
        }
        if (!empty($cartObj->getWarning())) {
            FatUtility::dieJsonError($cartObj->getWarning());
        }
        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("MSG_cart_updated_successfully", $this->siteLangId), 'cart_count'=>$cartObj->countProducts())));
    }

    public function apply_promo_code()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if (empty($post['coupon_code'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $couponCode = $post['coupon_code'];
        $couponInfo = DiscountCoupons::getValidCoupons($loggedUserId, $this->siteLangId, $couponCode);
        if ($couponInfo == false) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Coupon_Code', $this->siteLangId));
        }

        $cartObj = new Cart($loggedUserId);
        if (!$cartObj->updateCartDiscountCoupon($couponInfo['coupon_code'])) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
        }

        $holdCouponData = array(
        'couponhold_coupon_id'=>$couponInfo['coupon_id'],
        'couponhold_user_id'=>$loggedUserId,
        'couponhold_added_on'=>date('Y-m-d H:i:s'),
        );

        if (!FatApp::getDb()->insertFromArray(DiscountCoupons::DB_TBL_COUPON_HOLD, $holdCouponData, true, array(), $holdCouponData)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
        }

        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("MSG_cart_discount_coupon_applied", $this->siteLangId), 'cart_count'=>$cartObj->countProducts())));
    }

    public function remove_promo_code()
    {
        $userId = $this->getAppLoggedUserId();
        $cartObj = new Cart($userId);
        if (!$cartObj->removeCartDiscountCoupon()) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
        }
        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("MSG_cart_discount_coupon_removed", $this->siteLangId), 'cart_count'=>$cartObj->countProducts())));
    }

    public function apply_reward_points()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        //$post['redeem_rewards']=20;
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if (empty($post['redeem_rewards'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $rewardPoints = $post['redeem_rewards'];
        $totalBalance = UserRewardBreakup::rewardPointBalance($loggedUserId);
        /* var_dump($totalBalance);exit; */
        if ($totalBalance == 0 || $totalBalance < $rewardPoints) {
            FatUtility::dieJsonError(Labels::getLabel('ERR_Insufficient_reward_point_balance', $this->siteLangId));
        }

        $cartObj = new Cart($loggedUserId);
        $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
        $rewardPointValues = min(CommonHelper::convertRewardPointToCurrency($rewardPoints), $cartSummary['orderNetAmount']);
        $rewardPoints = CommonHelper::convertCurrencyToRewardPoint($rewardPointValues);

        if ($rewardPoints < FatApp::getConfig('CONF_MIN_REWARD_POINT') || $rewardPoints > FatApp::getConfig('CONF_MAX_REWARD_POINT')) {
            $msg = Labels::getLabel('ERR_PLEASE_USE_REWARD_POINT_BETWEEN_{MIN}_to_{MAX}', $this->siteLangId);
            $msg = str_replace('{MIN}', FatApp::getConfig('CONF_MIN_REWARD_POINT'), $msg);
            $msg = str_replace('{MAX}', FatApp::getConfig('CONF_MAX_REWARD_POINT'), $msg);
            FatUtility::dieJsonError($msg);
        }

        if (!$cartObj->updateCartUseRewardPoints($rewardPoints)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
        }

        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("MSG_Used_Reward_point", $this->siteLangId).'-'.$rewardPoints, 'cart_count'=>$cartObj->countProducts())));
    }

    public function remove_reward_points()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $cartObj = new Cart();
        if (!$cartObj->removeUsedRewardPoints($loggedUserId)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
        }
        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("MSG_used_reward_point_removed", $this->siteLangId), 'cart_count'=>$cartObj->countProducts())));
    }

    public function update_wallet($apply_wallet)
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $cartObj = new Cart($loggedUserId);
        if (!$cartObj->updateCartWalletOption($apply_wallet)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
        }
        $wallet_msg = $apply_wallet?Labels::getLabel("MSG_Wallet_Selected_Successfully", $this->siteLangId):Labels::getLabel("MSG_Wallet_Removed_Successfully", $this->siteLangId);
        die($this->json_encode_unicode(array('status'=>1, 'msg'=>$wallet_msg, 'cart_count'=>$cartObj->countProducts())));
    }

    public function update_cart_billing_address()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $cartObj = new Cart($loggedUserId);

        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if (empty($post['billing_address_id'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $billing_address_id = $post['billing_address_id'];
        $BillingAddressDetail = UserAddress::getUserAddresses($loggedUserId, 0, 0, $billing_address_id);
        if (!$BillingAddressDetail) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
        }
        if (!$cartObj->setCartBillingAddress($BillingAddressDetail['ua_id'])) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
        }

        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("LBL_Cart_Billing_Address_Updated_Successfully", $this->siteLangId), 'cart_count'=>$cartObj->countProducts())));
    }

    public function update_cart_shipping_address()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $cartObj = new Cart($loggedUserId);
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if (empty($post['shipping_address_id'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $shipping_address_id = $post['shipping_address_id'];
        $ShippingAddressDetail = UserAddress::getUserAddresses($loggedUserId, 0, 0, $shipping_address_id);
        if (!$ShippingAddressDetail) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
        }
        if (!$cartObj->setCartShippingAddress($ShippingAddressDetail['ua_id'])) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId));
        }

        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("LBL_Cart_Shipping_Address_Updated_Successfully", $this->siteLangId), 'cart_count'=>$cartObj->countProducts())));
    }

    public function get_cart_details()
    {
        /*$BillingAddressDetail = "";
        $ShippingddressDetail = "";*/

        $loggedUserId = $this->getAppLoggedUserId();
        //die($loggedUserId."#");
        $cartObj = new Cart($loggedUserId, 0, $this->app_user['temp_user_id']);
        $productsArr = $cartObj->getProducts($this->siteLangId);
        $cartProductsArr = array();
        foreach ($productsArr as $ckey=>$cval) {
            $product['image_url'] = CommonHelper::generateFullUrl('image', 'product', array($cval['product_id'], "", $cval['selprod_id'], 0, $this->siteLangId));
            $product['currency_selprod_price'] = CommonHelper::displayMoneyFormat($cval['selprod_price'], true, false, false);
            $product['currency_theprice'] = CommonHelper::displayMoneyFormat($cval['theprice'], true, false, false);
            $product['currency_tax'] = CommonHelper::displayMoneyFormat($cval['tax'], true, false, false);
            $product['currency_commission'] = CommonHelper::displayMoneyFormat($cval['commission'], true, false, false);
            $product['currency_volume_discount'] = CommonHelper::displayMoneyFormat($cval['volume_discount'], true, false, false);
            $product['currency_shipping_cost'] = CommonHelper::displayMoneyFormat($cval['shipping_cost'], true, false, false);
            $product['currency_total'] = CommonHelper::displayMoneyFormat($cval['total'], true, false, false);
            $cartProductsArr[] = array_merge($cval, $product);
        }

        $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
        foreach ($cartSummary as $key => $value) {
            $cartSummaryArr[$key] = CommonHelper::displayMoneyFormat($value, true, false, false);
        }

        $BillingAddressDetail = array();
        $billing_address_id = $cartObj->getCartBillingAddress();
        if ($billing_address_id>0) {
            $BillingAddressDetail = UserAddress::getUserAddresses($loggedUserId, 0, 0, $billing_address_id);
        }

        $ShippingddressDetail = array();
        $shipping_address_id = $cartObj->getCartShippingAddress();
        if ($shipping_address_id>0) {
            $ShippingddressDetail = UserAddress::getUserAddresses($loggedUserId, 0, 0, $shipping_address_id);
        }

        $cartHasPhysicalProduct = false;
        if ($cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }

        $cart_summary['products'] = $cartProductsArr;
        $cart_summary['cartSummary'] = $cartSummaryArr;
        $cart_summary['cart_selected_billing_address'] = $BillingAddressDetail;
        $cart_summary['cart_selected_shipping_address'] = $ShippingddressDetail;
        $cart_summary['hasPhysicalProduct']= $cartHasPhysicalProduct;
        $cart_summary['isShippingSameAsBilling']= $cartObj->getShippingAddressSameAsBilling();
        $cart_summary['selected_billing_address_id']= $cartObj->getCartBillingAddress();
        $cart_summary['selected_shipping_address_id']= $cartObj->getCartShippingAddress();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$cart_summary,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function has_stock($keyValue)
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $stock = false;
        $cartObj=new cart($loggedUserId);
        foreach ($cartObj->getProducts($this->siteLangId) as $product) {
            if ($product['key']!=$keyValue) {
                continue;
            }
            if ($product['in_stock']) {
                $stock = true;
                break;
            }
        }
        die($this->json_encode_unicode(array('status'=>1,'in_stock'=>$stock,'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'],'unread_notifications'=>$this->user_details['unreadNotifications'], 'cart'=>$cart), JSON_FORCE_OBJECT));
    }

    public function shipping_summary()
    {
        $user_id = $this->getAppLoggedUserId();
        $cartObj = new Cart($user_id);
        $cart_products=$cartObj->getProducts($this->siteLangId);

        if (count($cart_products)==0) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Your_Cart_is_empty', $this->siteLangId));
        }

        $productSelectedShippingMethodsArr = $cartObj->getProductShippingMethod();
        $selectedShippingapi_id = $cartObj->getCartShippingApi();

        $manualShippingArt = array('Seller Shiping');
        $shippingMethods = $this->getShippingMethods($this->siteLangId);


        /* get user shipping address[ */
        $shippingAddressDetail = UserAddress::getUserAddresses($user_id, $this->siteLangId, 0, $cartObj->getCartShippingAddress());


        /* ] */
        foreach ($cart_products as $cartkey=>$cartval) {
            $cart_products[$cartkey]['pship_id']= 0;
            $shipBy=0;

            if ($cart_products[$cartkey]['psbs_user_id']) {
                $shipBy =$cart_products[$cartkey]['psbs_user_id'];
            }
            $shipping_options = Product::getProductShippingRates($cartval['product_id'], $this->siteLangId, $shippingAddressDetail['ua_country_id'], $shipBy);

            if ($shipping_options) {
                foreach ($shipping_options as &$shipping_option) {
                    $shipping_option['currency_pship_charges'] = CommonHelper::displayMoneyFormat($shipping_option['pship_charges'], true, false, false);
                    $shipping_option['currency_pship_additional_charges'] = CommonHelper::displayMoneyFormat($shipping_option['pship_additional_charges'], true, false, false);
                }
            }

            $free_shipping_options = Product::getProductFreeShippingAvailabilty($cartval['product_id'], $this->siteLangId, $shippingAddressDetail['ua_country_id'], $shipBy);

            $cart_products[$cartkey]['is_shipping_selected'] =  isset($productSelectedShippingMethodsArr['product'][$cartval['selprod_id']])?$productSelectedShippingMethodsArr['product'][$cartval['selprod_id']]['mshipapi_id']:false;
            if ($cart_products[$cartkey]['is_shipping_selected'] && $productSelectedShippingMethodsArr['product'][$cartval['selprod_id']]['mshipapi_id']== SHIPPINGMETHODS::SHIPSTATION_SHIPPING) {
                $cart_products[$cartkey]['selected_shipping_option']=$productSelectedShippingMethodsArr['product'][$cartval['selprod_id']];
            } elseif ($cart_products[$cartkey]['is_shipping_selected'] && $productSelectedShippingMethodsArr['product'][$cartval['selprod_id']]['mshipapi_id']== SHIPPINGMETHODS::MANUAL_SHIPPING) {
                $cart_products[$cartkey]['pship_id']=$productSelectedShippingMethodsArr['product'][$cartval['selprod_id']]['pship_id'];
            }
            $cart_products[$cartkey]['manual_shipping_rates']=$shipping_options;
            $cart_products[$cartkey]['shipping_free_availbilty']=$free_shipping_options;
            $cart_products[$cartkey]['image_url'] = CommonHelper::generateFullUrl('image', 'product', array($cartval['product_id'], "MEDIUM", $cartval['selprod_id'], 0, $this->siteLangId));

            $cart_products[$cartkey]['currency_theprice'] = CommonHelper::displayMoneyFormat($cartval['theprice'], true, false, false);
            $cart_products[$cartkey]['currency_tax'] = CommonHelper::displayMoneyFormat($cartval['tax'], true, false, false);
            $cart_products[$cartkey]['currency_commission'] = CommonHelper::displayMoneyFormat($cartval['commission'], true, false, false);
            $cart_products[$cartkey]['currency_volume_discount'] = CommonHelper::displayMoneyFormat($cartval['volume_discount'], true, false, false);
            $cart_products[$cartkey]['currency_shipping_cost'] = CommonHelper::displayMoneyFormat($cartval['shipping_cost'], true, false, false);
            $cart_products[$cartkey]['currency_total'] = CommonHelper::displayMoneyFormat($cartval['total'], true, false, false);
        }

        $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
        foreach ($cartSummary as $key => $value) {
            $cartSummaryArr[$key] = CommonHelper::displayMoneyFormat($value, true, false, false);
        }

        $selectedProductShippingMethods  = $cartObj->getProductShippingMethod();
        if ($selectedProductShippingMethods) {
            foreach ($selectedProductShippingMethods['product'] as &$selectedProductShippingMethod) {
                $selectedProductShippingMethod['currency_mshipapi_cost'] = CommonHelper::displayMoneyFormat($selectedProductShippingMethod['mshipapi_cost'], true, false, false);
            }
        }

        if ($productSelectedShippingMethodsArr) {
            foreach ($productSelectedShippingMethodsArr['product'] as &$productSelectedShippingMethodArr) {
                $productSelectedShippingMethodArr['currency_mshipapi_cost'] = CommonHelper::displayMoneyFormat($productSelectedShippingMethodArr['mshipapi_cost'], true, false, false);
            }
        }

        $cart_shipping_summary['productSelectedShippingMethodsArr'] = $productSelectedShippingMethodsArr;
        $cart_shipping_summary['shipStationCarrierList'] = $cartObj->shipStationCarrierList();
        $cart_shipping_summary['shippingMethods'] = $shippingMethods;
        $cart_shipping_summary['products'] = $cart_products;
        $cart_shipping_summary['cartSummary']= $cartSummaryArr;
        $cart_shipping_summary['shippingAddressDetail']= UserAddress::getUserAddresses($user_id, $this->siteLangId, 0, $cartObj->getCartShippingAddress());
        $cart_shipping_summary['selectedProductShippingMethod']= $selectedProductShippingMethods;

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$cart_shipping_summary,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function get_carrier_services_list()
    {
        $post = FatApp::getPostedData();
        //$post['product_key']="czo1OiJTUF80MCI7";
        //$post['carrier_id']="ups";
        if (empty($post['product_key']) || empty($post['carrier_id'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $product_key = md5($post['product_key']);
        $carrier_id = $post['carrier_id'];
        $user_id = $this->getAppLoggedUserId();
        $cartObj = new Cart($user_id);
        $carrierList = $cartObj->getCarrierShipmentServicesList($product_key, $carrier_id, $this->siteLangId);
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$carrierList,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function setup_shipping_method()
    {
        $post = FatApp::getPostedData();
        //$post = array('product_key'=>"czo1OiJTUF80MCI7","seller_product_id"=>40,"shipping_type"=>1,"shipping_location"=>212,"shipping_carrier"=>"","shipping_service"=>"");
        //$post = array('product_key'=>"czo1OiJTUF8xNiI7","seller_product_id"=>16,"shipping_type"=>1,"shipping_location"=>190,"shipping_carrier"=>"","shipping_service"=>"");
        //$post = array('product_key'=>"czo1OiJTUF8xNiI7","seller_product_id"=>16,"shipping_type"=>2,"shipping_location"=>5,"shipping_carrier"=>"ups","shipping_service"=>"ups_worldwide_saver-701.87");
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if (empty($post['product_key']) || empty($post['seller_product_id']) || empty($post['shipping_type'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if (($post['shipping_type']==1) && (empty($post['shipping_location']))) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if (($post['shipping_type']==2) && (empty($post['shipping_service']) || empty($post['shipping_carrier']))) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $user_id = $this->getAppLoggedUserId();
        $cartObj = new Cart($user_id);
        $cartProducts = $cartObj->getProducts($this->siteLangId);
        $productToShippingMethods = array();
        $product_key = $post['product_key'];
        $seller_product_id = $post['seller_product_id'];
        $shipping_type = $post['shipping_type'];
        $shipping_location = $post['shipping_location'];
        $shipping_carrier = $post['shipping_carrier'];
        $shipping_service = $post['shipping_service'];
        $cartProducts = $cartObj->getProducts($this->siteLangId);
        $cartProductArray = array();
        foreach ($cartProducts as $cartkey=>$cartval) {
            if ($cartval["key"]==$product_key) {
                $cartProductArray = $cartval;
                break;
            }
        }
        if (empty($cartProductArray)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $parent_product_id = $cartProductArray['product_id'];

        $shipBy=0;
        if ($cartProductArray['psbs_user_id']) {
            $shipBy = $cartProductArray['psbs_user_id'];
        }

        /* get user shipping address[ */
        $shippingAddressDetail = UserAddress::getUserAddresses($user_id, $this->siteLangId, 0, $cartObj->getCartShippingAddress());

        /* ] */
        $sn= 0;
        $json= array();
        if (!empty($cartProducts)) {
            $shipping_address = UserAddress::getUserAddresses($user_id, $this->siteLangId);
            //die($seller_product_id."#".$shippingAddressDetail['ua_country_id']);
            $shipping_options = Product::getProductShippingRates($parent_product_id, $this->siteLangId, $shippingAddressDetail['ua_country_id'], $shipBy);

            $free_shipping_options = Product::getProductFreeShippingAvailabilty($seller_product_id, $this->siteLangId, $shippingAddressDetail['ua_country_id'], $shipBy);
            $productKey = md5($product_key);


            /* get Product Data[ */
            $prodSrch = new ProductSearch();
            $prodSrch->setDefinedCriteria();
            $prodSrch->joinProductToCategory();
            $prodSrch->joinProductShippedBy();
            $prodSrch->joinProductFreeShipping();
            $prodSrch->doNotCalculateRecords();
            $prodSrch->doNotLimitRecords();
            $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);

            $prodSrch->addCondition('selprod_id', '=', $seller_product_id);
            $prodSrch->addMultipleFields(array('selprod_id','product_seller_id','psbs_user_id as shippedBySellerId'));
            $productRs = $prodSrch->getResultSet();
            $product = FatApp::getDb()->fetch($productRs);
            /* ] */

            if (isset($productKey) && ($shipping_type ==  ShippingCompanies::MANUAL_SHIPPING) &&  !empty($shipping_location)) {
                foreach ($shipping_options as $shipOption) {
                    //echo $shipOption['pship_id']."#".$shipping_location."<br/>";
                    if ($shipOption['pship_id']==$shipping_location) {
                        $productToShippingMethods['product'][$seller_product_id] = array(
                        'selprod_id'    =>    $product['selprod_id'],
                        'pship_id'    =>    $shipping_location,
                        'sduration_id'    =>    $shipOption['sduration_id'],
                        'sduration_name' => $shipOption['sduration_name'],
                        'sduration_from' => $shipOption['sduration_from'],
                        'sduration_to' => $shipOption['sduration_to'],
                        'sduration_days_or_weeks' => $shipOption['sduration_days_or_weeks'],
                        'mshipapi_id'    =>    $shipping_type,
                        'mshipcompany_id'    =>    $shipOption['scompanylang_scompany_id'],
                        'mshipcompany_name'    =>    $shipOption['scompany_name'],
                        'shipped_by_seller'    =>    Product::isShippedBySeller($cartval['selprod_user_id'], $product['product_seller_id'], $product['shippedBySellerId']),
                        'mshipapi_cost' =>  ($free_shipping_options == 0)? ($shipOption['pship_charges'] + ($shipOption['pship_additional_charges'] * ($cartval['quantity'] -1))) : 0 ,
                        );
                    }
                }
            } elseif (isset($productKey) && ($shipping_type ==  ShippingCompanies::SHIPSTATION_SHIPPING) && !empty($shipping_service)) {
                list($carrier_name, $carrier_price) = explode("-", $shipping_service);
                $productToShippingMethods['product'][$seller_product_id] = array(
                 'selprod_id'    =>    $cartval['selprod_id'],
                 'mshipapi_id'    =>    $shipping_type,
                 'mshipcompany_name'    =>    ($carrier_name),
                 'mshipapi_cost' =>  $carrier_price ,
                 'mshipapi_key' =>  $shipping_service ,
                 'mshipapi_label' =>  str_replace("_", " ", $shipping_service) ,
                 'shipped_by_seller'    =>    Product::isShippedBySeller($cartval['selprod_user_id'], $product['product_seller_id'], $product['shippedBySellerId']),
                );
            } else {
                FatUtility::dieJsonError(sprintf(Labels::getLabel('M_Shipping_Info_Required_for_%s', $this->siteLangId), htmlentities($cartval['product_name'])));
            }



            if (!empty($productToShippingMethods)) {
                if (!$cartObj->setProductShippingMethod($productToShippingMethods, true)) {
                    FatUtility::dieJsonError(Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart', $this->siteLangId));
                }
            } else {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart', $this->siteLangId));
            }
        }
        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("MSG_Shipping_Method_selected_successfully", $this->siteLangId), 'cart_count'=>$cartObj->countProducts())));
    }

    public function check_shipping_method_cart()
    {
        $user_id = $this->getAppLoggedUserId();
        $cartObj = new Cart($user_id);
        if (!$cartObj->isProductShippingMethodSet()) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart', $this->siteLangId));
        }
        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("MSG_Yes", $this->siteLangId), 'cart_count'=>$cartObj->countProducts())));
    }

    public function getShippingMethods($langId)
    {
        $srch = ShippingMethods::getListingObj($langId, array('shippingapi_id'));
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $shippingApis = FatApp::getDb()->fetchAllAssoc($rs);

        return $shippingApis;
    }

    public function shop_send_message()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        //$post = array('shop_id'=>"3","thread_subject"=>'My Message Subject Day 1',"message_text"=>'My message body will go here');
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $shop_id = $post['shop_id'];
        $shop = $this->getShopInfo($shop_id);
        if (!$shop) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $threadObj = new Thread();
        $threadDataToSave = array(
        'thread_subject'    =>    $post['thread_subject'],
        'thread_started_by' =>    $loggedUserId,
        'thread_start_date'    =>    date('Y-m-d H:i:s'),
        'thread_type'        =>    Thread::THREAD_TYPE_SHOP,
        'thread_record_id'    =>    $shop_id,
        );

        $threadObj->assignValues($threadDataToSave);

        if (!$threadObj->save()) {
            FatUtility::dieJsonError(Labels::getLabel($threadObj->getError(), $this->siteLangId));
        }
        $thread_id = $threadObj->getMainTableRecordId();

        $threadMsgDataToSave = array(
        'message_thread_id'    =>    $thread_id,
        'message_from'        =>    $loggedUserId,
        'message_to'        =>    $shop['shop_user_id'],
        'message_text'        =>    $post['message_text'],
        'message_date'        =>    date('Y-m-d H:i:s'),
        'message_is_unread'    =>    1,
        'message_deleted'    =>    0
        );
        if (!$message_id = $threadObj->addThreadMessages($threadMsgDataToSave)) {
            FatUtility::dieJsonError(Labels::getLabel($threadObj->getError(), $this->siteLangId));
        }

        if ($message_id) {
            $emailObj = new EmailHandler();
            $emailObj->SendMessageNotification($message_id, $this->siteLangId);
        }
        die($this->json_encode_unicode(array('status'=>1, 'msg'=>Labels::getLabel("MSG_Message_Submitted_Successfully", $this->siteLangId))));
    }

    public function get_shops()
    {
        $db = FatApp::getDb();
        $loggedUserId = $this->getAppLoggedUserId();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }
        /* SubQuery, Shop have products[ */
        $prodShopSrch = new ProductSearch($this->siteLangId);
        $prodShopSrch->setDefinedCriteria(1);
        $prodShopSrch->joinProductToCategory();
        $prodShopSrch->doNotCalculateRecords();
        $prodShopSrch->doNotLimitRecords();
        $prodShopSrch->joinSellerSubscription($this->siteLangId, true);
        $prodShopSrch->addSubscriptionValidCondition();
        $prodShopSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodShopSrch->addGroupBy('shop_id');

        $prodShopSrch->addMultipleFields(array('shop_id'));
        /* ] */

        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->joinSellerSubscription();
        $srch->joinTable('(' . $prodShopSrch->getQuery() . ')', 'INNER JOIN', 'stemp.shop_id = s.shop_id', 'stemp');

        $collection_id =  FatApp::getPostedData('collection_id', FatUtility::VAR_INT, 0);

        if ($collection_id) {
            $srch->joinTable(Collections::DB_TBL_COLLECTION_TO_SHOPS, 'INNER JOIN', 'cts.ctps_shop_id = s.shop_id', 'cts');
            $srch->addCondition('ctps_collection_id', '=', $collection_id);
        }

        /* Sub query to find out that logged user have marked shops as favorite or not[ */
        $favSrchObj = new UserFavoriteShopSearch();
        $favSrchObj->doNotCalculateRecords();
        $favSrchObj->doNotLimitRecords();
        $favSrchObj->addMultipleFields(array('ufs_shop_id','ufs_id'));
        $favSrchObj->addCondition('ufs_user_id', '=', $loggedUserId);
        $srch->joinTable('('. $favSrchObj->getQuery() . ')', 'LEFT OUTER JOIN', 'ufs_shop_id = s.shop_id', 'ufs');
        /* ] */

        $srch->addMultipleFields(
            array( 's.shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_country_l.country_name as country_name', 'shop_state_l.state_name as state_name', 'shop_city',
            'IFNULL(ufs.ufs_id, 0) as is_favorite' )
        );

        $featured = FatApp::getPostedData('featured', null, '');
        if (!empty($featured)) {
            $srch->addCondition('shop_featured', '=', $featured);
        }

        $favorite = FatApp::getPostedData('favorite', FatUtility::VAR_INT, 0);
        if ($favorite > 0) {
            $srch->addHaving('is_favorite', '>', 0);
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addOrder('shop_created_on');

        $shopRs = $srch->getResultSet();
        $allShops = $db->fetchAll($shopRs);


        $totalProdCountToDisplay = 4;
        $productCustomSrchObj = new ProductSearch($this->siteLangId);
        $productCustomSrchObj->joinProductToCategory($this->siteLangId);
        $productCustomSrchObj->setDefinedCriteria();
        $productCustomSrchObj->joinSellerSubscription($this->siteLangId, true);
        $productCustomSrchObj->addSubscriptionValidCondition();
        $productCustomSrchObj->joinFavouriteProducts($loggedUserId);
        $productCustomSrchObj->joinProductRating();
        $productCustomSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $productCustomSrchObj->addGroupBy('product_id');


        /* $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type','=',SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating","count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $productCustomSrchObj->joinTable( '(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating' ); */

        $productCustomSrchObj->addMultipleFields(
            array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
            'theprice', 'selprod_price','selprod_stock', 'selprod_condition','prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','ifnull(sq_sprating.prod_rating,0) prod_rating ','ifnull(sq_sprating.totReviews,0) totReviews','selprod_sold_count','ufp_id','IF(ufp_id > 0, 1, 0) as isfavorite','selprod_min_order_qty')
        );


        $productCustomSrchObj->setPageSize($totalProdCountToDisplay);
        $shopsArr = array();
        $cnt=0;
        foreach ($allShops as $val) {
            $prodSrch = clone $productCustomSrchObj;
            $prodSrch->addShopIdCondition($val['shop_id']);
            $prodSrch->addGroupBy('product_id');
            //$prodSrch->addMultipleFields( array( 'selprod_id', 'product_id', 'shop_id','IFNULL(shop_name, shop_identifier) as shop_name',
            //'IFNULL(product_name, product_identifier) as product_name',
            //'IF(selprod_stock > 0, 1, 0) AS in_stock') );

            $prodRs = $prodSrch->getResultSet();
            $shopsArr[$cnt] = $val;
            $shopProducts = $db->fetchAll($prodRs);
            foreach ($shopProducts as &$shopProduct) {
                $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($shopProduct['product_id'], "MEDIUM", $shopProduct['selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $shopProduct['discounted_text'] =  CommonHelper::showProductDiscountedText($shopProduct, $this->siteLangId);
                $shopProduct['product_image'] =  $mainImgUrl;
                $shopProduct['currency_selprod_price'] = CommonHelper::displayMoneyFormat($shopProduct['selprod_price'], true, false, false);
                $shopProduct['currency_theprice'] = CommonHelper::displayMoneyFormat($shopProduct['theprice'], true, false, false);
            }
            $shopsArr[$cnt]['products'] = $shopProducts;
            $shopsArr[$cnt]['totalProducts'] = $prodSrch->recordCount();
            $shopsArr[$cnt]['shopRating'] = SelProdRating::getSellerRating($val['shop_user_id']);
            $shopsArr[$cnt]['shopTotalReviews'] = SelProdReview::getSellerTotalReviews($val['shop_user_id']);
            $shopsArr[$cnt]['logo'] = CommonHelper::generateFullUrl('image', 'shopLogo', array($val['shop_id'], $this->siteLangId));
            $shopsArr[$cnt]['banner'] = CommonHelper::generateFullUrl('image', 'shopBanner', array($val['shop_id'], $this->siteLangId));
            //commonhelper::printarray($shopProducts);
            //die();
            $cnt++;
        }
        $api_shops_elements = array('shops'=>$shopsArr,'total_pages'=>$srch->pages(),'page'=>$page,'total_records'=>$srch->recordCount());
        //commonhelper::printarray($api_shops_elements);
        //die();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_shops_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function get_brands()
    {
        $db = FatApp::getDb();
        $loggedUserId = $this->getAppLoggedUserId();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }
        /* SubQuery, Shop have products[ */
        $prodBrandSrch = new ProductSearch($this->siteLangId);
        $prodBrandSrch->setDefinedCriteria(1);
        $prodBrandSrch->joinProductToCategory();
        $prodBrandSrch->doNotCalculateRecords();
        $prodBrandSrch->doNotLimitRecords();
        $prodBrandSrch->joinSellerSubscription($this->siteLangId, true);
        $prodBrandSrch->addSubscriptionValidCondition();
        $prodBrandSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodBrandSrch->addGroupBy('selprod_id');

        $prodBrandSrch->addMultipleFields(array('product_brand_id'));
        $rs = $prodBrandSrch->getResultSet();
        $productRows = FatApp::getDb()->fetchAll($rs);
        $brandMainRootArr = array_unique(array_column($productRows, 'product_brand_id'));
        /* ] */


        $srch = Brand::getListingObj($this->siteLangId, array( 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'), true);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('brand_name', 'asc');
        $srch->addCondition('brand_id', 'in', $brandMainRootArr);


        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $brandRs = $srch->getResultSet();
        $allBrands = $db->fetchAll($brandRs, 'brand_id');

        $totalProdCountToDisplay = 4;
        /*$prodSrchObj = new ProductSearch( $this->siteLangId );
        $prodSrchObj->setDefinedCriteria( 0 );
        $prodSrchObj->setPageSize($totalProdCountToDisplay);
        $prodSrchObj->addCondition('selprod_deleted','=',applicationConstants::NO);*/
        $productCustomSrchObj = new ProductSearch($this->siteLangId);
        $productCustomSrchObj->joinProductToCategory($this->siteLangId);
        $productCustomSrchObj->setDefinedCriteria();
        $productCustomSrchObj->joinSellerSubscription($this->siteLangId, true);
        $productCustomSrchObj->addSubscriptionValidCondition();
        $productCustomSrchObj->joinFavouriteProducts($loggedUserId);
        $productCustomSrchObj->joinProductRating();
        $productCustomSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $productCustomSrchObj->addGroupBy('selprod_id');


        /* $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type','=',SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating","count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $productCustomSrchObj->joinTable( '(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating' ); */

        $productCustomSrchObj->addMultipleFields(
            array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
            'theprice', 'selprod_price','selprod_stock', 'selprod_condition','prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','ifnull(sq_sprating.prod_rating,0) prod_rating ','ifnull(sq_sprating.totReviews,0) totReviews','selprod_sold_count','ufp_id','IF(ufp_id > 0, 1, 0) as isfavorite','selprod_min_order_qty')
        );


        $productCustomSrchObj->setPageSize($totalProdCountToDisplay);
        $brandsArr = array();
        $cnt=0;
        foreach ($allBrands as $val) {
            $prodSrch = clone $productCustomSrchObj;
            $prodSrch->addBrandCondition($val['brand_id']);
            $prodSrch->addGroupBy('selprod_id');
            $prodRs = $prodSrch->getResultSet();
            $brandsArr[$cnt] = $val;
            $brandProducts = $db->fetchAll($prodRs);

            foreach ($brandProducts as &$brandProduct) {
                $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($brandProduct['product_id'], "MEDIUM", $brandProduct['selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $brandProduct['discounted_text'] =  CommonHelper::showProductDiscountedText($brandProduct, $this->siteLangId);
                $brandProduct['product_image'] =  $mainImgUrl;
                $brandProduct['currency_selprod_price'] = CommonHelper::displayMoneyFormat($brandProduct['selprod_price'], true, false, false);
                $brandProduct['currency_theprice'] = CommonHelper::displayMoneyFormat($brandProduct['theprice'], true, false, false);
            }
            $brandsArr[$cnt]['products'] = $brandProducts;
            $brandsArr[$cnt]['totalProducts'] = $prodSrch->recordCount();
            $cnt++;
            /*$prodRs = $prodSrch->getResultSet();
            $allBrands[$val['brand_id']]['products'] = $db->fetchAll( $prodRs);
            $allBrands[$val['brand_id']]['totalProducts'] = $prodSrch->recordCount();*/
        }
        $api_brands_elements = array('brands'=>$brandsArr,'total_pages'=>$srch->pages(),'page'=>$page,'total_records'=>$srch->recordCount());
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_brands_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function shop_detail($shop_id)
    {
        $db = FatApp::getDb();
        $loggedUserId = $this->getAppLoggedUserId();

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
        $favSrchObj->addCondition('ufs_user_id', '=', $loggedUserId);
        $favSrchObj->addCondition('ufs_shop_id', '=', $shop_id);
        $srch->joinTable('('. $favSrchObj->getQuery() . ')', 'LEFT OUTER JOIN', 'ufs_shop_id = shop_id', 'ufs');
        /* ] */

        $srch->addMultipleFields(
            array( 'shop_id','tu.user_name','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city',
            'IFNULL(ufs.ufs_id, 0) as is_favorite' )
        );
        $srch->addCondition('shop_id', '=', $shop_id);
        $srch->addMultipleFields(array('shop_payment_policy', 'shop_delivery_policy','shop_refund_policy','shop_additional_info','shop_seller_info'));
        //echo $srch->getQuery();
        $shopRs = $srch->getResultSet();
        $shop = $db->fetch($shopRs);
        if (!$shop) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $shop['logo']=CommonHelper::generateFullUrl('image', 'shopLogo', array($shop['shop_id'], $this->siteLangId));
        $shop['banner']=CommonHelper::generateFullUrl('image', 'shopBanner', array($shop['shop_id'], $this->siteLangId));
        $prodSrchObj = new ProductSearch($this->siteLangId);
        $prodSrchObj->setDefinedCriteria();
        $prodSrchObj->joinSellerSubscription();
        $prodSrchObj->addSubscriptionValidCondition();
        $prodSrchObj->addShopIdCondition($shop_id);
        $prodSrchObj->joinProductToCategory();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->doNotLimitRecords();
        $prodSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrchObj->addGroupBy('product_id');

        /* Categories Data[ */
        $catSrch = clone $prodSrchObj;
        $catSrch->addGroupBy('prodcat_id');

        $productCatObj = new ProductCategory;
        $productCategories =  $productCatObj->getCategoriesForSelectBox($this->siteLangId);

        $categoriesArr = ProductCategory::getProdCatParentChildWiseArr($this->siteLangId, 0, false, false, false, $catSrch, true);

        usort(
            $categoriesArr,
            function ($a, $b) {
                return $a['prodcat_code'] - $b['prodcat_code'];
            }
        );
        /* ] */

        /* Brand Filters Data[ */
        $brandSrch = clone $prodSrchObj;
        $brandSrch->removGroupBy('selprod_id');
        $brandSrch->addGroupBy('brand_id');
        $brandSrch->addOrder('brand_name');
        $brandSrch->addMultipleFields(array( 'brand_id', 'ifNull(brand_name,brand_identifier) as brand_name'));
        /* if needs to show product counts under brands[ */
        //$brandSrch->addFld('count(selprod_id) as totalProducts');
        /* ] */
        $brandRs = $brandSrch->getResultSet();
        $brandsArr = $db->fetchAll($brandRs);

        /* ] */

        /* Condition filters data[ */
        $conditionSrch = clone $prodSrchObj;
        $conditionSrch->addGroupBy('selprod_condition');
        $conditionSrch->removGroupBy('selprod_id');
        $conditionSrch->addOrder('selprod_condition');
        $conditionSrch->addMultipleFields(array('selprod_condition'));
        //echo $conditionSrch->getQuery(); die();
        /* if needs to show product counts under any condition[ */
        //$conditionSrch->addFld('count(selprod_condition) as totalProducts');
        /* ] */
        $conditionRs = $conditionSrch->getResultSet();
        $conditionsArr = $db->fetchAll($conditionRs);
        /* ] */

        /* Price Filters[ */
        $priceSrch = clone $prodSrchObj;
        $priceSrch->addMultipleFields(array('MIN(theprice) as minPrice', 'MAX(theprice) as maxPrice'));
        $qry = $priceSrch->getQuery();
        $qry .= ' having minPrice IS NOT NULL AND maxPrice IS NOT NULL';
        //echo $qry; die();
        //$priceRs = $priceSrch->getResultSet();
        $priceRs = $db->query($qry);
        $priceArr = $db->fetch($priceRs);
        //commonhelper::printarray($priceArr);
        $priceArrCurrency = array();
        if (!empty($priceArr)) {
            /* $priceArrCurrency = array_map( function( $item ){ return CommonHelper::displayMoneyFormat( $item, true, false ,false ); } , $priceArr );
            $priceArrCurrency['minPrice']=floor($priceArrCurrency['minPrice']);
            $priceArrCurrency['maxPrice']=ceil($priceArrCurrency['maxPrice']); */
            $priceArrCurrency['minPrice']= floor(CommonHelper::displayMoneyFormat($priceArr['minPrice'], false, false, false));
            $priceArrCurrency['maxPrice']= ceil(CommonHelper::displayMoneyFormat($priceArr['maxPrice'], false, false, false));
        }
        //commonhelper::printarray($priceArrCurrency);
        //die();
        /* ] */

        $productFiltersArr = array(
        'categoriesArr'        =>    $categoriesArr,
        'productCategories'        =>    $productCategories,
        'shopCatFilters'        =>    true,
        'brandsArr'            =>    $brandsArr,
        'CategoryCheckedArr' =>array(),
        'conditionsArr'        =>    $conditionsArr,
        'priceArr'            =>    $priceArr,
        'priceArrCurrency'    =>    $priceArrCurrency,
        'siteLangId'        =>    $this->siteLangId
        );

        $srchCat = Shop::getUserShopProdCategoriesObj($shop['shop_user_id'], $this->siteLangId, $shop['shop_id'], 0);
        $srchCat->doNotCalculateRecords();
        $srchCat->doNotLimitRecords();
        $rs = $srchCat->getResultSet();
        $shopCategories = $db->fetchAll($rs, 'prodcat_id');

        $api_shop_detail_elements['shop'] = $shop;
        $api_shop_detail_elements['shopRating'] = SelProdRating::getSellerRating($shop['shop_user_id']);
        $api_shop_detail_elements['shopTotalReviews'] = SelProdReview::getSellerTotalReviews($shop['shop_user_id']);
        $api_shop_detail_elements['productFiltersArr'] = $productFiltersArr;
        $api_shop_detail_elements['shopCategories'] = $shopCategories;
        /*commonhelper::printarray($api_shop_detail_elements);
        die();*/
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_shop_detail_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function shop_reviews($shop_id)
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $shop_id = FatUtility::int($shop_id);
        $sellerId = Shop::getAttributesById($shop_id, 'shop_user_id', false);
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }
        if ($shop_id <= 0) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $db = FatApp::getDb();
        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(
            array( 'shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city' )
        );
        $srch->addCondition('shop_id', '=', $shop_id);
        $shopRs = $srch->getResultSet();
        $shop = $db->fetch($shopRs);
        if (!$shop) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        /*$selProdRatingSrch = SelProdRating::getSearchObj();
        $selProdRatingSrch->doNotCalculateRecords();
        $selProdRatingSrch->addMultipleFields(array('sprating_spreview_id','round(avg(sprating_rating),2) seller_rating'));
        $selProdRatingSrch->addCondition('sprating_rating_type','in',array(SelProdRating::TYPE_SELLER_SHIPPING_QUALITY , SelProdRating::TYPE_SELLER_STOCK_AVAILABILITY , SelProdRating::TYPE_SELLER_PACKAGING_QUALITY));
        $selProdRatingSrch->addGroupBy('sprating_spreview_id');
        $spratingQuery = $selProdRatingSrch->getQuery();


        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinTable("($spratingQuery)",'left join','spr.spreview_id = selRating.sprating_spreview_id','selRating');
        $selProdReviewObj->addGroupBy('spr.spreview_seller_user_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addCondition('spreview_seller_user_id','=',$shop['shop_user_id']);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_seller_user_id','count(*) as totReviews',"ROUND(AVG(seller_rating),2) as avg_seller_rating",'sum(if(round(seller_rating)=1,1,0)) rated_1','sum(if(round(seller_rating)=2,1,0)) rated_2','sum(if(round(seller_rating)=3,1,0)) rated_3','sum(if(round(seller_rating)=4,1,0)) rated_4','sum(if(round(seller_rating)=5,1,0)) rated_5'));
        $reviews = FatApp::getDb()->fetch($selProdReviewObj->getResultSet());*/

        $selProdRatingSrch = SelProdRating::getSearchObj();
        $selProdRatingSrch->doNotCalculateRecords();
        $selProdRatingSrch->addMultipleFields(array('sprating_spreview_id','round(avg(sprating_rating),2) seller_rating'));
        $selProdRatingSrch->addCondition('sprating_rating_type', 'in', array(SelProdRating::TYPE_SELLER_SHIPPING_QUALITY , SelProdRating::TYPE_SELLER_STOCK_AVAILABILITY , SelProdRating::TYPE_SELLER_PACKAGING_QUALITY));
        $selProdRatingSrch->addGroupBy('sprating_spreview_id');
        $spratingQuery = $selProdRatingSrch->getQuery();

        $srch = new SelProdReviewSearch();
        $srch->joinTable("($spratingQuery)", 'left join', 'spr.spreview_id = selRating.sprating_spreview_id', 'selRating');
        $srch->joinUser();
        $srch->joinSelProdReviewHelpful();

        $srch->addCondition('spr.spreview_seller_user_id', '=', $sellerId);
        $srch->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $srch->addMultipleFields(array('spreview_id','spreview_seller_user_id',"ROUND(AVG(seller_rating),2) as shop_rating" ,'spreview_title','spreview_description','spreview_posted_on','spreview_postedby_user_id','user_name','group_concat(case when sprh_helpful = 1 then concat(sprh_user_id,"~",1) else concat(sprh_user_id,"~",0) end ) usersMarked' ,'sum(if(sprh_helpful = 1 , 1 ,0)) as helpful' ,'sum(if(sprh_helpful = 0 , 1 ,0)) as notHelpful','count(sprh_spreview_id) as countUsersMarked' ));
        $srch->addGroupBy('spr.spreview_id');

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        /* switch ($orderBy){
        case 'most_helpful':
        $srch->addOrder('helpful','desc');
        break;
        default:
        $srch->addOrder('spr.spreview_posted_on','desc');
        break;
        } */
        //die($srch->getquery());
        $records = FatApp::getDb()->fetchAll($srch->getResultSet());
        //$this->set('reviewsList',$records);

        $api_shop_reviews_elements = array('shop'=>$shop,'reviews'=>$records,'total_pages'=>$srch->pages(),'total_records'=>$srch->recordCount());
        //CommonHelper::printArray($api_shop_reviews_elements);
        //die();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_shop_reviews_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function countries()
    {
        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->siteLangId);
        foreach ($countriesArr as $key => $val) {
            $arr_country[]=array("id"=>$key,'name'=>$val);
        }
        die($this->json_encode_unicode(array('status'=>1, 'countries'=>$arr_country)));
    }

    public function get_states($countryId)
    {
        $countryId = FatUtility::int($countryId);
        $stateObj = new States();
        $statesArr = $stateObj->getStatesByCountryId($countryId, $this->siteLangId);
        $arr_states = [];
        foreach ($statesArr as $key => $val) {
            $arr_states[]=array("id"=>$key,'name'=>$val);
        }
        die($this->json_encode_unicode(array('status'=>1, 'states'=>$arr_states)));
    }

    public function bank_info()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $userObj = new User($loggedUserId);
        $bankinfo = $userObj->getUserBankInfo();

        $defaultCurrencyId = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyRow = Currency::getAttributesById($defaultCurrencyId);
        $defaultCurrencySymbol = !empty($currencyRow['currency_symbol_left'])?$currencyRow['currency_symbol_left']:$currencyRow['currency_symbol_right'];

        $userWalletBalance = User::getUserBalance($loggedUserId);

        die($this->json_encode_unicode(array('status'=>1,'defaultCurrencySymbol'=>$defaultCurrencySymbol,'userWalletBalance'=>$userWalletBalance,'bank_details'=> $bankinfo)));
    }

    public function update_bank_info()
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $userObj = new User($userId);
        if (!$userObj->updateBankInfo($post)) {
            FatUtility::dieJsonError(Labels::getLabel($userObj->getError(), $this->siteLangId));
        }
        die($this->json_encode_unicode(array('status'=>1,'msg'=>  Labels::getLabel('MSG_Setup_successful', $this->siteLangId))));
    }

    public function credits()
    {
        $userId = $this->getAppLoggedUserId();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }
        $debit_credit_type = FatApp::getPostedData('debit_credit_type', FatUtility::VAR_INT, -1);
        $balSrch = Transactions::getSearchObject();
        $balSrch->doNotCalculateRecords();
        $balSrch->doNotLimitRecords();
        $balSrch->addMultipleFields(array('utxn.*',"utxn_credit - utxn_debit as bal"));
        $balSrch->addCondition('utxn_user_id', '=', $userId);
        $balSrch->addCondition('utxn_status', '=', applicationConstants::ACTIVE);
        $qryUserPointsBalance = $balSrch->getQuery();


        $srch = Transactions::getSearchObject();
        $srch->joinTable('(' . $qryUserPointsBalance . ')', 'JOIN', 'tqupb.utxn_id <= utxn.utxn_id', 'tqupb');
        $srch->addMultipleFields(array('utxn.*',"SUM(tqupb.bal) balance"));
        $srch->addCondition('utxn.utxn_user_id', '=', $userId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addGroupBy('utxn.utxn_id');

        $dateOrder = FatApp::getPostedData('date_order', FatUtility::VAR_STRING, "DESC");
        $srch->addOrder('utxn.utxn_date', $dateOrder);
        $srch->addOrder('utxn_id', 'DESC');

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cond = $srch->addCondition('utxn.utxn_order_id', 'like', '%'.$keyword.'%');
            $cond->attachCondition('utxn.utxn_op_id', 'like', '%'.$keyword.'%', 'OR');
            $cond->attachCondition('utxn.utxn_comments', 'like', '%'.$keyword.'%', 'OR');
            $cond->attachCondition('concat("TN-" ,lpad( utxn.`utxn_id`,7,0))', 'like', '%'.$keyword.'%', 'OR', true);
        }

        $fromDate = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        if (!empty($fromDate)) {
            $cond = $srch->addCondition('utxn.utxn_date', '>=', $fromDate);
        }

        $toDate = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
        if (!empty($toDate)) {
            $cond = $srch->addCondition('cast( utxn.`utxn_date` as date)', '<=', $toDate, 'and', true);
        }

        if ($debit_credit_type > 0) {
            switch ($debit_credit_type) {
                case Transactions::CREDIT_TYPE:
                    $srch->addCondition('utxn.utxn_credit', '>', '0');
                    $srch->addCondition('utxn.utxn_debit', '=', '0');
                    break;

                case Transactions::DEBIT_TYPE:
                    $srch->addCondition('utxn.utxn_debit', '>', '0');
                    $srch->addCondition('utxn.utxn_credit', '=', '0');
                    break;
            }
        }
        $records = array();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        if ($records) {
            foreach ($records as &$record) {
                $record['currency_utxn_credit'] = CommonHelper::displayMoneyFormat($record['utxn_credit'], true, false, false);
                $record['currency_utxn_debit'] = CommonHelper::displayMoneyFormat($record['utxn_debit'], true, false, false);
                $record['currency_balance'] = CommonHelper::displayMoneyFormat($record['balance'], true, false, false);
            }
        }

        $userBalance = CommonHelper::displayMoneyFormat(User::getUserBalance($userId), true, false, false);
        $api_credits_elements = array('records'=>$records,'total_pages'=>$srch->pages(),'total_records'=>$srch->recordCount(),'statusArr'=>Transactions::getStatusArr($this->siteLangId),'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'userBalance'=>$userBalance);
        //commonhelper::printarray($api_credits_elements);
        //die();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=> $api_credits_elements)));
    }

    public function reward_points()
    {
        $userId = $this->getAppLoggedUserId();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }
        $srch = new UserRewardSearch;
        $srch->joinUser();
        $srch->addCondition('urp.urp_user_id', '=', $userId);
        $srch->addOrder('urp.urp_date_added', 'DESC');
        $srch->addOrder('urp.urp_id', 'DESC');
        $srch->addMultipleFields(array('urp.*','uc.credential_username'));
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $totalRewardPoints = UserRewardBreakup::rewardPointBalance($userId);
        $totalRewardPointsCurrency = CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($totalRewardPoints), true, false, false);
        $api_reward_points_elements = array('records'=>$records,'total_pages'=>$srch->pages(),'total_records'=>$srch->recordCount(),'totalRewardPoints'=>$totalRewardPoints,'totalRewardPointsCurrency'=>$totalRewardPointsCurrency);
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=> $api_reward_points_elements)));
    }

    public function share_earn_url()
    {
        $userId = $this->getAppLoggedUserId();
        if (!FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE")) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_This_module_is_not_enabled', $this->siteLangId));
        }
        $userObj = new User($userId);
        $userInfo = $userObj->getUserInfo(array('user_referral_code'), true, true);
        if (empty($userInfo['user_referral_code'])) {
            FatUtility::dieJsonError(Labels::getLabel('Msg_Referral_Code_is_empty', $this->siteLangId));
        }

        $referralTrackingUrl = CommonHelper::referralTrackingUrl($userInfo['user_referral_code']);
        die($this->json_encode_unicode(array('status'=>1,'data'=> $referralTrackingUrl)));
    }

    public function offers()
    {
        $userId = $this->getAppLoggedUserId();
        $offers = DiscountCoupons::getUserCoupons($userId, $this->siteLangId);
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=> $offers)));
    }

    public function create_wish_list()
    {
        $userId = $this->getAppLoggedUserId();
        if (empty($post['list_name'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $wListObj = new UserWishList();
        $data_to_save_arr = $post;
        $data_to_save_arr['uwlist_added_on'] = date('Y-m-d H:i:s');
        $data_to_save_arr['uwlist_user_id'] = $userId;
        $data_to_save_arr['uwlist_title'] = $post['list_name'];
        $wListObj->assignValues($data_to_save_arr);
        /* create new List[ */
        if (!$wListObj->save()) {
            FatUtility::dieJsonError($wListObj->getError());
        }
        $uwlp_uwlist_id = $wListObj->getMainTableRecordId();
        /* ] */
        $successMsg = Labels::getLabel('LBL_WishList_Created_Successfully', $this->siteLangId);
        die($this->json_encode_unicode(array('status'=>1,'msh'=> $successMsg)));
    }

    public function delete_wishlist()
    {
        $post = FatApp::getPostedData();
        $list_id = FatUtility::int($post['list_id']);
        if (empty($post['list_id'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $loggedUserId = $this->getAppLoggedUserId();
        $srch = UserWishList::getSearchObject($loggedUserId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('uwlist_id', '=', $list_id);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!$row) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $obj = new UserWishList();
        $obj->deleteWishList($row['uwlist_id']);
        die($this->json_encode_unicode(array('status'=>1,'msh'=> Labels::getLabel('LBL_Wishlist_deleted_successfully', $this->siteLangId))));
    }

    public function change_email()
    {
        $loggedUserId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        if (empty($post['new_email']) || empty($post['conf_new_email']) || empty($post['current_password'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $new_email = $post['new_email'];
        $conf_new_email = $post['conf_new_email'];
        $current_password = $post['current_password'];
        if ($new_email != $conf_new_email) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_New_email_confirm_email_does_not_match', $this->siteLangId));
        }

        $userObj = new User($loggedUserId);
        $srch = $userObj->getUserSearchObj(array('user_id','credential_password','credential_email','user_name'));
        $rs = $srch->getResultSet();
        if (!$rs) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $data = FatApp::getDb()->fetch($rs, 'user_id');
        if ($data === false) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        if ($data['credential_password'] != UserAuthentication::encryptPassword($current_password)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_YOUR_CURRENT_PASSWORD_MIS_MATCHED', $this->siteLangId));
        }

        $arr = array(
        'user_name' => $data['user_name'],
        'user_email' => $data['credential_email'],
        'user_id' => $data['user_id'],
        'user_new_email' => $new_email
        );

        if (!$this->userEmailVerification($userObj, $arr)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_ERROR_IN_SENDING_VERFICATION_EMAIL', $this->siteLangId));
        }

        $res=array('status'=>1,'msg'=>Labels::getLabel('MSG_CHANGE_EMAIL_REQUEST_SENT_SUCCESSFULLY', $this->siteLangId));
        die($this->json_encode_unicode($res));
    }

    public function buyer_orders()
    {
        $user_id = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinSellerProducts();
        $srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->joinPaymentMethod();
        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(
            array('order_id', 'order_user_id', 'order_date_added', 'order_net_amount', 'op_invoice_number',
            'totCombinedOrders as totOrders', 'op_selprod_title', 'op_product_name', 'op_id','op_other_charges','op_unit_price',
            'op_qty', 'op_selprod_options', 'op_brand_name', 'op_shop_name', 'op_status_id', 'op_product_type', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name','order_pmethod_id','order_status','pmethod_name','op_selprod_id','selprod_product_id')
        );

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');
        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")))) {
            $srch->addStatusCondition($op_status_id);
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        }

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if (!empty($priceFrom)) {
            $srch->addHaving('totOrders', '=', '1');
            $srch->addMinPriceCondition($priceFrom);
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addHaving('totOrders', '=', '1');
            $srch->addMaxPriceCondition($priceTo);
        }

        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $eligible_for_feedback = 0;
            $eligible_for_cancellation = 0;
            $eligible_for_return_refund = 0;

            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
            $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($order['selprod_product_id'], "MEDIUM", $order['op_selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
            $order['product_image'] =  $mainImgUrl;
            if (in_array($order["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses()) && FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                $eligible_for_feedback = 1;
            }
            if (in_array($order["op_status_id"], Orders::getBuyerAllowedOrderCancellationStatuses()) && ($order["op_product_type"] != Product::PRODUCT_TYPE_DIGITAL)) {
                $eligible_for_cancellation = 1;
            }
            if ($order["op_product_type"] == Product::PRODUCT_TYPE_DIGITAL) {
                $getBuyerAllowedOrderReturnStatuses = (array)Orders::getBuyerAllowedOrderReturnStatuses(true);
            } else {
                $getBuyerAllowedOrderReturnStatuses = (array)Orders::getBuyerAllowedOrderReturnStatuses();
            }
            if (in_array($order["op_status_id"], $getBuyerAllowedOrderReturnStatuses)) {
                $eligible_for_return_refund = 1;
            }
            $order['eligible_for_feedback'] =  $eligible_for_feedback;
            $order['eligible_for_cancellation'] =  $eligible_for_cancellation;
            $order['eligible_for_return_refund'] =  $eligible_for_return_refund;

            $order['currency_op_other_charges'] = CommonHelper::displayMoneyFormat($order['op_other_charges'], true, false, false);
            $order['currency_op_unit_price'] = CommonHelper::displayMoneyFormat($order['op_unit_price'], true, false, false);
            $order['currency_op_order_amount'] = CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order), true, false, false);
        }



        $api_orders_elements = array('orders'=>$orders,'total_pages'=>$srch->pages(),'page'=>$page,'total_records'=>$srch->recordCount());
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_orders_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function view_buyer_order($orderId, $opId = 0)
    {
        if (!$orderId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $opId = FatUtility::int($opId);
        $primaryOrderDisplay = false;

        $orderObj = new Orders();
        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();
        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
        $userId = $this->getAppLoggedUserId();

        $orderDetail = $orderObj->getOrderById($orderId, $this->siteLangId);
        if (!$orderDetail || ($orderDetail && $orderDetail['order_user_id'] != $userId)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $orderDetail['charges'] = $orderObj->getOrderProductChargesByOrderId($orderDetail['order_id']);

        $orderDetail['currency_order_net_amount'] = CommonHelper::displayMoneyFormat($orderDetail['order_net_amount'], true, false, false);
        $orderDetail['currency_order_wallet_amount_charge'] = CommonHelper::displayMoneyFormat($orderDetail['order_wallet_amount_charge'], true, false, false);
        $orderDetail['currency_order_tax_charged'] = CommonHelper::displayMoneyFormat($orderDetail['order_tax_charged'], true, false, false);
        $orderDetail['currency_order_site_commission'] = CommonHelper::displayMoneyFormat($orderDetail['order_site_commission'], true, false, false);
        $orderDetail['currency_order_discount_value'] = CommonHelper::displayMoneyFormat($orderDetail['order_discount_value'], true, false, false);
        $orderDetail['currency_order_discount_total'] = CommonHelper::displayMoneyFormat($orderDetail['order_discount_total'], true, false, false);
        $orderDetail['currency_order_volume_discount_total'] = CommonHelper::displayMoneyFormat($orderDetail['order_volume_discount_total'], true, false, false);
        $orderDetail['currency_order_reward_point_value'] = CommonHelper::displayMoneyFormat($orderDetail['order_reward_point_value'], true, false, false);

        //        commonhelper::printarray($orderDetail['charges']);
        //        die();
        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        //$srch->joinShippingUsers();
        $srch->addOrderProductCharges();
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('order_id', '=', $orderId);

        if ($opId > 0) {
            $srch->addCondition('op_id', '=', $opId);
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
            $primaryOrderDisplay = true;
        }

        $rs = $srch->getResultSet();

        $childOrderDetail = FatApp::getDb()->fetchAll($rs, 'op_id');
        $orderCartTotal = 0 ;
        $orderShippingCharges = 0 ;
        foreach ($childOrderDetail as $opID => $val) {
            $childOrderDetail[$opID]['charges'] = $orderDetail['charges'][$opID];
            $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($val['selprod_product_id'], "MEDIUM", $val['op_selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
            $childOrderDetail[$opID]['product_image'] =  $mainImgUrl;

            $childOrder = $childOrderDetail[$opID];
            $childOrderDetail[$opID]['shipping'] = CommonHelper::orderProductAmount($childOrder, 'shipping');
            $childOrderDetail[$opID]['tax'] = CommonHelper::orderProductAmount($childOrder, 'tax');
            $childOrderDetail[$opID]['volume_discount'] = CommonHelper::orderProductAmount($childOrder, 'VOLUME_DISCOUNT');
            $childOrderDetail[$opID]['reward_discount'] = CommonHelper::orderProductAmount($childOrder, 'REWARDPOINT');


            $childOrderDetail[$opID]['currency_op_unit_price'] = CommonHelper::displayMoneyFormat($childOrder['op_unit_price'], true, false, false);
            $childOrderDetail[$opID]['currency_op_commission_charged'] = CommonHelper::displayMoneyFormat($childOrder['op_commission_charged'], true, false, false);
            $childOrderDetail[$opID]['currency_op_affiliate_commission_charged'] = CommonHelper::displayMoneyFormat($childOrder['op_affiliate_commission_charged'], true, false, false);
            $childOrderDetail[$opID]['currency_op_refund_amount'] = CommonHelper::displayMoneyFormat($childOrder['op_refund_amount'], true, false, false);
            $childOrderDetail[$opID]['currency_op_refund_commission'] = CommonHelper::displayMoneyFormat($childOrder['op_refund_commission'], true, false, false);
            $childOrderDetail[$opID]['currency_op_refund_shipping'] = CommonHelper::displayMoneyFormat($childOrder['op_refund_shipping'], true, false, false);
            $childOrderDetail[$opID]['currency_op_refund_affiliate_commission'] = CommonHelper::displayMoneyFormat($childOrder['op_refund_affiliate_commission'], true, false, false);
            $childOrderDetail[$opID]['currency_order_net_amount'] = CommonHelper::displayMoneyFormat($childOrder['order_net_amount'], true, false, false);
            $childOrderDetail[$opID]['currency_order_wallet_amount_charge'] = CommonHelper::displayMoneyFormat($childOrder['order_wallet_amount_charge'], true, false, false);
            $childOrderDetail[$opID]['currency_order_tax_charged'] = CommonHelper::displayMoneyFormat($childOrder['order_tax_charged'], true, false, false);

            $childOrderDetail[$opID]['currency_order_site_commission'] = CommonHelper::displayMoneyFormat($childOrder['order_site_commission'], true, false, false);
            $childOrderDetail[$opID]['currency_order_discount_value'] = CommonHelper::displayMoneyFormat($childOrder['order_discount_value'], true, false, false);
            $childOrderDetail[$opID]['currency_order_discount_total'] = CommonHelper::displayMoneyFormat($childOrder['order_discount_total'], true, false, false);
            $childOrderDetail[$opID]['currency_order_volume_discount_total'] = CommonHelper::displayMoneyFormat($childOrder['order_volume_discount_total'], true, false, false);

            $childOrderDetail[$opID]['currency_order_reward_point_value'] = CommonHelper::displayMoneyFormat($childOrder['order_reward_point_value'], true, false, false);
            $childOrderDetail[$opID]['currency_shipping'] = CommonHelper::displayMoneyFormat($childOrderDetail[$opID]['shipping'], true, false, false);
            $childOrderDetail[$opID]['currency_tax'] = CommonHelper::displayMoneyFormat($childOrderDetail[$opID]['tax'], true, false, false);
            $childOrderDetail[$opID]['currency_volume_discount'] = CommonHelper::displayMoneyFormat($childOrderDetail[$opID]['volume_discount'], true, false, false);
            $childOrderDetail[$opID]['currency_reward_discount'] = CommonHelper::displayMoneyFormat($childOrderDetail[$opID]['reward_discount'], true, false, false);
            $childOrderDetail[$opID]['order_total'] = CommonHelper::orderProductAmount($childOrder);
            $childOrderDetail[$opID]['currency_order_total'] = CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder), true, false, false);



            $orderCartTotal = $orderCartTotal + CommonHelper::orderProductAmount($childOrder, 'cart_total');
            $orderShippingCharges = $orderShippingCharges + CommonHelper::orderProductAmount($childOrder, 'shipping');
        }
        $orderDetail['combined_order_cart_total'] = $orderCartTotal;
        $orderDetail['combined_order_cart_shipping_total']=$orderShippingCharges;
        $orderDetail['currency_combined_order_cart_total']=CommonHelper::displayMoneyFormat($orderCartTotal, true, false, false);
        $orderDetail['currency_combined_order_cart_shipping_total']=CommonHelper::displayMoneyFormat($orderShippingCharges, true, false, false);




        //commonhelper::printarray($childOrderDetail);
        //die();
        if ($opId > 0) {
            $childOrderDetail = array_shift($childOrderDetail);
        }

        if (!$childOrderDetail) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $address = $orderObj->getOrderAddresses($orderDetail['order_id']);
        $orderDetail['billingAddress'] = $address[Orders::BILLING_ADDRESS_TYPE];
        $orderDetail['shippingAddress'] = (!empty($address[Orders::SHIPPING_ADDRESS_TYPE]))?$address[Orders::SHIPPING_ADDRESS_TYPE]:array();
        if ($opId > 0) {
            $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("op_id"=>$childOrderDetail['op_id']));
        } else {
            $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("order_id"=>$orderDetail['order_id']));
            $orderDetail['payments'] = $orderObj->getOrderPayments(array("order_id"=>$orderDetail['order_id']));
        }
        $childOrderDetailCharges = $orderDetail['charges'];
        $api_orders_elements = array(
                                    'orderDetail'=>$orderDetail,
                                    'child_order_detail'=>$childOrderDetail,
                                    'orderStatuses'=>$orderStatuses,
                                    'isCombinedOrder'=>!$primaryOrderDisplay,
                                    'yesNoArr'=>applicationConstants::getYesNoArr($this->siteLangId),
                                    );
        //commonhelper::printarray($api_orders_elements);
        //die();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_orders_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function buyer_dashboard()
    {
        $userId = $this->getAppLoggedUserId();
        $user = new User($userId);

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinSellerProducts();
        $srch->joinSellerProductGroup();
        $srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        //$srch->addBuyerOrdersCounts(date('Y-m-d',strtotime("-1 days")),date('Y-m-d'),'yesterdayOrder');
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber(1);
        $srch->setPageSize(5);

        $srch->addMultipleFields(
            array('order_id', 'order_user_id','op_selprod_id','op_is_batch','selprod_product_id','order_date_added', 'order_net_amount', 'op_invoice_number','totCombinedOrders as totOrders', 'op_selprod_title', 'op_product_name', 'op_product_type', 'op_status_id', 'op_id','op_qty','op_selprod_options', 'op_brand_name', 'op_shop_name','op_other_charges','op_unit_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name')
        );
        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);
        if ($orders) {
            foreach ($orders as &$order) {
                $order['currency_order_net_amount'] = CommonHelper::displayMoneyFormat($order['order_net_amount'], true, false, false);
                $order['currency_op_other_charges'] = CommonHelper::displayMoneyFormat($order['op_other_charges'], true, false, false);
                $order['currency_op_unit_price'] = CommonHelper::displayMoneyFormat($order['op_unit_price'], true, false, false);
                $order['currency_amount'] = CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order), true, false, false);
            }
        }


        $getPurchasedsrch = clone $srch;
        $getPurchasedsrch->addCondition('order_is_paid', '=', 1);
        $getPurchasedsrch->addfld('count(order_id) as totalPurchasedItems');
        $countPurchasedItemsRs = $getPurchasedsrch->getResultSet();
        $totalPurchasedItems = FatApp::getDb()->fetch($countPurchasedItemsRs, 'totalPurchasedItems');

        $totalFavouriteItems = UserFavorite::getUserFavouriteItemCount($userId);
        $totalWishlistItems = UserWishList::getUserWishlistItemCount($userId);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
        }

        /* Orders Counts [*/
        $orderSrch = new OrderProductSearch($this->siteLangId, true, true);
        $orderSrch->doNotCalculateRecords();
        $orderSrch->doNotLimitRecords();
        $orderSrch->addBuyerOrdersCounts(date('Y-m-d', strtotime("-1 days")), date('Y-m-d', strtotime("-1 days")), 'yesterdayOrder');
        $orderSrch->addGroupBy('order_user_id');
        $orderSrch->addCondition('order_user_id', '=', $userId);
        $orderSrch->addMultipleFields(array('yesterdayOrderCount'));
        $rs = $orderSrch->getResultSet();
        $ordersStats = FatApp::getDb()->fetch($rs);
        /* ]*/

        /* Unread Message Count [*/
        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        $totalMessageCount = $threadObj->getMessageCount($userId);
        /*]*/

        /* Total Purchases [*/
        /*]*/

        $api_buyer_dashboard_elements = array(
                                        'profile_data'=>$user->getProfileData(),
                                        'orders'=>$orders,
                                        'ordersCount'=>$srch->recordCount(),
                                        'totalFavouriteItems'=>$totalFavouriteItems,
                                        'totalWishlistItems'=>$totalWishlistItems,
                                        'yesterdayOrderCount'=>FatUtility::int($ordersStats['yesterdayOrderCount']),
                                        'todayUnreadMessageCount'=>$todayUnreadMessageCount,
                                        'totalMessageCount'=>$totalMessageCount,
                                        'userBalance'=>commonHelper::displayMoneyFormat(User::getUserBalance($userId), true, false, false)
                                        );

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_buyer_dashboard_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function seller_dashboard()
    {
        $userId = $this->getAppLoggedUserId();
        $user = new User($userId);

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->joinSellerProducts();
        $srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        //$srch->addSellerOrderCounts(date('Y-m-d',strtotime("-1 days")),date('Y-m-d'),'yesterdayOrder');
        $srch->addCondition('op_selprod_user_id', '=', $userId);

        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber(1);
        $srch->setPageSize(5);

        $srch->addMultipleFields(
            array('order_id', 'order_user_id','op_selprod_id','op_is_batch','selprod_product_id', 'order_date_added', 'order_net_amount', 'op_invoice_number','totCombinedOrders as totOrders', 'op_selprod_title', 'op_product_name', 'op_id','op_qty','op_selprod_options','op_status_id', 'op_brand_name', 'op_shop_name','op_other_charges','op_unit_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name')
        );

        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            /*$arrCharges = array();
            $count = 0;
            foreach($charges as $key => $val){
            $arrCharges[$count]['type']= $val['opcharge_type'];
            $arrCharges[$count]['amount']= $val['opcharge_amount'];
            $arrCharges[$count]['currency_amount'] = CommonHelper::displayMoneyFormat($val['opcharge_amount'],true,false,false);
            $count++;
            }*/
            //commonhelper::printarray($arrCharges);
            //die();
            $order['charges'] = $charges;

            $order['shipping'] = CommonHelper::orderProductAmount($order, 'shipping');
            $order['tax'] = CommonHelper::orderProductAmount($order, 'tax');
            $order['volume_discount'] = CommonHelper::orderProductAmount($order, 'VOLUME_DISCOUNT');
            $order['reward_discount'] = CommonHelper::orderProductAmount($order, 'REWARDPOINT');

            $order['currency_shipping'] = CommonHelper::displayMoneyFormat($order['shipping'], true, false, false);
            $order['currency_tax'] = CommonHelper::displayMoneyFormat($order['tax'], true, false, false);
            $order['currency_volume_discount'] = CommonHelper::displayMoneyFormat($order['volume_discount'], true, false, false);
            $order['currency_reward_discount'] = CommonHelper::displayMoneyFormat($order['reward_discount'], true, false, false);


            $order['currency_order_net_amount'] = CommonHelper::displayMoneyFormat($order['order_net_amount'], true, false, false);
            $order['currency_op_other_charges'] = CommonHelper::displayMoneyFormat($order['op_other_charges'], true, false, false);
            $order['currency_op_unit_price'] = CommonHelper::displayMoneyFormat($order['op_unit_price'], true, false, false);
            $order['currency_amount'] = CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order), true, false, false);
        }

        /* Orders Counts [*/
        $orderSrch = new OrderProductSearch($this->siteLangId, true, true);
        $orderSrch->doNotCalculateRecords();
        $orderSrch->doNotLimitRecords();
        /* $orderSrch->addSellerOrdersCounts( date('Y-m-d',strtotime("-1 days") ), date('Y-m-d'), 'yesterdayOrder');
        $orderSrch->addSellerCompletedOrdersStats( date('Y-m-d', strtotime("-1 days")),date('Y-m-d'), 'yesterdaySold' ); */

        $orderSrch->addSellerOrdersCounts(date('Y-m-d', strtotime("-1 days")), date('Y-m-d', strtotime("-1 days")), 'yesterdayOrder');
        $orderSrch->addSellerCompletedOrdersStats(date('Y-m-d', strtotime("-1 days")), date('Y-m-d', strtotime("-1 days")), 'yesterdaySold');

        $orderSrch->addSellerCompletedOrdersStats(false, false, 'totalSold');
        $orderSrch->addGroupBy('op_selprod_user_id');
        $orderSrch->addCondition('op_selprod_user_id', '=', $userId);
        $orderSrch->addMultipleFields(array('yesterdayOrderCount' ,'yesterdaySoldCount','totalSoldCount','totalSoldSales' ));
        $rs = $orderSrch->getResultSet();
        $ordersStats = FatApp::getDb()->fetch($rs);
        /* ]*/


        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        $unreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD);
        $totalMessageCount = $threadObj->getMessageCount($userId);
        /*]*/
        $orderObj = new Orders();
        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();

        /* Remaining Products and Days Count [*/
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $products = new Product();



            $latestOrder = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $userId, array('ossubs_till_date','ossubs_id','ossubs_products_allowed'));

            $pendingDaysForCurrentPlan=0;
            $remainingAllowedProducts=0;
            if ($latestOrder) {
                $pendingDaysForCurrentPlan=FatDate::diff(date("Y-m-d"), $latestOrder['ossubs_till_date']);
                $totalProducts  =  $products->getTotalProductsAddedByUser($userId);
                $remainingAllowedProducts = $latestOrder['ossubs_products_allowed'] - $totalProducts;
                $this->set('subscriptionTillDate', $latestOrder['ossubs_till_date']);
            }

            $this->set('pendingDaysForCurrentPlan', $pendingDaysForCurrentPlan);
            $this->set('remainingAllowedProducts', $remainingAllowedProducts);
        }
        /*]*/



        $api_seller_dashboard_elements = array(
                                        'profile_data'=>$user->getProfileData(),
                                        'orders'=>$orders,
                                        'ordersCount'=>$srch->recordCount(),
                                        'totalSoldCount'=>FatUtility::int($ordersStats['totalSoldCount']),
                                        'totalSoldSales'=>FatUtility::float($ordersStats['totalSoldSales']),
                                        'yesterdayOrderCount'=>FatUtility::int($ordersStats['yesterdayOrderCount']),
                                        'todayUnreadMessageCount'=>$todayUnreadMessageCount,
                                        'totalMessageCount'=>$totalMessageCount,
                                        'userBalance'=>commonHelper::displayMoneyFormat(User::getUserBalance($userId), true, false, false),
                                        'notAllowedStatues'=>$notAllowedStatues,
                                        'dashboardStats'=>Stats::getUserSales($userId)
                                        );

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_seller_dashboard_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function buyer_order_cancellation_reasons()
    {
        $orderCancelReasonsArr = OrderCancelReason::getOrderCancelReasonArr($this->siteLangId);
        $count = 0;
        foreach ($orderCancelReasonsArr as $key => $val) {
            $cancelReasonsArr[$count]['key']= $key;
            $cancelReasonsArr[$count]['value']= $val;
            $count++;
        }
        die($this->json_encode_unicode(array('status'=>1, 'reasons'=>$cancelReasonsArr)));
    }

    public function get_order_return_request_paramters($op_id)
    {
        $user_id = $this->getAppLoggedUserId();
        $orderReturnReasonsArr = OrderReturnReason::getOrderReturnReasonArr($this->siteLangId);
        $count = 0;
        foreach ($orderReturnReasonsArr as $key => $val) {
            $returnReasonsArr[$count]['key']= $key;
            $returnReasonsArr[$count]['value']= $val;
            $count++;
        }
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(array('op_status_id', 'op_id', 'op_qty','op_product_type'));
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        die($this->json_encode_unicode(array('status'=>1, 'max_qty'=>$opDetail['op_qty'],'type'=>OrderReturnRequest::RETURN_REQUEST_TYPE_REFUND, 'reasons'=>$returnReasonsArr)));
        //getOrderReturnRequestForm
    }

    public function buyer_order_cancellation_requests()
    {
        $user_id = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }

        $srch = new OrderCancelRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrderCancelReasons();
        $srch->joinOrders();
        $srch->addCondition('ocrequest_user_id', '=', $user_id);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(array( 'ocrequest_id', 'ocrequest_date', 'ocrequest_status', 'order_id', 'op_invoice_number', 'IFNULL(ocreason_title, ocreason_identifier) as ocreason_title', 'ocrequest_message'));
        $srch->addOrder('ocrequest_date', 'DESC');

        $op_invoice_number = FatApp::getPostedData('op_invoice_number', null, '');
        if (!empty($op_invoice_number)) {
            $srch->addCondition('op_invoice_number', '=', $op_invoice_number);
        }

        $ocrequest_date_from = FatApp::getPostedData('ocrequest_date_from', FatUtility::VAR_DATE, '');
        if (!empty($ocrequest_date_from)) {
            $srch->addCondition('ocrequest_date', '>=', $ocrequest_date_from. ' 00:00:00');
        }

        $ocrequest_date_to = FatApp::getPostedData('ocrequest_date_to', FatUtility::VAR_DATE, '');
        if (!empty($ocrequest_date_to)) {
            $srch->addCondition('ocrequest_date', '<=', $ocrequest_date_to. ' 23:59:59');
        }

        $ocrequest_status = FatApp::getPostedData('ocrequest_status', null, '-1');
        if ($ocrequest_status > -1) {
            $ocrequest_status = FatUtility::int($ocrequest_status);
            $srch->addCondition('ocrequest_status', '=', $ocrequest_status);
        }

        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        $api_cancellation_requests_elements = array('requests'=>$requests,'total_pages'=>$srch->pages(),'total_records'=>$srch->recordCount(),'OrderCancelRequestStatusArr'=>OrderCancelRequest::getRequestStatusArr($this->siteLangId));

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_cancellation_requests_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function submit_order_cancellation_request()
    {
        $user_id = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();

        if (empty($post['ocrequest_message']) || empty($post['child_order_id']) || empty($post['ocrequest_ocreason_id'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $op_id = FatUtility::int($post['child_order_id']);
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addOrder("op_id", "DESC");
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
        }

        if ($opDetail["op_product_type"] == Product::PRODUCT_TYPE_DIGITAL) {
            if (!in_array($opDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses(true))) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Order_Cancellation_cannot_placed', $this->siteLangId));
            }
        } else {
            if (!in_array($opDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses())) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Order_Cancellation_cannot_placed', $this->siteLangId));
            }
        }


        $ocRequestSrch = new OrderCancelRequestSearch();
        $ocRequestSrch->doNotCalculateRecords();
        $ocRequestSrch->doNotLimitRecords();
        $ocRequestSrch->addCondition('ocrequest_op_id', '=', $opDetail['op_id']);
        $ocRequestRs = $ocRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($ocRequestRs)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_You_have_already_sent_the_cancellation_request_for_this_order', $this->siteLangId));
        }

        $dataToSave = array(
        'ocrequest_user_id'    =>    $user_id,
        'ocrequest_op_id'    =>    $opDetail['op_id'],
        'ocrequest_ocreason_id'    =>    FatUtility::int($post['ocrequest_ocreason_id']),
        'ocrequest_message'        =>    $post['ocrequest_message'],
        'ocrequest_date'        =>    date('Y-m-d H:i:s'),
        'ocrequest_status'        =>    OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING
        );

        $oCRequestObj = new OrderCancelRequest();
        $oCRequestObj->assignValues($dataToSave, true);
        if (!$oCRequestObj->save()) {
            FatUtility::dieJsonError($oCRequestObj->getError());
        }
        $ocrequest_id = $oCRequestObj->getMainTableRecordId();
        if (!$ocrequest_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId));
        }

        $emailObj = new EmailHandler();
        if (!$emailObj->sendOrderCancellationNotification($ocrequest_id, $this->siteLangId)) {
            FatUtility::dieJsonError($emailObj->getError());
        }
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'msg'=>Labels::getLabel('MSG_Your_cancellation_request_submitted', $this->siteLangId))));
    }

    public function buyer_order_return_requests()
    {
        $user_id = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinSellerProducts();
        $srch->addCondition('orrequest_user_id', '=', $user_id);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(
            array( 'orrequest_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type', 'orrequest_reference', 'orrequest_date', 'orrequest_status',
            'op_invoice_number', 'op_selprod_title', 'op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model','op_selprod_id','selprod_product_id')
        );

        $srch->addOrder('orrequest_date', 'DESC');

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('op_invoice_number', '=', $keyword);
            $cnd->attachCondition('op_selprod_title', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_product_name', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_brand_name', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_selprod_options', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_selprod_sku', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_product_model', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('orrequest_reference', 'LIKE', '%'.$keyword.'%', 'OR');
        }

        $orrequest_status = FatApp::getPostedData('orrequest_status', null, '-1');
        if ($orrequest_status > -1) {
            $orrequest_status = FatUtility::int($orrequest_status);
            $srch->addCondition('orrequest_status', '=', $orrequest_status);
        }

        $orrequest_date_from = FatApp::getPostedData('orrequest_date_from', FatUtility::VAR_DATE, '');
        if (!empty($orrequest_date_from)) {
            $srch->addCondition('orrequest_date', '>=', $orrequest_date_from. ' 00:00:00');
        }

        $orrequest_date_to = FatApp::getPostedData('orrequest_date_to', FatUtility::VAR_DATE, '');
        if (!empty($orrequest_date_to)) {
            $srch->addCondition('orrequest_date', '<=', $orrequest_date_to. ' 23:59:59');
        }
        //die($srch->getquery());
        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        foreach ($requests as &$request) {
            $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($request['op_selprod_id'], "MEDIUM", $request['selprod_product_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
            $request['product_image'] =  $mainImgUrl;
        }

        //commonhelper::printarray($requests);
        //die();

        $returnRequestTypeArr = OrderReturnRequest::getRequestTypeArr($this->siteLangId);
        $count = 0;
        foreach ($returnRequestTypeArr as $key => $val) {
            $returnRequestTypeDispArr[$count]['key']= $key;
            $returnRequestTypeDispArr[$count]['value']= $val;
            $count++;
        }

        $api_return_requests_elements = array(
                                                    'requests'=>$requests,
                                                    'total_pages'=>$srch->pages(),
                                                    'total_records'=>$srch->recordCount(),
                                                    'returnRequestTypeArr'=>$returnRequestTypeDispArr,
                                                    'OrderReturnRequestStatusArr'=>OrderReturnRequest::getRequestStatusArr($this->siteLangId),
                                                    'sellerPage'=>false,
                                                    'buyerPage'=>true
                                                    );

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_return_requests_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function view_order_return_request($orrequest_id)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = $this->getAppLoggedUserId();
        //die($user_id."@");
        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('orrequest_user_id', '=', $user_id);
        $srch->joinOrderProducts();
        $srch->joinOrderProductSettings();
        $srch->joinOrders();
        $srch->joinOrderReturnReasons();
        $srch->addOrderProductCharges();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array( 'orrequest_id','orrequest_op_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type',
            'orrequest_date', 'orrequest_status', 'orrequest_reference', 'op_invoice_number', 'op_selprod_title', 'op_product_name',
            'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model','op_qty',
            'op_unit_price', 'op_selprod_user_id', 'IFNULL(orreason_title, orreason_identifier) as orreason_title','op_shop_id', 'op_shop_name', 'op_shop_owner_name', 'order_tax_charged','op_other_charges','op_refund_amount','op_commission_percentage','op_affiliate_commission_percentage','op_commission_include_tax','op_tax_collected_by_seller','op_commission_include_shipping','op_free_ship_upto','op_actual_shipping_charges')
        );

        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);
        if (!$request) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $oObj = new Orders();
        $charges = $oObj->getOrderProductChargesArr($request['orrequest_op_id']);
        $request['charges'] = $charges;
        $request['currency_order_tax_charged'] = CommonHelper::displayMoneyFormat($request['order_tax_charged'], true, false, false);
        $request['currency_op_other_charges'] = CommonHelper::displayMoneyFormat($request['op_other_charges'], true, false, false);
        $request['currency_op_refund_amount'] = CommonHelper::displayMoneyFormat($request['op_refund_amount'], true, false, false);
        $returnDataArr = CommonHelper::getOrderProductRefundAmtArr($request);
        $request['net_amount'] = $returnDataArr['op_refund_amount'];
        $request['currency_net_amount'] = CommonHelper::displayMoneyFormat($request['net_amount'], true, false, false);


        $sellerUserObj = new User($request['op_selprod_user_id']);
        $vendorReturnAddress = $sellerUserObj->getUserReturnAddress($this->siteLangId);


        $canEscalateRequest = 0;
        $canWithdrawRequest = 0;

        if (($request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING) || $request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED) {
            $canWithdrawRequest = true;
        }

        $userObj = new User($user_id);
        $srch = $userObj->getUserSearchObj();
        $rs = $srch->getResultSet();
        $user = FatApp::getDb()->fetch($rs, 'user_id');
        $returnRequestTypeArr = OrderReturnRequest::getRequestTypeArr($this->siteLangId);

        $count = 0;
        foreach ($returnRequestTypeArr as $key => $val) {
            $returnRequestTypeDispArr[$count]['key']= $key;
            $returnRequestTypeDispArr[$count]['value']= $val;
            $count++;
        }

        $api_view_return_requests_elements = array(
                                                    'canEscalateRequest'=>$canEscalateRequest,
                                                    'canWithdrawRequest'=>$canWithdrawRequest,
                                                    'request'=>$request,
                                                    'vendorReturnAddress'=>$vendorReturnAddress,
                                                    'returnRequestTypeArr'=>$returnRequestTypeDispArr,
                                                    'requestRequestStatusArr'=>OrderReturnRequest::getRequestStatusArr($this->siteLangId) ,
                                                    'logged_user_name'=>$user['user_name'],
                                                    'logged_user_id'=>$user_id
                                                    );
        //commonhelper::printarray($api_view_return_requests_elements);
        //die();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_view_return_requests_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function view_order_return_request_messages($orrequest_id)
    {
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);

        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = $this->getAppLoggedUserId();
        //die($user_id."@");
        $srch = new OrderReturnRequestMessageSearch($this->siteLangId);
        $srch->joinOrderReturnRequests();
        $srch->joinMessageUser();
        $srch->joinMessageAdmin();
        $srch->addCondition('orrmsg_orrequest_id', '=', $orrequest_id);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('orrmsg_id', 'DESC');
        $srch->addMultipleFields(
            array( 'orrmsg_id', 'orrmsg_from_user_id', 'orrmsg_msg',
            'orrmsg_date', 'msg_user.user_name as msg_user_name', 'orrequest_status',
            'orrmsg_from_admin_id', 'admin_name' )
        );
        $rs = $srch->getResultSet();
        $srch->addOrder('orrmsg_date', 'desc');
        $messagesList = FatApp::getDb()->fetchAll($rs);

        $message_records = array();
        foreach ($messagesList as $mkey=>$mval) {
            $arr = array_merge($mval, array("message_timestamp"=>strtotime($mval['orrmsg_date'])));
            array_unshift($message_records, $arr);
        }

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'messagesList'=>$message_records,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function withdraw_order_return_request($orrequest_id)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = $this->getAppLoggedUserId();

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();

        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('orrequest_user_id', '=', $user_id);
        $cnd = $srch->addCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING);
        $cnd->attachCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'op_id', 'order_language_id'));
        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);
        if (!$request) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $orrObj = new OrderReturnRequest();
        if (!$orrObj->withdrawRequest($request['orrequest_id'], $user_id, $this->siteLangId, $request['op_id'], $request['order_language_id'])) {
            FatUtility::dieJsonError(Labels::getLabel($orrObj->getError(), $this->siteLangId));
        }

        /* email notification handling[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification($request['orrequest_id'], $this->siteLangId)) {
            FatUtility::dieJsonError(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
        }
        /* ] */
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'msg'=> Labels::getLabel('MSG_Request_Withdrawn', $this->siteLangId) ,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function submit_order_return_request($id)
    {
        $op_id = FatUtility::int($id);
        $user_id = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        //$post = array('orrmsg_msg'=>"A1","orrequest_qty"=>1,"orrequest_returnreason_id"=>1,"orrequest_type"=>1);
        if ((1 > $id) || empty($post['orrmsg_msg']) || empty($post['orrequest_qty']) || empty($post['orrequest_returnreason_id']) || empty($post['orrequest_type'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(array('order_language_id', 'op_status_id', 'op_id', 'op_qty', 'op_product_type'));
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);

        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
        }


        if ($opDetail["op_product_type"] == Product::PRODUCT_TYPE_DIGITAL) {
            $getBuyerAllowedOrderReturnStatuses = (array)Orders::getBuyerAllowedOrderReturnStatuses(true);
        } else {
            $getBuyerAllowedOrderReturnStatuses = (array)Orders::getBuyerAllowedOrderReturnStatuses();
        }
        $oReturnRequestSrch = new OrderReturnRequestSearch();
        $oReturnRequestSrch->doNotCalculateRecords();
        $oReturnRequestSrch->doNotLimitRecords();
        $oReturnRequestSrch->addCondition('orrequest_op_id', '=', $opDetail['op_id']);
        $oReturnRequestRs = $oReturnRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oReturnRequestRs)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Already_submitted_return_request_order', $this->siteLangId));
        }

        if (!in_array($opDetail["op_status_id"], $getBuyerAllowedOrderReturnStatuses)) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
            $statuses = $getBuyerAllowedOrderReturnStatuses;
            $status_names = array();
            foreach ($statuses as $status) {
                $status_names[] = $orderStatuses[$status];
            }
            FatUtility::dieJsonError(sprintf(Labels::getLabel('MSG_Return_Refund_cannot_placed', $this->siteLangId), implode(',', $status_names)));
        }


        $reference_number = $user_id.'-'.time();
        $returnRequestDataToSave = array(
        'orrequest_user_id'            =>    $user_id,
        'orrequest_reference'        =>    $reference_number,
        'orrequest_op_id'            =>    $opDetail['op_id'],
        'orrequest_qty'                =>    FatUtility::int($post['orrequest_qty']),
        'orrequest_returnreason_id'    =>    FatUtility::int($post['orrequest_returnreason_id']),
        'orrequest_type'            =>    FatUtility::int($post['orrequest_type']),
        'orrequest_date'            =>    date('Y-m-d H:i:s'),
        'orrequest_status'            =>    OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING
        );

        $oReturnRequestObj = new OrderReturnRequest();
        $oReturnRequestObj->assignValues($returnRequestDataToSave, true);
        if (!$oReturnRequestObj->save()) {
            FatUtility::dieJsonError($oReturnRequestObj->getError());
        }
        $orrequest_id = $oReturnRequestObj->getMainTableRecordId();
        if (!$orrequest_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId));
        }

        /* attach file with request [ */

        if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
            $uploadedFile = $_FILES['file']['tmp_name'];
            $uploadedFileExt = pathinfo($uploadedFile, PATHINFO_EXTENSION);

            if (filesize($uploadedFile) > 10240000) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId));
            }

            if (getimagesize($uploadedFile) === false && in_array($uploadedFileExt, array('.zip'))) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->siteLangId));
            }

            $fileHandlerObj = new AttachedFile();
            if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $orrequest_id, 0, $_FILES['file']['name'], -1, true)) {
                FatUtility::dieJsonError($fileHandlerObj->getError());
            }
        }

        /* ] */

        /* save return request message[ */
        $returnRequestMsgDataToSave = array(
        'orrmsg_orrequest_id'    =>    $orrequest_id,
        'orrmsg_from_user_id'    =>    $user_id,
        'orrmsg_msg'            =>    $post['orrmsg_msg'],
        'orrmsg_date'            =>    date('Y-m-d H:i:s'),
        );

        $oReturnRequestMsgObj = new OrderReturnRequestMessage();
        $oReturnRequestMsgObj->assignValues($returnRequestMsgDataToSave, true);
        if (!$oReturnRequestMsgObj->save()) {
            FatUtility::dieJsonError($oReturnRequestMsgObj->getError());
        }
        $orrmsg_id = $oReturnRequestMsgObj->getMainTableRecordId();
        if (!$orrmsg_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId));
        }
        /* ] */

        /* adding child order history[ */
        $orderObj = new Orders();
        $orderObj->addChildProductOrderHistory($opDetail['op_id'], $opDetail['order_language_id'], FatApp::getConfig("CONF_RETURN_REQUEST_ORDER_STATUS"), Labels::getLabel('LBL_Buyer_Raised_Return_Request', $opDetail['order_language_id']), 1);
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestNotification($orrmsg_id, $opDetail['order_language_id'])) {
            FatUtility::dieJsonError($emailNotificationObj->getError());
        }
        /* ] */

        /* ] */
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'msg'=> Labels::getLabel('MSG_Your_return_request_submitted', $this->siteLangId) ,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function submit_order_feedback($id)
    {
        $op_id = FatUtility::int($id);
        $user_id = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        /*$review_rating = array("1"=>4,"2"=>3,"3"=>3,"4"=>5);
        $post = array('review_rating'=>$review_rating,"title"=>"A","description"=>"D");
        */
        if ((1 > $op_id) ||  empty($post['review_rating']) || empty($post['title']) || empty($post['description'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(array('op_status_id', 'op_selprod_user_id', 'op_selprod_code','op_order_id','op_selprod_id','op_is_batch','op_batch_selprod_id'));

        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);

        if (!$opDetail || CommonHelper::isMultidimArray($opDetail) || !(FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0))) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
        }

        if ($opDetail['op_is_batch']) {
            $selProdIdArr = explode('|', $opDetail['op_batch_selprod_id']);
            $selProdId = array_shift($selProdIdArr);
        } else {
            $selProdId = $opDetail['op_selprod_id'];
        }

        if (1 > FatUtility::int($selProdId)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
        }

        if (!in_array($opDetail["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
            $statuses = SelProdReview::getBuyerAllowedOrderReviewStatuses();
            $statusNames = array();

            foreach ($statuses as $status) {
                $statusNames[] = $orderStatuses[$status];
            }
            FatUtility::dieJsonError(sprintf(Labels::getLabel('MSG_Feedback_can_be_placed_', $this->siteLangId), implode(',', $statusNames)));
        }


        /* checking Abusive Words[ */
        $enteredAbusiveWordsArr = array();
        if (!Abusive::validateContent(FatApp::getPostedData('spreview_description', FatUtility::VAR_STRING, ''), $enteredAbusiveWordsArr)) {
            if (!empty($enteredAbusiveWordsArr)) {
                $errStr =  Labels::getLabel("LBL_Word_{abusiveword}_is/are_not_allowed_to_post", $this->siteLangId);
                $errStr = str_replace("{abusiveword}", '"'.implode(", ", $enteredAbusiveWordsArr).'"', $errStr);
                FatUtility::dieJsonError($errStr);
            }
        }
        /* ] */

        $sellerId = $opDetail['op_selprod_user_id'];

        /* $selProdDetail = SellerProduct::getAttributesById($selProdId);
        $productId = FatUtility::int($selProdDetail['selprod_product_id']); */

        if ($opDetail['op_is_batch']) {
            $selProdCode = array_shift(explode('|', $opDetail['op_selprod_code']));
        } else {
            $selProdCode = $opDetail['op_selprod_code'];
        }
        $arr = explode('_', $selProdCode);
        $productId = array_shift($arr);
        //die($productId."#");
        $oFeedbackSrch = new SelProdReviewSearch();
        $oFeedbackSrch->doNotCalculateRecords();
        $oFeedbackSrch->doNotLimitRecords();
        $oFeedbackSrch->addCondition('spreview_postedby_user_id', '=', $user_id);
        $oFeedbackSrch->addCondition('spreview_order_id', '=', $opDetail['op_order_id']);
        $oFeedbackSrch->addCondition('spreview_selprod_id', '=', $selProdId);
        $oFeedbackRs = $oFeedbackSrch->getResultSet();
        if (FatApp::getDb()->fetch($oFeedbackRs)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Already_submitted_order_feedback', $this->siteLangId));
        }



        $post['spreview_seller_user_id'] = $sellerId;
        $post['spreview_order_id'] = $opDetail['op_order_id'];
        $post['spreview_product_id'] = $productId ;
        $post['spreview_selprod_id'] = $selProdId;
        $post['spreview_selprod_code'] = $selProdCode;
        $post['spreview_postedby_user_id'] = $user_id;
        $post['spreview_posted_on'] = date('Y-m-d H:i:s');
        $post['spreview_lang_id'] = $this->siteLangId;
        $post['spreview_status'] = FatApp::getConfig('CONF_DEFAULT_REVIEW_STATUS', FatUtility::VAR_INT, 0);
        $post['spreview_title'] = $post['title'];
        $post['spreview_description'] = $post['description'];

        $selProdReview = new SelProdReview();
        $selProdReview->assignValues($post);

        $db = FatApp::getDb();
        $db->startTransaction();

        if (!$selProdReview->save()) {
            $db->rollbackTransaction();
            FatUtility::dieJsonError($selProdReview->getError());
        }
        $spreviewId = $selProdReview->getMainTableRecordId();
        $ratingsPosted = $post['review_rating'];
        $ratingAspects = SelProdRating::getRatingAspectsArr($this->siteLangId);
        foreach ($ratingsPosted as $ratingAspect => $ratingValue) {
            if (isset($ratingAspects[$ratingAspect])) {
                $selProdRating = new SelProdRating();
                $ratingRow = array('sprating_spreview_id' => $spreviewId, 'sprating_rating_type'=> $ratingAspect ,'sprating_rating' => $ratingValue);
                $selProdRating->assignValues($ratingRow);
                if (!$selProdRating->save()) {
                    $db->rollbackTransaction();
                    FatUtility::dieJsonError($selProdRating->getError());
                }
            }
        }
        $db->commitTransaction();
        $emailNotificationObj = new EmailHandler();
        if ($post['spreview_status'] == SelProdReview::STATUS_APPROVED) {
            $emailNotificationObj->sendBuyerReviewStatusUpdatedNotification($spreviewId, $this->siteLangId);
        }
        $reviewTitle = $post['title'];
        $reviewTitleArr = preg_split("/[\s,-]+/", $reviewTitle);
        $reviewDesc = $post['description'];
        $reviewDescArr = preg_split("/[\s,-]+/", $reviewDesc);

        $abusiveWords = Abusive::getAbusiveWords();
        if (!empty(array_intersect($abusiveWords, $reviewTitleArr)) || !empty(array_intersect($abusiveWords, $reviewDescArr))) {
            $emailNotificationObj->sendAdminAbusiveReviewNotification($spreviewId, $this->siteLangId);
        }

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'msg'=> Labels::getLabel('MSG_Feedback_Submitted_Successfully', $this->siteLangId) ,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function seller_orders()
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinSellerProducts();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addMultipleFields(
            array('order_id', 'order_user_id','op_selprod_id','op_is_batch','selprod_product_id','order_date_added', 'order_net_amount', 'op_invoice_number','totCombinedOrders as totOrders', 'op_selprod_title', 'op_product_name', 'op_id','op_qty','op_selprod_options', 'op_brand_name', 'op_shop_name','op_other_charges','op_unit_price','op_tax_collected_by_seller','op_selprod_user_id','opshipping_by_seller_user_id','orderstatus_id','IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name')
        );

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');

        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")))) {
            $srch->addStatusCondition($op_status_id);
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        }

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if (!empty($priceFrom)) {
            $srch->addMinPriceCondition($priceFrom);
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addMaxPriceCondition($priceTo);
        }

        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            //$charges = $oObj->getOrderProductChargesArr($order['op_id']);
            //$order['charges'] = $charges;
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;

            $order['shipping'] = CommonHelper::orderProductAmount($order, 'shipping');
            $order['tax'] = CommonHelper::orderProductAmount($order, 'tax');
            $order['volume_discount'] = CommonHelper::orderProductAmount($order, 'VOLUME_DISCOUNT');
            $order['reward_discount'] = CommonHelper::orderProductAmount($order, 'REWARDPOINT');

            $order['currency_shipping'] = CommonHelper::displayMoneyFormat($order['shipping'], true, false, false);
            $order['currency_tax'] = CommonHelper::displayMoneyFormat($order['tax'], true, false, false);
            $order['currency_volume_discount'] = CommonHelper::displayMoneyFormat($order['volume_discount'], true, false, false);
            $order['currency_reward_discount'] = CommonHelper::displayMoneyFormat($order['reward_discount'], true, false, false);


            $order['currency_order_net_amount'] = CommonHelper::displayMoneyFormat($order['order_net_amount'], true, false, false);
            $order['currency_op_other_charges'] = CommonHelper::displayMoneyFormat($order['op_other_charges'], true, false, false);
            $order['currency_op_unit_price'] = CommonHelper::displayMoneyFormat($order['op_unit_price'], true, false, false);
            $order['currency_amount'] = CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'netamount', false, USER::USER_TYPE_SELLER), true, false, false);
        }

        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
        $count = 0;
        foreach ($orderStatuses as $key => $val) {
            $orderStsArr[$count]['key']= $key;
            $orderStsArr[$count]['value']= $val;
            $count++;
        }

        $api_orders_elements = array('orders'=>$orders,'orderStatuses'=>$orderStsArr,'total_pages'=>$srch->pages(),'page'=>$page,'total_records'=>$srch->recordCount());
        //commonhelper::printarray($api_orders_elements);
        //die();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_orders_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount), true));
    }

    public function view_seller_order($op_id)
    {
        $userId = $this->getAppLoggedUserId();
        if (!$op_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $orderObj = new Orders();
        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
        /* $count = 0;
        foreach($orderStatuses as $key => $val){
        $orderStsArr[$count]['key']= $key;
        $orderStsArr[$count]['value']= $val;
        $count++;
        } */

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addOrderProductCharges();
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $paymentMethodName = ($orderDetail['pmethod_name'] !='')?$orderDetail['pmethod_name']:$orderDetail['pmethod_identifier'];
        if ($orderDetail['order_pmethod_id'] > 0 && $orderDetail['order_is_wallet_selected'] > 0) {
            $paymentMethodName .= ' + ';
        }

        if ($orderDetail['order_is_wallet_selected'] > 0) {
            $paymentMethodName .= Labels::getLabel("LBL_Wallet", $this->siteLangId);
        }
        $orderDetail['pmethod_name']= $paymentMethodName;

        $codOrder = false;
        if (strtolower($orderDetail['pmethod_code']) == 'cashondelivery') {
            $codOrder = true;
        }

        if ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(true, $codOrder);
        } elseif ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder);
        } else {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder);
        }

        /*[ if shipping not handled by seller then seller can not update status to ship and delived*/
        if (!CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id'])) {
            $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
            $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
        }
        /*]*/

        $allowedStatuses = Orders::getOrderProductStatusArr($this->siteLangId, $processingStatuses, $orderDetail['op_status_id']);

        $count = 0;
        $orderStsArr = array();
        foreach ($orderStatuses as $key => $val) {
            if (!array_key_exists($key, $allowedStatuses)) {
                continue;
            }
            $orderStsArr[$count]['key']= $key;
            $orderStsArr[$count]['value']= $val;
            $count++;
        }

        $orderDetail['currency_op_unit_price'] = CommonHelper::displayMoneyFormat($orderDetail['op_unit_price'], true, false, false);
        $orderDetail['currency_op_commission_charged'] = CommonHelper::displayMoneyFormat($orderDetail['op_commission_charged'], true, false, false);
        $orderDetail['currency_op_affiliate_commission_charged'] = CommonHelper::displayMoneyFormat($orderDetail['op_affiliate_commission_charged'], true, false, false);
        $orderDetail['currency_op_refund_amount'] = CommonHelper::displayMoneyFormat($orderDetail['op_refund_amount'], true, false, false);
        $orderDetail['currency_op_refund_commission'] = CommonHelper::displayMoneyFormat($orderDetail['op_refund_commission'], true, false, false);
        $orderDetail['currency_op_refund_shipping'] = CommonHelper::displayMoneyFormat($orderDetail['op_refund_shipping'], true, false, false);

        $orderDetail['currency_op_refund_affiliate_commission'] = CommonHelper::displayMoneyFormat($orderDetail['op_refund_affiliate_commission'], true, false, false);
        $orderDetail['currency_order_net_amount'] = CommonHelper::displayMoneyFormat($orderDetail['order_net_amount'], true, false, false);
        $orderDetail['currency_order_wallet_amount_charge'] = CommonHelper::displayMoneyFormat($orderDetail['order_wallet_amount_charge'], true, false, false);
        $orderDetail['currency_order_tax_charged'] = CommonHelper::displayMoneyFormat($orderDetail['order_tax_charged'], true, false, false);
        $orderDetail['currency_order_site_commission'] = CommonHelper::displayMoneyFormat($orderDetail['order_site_commission'], true, false, false);

        $orderDetail['currency_order_discount_value'] = CommonHelper::displayMoneyFormat($orderDetail['order_discount_value'], true, false, false);
        $orderDetail['currency_order_discount_total'] = CommonHelper::displayMoneyFormat($orderDetail['order_discount_total'], true, false, false);
        $orderDetail['currency_order_volume_discount_total'] = CommonHelper::displayMoneyFormat($orderDetail['order_volume_discount_total'], true, false, false);
        $orderDetail['currency_order_reward_point_value'] = CommonHelper::displayMoneyFormat($orderDetail['order_reward_point_value'], true, false, false);




        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $orderDetail['charges'] = $charges;



        $address = $orderObj->getOrderAddresses($orderDetail['op_order_id']);
        $orderDetail['billingAddress'] = (isset($address[Orders::BILLING_ADDRESS_TYPE]))?$address[Orders::BILLING_ADDRESS_TYPE]:(object)array();
        $orderDetail['shippingAddress'] = (isset($address[Orders::SHIPPING_ADDRESS_TYPE]))?$address[Orders::SHIPPING_ADDRESS_TYPE]:(object)array();

        $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("op_id"=>$op_id,'seller_id'=>$userId));

        $data = array('op_id'=>$op_id , 'op_status_id' => $orderDetail['op_status_id']);


        $shippedBySeller = applicationConstants::NO;
        if (CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id'])) {
            $shippedBySeller = applicationConstants::YES;
        }
        $orderDetail['cart_total'] = CommonHelper::orderProductAmount($orderDetail, 'CART_TOTAL');
        $orderDetail['currency_cart_total'] = CommonHelper::displayMoneyFormat($orderDetail['cart_total'], true, false, false);
        $orderDetail['shipping'] = $shippedBySeller?CommonHelper::orderProductAmount($orderDetail, 'SHIPPING'):0;
        $orderDetail['currency_shipping'] = CommonHelper::displayMoneyFormat($orderDetail['shipping'], true, false, false);
        $orderDetail['tax'] = $orderDetail['op_tax_collected_by_seller']?CommonHelper::orderProductAmount($orderDetail, 'TAX'):0;
        $orderDetail['currency_tax'] = CommonHelper::displayMoneyFormat($orderDetail['tax'], true, false, false);
        $orderDetail['volume_discount'] = $orderDetail['op_tax_collected_by_seller']?CommonHelper::orderProductAmount($orderDetail, 'VOLUME_DISCOUNT'):0;
        $orderDetail['currency_volume_discount'] = CommonHelper::displayMoneyFormat($orderDetail['volume_discount'], true, false, false);
        $orderDetail['net_amount'] = CommonHelper::orderProductAmount($orderDetail, 'netamount', false, USER::USER_TYPE_SELLER);
        $orderDetail['currency_net_amount'] = CommonHelper::displayMoneyFormat($orderDetail['net_amount'], true, false, false);
        $orderDetail['order_date_updated'] = ($orderDetail['order_date_updated'] == '0000-00-00 00:00:00')?$orderDetail['order_date_added']:$orderDetail['order_date_updated'];

        $displayForm = 0;
        if (in_array($orderDetail['op_status_id'], $processingStatuses)) {
            $displayForm = 1;
        }

        $api_orders_elements = array('orderDetail'=>$orderDetail,'orderStatuses'=>$orderStsArr,'shippedBySeller'=>$shippedBySeller,'displayForm'=>$displayForm,'yesNoArr'=>applicationConstants::getYesNoArr($this->siteLangId));

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_orders_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function view_subscription_order($ossubs_id)
    {
        $userId = $this->getAppLoggedUserId();
        $op_id =  FatUtility::int($ossubs_id);
        if (1 > $ossubs_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $orderObj = new Orders();
        $orderStatuses = Orders::getOrderSubscriptionStatusArr($this->siteLangId);
        $srch = new OrderSubscriptionSearch($this->siteLangId, true, true);
        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('ossubs_id', '=', $op_id);
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);
        if (!$orderDetail) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $orderDetail['charges'] = $charges;
        $data = array('ossubs_id'=>$ossubs_id , 'ossubs_status_id' => $orderDetail['ossubs_status_id']);
        $api_orders_elements = array('orderDetail'=>$orderDetail,'orderStatuses'=>$orderStatuses,'yesNoArr'=>applicationConstants::getYesNoArr($this->siteLangId));
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_orders_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function change_order_status($op_id)
    {
        $op_id = FatUtility::int($op_id);
        $post = FatApp::getPostedData();
        //$post = array('comments'=>"AAA","op_status_id"=>3,"customer_notified"=>1,'tracking_number'=>"");
        if ((1 > $op_id) || (empty($post['comments'])) || (empty($post['op_status_id']))) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $loggedUserId = $this->getAppLoggedUserId();
        $orderObj = new Orders();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->addCondition('op_selprod_user_id', '=', $loggedUserId);
        $srch->addCondition('op_id', '=', $op_id);

        $rs = $srch->getResultSet();

        $orderDetail = FatApp::getDb()->fetch($rs);
        if (empty($orderDetail)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        if (strtolower($orderDetail['pmethod_code']) == 'cashondelivery') {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, true);
        } else {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses();
        }

        if (in_array($orderDetail["op_status_id"], $processingStatuses) && in_array($post["op_status_id"], $processingStatuses)) {
            if (!$orderObj->addChildProductOrderHistory($op_id, $orderDetail["order_language_id"], $post["op_status_id"], $post["comments"], $post["customer_notified"], $post["tracking_number"])) {
                FatUtility::dieJsonError($orderObj->getError());
            }
        } else {
            FatUtility::dieJsonError(Labels::getLabel('M_ERROR_INVALID_REQUEST', $this->siteLangId));
        }
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>Labels::getLabel('MSG_Updated_Successfully', $this->siteLangId))));
    }

    public function seller_cancel_order($op_id)
    {
        $op_id = FatUtility::int($op_id);
        $post = FatApp::getPostedData();
        if ((1 > $op_id) || (empty($post['comments']))) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $userId = $this->getAppLoggedUserId();

        $orderObj = new Orders();
        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $op_id);
        $rs = $srch->getResultSet();

        $orderDetail = FatApp::getDb()->fetch($rs);

        if (empty($orderDetail)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();
        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);

        if (in_array($orderDetail["op_status_id"], $notAllowedStatues)) {
            FatUtility::dieJsonError(sprintf(Labels::getLabel('LBL_this_order_already', $this->siteLangId), $orderStatuses[$orderDetail["op_status_id"]]));
        }

        if (!$orderObj->addChildProductOrderHistory($op_id, $this->siteLangId, FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"), $post["comments"], true)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_ERROR_INVALID_REQUEST', $this->siteLangId));
        }

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>Labels::getLabel('MSG_Updated_Successfully', $this->siteLangId))));
    }

    public function seller_subscription_orders()
    {
        $userId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addCondition('opcharge_order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderSubscriptionSearch($this->siteLangId, true, true);
        $srch->joinSubscription();
        $srch->joinOrderUser();
        //$srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'oss.ossubs_id = opcc.opcharge_op_id', 'opcc');
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $srch->addOrder("ossubs_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addMultipleFields(
            array('order_id', 'order_user_id','user_autorenew_subscription','ossubs_id','ossubs_type','ossubs_plan_id','order_date_added', 'order_net_amount', 'ossubs_invoice_number','ossubs_subscription_name',  'ossubs_id', 'op_other_charges','ossubs_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name','ossubs_interval','ossubs_frequency','ossubs_till_date','ossubs_status_id','ossubs_from_date','order_language_id')
        );

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');

        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_SELLER_SUBSCRIPTION_STATUS")))) {
            $srch->addStatusCondition($op_status_id);
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_SELLER_SUBSCRIPTION_STATUS")));
        }

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if (!empty($priceFrom)) {
            $srch->addHaving('totOrders', '=', '1');
            $srch->addMinPriceCondition($priceFrom);
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addHaving('totOrders', '=', '1');
            $srch->addMaxPriceCondition($priceTo);
        }
        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['ossubs_id']);
            $order['charges'] = $charges;
        }
        $orderStatuses = Orders::getOrderSubscriptionStatusArr($this->siteLangId);

        $api_orders_elements = array('orders'=>$orders,'total_pages'=>$srch->pages(),'page'=>$page,'total_records'=>$srch->recordCount(),'orderStatuses'=>$orderStatuses);
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_orders_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function submit_return_order_request_message($orrmsg_orrequest_id)
    {
        $user_id = $this->getAppLoggedUserId();
        $orrmsg_orrequest_id = FatUtility::int($orrmsg_orrequest_id);
        $post = FatApp::getPostedData();
        //$post['msg'] = 'My Message will go here 1';
        if ((1 > $orrmsg_orrequest_id) || empty($post['msg'])) {
            FatUtility::dieJsonError(Labels::getLabel('M_ERROR_INVALID_REQUEST', $this->siteLangId));
        }

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->addCondition('orrequest_id', '=', $orrmsg_orrequest_id);
        $cond = $srch->addCondition('orrequest_user_id', '=', $user_id, 'AND');
        $cond->attachCondition('op_selprod_user_id', '=', $user_id, 'OR');
        $srch->joinOrderProducts();
        $srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'orrequest_status'));
        //die($srch->getquery());
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            //die('TT');
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        if ($requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED || $requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_WITHDRAWN) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Message_cannot_be_posted_now,_as_order_is_refunded_or_withdrawn.', $this->siteLangId));
        }

        /* save return request message[ */
        $returnRequestMsgDataToSave = array(
        'orrmsg_orrequest_id'    =>    $requestRow['orrequest_id'],
        'orrmsg_from_user_id'    =>    $user_id,
        'orrmsg_msg'            =>    $post['msg'],
        'orrmsg_date'            =>    date('Y-m-d H:i:s'),
        );
        $oReturnRequestMsgObj = new OrderReturnRequestMessage();
        $oReturnRequestMsgObj->assignValues($returnRequestMsgDataToSave, true);
        if (!$oReturnRequestMsgObj->save()) {
            FatUtility::dieJsonError($oReturnRequestMsgObj->getError());
        }
        $orrmsg_id = $oReturnRequestMsgObj->getMainTableRecordId();
        if (!$orrmsg_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId));
        }
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendReturnRequestMessageNotification($orrmsg_id, $this->siteLangId)) {
            FatUtility::dieJsonError($emailNotificationObj->getError());
        }
        /* ] */

        $userObj = new User($user_id);
        $userInfo = $userObj->getUserInfo(array('user_id','user_name'), true, true);
        $returnRequestMsgDataToSave['msg_user_name'] =  $userInfo['user_name'];

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'msg_data'=>$returnRequestMsgDataToSave,'data'=>Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->siteLangId))));
    }

    public function approve_order_return_request($orrequest_id)
    {
        $user_id = $this->getAppLoggedUserId();
        $orrequest_id = FatUtility::int($orrequest_id);


        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderReturnReasons();

        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('op_selprod_user_id', '=', $user_id);

        $cnd = $srch->addCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING);
        $cnd->attachCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id'));

        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
        }

        $orrObj = new OrderReturnRequest();
        if (!$orrObj->approveRequest($requestRow['orrequest_id'], $user_id, $this->siteLangId)) {
            FatUtility::dieJsonError(Labels::getLabel($orrObj->getError(), $this->siteLangId));
        }

        /* email notification handling[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification($requestRow['orrequest_id'], $this->siteLangId)) {
            FatUtility::dieJsonError(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
        }
        /* ] */
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>Labels::getLabel('MSG_Request_Approved_Refund', $this->siteLangId))));
    }

    public function seller_view_order_return_request($orrequest_id)
    {
        $user_id = $this->getAppLoggedUserId();
        $orrequest_id = FatUtility::int($orrequest_id);

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrderProductSettings();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderReturnReasons();
        $srch->addOrderProductCharges();

        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('op_selprod_user_id', '=', $user_id);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array( 'orrequest_id','orrequest_op_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type',
            'orrequest_date', 'orrequest_status','orrequest_reference',  'op_invoice_number', 'op_selprod_title', 'op_product_name',
            'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_qty',
            'op_unit_price', 'op_selprod_user_id', 'IFNULL(orreason_title, orreason_identifier) as orreason_title','op_shop_id', 'op_shop_name', 'op_shop_owner_name', 'buyer.user_name as buyer_name','order_tax_charged','op_other_charges','op_refund_shipping','op_refund_amount','op_commission_percentage','op_affiliate_commission_percentage','op_commission_include_tax','op_commission_include_shipping','op_free_ship_upto','op_actual_shipping_charges')
        );

        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);

        if (!$request) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $oObj = new Orders();
        $charges = $oObj->getOrderProductChargesArr($request['orrequest_op_id']);
        $request['charges'] = $charges;
        $request['currency_order_tax_charged'] = CommonHelper::displayMoneyFormat($request['order_tax_charged'], true, false, false);
        $request['currency_op_other_charges'] = CommonHelper::displayMoneyFormat($request['op_other_charges'], true, false, false);
        $request['currency_op_refund_amount'] = CommonHelper::displayMoneyFormat($request['op_refund_amount'], true, false, false);
        $returnDataArr = CommonHelper::getOrderProductRefundAmtArr($request);
        $request['net_amount'] = $returnDataArr['op_refund_amount'];
        $request['currency_net_amount'] = CommonHelper::displayMoneyFormat($request['net_amount'], true, false, false);

        $sellerUserObj = new User($request['op_selprod_user_id']);
        $vendorReturnAddress = $sellerUserObj->getUserReturnAddress($this->siteLangId);



        $canEscalateRequest = false;
        $canApproveReturnRequest = false;
        if ($request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING) {
            $canEscalateRequest = true;
        }

        if (($request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING) || $request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED) {
            $canApproveReturnRequest = true;
        }

        if ($attachedFile = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $orrequest_id)) {
            if (file_exists(CONF_UPLOADS_PATH.$attachedFile['afile_physical_path'])) {
                $this->set('attachedFile', $attachedFile);
            }
        }

        $userObj = new User($user_id);
        $srch = $userObj->getUserSearchObj();
        $rs = $srch->getResultSet();
        $user = FatApp::getDb()->fetch($rs, 'user_id');

        $returnRequestTypeArr = OrderReturnRequest::getRequestTypeArr($this->siteLangId);
        $count = 0;
        foreach ($returnRequestTypeArr as $key => $val) {
            $returnRequestTypeDispArr[$count]['key']= $key;
            $returnRequestTypeDispArr[$count]['value']= $val;
            $count++;
        }

        $return_request['canEscalateRequest'] = ($canEscalateRequest)?1:0;
        $return_request['canApproveReturnRequest'] = ($canApproveReturnRequest)?1:0;
        $return_request['request'] = $request;
        $return_request['vendorReturnAddress'] = $vendorReturnAddress;
        $return_request['returnRequestTypeArr']= $returnRequestTypeDispArr;
        $return_request['requestRequestStatusArr']= OrderReturnRequest::getRequestStatusArr($this->siteLangId);
        $return_request['logged_user_name']= $user['user_name'];
        $return_request['logged_user_id']= $this->getAppLoggedUserId();

        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$return_request,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function downloadAttachedFileForReturn($recordId, $recordSubid = 0)
    {
        $recordId = FatUtility::int($recordId);
        if (1 > $recordId) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $recordId, $recordSubid);

        if (false == $file_row) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        if (!file_exists(CONF_UPLOADS_PATH.$file_row['afile_physical_path'])) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

    public function seller_order_return_requests()
    {
        $user_id = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinSellerProducts();

        $srch->addCondition('op_selprod_user_id', '=', $user_id);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(
            array( 'orrequest_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type', 'orrequest_reference', 'orrequest_date', 'orrequest_status',
            'op_invoice_number', 'op_selprod_title', 'op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model','op_selprod_id','selprod_product_id')
        );
        $srch->addOrder('orrequest_date', 'DESC');
        //die($srch->getquery());
        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('op_invoice_number', '=', $keyword);
            $cnd->attachCondition('op_order_id', '=', $keyword);
            $cnd->attachCondition('op_selprod_title', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_product_name', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_brand_name', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_selprod_options', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_selprod_sku', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_product_model', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('orrequest_reference', 'LIKE', '%'.$keyword.'%', 'OR');
        }

        $orrequest_status = FatApp::getPostedData('orrequest_status', null, '-1');
        if ($orrequest_status > -1) {
            $orrequest_status = FatUtility::int($orrequest_status);
            $srch->addCondition('orrequest_status', '=', $orrequest_status);
        }

        $orrequest_type = FatApp::getPostedData('orrequest_type', null, '-1');
        if ($orrequest_type > -1) {
            $orrequest_type = FatUtility::int($orrequest_type);
            $srch->addCondition('orrequest_type', '=', $orrequest_type);
        }

        $orrequest_date_from = FatApp::getPostedData('orrequest_date_from', FatUtility::VAR_DATE, '');
        if (!empty($orrequest_date_from)) {
            $srch->addCondition('orrequest_date', '>=', $orrequest_date_from. ' 00:00:00');
        }

        $orrequest_date_to = FatApp::getPostedData('orrequest_date_to', FatUtility::VAR_DATE, '');
        if (!empty($orrequest_date_to)) {
            $srch->addCondition('orrequest_date', '<=', $orrequest_date_to. ' 23:59:59');
        }

        //echo $srch->getQuery(); die();
        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        foreach ($requests as &$request) {
            $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($request['selprod_product_id'], "MEDIUM", $request['op_selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
            $request['product_image'] =  $mainImgUrl;
        }

        $returnRequestTypeArr = OrderReturnRequest::getRequestTypeArr($this->siteLangId);
        $count = 0;
        foreach ($returnRequestTypeArr as $key => $val) {
            $returnRequestTypeDispArr[$count]['key']= $key;
            $returnRequestTypeDispArr[$count]['value']= $val;
            $count++;
        }

        $api_return_requests_elements = array(
                                            'requests'=>$requests,
                                            'total_pages'=>$srch->pages(),
                                            'total_records'=>$srch->recordCount(),
                                            'returnRequestTypeArr'=>$returnRequestTypeDispArr,
                                            'OrderReturnRequestStatusArr'=>OrderReturnRequest::getRequestStatusArr($this->siteLangId),
                                            'sellerPage'=>true,
                                            'buyerPage'=>false
                                        );
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_return_requests_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount), true));
    }

    public function seller_order_cancellation_requests()
    {
        $user_id = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }

        $srch = new OrderCancelRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrderCancelReasons();
        $srch->joinOrders();
        $srch->addCondition('op_selprod_user_id', '=', $user_id);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(array( 'ocrequest_id', 'ocrequest_date', 'ocrequest_status', 'order_id', 'op_invoice_number', 'IFNULL(ocreason_title, ocreason_identifier) as ocreason_title', 'ocrequest_message'));
        $srch->addOrder('ocrequest_date', 'DESC');

        $op_invoice_number = FatApp::getPostedData('op_invoice_number', null, '');
        if (!empty($op_invoice_number)) {
            $srch->addCondition('op_invoice_number', '=', $op_invoice_number);
        }

        $ocrequest_date_from = FatApp::getPostedData('ocrequest_date_from', FatUtility::VAR_DATE, '');
        if (!empty($ocrequest_date_from)) {
            $srch->addCondition('ocrequest_date', '>=', $ocrequest_date_from. ' 00:00:00');
        }

        $ocrequest_date_to = FatApp::getPostedData('ocrequest_date_to', FatUtility::VAR_DATE, '');
        if (!empty($ocrequest_date_to)) {
            $srch->addCondition('ocrequest_date', '<=', $ocrequest_date_to. ' 23:59:59');
        }

        //$ocrequest_status = $post['ocrequest_status'];
        $ocrequest_status = FatApp::getPostedData('ocrequest_status', null, -1);
        if ($ocrequest_status > -1) {
            $ocrequest_status = FatUtility::int($ocrequest_status);
            $srch->addCondition('ocrequest_status', '=', $ocrequest_status);
        }

        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        $api_cancellation_requests_elements = array('requests'=>$requests,'total_pages'=>$srch->pages(),'total_records'=>$srch->recordCount(),'OrderCancelRequestStatusArr'=>OrderCancelRequest::getRequestStatusArr($this->siteLangId));
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_cancellation_requests_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function seller_products_autocomplete()
    {
        $userId = $this->getAppLoggedUserId();
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE');
        $db = FatApp::getDb();
        $json = array();
        $post = FatApp::getPostedData();

        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.$this->siteLangId, 'p_l');
        $srch->addCondition('selprod_user_id', '=', $userId);
        $srch->addCondition('sp.selprod_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_approved', '=', Product::APPROVED);
        $srch->addOrder('product_name');
        $srch->addOrder('selprod_title');
        $srch->addOrder('selprod_id');
        $srch->addMultipleFields(array( 'selprod_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_price'));
        //$srch->setPageSize( $pageSize );
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
            //$cnd->attachCondition('option_identifier', 'LIKE', '%'. $post['keyword'] . '%', 'OR');
        }

        $rs = $srch->getResultSet();
        $products = $db->fetchAll($rs, 'selprod_id');

        if ($products) {
            foreach ($products as $selprod_id => $product) {
                $options = SellerProduct::getSellerProductOptions($product['selprod_id'], true, $this->siteLangId);

                $variantStr = $product['product_name'];
                //$variantStr .= ( $product['selprod_title'] != '') ? $product['selprod_title'] : $product['product_name'];

                if (is_array($options) && count($options)) {
                    $variantStr .= ' (';
                    $counter = 1;
                    foreach ($options as $op) {
                        $variantStr .= $op['option_name'].': '.$op['optionvalue_name'];
                        if ($counter != count($options)) {
                            $variantStr .= ', ';
                        }
                        $counter++;
                    }
                    $variantStr .= ' )';
                }
                $json[] = array(
                'id'    =>    $selprod_id,
                'value'    =>    strip_tags(html_entity_decode($variantStr, ENT_QUOTES, 'UTF-8')),
                );
            }
        }
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'suggestions' => $json)));
    }

    public function toggle_auto_renewal_subscription()
    {
        $userId = $this->getAppLoggedUserId();
        $status = User::getAttributesById($userId, 'user_autorenew_subscription');
        if ($status) {
            $status = applicationConstants::OFF;
        } else {
            $status = applicationConstants::ON;
        }
        $dataToUpdate = array('user_autorenew_subscription'=>$status);
        $record = new User($userId);
        $record->assignValues($dataToUpdate);

        if (!$record->save()) {
            FatUtility::dieJsonError(Labels::getLabel('M_Unable_to_Process_the_request,Please_try_later', $this->siteLangId));
        }
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>Labels::getLabel('M_Settings_updated_successfully', $this->siteLangId))));
    }

    public function toggleProductFavorite($selprod_id)
    {
        $post = FatApp::getPostedData();
        $product_id = FatUtility::int($selprod_id);
        $loggedUserId = $this->getAppLoggedUserId();
        $db = FatApp::getDb();

        $srch = new ProductSearch($this->siteLangId);
        $srch->setDefinedCriteria(0, 0, array(), false);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array( 'selprod_id'));
        $srch->addCondition('selprod_id', '=', $product_id);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);

        $productRs = $srch->getResultSet();
        $product= $db->fetch($productRs);

        if (!$product) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        $action = 'N'; //nothing happened
        $srch = new UserFavoriteProductSearch();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('ufp_user_id', '=', $loggedUserId);
        $srch->addCondition('ufp_selprod_id', '=', $product_id);
        $rs = $srch->getResultSet();
        if (!$row = $db->fetch($rs)) {
            $prodObj = new Product();
            if (!$prodObj->addUpdateUserFavoriteProduct($loggedUserId, $product_id)) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId));
            }
            $action = 'A'; //Added to favorite
            die($this->json_encode_unicode(array('status'=>1,'msg'=>Labels::getLabel('LBL_Product_has_been_marked_as_favourite_successfully', $this->siteLangId))));
        } else {
            if (!$db->deleteRecords(Product::DB_TBL_PRODUCT_FAVORITE, array('smt'=>'ufp_user_id = ? AND ufp_selprod_id = ?', 'vals'=>array($loggedUserId, $product_id)))) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId));
            }
            $action = 'R'; //Removed from favorite
            die($this->json_encode_unicode(array('status'=>1,'msg'=>Labels::getLabel('LBL_Product_has_been_removed_from_favourite_list', $this->siteLangId))));
        }
    }

    public function toggleShopFavorite($shop_id)
    {
        $post = FatApp::getPostedData();
        $shop_id = FatUtility::int($shop_id);
        $loggedUserId = $this->getAppLoggedUserId();
        $db = FatApp::getDb();

        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(
            array( 'shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city' )
        );
        $srch->addCondition('shop_id', '=', $shop_id);
        //echo $srch->getQuery();
        $shopRs = $srch->getResultSet();
        $shop = $db->fetch($shopRs);

        if (!$shop) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        $action = 'N'; //nothing happened
        $srch = new UserFavoriteShopSearch();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('ufs_user_id', '=', $loggedUserId);
        $srch->addCondition('ufs_shop_id', '=', $shop_id);
        $rs = $srch->getResultSet();
        if (!$row = $db->fetch($rs)) {
            $shopObj = new Shop();
            if (!$shopObj->addUpdateUserFavoriteShop($loggedUserId, $shop_id)) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId));
            }
            $action = 'A'; //Added to favorite
            die($this->json_encode_unicode(array('status'=>1,'action'=>$action,'msg'=>Labels::getLabel('LBL_Shop_is_marked_as_favoutite', $this->siteLangId))));
        } else {
            if (!$db->deleteRecords(Shop::DB_TBL_SHOP_FAVORITE, array('smt'=>'ufs_user_id = ? AND ufs_shop_id = ?', 'vals'=>array($loggedUserId, $shop_id)))) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId));
            }
            $action = 'R'; //Removed from favorite
            die($this->json_encode_unicode(array('status'=>1,'action'=>$action,'msg'=>Labels::getLabel('LBL_Shop_has_been_removed_from_your_favourite_list', $this->siteLangId))));
        }
    }

    public function addRemoveWishListProduct($selprod_id, $wish_list_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $wish_list_id = FatUtility::int($wish_list_id);
        $loggedUserId = $this->getAppLoggedUserId();

        if (!$selprod_id || !$wish_list_id) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $db = FatApp::getDb();
        $wListObj = new UserWishList();
        $srch = UserWishList::getSearchObject($loggedUserId);
        $wListObj->joinWishListProducts($srch);
        $srch->addMultipleFields(array('uwlist_id'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('uwlp_selprod_id', '=', $selprod_id);
        $srch->addCondition('uwlp_uwlist_id', '=', $wish_list_id);

        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        $rs = $srch->getResultSet();


        $action = 'N'; //nothing happened
        if (!$row = $db->fetch($rs)) {
            if (!$wListObj->addUpdateListProducts($wish_list_id, $selprod_id)) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId));
            }
            $action = 'A'; //Added to wishlist
            die($this->json_encode_unicode(array('status'=>1,'msg'=>Labels::getLabel('LBL_Product_Added_in_list_successfully', $this->siteLangId))));
        } else {
            if (!$db->deleteRecords(UserWishList::DB_TBL_LIST_PRODUCTS, array('smt'=>'uwlp_uwlist_id = ? AND uwlp_selprod_id = ?', 'vals'=>array($wish_list_id, $selprod_id)))) {
                Message::addErrorMessage(Labels::getLabel('LBL_Some_problem_occurred,_Please_contact_webmaster', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $action = 'R'; //Removed from wishlist
            die($this->json_encode_unicode(array('status'=>1,'msg'=>Labels::getLabel('LBL_Product_Removed_from_list_successfully', $this->siteLangId))));
        }
    }

    public function searchProductSuggestionsAutocomplete()
    {
        $post = FatApp::getPostedData();
        $json = array();
        $srch = Tag::getSearchObject($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('tag_id', 'IFNULL(tag_name, tag_identifier) as tag_name'));
        $srch->addOrder('tag_name');
        $srch->addCondition('tag_name', 'LIKE', '%'.urldecode($post["keyword"]).'%');
        $rs = $srch->getResultSet();
        $tags = FatApp::getDb()->fetchAll($rs);

        foreach ($tags as $key => $tag) {
            $json[] = array(
            'value'     => strip_tags(html_entity_decode($tag['tag_name'], ENT_QUOTES, 'UTF-8')),
            );
        }
        die($this->json_encode_unicode(array('status'=>1,'suggestions'=>$json)));
    }

    public function faq($catId = '')
    {
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        if ($page < 2) {
            $page = 1;
        }
        $faqMainCat = FatApp::getConfig("CONF_FAQ_PAGE_MAIN_CATEGORY");
        if (!empty($catId) && $catId > 0) {
            $faqCatId = array( $catId );
        } elseif ($faqMainCat) {
            $faqCatId=array($faqMainCat);
        } else {
            $srchFAQCat = FaqCategory::getSearchObject($this->siteLangId);
            $srchFAQCat->setPageSize(1);
            $srchFAQCat->addFld('faqcat_id');
            $srchFAQCat->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
            $srchFAQCat->addCondition('faqcat_type', '=', FaqCategory::FAQ_PAGE);
            $rs = $srchFAQCat->getResultSet();
            $faqCatId = FatApp::getDb()->fetch($rs, 'faqcat_id');
        }
        $srch = FaqCategory::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_faqs', 'LEFT OUTER JOIN', 'faq_faqcat_id = faqcat_id and faq_active = '.applicationConstants::ACTIVE.'  and faq_deleted = '.applicationConstants::NO);
        $srch->joinTable('tbl_faqs_lang', 'LEFT OUTER JOIN', 'faqlang_faq_id = faq_id');
        $srch->addCondition('faqlang_lang_id', '=', $this->siteLangId);
        $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('faqcat_type', '=', FaqCategory::FAQ_PAGE);
        if ($faqCatId) {
            $srch->addCondition('faqcat_id', 'IN', $faqCatId);
        }
        $question = FatApp::getPostedData('question', FatUtility::VAR_STRING, '');
        if (!empty($question)) {
            $srchCondition = $srch->addCondition('faq_title', 'like', "%$question%");
            $srch->doNotLimitRecords();
        }
        if ($pagesize) {
            $srch->setPageSize($pagesize);
        }
        $srch->setPageNumber($page);
        $srch->addOrder('faqcat_display_order', 'asc');
        $srch->addOrder('faq_faqcat_id', 'asc');
        $srch->addOrder('faq_display_order', 'asc');
        //die($srch->getquery());
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        //commonhelper::printarray($records);

        $api_faq_elements = array('faqs'=>$records,'total_pages'=>$srch->pages(),'total_records'=>$srch->recordCount());
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_faq_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function contactus()
    {
        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::CONTACT_US_CONTENT_BLOCK, $this->siteLangId);
        die($this->json_encode_unicode(array('status'=>1,'page_content'=>$pageData)));
    }

    public function contactsubmit()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $email = explode(',', FatApp::getConfig("CONF_CONTACT_EMAIL"));
        foreach ($email as $emailId) {
            $emailId = trim($emailId);
            if (filter_var($emailId, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }
            $email = new EmailHandler();
            if (!$email->sendContactFormEmail($emailId, $this->siteLangId, $post)) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_email_not_sent_server_issue', $this->siteLangId));
            } else {
                $msg = Labels::getLabel('MSG_your_message_sent_successfully', $this->siteLangId);
            }
        }
        die($this->json_encode_unicode(array('status'=>1,'msg'=>$msg)));
    }

    public function requestWithdrawal()
    {
        $post = FatApp::getPostedData();
        $userId = $this->getAppLoggedUserId();
        $balance = User::getUserBalance($userId);
        $lastWithdrawal = User::getUserLastWithdrawalRequest($userId);
        if ($lastWithdrawal && (strtotime($lastWithdrawal["withdrawal_request_date"] . "+".FatApp::getConfig("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")." days") - time()) > 0) {
            $nextWithdrawalDate = date('d M,Y', strtotime($lastWithdrawal["withdrawal_request_date"] . "+".FatApp::getConfig("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")." days"));
            FatUtility::dieJsonError(sprintf(Labels::getLabel('MSG_Withdrawal_Request_Date', $this->siteLangId), FatDate::format($lastWithdrawal["withdrawal_request_date"]), FatDate::format($nextWithdrawalDate), FatApp::getConfig("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")));
        }

        $minimumWithdrawLimit = FatApp::getConfig("CONF_MIN_WITHDRAW_LIMIT");
        if ($balance < $minimumWithdrawLimit) {
            FatUtility::dieJsonError(sprintf(Labels::getLabel('MSG_Withdrawal_Request_Minimum_Balance_Less', $this->siteLangId), CommonHelper::displayMoneyFormat($minimumWithdrawLimit)));
        }

        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        if (($minimumWithdrawLimit > $post["withdrawal_amount"])) {
            FatUtility::dieJsonError(sprintf(Labels::getLabel('MSG_Your_withdrawal_request_amount_is_less_than_the_minimum_allowed_amount_of_%s', $this->siteLangId), CommonHelper::displayMoneyFormat($minimumWithdrawLimit)));
        }

        $maximumWithdrawLimit = FatApp::getConfig("CONF_MAX_WITHDRAW_LIMIT");
        if (($maximumWithdrawLimit < $post["withdrawal_amount"])) {
            FatUtility::dieJsonError(sprintf(Labels::getLabel('MSG_Your_withdrawal_request_amount_is_greater_than_the_maximum_allowed_amount_of_%s', $this->siteLangId), CommonHelper::displayMoneyFormat($maximumWithdrawLimit)));
        }

        if (($post["withdrawal_amount"] > $balance)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Withdrawal_Request_Greater', $this->siteLangId));
        }

        $userObj = new User($userId);
        if (!$userObj->updateBankInfo($post)) {
            FatUtility::dieJsonError($userObj->getError());
        }

        $withdrawal_payment_method = FatApp::getPostedData('uextra_payment_method', FatUtility::VAR_INT, 0);

        $withdrawal_payment_method = ($withdrawal_payment_method > 0 && array_key_exists($withdrawal_payment_method, User::getAffiliatePaymentMethodArr($this->siteLangId))) ? $withdrawal_payment_method  : User::AFFILIATE_PAYMENT_METHOD_BANK;
        $withdrawal_cheque_payee_name = '';
        $withdrawal_paypal_email_id = '';
        $withdrawal_bank = '';
        $withdrawal_account_holder_name = '';
        $withdrawal_account_number = '';
        $withdrawal_ifc_swift_code = '';
        $withdrawal_bank_address = '';
        $withdrawal_comments = $post['withdrawal_comments'];

        switch ($withdrawal_payment_method) {
            case User::AFFILIATE_PAYMENT_METHOD_CHEQUE:
                $withdrawal_cheque_payee_name = $post['uextra_cheque_payee_name'];
                break;

            case User::AFFILIATE_PAYMENT_METHOD_BANK:
                $withdrawal_bank = $post['ub_bank_name'];
                $withdrawal_account_holder_name = $post['ub_account_holder_name'];
                $withdrawal_account_number = $post['ub_account_number'];
                $withdrawal_ifc_swift_code = $post['ub_ifsc_swift_code'];
                $withdrawal_bank_address = $post['ub_bank_address'];

                break;

            case User::AFFILIATE_PAYMENT_METHOD_PAYPAL:
                $withdrawal_paypal_email_id = $post['uextra_paypal_email_id'];
                break;
        }


        $post['withdrawal_payment_method'] = $withdrawal_payment_method;
        $post['withdrawal_cheque_payee_name'] = $withdrawal_cheque_payee_name;
        $post['withdrawal_paypal_email_id'] = $withdrawal_paypal_email_id;

        $post['ub_bank_name'] = $withdrawal_bank;
        $post['ub_account_holder_name'] = $withdrawal_account_holder_name;
        $post['ub_account_number'] = $withdrawal_account_number;
        $post['ub_ifsc_swift_code'] = $withdrawal_ifc_swift_code;
        $post['ub_bank_address'] = $withdrawal_bank_address;

        $post['withdrawal_comments'] = $withdrawal_comments;

        if (!$withdrawRequestId = $userObj->addWithdrawalRequest(array_merge($post, array("ub_user_id"=>$userId)), $this->siteLangId)) {
            FatUtility::dieJsonError($userObj->getError());
        }

        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendWithdrawRequestNotification($withdrawRequestId, $this->siteLangId, "A")) {
            FatUtility::dieJsonError(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
        }
        die($this->json_encode_unicode(array('status'=>1,'msg'=>Labels::getLabel('MSG_Withdraw_request_placed_successfully', $this->siteLangId))));
    }

    public function get_temp_token()
    {
        $user_id = $this->getAppLoggedUserId();
        $uObj=new User($user_id);
        $temp_token = substr(md5(rand(1, 99999) . microtime()), 1, 25);
        if ($uObj->createUserTempToken($temp_token)) {
            die($this->json_encode_unicode(array('status'=>1, 'tkn'=>$temp_token)));
        } else {
            FatUtility::dieJSONError($uObj->getError());
        }
    }

    public function send_to_web()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $is_wallet = $post['is_wallet'];
        $order_id = $post['order_id'];
        if ($is_wallet && empty($order_id)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $user_id = $this->getAppLoggedUserId();
        $uObj=new User($user_id);
        if (isset($post['ttkn'])) {
            $temp_token=$post['ttkn'];
            if (strlen($temp_token) != 25) {
                FatUtility::dieJSONError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            }
            if (!$user_temp_token_data = $uObj->validateAPITempToken($temp_token)) {
                FatUtility::dieJSONError(Labels::getLabel('LBL_Invalid_Temp_Token', $this->siteLangId));
            }
            if (!$user = $uObj->getUserInfo(array('credential_username','credential_password','user_id'), true, true)) {
                FatUtility::dieJSONError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            }
            $authentication = new UserAuthentication();
            if ($authentication->login($user['credential_username'], $user['credential_password'], $_SERVER['REMOTE_ADDR'], false)) {
                if ($uObj->deleteUserAPITempToken()) {
                    if ($is_wallet) {
                        FatApp::redirectUser(CommonHelper::generateUrl('WalletPay', 'Recharge', array($order_id,'api',$this->siteLangId,$this->siteCurrencyId)));
                    } else {
                        FatApp::redirectUser(CommonHelper::generateUrl('checkout', 'index', array('api',$this->siteLangId,$this->siteCurrencyId)));
                    }
                }
            }
        }
    }

    public function brand($brandId)
    {
        $brandId = FatUtility::int($brandId);
        Brand::recordBrandWeightage($brandId);
        $db = FatApp::getDb();

        $this->includeProductPageJsCss();
        $frm = $this->getProductSearchForm();

        $prodSrchObj = new ProductSearch($this->siteLangId);
        $prodSrchObj->setDefinedCriteria();
        $prodSrchObj->joinProductToCategory();
        $prodSrchObj->joinSellerSubscription();
        $prodSrchObj->addSubscriptionValidCondition();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->doNotLimitRecords();
        $prodSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrchObj->addGroupBy('selprod_id');


        if ($brandId > 0) {
            $prodSrchObj->addBrandCondition($brandId);
        }
        $rs = $prodSrchObj->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        /* Brand Filters Data[ */
        $brandSrch = Brand::getListingObj($this->siteLangId, array( 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name', 'brand_short_description'));
        $brandSrch->doNotCalculateRecords();
        $brandSrch->doNotLimitRecords();
        $brandSrch->addCondition('brand_id', '=', $brandId);
        $brandRs = $brandSrch->getResultSet();
        $brandsArr = $db->fetchAll($brandRs);


        /* Condition filters data[ */
        $conditionSrch = clone $prodSrchObj;
        $conditionSrch->addGroupBy('selprod_condition');
        $conditionSrch->removGroupBy('selprod_id');
        $conditionSrch->addOrder('selprod_condition');
        $conditionSrch->addMultipleFields(array('selprod_condition'));
        //echo $conditionSrch->getQuery(); die();
        /* if needs to show product counts under any condition[ */
        //$conditionSrch->addFld('count(selprod_condition) as totalProducts');
        /* ] */
        $conditionRs = $conditionSrch->getResultSet();
        $conditionsArr = $db->fetchAll($conditionRs);
        /* ] */


        /* Price Filters[ */
        $priceSrch = clone $prodSrchObj;
        $priceSrch->addMultipleFields(array('MIN(theprice) as minPrice', 'MAX(theprice) as maxPrice'));
        $qry = $priceSrch->getQuery();
        $qry .= ' having minPrice IS NOT NULL AND maxPrice IS NOT NULL';
        //echo $qry; die();
        //$priceRs = $priceSrch->getResultSet();
        $priceRs = $db->query($qry);
        $priceArr = $db->fetch($priceRs);
        /* ] */


        /* Categories Data[ */
        //echo $prodSrchObj->getQuery();die();
        $catSrch = clone $prodSrchObj;
        $catSrch->addGroupBy('prodcat_code');
        //$categoriesArr = productCategory::getProdCatParentChildWiseArr( $this->siteLangId, 0, true, false, false, $catSrch );

        $productCatObj = new ProductCategory;
        $productCategories =  $productCatObj->getCategoriesForSelectBox($this->siteLangId);

        $categoriesArr = ProductCategory::getProdCatParentChildWiseArr($this->siteLangId, 0, false, false, false, $catSrch, true);


        usort(
            $categoriesArr,
            function ($a, $b) {
                return $a['prodcat_code'] - $b['prodcat_code'];
            }
        );

        /* ] */

        $productFiltersArr = array(
        'categoriesArr'        =>    $categoriesArr,
        'productCategories'        =>    $productCategories,
        'shopCatFilters'        =>    true,
        'brandsArr'            =>    $brandsArr,
        'brandsCheckedArr'    =>    array($brandId),
        'conditionsArr'        =>    $conditionsArr,
        'priceArr'            =>    $priceArr,
        'siteLangId'        =>    $this->siteLangId
        );

        $brandData = array();
        $brandData = array_shift($brandsArr);



        /*commonhelper::printarray($categoryData);
        die();*/
        //CommonHelper::printArray(array('status'=>1 ,'data'=>$api_home_page_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->user_details['favItems'],'unread_messages'=>$this->user_details['unreadMessages']));
        $api_brand_page_elements = array('brandData'=>$brandData,'product_filters'=>$productFiltersArr);
        //commonhelper::printarray($api_brand_page_elements);
        //die();
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=>$api_brand_page_elements,'cart_count'=>$this->cart_items,'fav_count'=>$this->totalFavouriteItems,'unread_messages'=>$this->totalUnreadMessageCount)));
    }

    public function EscalateOrderReturnRequest($orrequest_id)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        if (!$orrequest_id) {
            FatUtility::dieJSONError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $user_id = $this->getAppLoggedUserId();
        $srch = new OrderReturnRequestSearch();
        $srch->joinOrderProducts();
        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING);
        $srch->addCondition('op_selprod_user_id', '=', $user_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'orrequest_user_id'));
        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);
        if (!$request || $request['orrequest_id'] != $orrequest_id) {
            FatUtility::dieJSONError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $userObj=new User($user_id);
        $user = $userObj->getUserInfo(array('credential_username','credential_password','user_preferred_dashboard'), false, false);
        if (!$user) {
            FatUtility::dieJSONError($this->str_invalid_request);
        }
        $userAuthObj = new UserAuthentication();
        if (!$userAuthObj->login($user['credential_username'], $user['credential_password'], $_SERVER['REMOTE_ADDR'], false, true) === true) {
            FatUtility::dieJSONError($userObj->getError());
        }
        /* buyer cannot escalate request[ */
        // if( $user_id == $request['orrequest_user_id'] ){

        if (!User::isSeller()) {
            FatUtility::dieJSONError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        /* ] */

        //die('abc');
        $orrObj = new OrderReturnRequest();
        if (!$orrObj->escalateRequest($request['orrequest_id'], $user_id, $this->siteLangId)) {
            FatUtility::dieJSONError(Labels::getLabel($orrObj->getError(), $this->siteLangId));
        }

        /* email notification handling[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification($orrequest_id, $this->siteLangId)) {
            FatUtility::dieJSONError(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
        }
        /* ] */
        die($this->json_encode_unicode(array('status'=>1,'msg'=>Labels::getLabel('MSG_Your_request_sent', $this->siteLangId))));
    }

    public function markReviewHelpful($reviewId)
    {
        $reviewId = FatUtility::int($reviewId);
        $isHelpful = FatApp::getPostedData('isHelpful', FatUtility::VAR_INT, 0);
        if ($reviewId <= 0) {
            FatUtility::dieJSONError(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
        }
        $userId = $this->getAppLoggedUserId();
        $tblRecObj = new SelProdReviewHelpful();
        $tblRecObj->assignValues(array('sprh_spreview_id'=>$reviewId , 'sprh_user_id'=>$userId, 'sprh_helpful'=>$isHelpful));
        if (!$tblRecObj->addNew(array(), array('sprh_helpful'=>$isHelpful))) {
            FatUtility::dieJSONError($tblRecObj->getError());
        }
        die($this->json_encode_unicode(array('status'=>1,'msg'=>Labels::getLabel('Msg_Successfully_Updated', $this->siteLangId))));
    }

    public function report_shop_spam_reasons()
    {
        $orderCancelReasonsArr = ShopReportReason::getReportReasonArr($this->siteLangId);
        $count = 0;
        foreach ($orderCancelReasonsArr as $key => $val) {
            $cancelReasonsArr[$count]['key']= $key;
            $cancelReasonsArr[$count]['value']= $val;
            $count++;
        }
        die($this->json_encode_unicode(array('status'=>1, 'reasons'=>$cancelReasonsArr)));
    }

    public function submitShopReportSpam($shop_id)
    {
        $shop_id = FatUtility::int($shop_id);
        $loggedUserId = $this->getAppLoggedUserId();
        $post = FatApp::getPostedData();
        if ((1 > $shop_id) || empty($post['sreport_reportreason_id']) || empty($post['sreport_message'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }


        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array( 'shop_id', 'shop_user_id'));
        $srch->addCondition('shop_id', '=', $shop_id);
        $shopRs = $srch->getResultSet();
        $shopData = FatApp::getDb()->fetch($shopRs);
        if (!$shopData) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
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
            Message::addErrorMessage(Labels::getLabel($sReportObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $sreport_id = $sReportObj->getMainTableRecordId();

        if (!$sreport_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        /* email notification[ */
        if ($sreport_id) {
            $emailObj = new EmailHandler();
            $emailObj->sendShopReportNotification($sreport_id, $this->siteLangId);
        }
        /* ] */

        $sucessMsg = Labels::getLabel('MSG_Your_report_sent_review!', $this->siteLangId);
        die($this->json_encode_unicode(array('status'=>1,'msg'=>$sucessMsg)));
    }

    public function shopinfo($shop_id)
    {
        $shop_id = FatUtility::int($shop_id);
        $shop = $this->getShopInfo($shop_id);
        if (!$shop) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        die($this->json_encode_unicode(array('status'=>1, 'data'=>$shop)));
    }

    public function setUpWalletRecharge()
    {
        $minimumRechargeAmount = 1;
        $post = FatApp::getPostedData();
        $loggedUserId = $this->getAppLoggedUserId();
        if (false === $post) {
            FatUtility::dieJSONError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $order_net_amount = $post['amount'];
        if ($order_net_amount < $minimumRechargeAmount) {
            $str = Labels::getLabel("LBL_Recharge_amount_must_be_greater_than_{minimumrechargeamount}", $this->siteLangId);
            $str = str_replace("{minimumrechargeamount}", CommonHelper::displayMoneyFormat($minimumRechargeAmount, true, true), $str);
            FatUtility::dieJSONError($str);
        }
        $orderData = array();
        $order_id = isset($_SESSION['wallet_recharge_cart']["order_id"]) ? $_SESSION['wallet_recharge_cart']["order_id"] : false;
        $orderData['order_type']= Orders::ORDER_WALLET_RECHARGE;

        $orderData['userAddresses'] = array(); //No Need of it
        $orderData['order_id'] = $order_id;
        $orderData['order_user_id'] = $loggedUserId;
        $orderData['order_is_paid'] = Orders::ORDER_IS_PENDING;
        $orderData['order_date_added'] = date('Y-m-d H:i:s');

        /* order extras[ */
        $orderData['extra'] = array(
        'oextra_order_id'    =>    $order_id,
        'order_ip_address'    =>    $_SERVER['REMOTE_ADDR']
        );

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $orderData['extra']['order_forwarded_ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $orderData['extra']['order_forwarded_ip'] = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $orderData['extra']['order_forwarded_ip'] = '';
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $orderData['extra']['order_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $orderData['extra']['order_user_agent'] = '';
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $orderData['extra']['order_accept_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $orderData['extra']['order_accept_language'] = '';
        }
        /* ] */

        $languageRow = Language::getAttributesById($this->siteLangId);
        $orderData['order_language_id'] =  $languageRow['language_id'];
        $orderData['order_language_code'] =  $languageRow['language_code'];

        $currencyRow = Currency::getAttributesById($this->siteCurrencyId);
        $orderData['order_currency_id'] =  $currencyRow['currency_id'];
        $orderData['order_currency_code'] =  $currencyRow['currency_code'];
        $orderData['order_currency_value'] =  $currencyRow['currency_value'];

        $orderData['order_user_comments'] =  '';
        $orderData['order_admin_comments'] =  '';

        $orderData['order_shippingapi_id'] = 0;
        $orderData['order_shippingapi_code'] = '';
        $orderData['order_tax_charged'] = 0;
        $orderData['order_site_commission'] = 0;
        $orderData['order_net_amount'] = $order_net_amount;
        $orderData['order_wallet_amount_charge'] = 0;

        $orderData['orderLangData'] = array();
        $orderObj = new Orders();
        if ($orderObj->addUpdateOrder($orderData, $this->siteLangId)) {
            $order_id = $orderObj->getOrderId();
        } else {
            FatUtility::dieJSONError($orderObj->getError());
        }
        die($this->json_encode_unicode(array('status'=>1, 'order_id'=>$order_id)));
        /*$this->set( 'redirectUrl', CommonHelper::generateUrl('WalletPay', 'Recharge', array($order_id)) );
        $this->set('msg', Labels::getLabel('MSG_Redirecting',$this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');*/
    }

    public function privacy_policy()
    {
        $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
        $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_PRIVACY_POLICY_PAGE', FatUtility::VAR_INT, 0));
        $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
        die(
            $this->json_encode_unicode(
                array(
                'status'=>1,
                'title'=>$cpage['cpage_title'],
                'content'=>$cpage['cpage_content'],
                'web_url'=>CommonHelper::generateFullUrl('Cms', 'view', array($cpage['cpage_id'],true)),
                )
            )
        );
    }

    public function terms_conditions()
    {
        $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
        $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_TERMS_AND_CONDITIONS_PAGE', FatUtility::VAR_INT, 0));
        $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
        die(
            $this->json_encode_unicode(
                array(
                'status'=>1,
                'title'=>$cpage['cpage_title'],
                'content'=>$cpage['cpage_content'],
                'web_url'=>CommonHelper::generateFullUrl('Cms', 'view', array($cpage['cpage_id'])),
                )
            )
        );
    }

    public function about_us()
    {
        $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
        $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_ABOUT_US_PAGE', FatUtility::VAR_INT, 0));
        $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
        die(
            $this->json_encode_unicode(
                array(
                'status'=>1,
                'title'=>$cpage['cpage_title'],
                'content'=>$cpage['cpage_content'],
                'web_url'=>CommonHelper::generateFullUrl('Cms', 'view', array($cpage['cpage_id'])),
                )
            )
        );
    }

    public function setUserPushNotificationToken($fcmDeviceId)
    {
        if (empty($fcmDeviceId)) {
            FatUtility::dieJSONError(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
        }
        $userId = $this->getAppLoggedUserId();
        $uObj= new User($userId);
        if (!$uObj->setPushNotificationToken($this->appToken, $fcmDeviceId)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        die($this->json_encode_unicode(array('status'=>1,'msg'=>Labels::getLabel('Msg_Successfully_Updated', $this->siteLangId))));
    }

    public function markNotificationRead($notificationId)
    {
        if ($notificationId <= 0) {
            FatUtility::dieJSONError(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
        }
        $userId = $this->getAppLoggedUserId();

        $srch = Notifications::getSearchObject();
        $srch->addCondition('unt.unotification_user_id', '=', $userId);
        $srch->addCondition('unt.unotification_id', '=', $notificationId);
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $notification = FatApp::getDb()->fetch($rs);
        if (!($notification)) {
            FatUtility::dieJSONError(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
        }
        $nObj = new Notifications();
        if ($nObj->readUserNotification($notificationId, $userId)) {
            die(json_encode(array('status'=>1, 'msg'=>Labels::getLabel('Msg_Successfully_Updated', $this->siteLangId))));
        } else {
            FatUtility::dieJsonError(Labels::getLabel('M_ERROR_INVALID_REQUEST', $this->siteLangId));
        }
    }

    public function notifications()
    {
        $userId = $this->getAppLoggedUserId();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        $srch = Notifications::getSearchObject();
        $srch->addCondition('unt.unotification_user_id', '=', $userId);
        $srch->addOrder('unt.unotification_id', 'DESC');
        $srch->addMultipleFields(array('unt.*'));
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $api_notification_elements = array('records'=>$records,'total_pages'=>$srch->pages(),'total_records'=>$srch->recordCount());
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=> $api_notification_elements)));
    }

    public function downloads()
    {
        $userId = $this->getAppLoggedUserId();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, $this->pagesize);
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->joinDigitalDownloads();
        $srch->addDigitalDownloadCondition();
        $srch->addMultipleFields(array('op_selprod_id','selprod_product_id','op_id','op_invoice_number','order_user_id','op_product_type','order_date_added','op_qty','op_status_id','op_selprod_max_download_times','op_selprod_download_validity_in_days','opa.*'));
        $srch->setPageNumber($page);
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addOrder('order_date_added', 'desc');
        $srch->addOrder('afile_id', 'asc');
        $srch->setPageSize($pagesize);
        //die($srch->getquery());
        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->addKeywordSearch($keyword);
        }

        $rs = $srch->getResultSet();
        $downloads = FatApp::getDb()->fetchAll($rs);
        $digitalDownloads = Orders::digitalDownloadFormat($downloads, $this->siteLangId);
        $api_download_elements = array('records'=>$digitalDownloads,'total_pages'=>$srch->pages(),'total_records'=>$srch->recordCount());
        die($this->json_encode_unicode(array('status'=>1,'currencySymbol'=>$this->currencySymbol,'unread_notifications'=>$this->totalUnreadNotificationCount,'data'=> $digitalDownloads)));
    }

    public function downloadDigitalFile($aFileId, $recordId = 0)
    {
        $aFileId = FatUtility::int($aFileId);
        $recordId = FatUtility::int($recordId);
        $userId = $this->getAppLoggedUserId();

        if (1 > $aFileId || 1 > $recordId) {
            dieJsonError(Utilities::getLabel('M_ERROR_INVALID_REQUEST', $this->siteLangId));
        }

        $digitalDownloads = Orders::getOrderProductDigitalDownloads($recordId, $aFileId);

        if ($digitalDownloads == false || empty($digitalDownloads) || $digitalDownloads[0]['order_user_id']!= $userId) {
            dieJsonError(Utilities::getLabel('MSG_INVALID_ACCESS', $this->siteLangId));
        }

        $res = array_shift($digitalDownloads);

        if ($res == false || !$res['downloadable']) {
            dieJsonError(Utilities::getLabel('MSG_Not_available_to_download', $this->siteLangId));
        }

        if (!file_exists(CONF_UPLOADS_PATH.$res['afile_physical_path'])) {
            dieJsonError(Utilities::getLabel('LBL_File_not_found', $this->siteLangId));
        }

        $fileName = isset($res['afile_physical_path']) ? $res['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $res['afile_name']);
        AttachedFile::updateDownloadCount($res['afile_id']);
    }

    private function getShopInfo($shop_id)
    {
        $db = FatApp::getDb();
        $shop_id = FatUtility::int($shop_id);
        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->doNotCalculateRecords();

        $srch->addMultipleFields(
            array( 'shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_payment_policy', 'shop_delivery_policy', 'shop_refund_policy', 'shop_additional_info', 'shop_seller_info',
            'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city','u.user_name as shop_owner_name', 'u_cred.credential_username as shop_owner_username' )
        );

        $srch->addCondition('shop_id', '=', $shop_id);
        $shopRs = $srch->getResultSet();
        return $shop = $db->fetch($shopRs);
    }

    private function userEmailVerification($userObj, $data)
    {
        $verificationCode = $userObj->prepareUserVerificationCode();
        $link = FatUtility::generateFullUrl('GuestUser', 'userCheckEmailVerification', array('verify'=>$verificationCode));
        $data = array(
            'user_name' => $data['user_name'],
            'link' => $link,
        'user_email' => $data['user_email'],
        'user_id' => $data['user_id'],
        );
        $email = new EmailHandler();
        if (!$email->sendSignupVerificationLink($this->siteLangId, $data)) {
            return false;
        }
        return true;
    }

    private function userWelcomeEmailRegistration($userObj, $data)
    {
        $link = FatUtility::generateFullUrl('GuestUser', 'loginForm');
        $data = array(
            'user_name' => $data['user_name'],
        'user_email' => $data['user_email'],
        'link' => $link,
        );
        $email = new EmailHandler();
        if (!$email->sendWelcomeEmail($this->siteLangId, $data)) {
            return false;
        }
        return true;
    }

    private function getAppLoggedUserId()
    {
        return isset($this->app_user["user_id"])?$this->app_user["user_id"]:0;
    }
}
