<?php
class HomeController extends MyAppController
{
    public function index()
    {
        $db = FatApp::getDb();
        $loggedUserId = UserAuthentication::getLoggedUserId(true);

        $productSrchObj = $this->getProductSearchObj($loggedUserId);
        $collections = $this->getCollections($productSrchObj);
        $sponsoredShops = $this->getSponsoredShops($productSrchObj);
        $sponsoredProds = $this->getSponsoredProducts($productSrchObj);
        $slides = $this->getSlides();
        $banners = $this->getBanners();

        $this->set('sponsoredProds', $sponsoredProds);
        $this->set('sponsoredShops', $sponsoredShops);
        $this->set('slides', $slides);
        $this->set('banners', $banners);
        $this->set('collections', $collections);

        if (true ===  MOBILE_APP_API_CALL) {
            $orderProducts['pendingForReviews'] = array();
            if (0 < $loggedUserId && (FatApp::getConfig('CONF_ALLOW_REVIEWS', FatUtility::VAR_INT, 0))) {
                $orderProducts['pendingForReviews'] = OrderProduct::pendingForReviews($loggedUserId, $this->siteLangId);
                if (count($orderProducts['pendingForReviews'])) {
                    foreach ($orderProducts['pendingForReviews'] as $key => $orderProduct) {
                        $canSubmitFeedback = Orders::canSubmitFeedback($orderProduct['order_user_id'], $orderProduct['order_id'], $orderProduct['op_selprod_id']);
                        if (false === $canSubmitFeedback) {
                            continue;
                        }
                        $options = SellerProduct::getSellerProductOptions($orderProduct['op_selprod_id'], true, $this->siteLangId);
                        $optionTitle = '';
                        if (is_array($options) && count($options)) {
                            foreach ($options as $op) {
                                $optionTitle .= $op['option_name'].': '.$op['optionvalue_name'].', ';
                            }
                        }
                        $orderProducts['pendingForReviews'][$key]['optionsTitle'] = rtrim($optionTitle, ', ');
                        $orderProducts['pendingForReviews'][$key]['product_image_url'] = CommonHelper::generateFullUrl('image', 'product', array($orderProduct['selprod_product_id'], "THUMB", $orderProduct['op_selprod_id'], 0, $this->siteLangId));
                    }
                }
            }
            $this->set('layoutType', Collections::getLayoutTypeArr($this->siteLangId));
            $this->set('orderProducts', $orderProducts);
        } else {
            $this->_template->addJs(array('js/slick.min.js', 'js/responsive-img.min.js'));
            $this->_template->addCss(array('css/slick.css', 'css/product-detail.css'));
            $cacheKey = $this->siteLangId.'-'.$this->siteCurrencyId;

            /*[ As all layout in sequence so added in one cache]*/
            $homePageFirstLayout =  FatCache::get('homePageFirstLayout'.$cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if (!$homePageFirstLayout) {
                $homePageProdLayout1 = '';
                if (isset($collections[Collections::TYPE_PRODUCT_LAYOUT1])) {
                    $tpl = new FatTemplate('', '');
                    $tpl->set('siteLangId', $this->siteLangId);
                    $tpl->set('collections', $collections[Collections::TYPE_PRODUCT_LAYOUT1]);
                    $homePageProdLayout1 = $this->_template->render(false, false, '_partial/collection/product-layout-1.php', true, true);
                }

                $homePageCatLayout1 = '';
                if (isset($collections[Collections::TYPE_CATEGORY_LAYOUT1])) {
                    $tpl = new FatTemplate('', '');
                    $tpl->set('siteLangId', $this->siteLangId);
                    $tpl->set('collections', $collections[Collections::TYPE_CATEGORY_LAYOUT1]);
                    $homePageCatLayout1 = $tpl->render(false, false, '_partial/collection/category-layout-1.php', true, true);
                }

                $homePageFirstLayout = $homePageProdLayout1.$homePageCatLayout1;
                FatCache::set('homePageFirstLayout'.$cacheKey, $homePageFirstLayout, '.txt');
            }

            $this->set('homePageFirstLayout', $homePageFirstLayout);
            /*]*/

            /* Product Layout2[ */
            $homePageProdLayout2 =  FatCache::get('homePageProdLayout2'.$cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if (!$homePageProdLayout2 && (isset($collections[Collections::TYPE_PRODUCT_LAYOUT2]))) {
                $tpl = new FatTemplate('', '');
                $tpl->set('siteLangId', $this->siteLangId);
                $tpl->set('collections', $collections[Collections::TYPE_PRODUCT_LAYOUT2]);
                $homePageProdLayout2 = $tpl->render(false, false, '_partial/collection/product-layout-2.php', true, true);
                FatCache::set('homePageProdLayout2'.$cacheKey, $homePageProdLayout2, '.txt');
            }
            $this->set('homePageProdLayout2', $homePageProdLayout2);
            /* ] */

            /* Shop Layout1[ */
            $homePageShopLayout1 =  FatCache::get('homePageShopLayout1'.$cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if (!$homePageShopLayout1 && (isset($collections[Collections::TYPE_SHOP_LAYOUT1]))) {
                $tpl = new FatTemplate('', '');
                $tpl->set('siteLangId', $this->siteLangId);
                $tpl->set('collections', $collections[Collections::TYPE_SHOP_LAYOUT1]);
                $homePageShopLayout1 = $tpl->render(false, false, '_partial/collection/shop-layout-1.php', true, true);
                FatCache::set('homePageShopLayout1'.$cacheKey, $homePageShopLayout1, '.txt');
            }
            $this->set('homePageShopLayout1', $homePageShopLayout1);
            /* ] */

            /*[ As all layout in sequence so added in one cache]*/
            $homePageFooterLayout =  FatCache::get('homePageFooterLayout'.$cacheKey, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if (!$homePageFooterLayout) {
                $homePageCatLayout2 = '';
                if (isset($collections[Collections::TYPE_CATEGORY_LAYOUT2])) {
                    $tpl = new FatTemplate('', '');
                    $tpl->set('siteLangId', $this->siteLangId);
                    $tpl->set('collections', $collections[Collections::TYPE_CATEGORY_LAYOUT2]);
                    $homePageCatLayout2 = $tpl->render(false, false, '_partial/collection/category-layout-2.php', true, true);
                }

                $homePageProdLayout3 = '';
                if (isset($collections[Collections::TYPE_PRODUCT_LAYOUT3])) {
                    $tpl = new FatTemplate('', '');
                    $tpl->set('siteLangId', $this->siteLangId);
                    $tpl->set('collections', $collections[Collections::TYPE_PRODUCT_LAYOUT3]);
                    $homePageProdLayout3 = $tpl->render(false, false, '_partial/collection/product-layout-3.php', true, true);
                }

                $homePageBrandLayout1 = '';
                if (isset($collections[Collections::TYPE_BRAND_LAYOUT1])) {
                    $tpl = new FatTemplate('', '');
                    $tpl->set('siteLangId', $this->siteLangId);
                    $tpl->set('collections', $collections[Collections::TYPE_BRAND_LAYOUT1]);
                    $homePageBrandLayout1 = $tpl->render(false, false, '_partial/collection/brand-layout-1.php', true, true);
                }

                $homePageFooterLayout = $homePageCatLayout2.$homePageProdLayout3.$homePageBrandLayout1;
                FatCache::set('homePageFooterLayout'.$cacheKey, $homePageFooterLayout, '.txt');
            }
            $this->set('homePageFooterLayout', $homePageFooterLayout);
        }

        $this->_template->render();
    }

    private function getProductSearchObj($loggedUserId)
    {
        $loggedUserId = FatUtility::int($loggedUserId);

        $productSrchObj = new ProductSearch($this->siteLangId);
        $productSrchObj->joinProductToCategory();
        /* $productSrchObj->doNotCalculateRecords();
        $productSrchObj->setPageSize( 10 ); */
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
        $productSrchObj->addMultipleFields(array('product_id','selprod_id','IFNULL(product_name, product_identifier) as product_name','IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title','product_image_updated_on','special_price_found', 'splprice_display_list_price','splprice_display_dis_val','splprice_display_dis_type','theprice','selprod_price','selprod_stock','selprod_condition','prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','selprod_sold_count','IF(selprod_stock > 0, 1, 0) AS in_stock'));
        return $productSrchObj;
    }

    public function languages()
    {
        $languages = Language::getAllNames(false);
        $languageArr = array();
        if (0 < count($languages)) {
            $siteDefaultLangId = FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1);
            foreach ($languages as &$language) {
                $language['isSiteDefaultLang'] = ($language['language_id'] === $siteDefaultLangId) ? 1 : 0;
                $languageArr[] = $language;
            }
        }
        $this->set('languages', $languageArr);
        $this->_template->render();
    }

    public function setLanguage($langId = 0)
    {
        if (!FatUtility::isAjaxCall()) {
            die('Invalid Action.');
        }

        $langId =  FatUtility::int($langId);
        if (0 < $langId) {
            $languages = Language::getAllNames();
            if (array_key_exists($langId, $languages)) {
                setcookie('defaultSiteLang', $langId, time()+3600*24*10, CONF_WEBROOT_URL);
            }
        }
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
        $this->set('currencies', $currencies);
        $this->_template->render();
    }

    public function setCurrency($currencyId = 0)
    {
        if (!FatUtility::isAjaxCall()) {
            die('Invalid Action.');
        }

        $currencyId =  FatUtility::int($currencyId);
        $currencyObj = new Currency();
        if (0 < $currencyId) {
            $currencies = Currency::getCurrencyAssoc($this->siteLangId);
            if (array_key_exists($currencyId, $currencies)) {
                setcookie('defaultSiteCurrency', $currencyId, time()+3600*24*10, CONF_WEBROOT_URL);
            }
        }
    }

    public function languageLabels($download = 0, $langId = 0)
    {
        $langId = FatUtility::int($langId) > 0 ? $langId : $this->siteLangId;
        $download = FatUtility::int($download);
        $langCode = Language::getAttributesById($langId, 'language_code', false);

        if (0 < $download) {
            if (!Labels::updateDataToFile($langId, $langCode, Labels::TYPE_APP)) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Unable_to_update_file', $langId));
            }
            $fileName = $langCode.'.json';
            $filePath = Labels::JSON_FILE_DIR_NAME.'/'.Labels::TYPE_APP.'/'.$fileName;

            AttachedFile::downloadAttachment($filePath, $fileName);
            exit;
        }

        $data = array(
           'languageCode'=>$langCode,
           'downloadUrl' => CommonHelper::generateFullUrl('Home', 'languageLabels', array(1, $langId)),
           'langLabelUpdatedAt' => FatApp::getConfig('CONF_LANG_LABELS_UPDATED_AT', FatUtility::VAR_INT, time())
        );

        $this->set('data', $data);
        $this->_template->render();
    }

    public function setCurrentLocation()
    {
        $post = FatApp::getPostedData();

        $countryCode = $post['country'];
        $this->updateSettingByCurrentLocation($countryCode);

        if (!$_SESSION['geo_location']) {
            Message::addErrorMessage(Labels::getLabel('MSG_Current_Location', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Settings_with_your_current_location_setup_successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateSettingByCurrentLocation($countryCode = '')
    {
        if (!$countryCode) {
            return ;
        }

        $row = Countries::getCountryByCode($countryCode, array('country_code','country_id','country_currency_id','country_language_id'));
        if ($row == false) {
            return false;
        }
        $_SESSION['geo_location'] = true;
        $this->setCurrency($row['country_currency_id']);
        $this->setLanguage($row['country_language_id']);
    }

    public function affiliateReferral($referralCode)
    {
        $userSrchObj = User::getSearchObject();
        $userSrchObj->doNotCalculateRecords();
        $userSrchObj->doNotLimitRecords();
        $userSrchObj->addCondition('user_referral_code', '=', $referralCode);
        $userSrchObj->addMultipleFields(array('user_id', 'user_referral_code' ));
        $rs = $userSrchObj->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if ($row && $referralCode != '' && $row['user_referral_code'] == $referralCode) {
            $cookieExpiryDays = FatApp::getConfig("CONF_AFFILIATE_REFERRER_URL_VALIDITY", FatUtility::VAR_INT, 5);

            $cookieValue = array('data' => $row['user_referral_code'], 'creation_time' => time());
            $cookieValue = serialize($cookieValue);
            CommonHelper::setCookie('affiliate_referrer_code_signup', $cookieValue, time()+3600*24*$cookieExpiryDays);
        }
        FatApp::redirectUser(CommonHelper::generateUrl());
    }

    public function referral($userReferralCode)
    {
        $userSrchObj = User::getSearchObject();
        $userSrchObj->doNotCalculateRecords();
        $userSrchObj->doNotLimitRecords();
        $userSrchObj->addCondition('user_referral_code', '=', $userReferralCode);
        $userSrchObj->addMultipleFields(array('user_id', 'user_referral_code' ));
        $rs = $userSrchObj->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if ($row && $userReferralCode != '' && $row['user_referral_code'] == $userReferralCode) {
            $cookieExpiryDays = FatApp::getConfig("CONF_REFERRER_URL_VALIDITY", FatUtility::VAR_INT, 10);

            $cookieValue = array('data' => $row['user_referral_code'], 'creation_time' => time());
            $cookieValue = serialize($cookieValue);

            CommonHelper::setCookie('referrer_code_signup', $cookieValue, time()+3600*24*$cookieExpiryDays);
            CommonHelper::setCookie('referrer_code_checkout', $row['user_referral_code'], time()+3600*24*$cookieExpiryDays);
        }
        FatApp::redirectUser(CommonHelper::generateUrl());
    }

    private function getCollections($productSrchObj)
    {
        $langId = $this->siteLangId;

        $apiCall = (true === MOBILE_APP_API_CALL) ? 1 : 0;
        $collectionCache =  FatCache::get('collectionCache_'.$langId.'_'.$apiCall, CONF_HOME_PAGE_CACHE_TIME, '.txt');

        if ($collectionCache) {
            return  unserialize($collectionCache);
        }

        $db = FatApp::getDb();

        $srch = new CollectionSearch($langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('collection_display_order', 'ASC');
        $srch->addMultipleFields(array('collection_id', 'IFNULL(collection_name,collection_identifier) as collection_name','IFNULL( collection_description, "" ) as collection_description','IFNULL(collection_link_caption, "") as collection_link_caption','collection_link_url', 'collection_layout_type','collection_type','collection_criteria','collection_child_records','collection_primary_records', 'collection_display_media_only'));
        $rs = $srch->getResultSet();
        $collectionsArr = $db->fetchAll($rs, 'collection_id');
        if (empty($collectionsArr)) {
            return array();
        }
        $collections = array();

        $productCatSrchObj = ProductCategory::getSearchObject(false, $langId);
        $productCatSrchObj->doNotCalculateRecords();
        $productCatSrchObj->addMultipleFields(array('prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','prodcat_description'));

        $collectionObj = new CollectionSearch();
        $i = 0;
        foreach ($collectionsArr as $collection_id => $collection) {
            if (!$collection['collection_primary_records']) {
                continue;
            }

            if (true ===  MOBILE_APP_API_CALL && 0 < $collection['collection_display_media_only'] && !in_array($collection['collection_type'], Collections::COLLECTION_WITHOUT_MEDIA)) {
                $imgUpdatedOn = Collections::getAttributesById($collection_id, 'collection_img_updated_on');
                $uploadedTime = AttachedFile::setTimeParam($imgUpdatedOn);

                $collection['collection_image'] = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'collectionReal', array( $collection_id, $langId,  'ORIGINAL', AttachedFile::FILETYPE_COLLECTION_IMAGE)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                $collections[] = $collection;
                $i++;
                continue;
            }

            switch ($collection['collection_type']) {
                case Collections::COLLECTION_TYPE_PRODUCT:
                    $tempObj = clone $collectionObj;
                    $tempObj->joinCollectionProducts();
                    $tempObj->addCondition('collection_id', '=', $collection_id);
                    /* $tempObj->setPageSize( $collection['collection_primary_records']); */
                    $tempObj->addMultipleFields(array( 'ctsp_selprod_id' ));
                    $tempObj->addCondition('ctsp_selprod_id', '!=', 'NULL');
                    $rs = $tempObj->getResultSet();

                    if (!$productIds = $db->fetchAll($rs, 'ctsp_selprod_id')) {
                        continue 2;
                    }

                    /* fetch Products data[ */
                    $orderBy = 'ASC';
                    if ($collection['collection_criteria'] == Collections::COLLECTION_CRITERIA_PRICE_HIGH_TO_LOW) {
                        $orderBy = 'DESC';
                    }

                    $productSrchTempObj = clone $productSrchObj;
                    if (true ===  MOBILE_APP_API_CALL) {
                        $productSrchTempObj->joinProductRating();
                        $productSrchTempObj->addFld('IFNULL(prod_rating, 0) as prod_rating');
                    }
                    $productSrchTempObj->addCondition('selprod_id', 'IN', array_keys($productIds));
                    $productSrchTempObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
                    $productSrchTempObj->addOrder('theprice', $orderBy);
                    $productSrchTempObj->joinSellers();
                    $productSrchTempObj->joinSellerSubscription($langId);
                    $productSrchTempObj->addGroupBy('selprod_id');
                    $productSrchTempObj->setPageSize($collection['collection_primary_records']);
                    $rs = $productSrchTempObj->getResultSet();

                    if (true ===  MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $collections[$i]['products'] = $db->fetchAll($rs);
                        $collections[$i]['totProducts'] = $productSrchTempObj->recordCount();
                    } else {
                        $collections[$collection['collection_layout_type']][$collection['collection_id']] = $collection;
                        $collections[$collection['collection_layout_type']][$collection['collection_id']]['products'] = $db->fetchAll($rs, 'selprod_id');
                        $collections[$collection['collection_layout_type']][$collection['collection_id']]['totProducts'] = $productSrchTempObj->recordCount();
                    }
                    /* ] */
                    unset($tempObj);
                    unset($productSrchTempObj);
                    break;

                case Collections::COLLECTION_TYPE_CATEGORY:
                    if (true ===  MOBILE_APP_API_CALL && Collections::TYPE_CATEGORY_LAYOUT2 == $collection['collection_layout_type']) {
                        continue 2;
                    }
                    $tempObj = clone $collectionObj;
                    $tempObj->addCondition('collection_id', '=', $collection_id);
                    $tempObj->joinCollectionCategories($langId);
                    $tempObj->addMultipleFields(array( 'ctpc_prodcat_id'));
                    $tempObj->addCondition('ctpc_prodcat_id', '!=', 'NULL');
                    $tempObj->setPageSize($collection['collection_primary_records']);

                    /* Exclude categories having no product [ */
                    /* $productSrchTempObj = clone $productSrchObj;
                    $productSrchTempObj->addGroupBy( 'prodcat_id' );
                    $productSrchTempObj->addMultipleFields( array('count(selprod_id) as productCounts', 'prodcat_id as qryProducts_prodcat_id') );
                    $productSrchTempObj->addCondition('selprod_deleted','=',applicationConstants::NO);
                    $tempObj->joinTable( '('.$productSrchTempObj->getQuery().')', 'LEFT OUTER JOIN', 'qryProducts.qryProducts_prodcat_id = prodcat_id', 'qryProducts' );
                    $tempObj->addCondition( 'qryProducts.productCounts', '>', 0 ); */
                    /* ] */

                    $rs = $tempObj->getResultSet();
                    if (!$categoryIds = $db->fetchAll($rs, 'ctpc_prodcat_id')) {
                        continue 2;
                    }

                    /* fetch Categories data[ */
                    $productCatSrchTempObj = clone $productCatSrchObj;
                    $productCatSrchTempObj->addCondition('prodcat_id', 'IN', array_keys($categoryIds));
                    $rs = $productCatSrchTempObj->getResultSet();
                    /* ] */
                    if (true ===  MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                    } else {
                        $collections[$collection['collection_layout_type']][$collection['collection_id']] = $collection;
                    }
                    $counter = 0;
                    if ($collection['collection_layout_type'] == Collections::TYPE_CATEGORY_LAYOUT2) {
                        while ($catData = $db->fetch($rs)) {
                            /* fetch Sub-Categories[ */
                            $subCategorySrch = clone $productCatSrchObj;
                            $subCategorySrch->addCondition('prodcat_parent', '=', $catData['prodcat_id']);
                            $Catrs = $subCategorySrch->getResultSet();

                            if (true ===  MOBILE_APP_API_CALL) {
                                $collections[$i]['categories'][$counter] = $catData;
                            // $collections[$i]['categories'][$counter]['subCategories'] = $db->fetchAll($Catrs);
                            } else {
                                $collections[$collection['collection_layout_type']][$collection['collection_id']]['categories'][$catData['prodcat_id']] = $catData;
                                $collections[$collection['collection_layout_type']][$collection['collection_id']]['categories'][$catData['prodcat_id']]['subCategories'] = $db->fetchAll($Catrs);
                            }
                            /* ] */
                            $counter++;
                        }
                    } else {
                        while ($catData = $db->fetch($rs)) {
                            /* fetch Product data[ */
                            $productShopSrchTempObj = clone $productSrchObj;
                            $productShopSrchTempObj->addCondition('prodcat_id', '=', $catData['prodcat_id']);
                            $productShopSrchTempObj->addOrder('in_stock', 'DESC');
                            $productShopSrchTempObj->addGroupBy('selprod_product_id');
                            $productShopSrchTempObj->setPageSize(7);
                            $Prs = $productShopSrchTempObj->getResultSet();
                            if ($productShopSrchTempObj->recordCount() == 0) {
                                continue;
                            }
                            if (true ===  MOBILE_APP_API_CALL) {
                                $collections[$i]['categories'][$counter] = $catData;
                            // $collections[$i]['categories'][$counter]['products'] = $db->fetchAll($Prs);
                            } else {
                                $collections[$collection['collection_layout_type']][$collection['collection_id']]['categories'][$catData['prodcat_id']]['catData'] = $catData;
                                $collections[$collection['collection_layout_type']][$collection['collection_id']]['categories'][$catData['prodcat_id']]['products'] = $db->fetchAll($Prs);
                            }
                            /* ] */
                            $counter++;
                        }
                    }
                    if (true ===  MOBILE_APP_API_CALL) {
                        $collections[$i]['totCategories'] = $tempObj->recordCount();
                    } else {
                        $collections[$collection['collection_layout_type']][$collection['collection_id']]['totCategories'] = $tempObj->recordCount();
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

                    if (!$shopIds = $db->fetchAll($rs, 'ctps_shop_id')) {
                        continue 2;
                    }

                    $shopObj = new ShopSearch($langId);
                    $shopObj->setDefinedCriteria($langId);
                    $shopObj->joinSellerSubscription();
                    $shopObj->addCondition('shop_id', 'IN', array_keys($shopIds));
                    if (false ===  MOBILE_APP_API_CALL) {
                        $shopObj->setPageSize($collection['collection_primary_records']);
                    }
                    $shopObj->addMultipleFields(array( 'shop_id','shop_user_id','IFNULL(shop_name, shop_identifier) as shop_name','IFNULL(country_name, country_code) as country_name','IFNULL(state_name, state_identifier) as state_name'));
                    $rs = $shopObj->getResultSet();

                    if (true ===  MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $collections[$i]['totShops'] = $shopObj->recordCount();
                    } else {
                        $collections[$collection['collection_layout_type']][$collection['collection_id']] = $collection;
                        $collections[$collection['collection_layout_type']][$collection['collection_id']]['totShops'] = $shopObj->recordCount();
                    }

                    $counter = 0;
                    while ($shopsData = $db->fetch($rs)) {
                        /* fetch Shop data[ */
                        /*$productShopSrchTempObj = clone $productSrchObj;
                        $productShopSrchTempObj->addCondition('selprod_user_id', '=', $shopsData['shop_user_id']);
                        $productShopSrchTempObj->addOrder('in_stock', 'DESC');
                        $productShopSrchTempObj->addGroupBy('selprod_product_id');
                        $productShopSrchTempObj->setPageSize(3);
                        $Prs = $productShopSrchTempObj->getResultSet();*/

                        $rating = 0;
                        if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                            $rating = SelProdRating::getSellerRating($shopsData['shop_user_id']);
                        }

                        if (true ===  MOBILE_APP_API_CALL) {
                            $collections[$i]['shops'][$counter] = $shopsData;

                            $collections[$i]['shops'][$counter]['rating'] =  $rating;
                        } else {
                            $collections[$collection['collection_layout_type']][$collection['collection_id']]['shops'][$shopsData['shop_id']]['shopData'] = $shopsData;

                            $collections[$collection['collection_layout_type']][$collection['collection_id']]['rating'][$shopsData['shop_id']] =  $rating;
                        }


                        /*$collections[$collection['collection_layout_type']][$collection['collection_id']]['shops'][$shopsData['shop_id']]['products'] = $db->fetchAll($Prs);*/
                        /* ] */
                        $counter++;
                    }
                    unset($tempObj);
                    break;
                case Collections::COLLECTION_TYPE_BRAND:
                    $tempObj = clone $collectionObj;
                    $tempObj->addCondition('collection_id', '=', $collection_id);
                    $tempObj->joinCollectionBrands($langId);
                    $tempObj->addMultipleFields(array('ctpb_brand_id'));
                    $tempObj->addCondition('ctpb_brand_id', '!=', 'NULL');
                    $rs = $tempObj->getResultSet();
                    $brandIds = $db->fetchAll($rs, 'ctpb_brand_id');

                    if (empty($brandIds)) {
                        continue 2;
                    }

                    /* fetch Brand data[ */
                    $brandSearchObj = Brand::getSearchObject($langId, true, true);
                    $brandSearchTempObj = clone $brandSearchObj;
                    $brandSearchTempObj->addMultipleFields(array('brand_id','IFNULL(brand_name, brand_identifier) as brand_name'));
                    $brandSearchTempObj->addCondition('brand_id', 'IN', array_keys($brandIds));
                    if (false ===  MOBILE_APP_API_CALL) {
                        $brandSearchTempObj->setPageSize($collection['collection_primary_records']);
                    }
                    $rs = $brandSearchTempObj->getResultSet();
                    /* ] */
                    if (true ===  MOBILE_APP_API_CALL) {
                        $collections[$i] = $collection;
                        $collections[$i]['totBrands'] = $brandSearchTempObj->recordCount();
                        $collections[$i]['brands'] = $db->fetchAll($rs);
                    } else {
                        $collections[$collection['collection_layout_type']][$collection['collection_id']] = $collection;
                        $collections[$collection['collection_layout_type']][$collection['collection_id']]['totBrands'] = $brandSearchTempObj->recordCount();
                        $collections[$collection['collection_layout_type']][$collection['collection_id']]['brands'] = $db->fetchAll($rs);
                    }

                    unset($brandSearchTempObj);
                    unset($tempObj);
                    break;
            }
            $i++;
        }

        FatCache::set('collectionCache_'.$langId.'_'.$apiCall, serialize($collections), '.txt');
        return $collections;
    }

    private function getSlides()
    {
        $langId = $this->siteLangId;
        $db = FatApp::getDb();
        $srchSlide = new SlideSearch($langId);
        $srchSlide->doNotCalculateRecords();
        $srchSlide->joinPromotions($langId, true, true, true);
        $srchSlide->addPromotionTypeCondition();
        $srchSlide->joinUserWallet();
        $srchSlide->joinActiveUser();
        $srchSlide->addMinimiumWalletbalanceCondition();
        $srchSlide->addSkipExpiredPromotionAndSlideCondition();
        $srchSlide->joinBudget();
        $srchSlide->joinAttachedFile();
        $srchSlide->addMultipleFields(array('slide_id','slide_record_id', 'slide_type','IFNULL(promotion_name, promotion_identifier) as promotion_name,IFNULL(slide_title, slide_identifier) as slide_title','slide_target','slide_url','promotion_id','daily_cost','weekly_cost','monthly_cost','total_cost','slide_img_updated_on'));

        $totalSlidesPageSize = FatApp::getConfig('CONF_TOTAL_SLIDES_HOME_PAGE', FatUtility::VAR_INT, 4);
        $ppcSlidesPageSize = FatApp::getConfig('CONF_PPC_SLIDES_HOME_PAGE', FatUtility::VAR_INT, 4);

        $ppcSlides = array();
        $adminSlides = array();

        $slidesSrch = new SearchBase('('.$srchSlide->getQuery().') as t');
        $slidesSrch->addMultipleFields(array('slide_id','slide_type','slide_record_id','slide_url','slide_target','slide_title','promotion_id','userBalance','daily_cost','weekly_cost','monthly_cost','total_cost','promotion_budget' ,'promotion_duration','slide_img_updated_on'));
        $slidesSrch->addOrder('', 'rand()');

        if (0 < $ppcSlidesPageSize) {
            $ppcSrch  = clone $slidesSrch;
            $ppcSrch->addDirectCondition(
                '((CASE
					WHEN promotion_duration='.Promotion::DAILY.' THEN promotion_budget > COALESCE(daily_cost,0)
					WHEN promotion_duration='.Promotion::WEEKLY.' THEN promotion_budget > COALESCE(weekly_cost,0)
					WHEN promotion_duration='.Promotion::MONTHLY.' THEN promotion_budget > COALESCE(monthly_cost,0)
					WHEN promotion_duration='.Promotion::DURATION_NOT_AVAILABALE.' THEN promotion_budget = -1
				  END ) )'
            );

            $ppcSrch->addCondition('slide_type', '=', Slides::TYPE_PPC);
            $ppcSrch->setPageSize($ppcSlidesPageSize);
            $ppcRs = $ppcSrch->getResultSet();
            $ppcSlides = $db->fetchAll($ppcRs, 'slide_id');
        }

        $ppcSlidesCount = count($ppcSlides);
        if ($totalSlidesPageSize > $ppcSlidesCount) {
            $totalSlidesPageSize = $totalSlidesPageSize - $ppcSlidesCount;
            $adminSlideSrch  = clone $slidesSrch;
            $adminSlideSrch->addCondition('slide_type', '=', Slides::TYPE_SLIDE);
            $adminSlideSrch->setPageSize($totalSlidesPageSize);
            $slideRs = $adminSlideSrch->getResultSet();
            $adminSlides = $db->fetchAll($slideRs, 'slide_id');
        }

        $slides = array_merge($ppcSlides, $adminSlides);
        return $slides;
    }

    private function getBanners()
    {
        $langId = $this->siteLangId;
        $top_banners =  BannerLocation::getPromotionalBanners(BannerLocation::HOME_PAGE_TOP_BANNER, $langId);
        $middle_banners = array();
        $pageSize = 0;
        if (true ===  MOBILE_APP_API_CALL) {
            $pageSize = BannerLocation::MOBILE_API_BANNER_PAGESIZE;
            $middle_banners =  BannerLocation::getPromotionalBanners(BannerLocation::HOME_PAGE_MIDDLE_BANNER, $langId, $pageSize);
        }
        $bottom_banners =  BannerLocation::getPromotionalBanners(BannerLocation::HOME_PAGE_BOTTOM_BANNER, $langId, $pageSize);
        $banners = array_merge($top_banners, $middle_banners, $bottom_banners);
        return $banners;
    }

    private function getSponsoredShops($productSrchObj)
    {
        $langId = $this->siteLangId;
        $shopPageSize = FatApp::getConfig('CONF_PPC_SHOPS_HOME_PAGE', FatUtility::VAR_INT, 2);
        if (1 > $shopPageSize) {
            return array();
        }

        $sponsoredShops = array();
        $db = FatApp::getDb();

        $shopObj  = new PromotionSearch($langId);
        $shopObj->setDefinedCriteria();
        $shopObj->joinActiveUser();
        $shopObj->joinShops($langId, true, true);
        $shopObj->joinShopCountry();
        $shopObj->joinShopState();
        $shopObj->addPromotionTypeCondition(Promotion::TYPE_SHOP);
        $shopObj->addShopActiveExpiredCondition();
        $shopObj->joinUserWallet();
        $shopObj->joinBudget();
        $shopObj->addBudgetCondition();
        $shopObj->addOrder('', 'rand()');
        $shopObj->setPageSize($shopPageSize);

        $rs = $shopObj->getResultSet();
        $i = 0;
        while ($shops = $db->fetch($rs)) {
            /* fetch Shop data[ */
            $productShopSrchTempObj = clone $productSrchObj;

            if (true ===  MOBILE_APP_API_CALL) {
                $productShopSrchTempObj->joinProductRating();
                $productShopSrchTempObj->addFld('IFNULL(prod_rating, 0) as prod_rating');
            }

            $productShopSrchTempObj->addCondition('selprod_user_id', '=', $shops['shop_user_id']);
            $productShopSrchTempObj->addGroupBy('selprod_product_id');
            $productShopSrchTempObj->doNotCalculateRecords();
            $productShopSrchTempObj->setPageSize(Shop::SHOP_PRODUCTS_COUNT_AT_HOMEPAGE);
            $Prs = $productShopSrchTempObj->getResultSet();

            $rating = 0;
            if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                $rating = SelProdRating::getSellerRating($shops['shop_user_id']);
            }

            if (true ===  MOBILE_APP_API_CALL) {
                $sponsoredShops[$i]['shopData'] = $shops;
                $sponsoredShops[$i]['shopData']['promotion_id'] = $shops['promotion_id'];
                $sponsoredShops[$i]['shopData']['rating'] = $rating;
                $sponsoredShops[$i]['shopData']['shop_logo'] = CommonHelper::generateFullUrl('image', 'shopLogo', array($shops['shop_id'], $langId));
                $sponsoredShops[$i]['shopData']['shop_banner'] = CommonHelper::generateFullUrl('image', 'shopBanner', array($shops['shop_id'], $langId));
                $sponsoredShops[$i]['products'] = $db->fetchAll($Prs);
            } else {
                $sponsoredShops['shops'][$shops['shop_id']]['shopData'] = $shops;
                $sponsoredShops['shops'][$shops['shop_id']]['shopData']['promotion_id'] = $shops['promotion_id'];

                $sponsoredShops['rating'][$shops['shop_id']] =  $rating;
                $sponsoredShops['shops'][$shops['shop_id']]['products'] = $db->fetchAll($Prs);
            }
            /* ] */
            $i++;
        }
        return $sponsoredShops;
    }

    private function getSponsoredProductsObj($productSrchObj)
    {
        $langId = $this->siteLangId;
        $prodObj  = new PromotionSearch($langId);
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

        $productSrchSponObj = clone $productSrchObj;
        $productSrchSponObj->joinTable('(' . $prodObj->getQuery().') ', 'INNER JOIN', 'selprod_id = ppr.proSelProdId ', 'ppr');
        $productSrchSponObj->addFld(array('promotion_id','promotion_record_id'));
        $productSrchSponObj->addOrder('theprice', 'ASC');
        $productSrchSponObj->joinSellers();
        $productSrchSponObj->joinSellerSubscription($langId);
        $productSrchSponObj->addGroupBy('selprod_id');
        $productSrchSponObj->addOrder('', 'rand()');
        return $productSrchSponObj;
    }

    // For Home Page
    private function getSponsoredProducts($productSrchObj)
    {
        $productPageSize = (true ===  MOBILE_APP_API_CALL) ? 4 : FatApp::getConfig('CONF_PPC_PRODUCTS_HOME_PAGE', FatUtility::VAR_INT, 6);

        if (1 > $productPageSize) {
            return array();
        }

        $db = FatApp::getDb();
        $productSrchSponObj = $this->getSponsoredProductsObj($productSrchObj);
        if (true ===  MOBILE_APP_API_CALL) {
            $productSrchSponObj->joinProductRating();
            $productSrchSponObj->addFld('IFNULL(prod_rating, 0) as prod_rating');
        }
        $productSrchSponObj->doNotCalculateRecords();
        $productSrchSponObj->setPageSize($productPageSize);
        $rs = $productSrchSponObj->getResultSet();
        return $db->fetchAll($rs);
    }

    // Used for APP
    public function getAllSponsoredProducts()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId(true);

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $page = ($page < 2) ? 1 : $page;

        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $productSrchObj = $this->getProductSearchObj($loggedUserId);

        $productSrchSponObj = $this->getSponsoredProductsObj($productSrchObj);
        $productSrchSponObj->joinProductRating();
        $productSrchSponObj->addFld('IFNULL(prod_rating, 0) as prod_rating');
        $productSrchSponObj->setPageNumber($page);
        $productSrchSponObj->setPageSize($pagesize);

        $rs = $productSrchSponObj->getResultSet();
        $sponsoredProds = FatApp::getDb()->fetchAll($rs);

        $this->set('sponsoredProds', $sponsoredProds);
        $this->set('page', $page);
        $this->set('pageCount', $productSrchSponObj->pages());
        $this->set('recordCount', $productSrchSponObj->recordCount());
        $this->set('postedData', FatApp::getPostedData());
        $this->_template->render();
    }

    public function getImage()
    {
        $post = FatApp::getPostedData();
        if (1 > count($post)) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }
        $type = FatApp::getPostedData('type', null, '');
        if (empty($type)) {
            $message = Labels::getLabel('MSG_Type_is_mandatory', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }
        $image_url = "";
        switch (strtoupper($type)) {
            case 'PRODUCT_PRIMARY':
                $product_id = FatApp::getPostedData('product_id', null, 0);
                $seller_product_id = FatApp::getPostedData('seller_product_id', null, 0);
                if (1 > $product_id || 1 > $seller_product_id) {
                    $message = Labels::getLabel('MSG_Product_id_&_Seller_product_id_is_mandatory.', $this->siteLangId);
                    FatUtility::dieJsonError($message);
                }
                $image_url = CommonHelper::generateFullUrl('image', 'product', array($product_id, "MEDIUM", $seller_product_id, 0, $this->siteLangId));
                break;
            case 'SLIDE':
                $slide_id = FatApp::getPostedData('slide_id', null, 0);
                if (1 > $slide_id) {
                    $message = Labels::getLabel('MSG_Slide_id_is_mandatory.', $this->siteLangId);
                    FatUtility::dieJsonError($message);
                }
                $image_url = CommonHelper::generateFullUrl('Image', 'slide', array($slide_id,0,$this->siteLangId));
                break;
            case 'BANNER':
                $banner_id = FatApp::getPostedData('banner_id', null, 0);
                if (1 > $banner_id) {
                    $message = Labels::getLabel('MSG_Banner_id_is_mandatory.', $this->siteLangId);
                    FatUtility::dieJsonError($message);
                }
                $image_url = CommonHelper::generateFullUrl('Banner', 'HomePageAfterFirstLayout', array($banner_id, $this->siteLangId));
                break;
        }
        $this->set('image_url', $image_url);
        $this->_template->render();
    }
    public function countries()
    {
        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->siteLangId);
        $arr_country = array();
        foreach ($countriesArr as $key => $val) {
            $arr_country[] = array("id"=>$key,'name'=>$val);
        }
        $this->set('countries', $arr_country);
        $this->_template->render();
    }
    public function states($countryId)
    {
        $countryId = FatUtility::int($countryId);
        if (1 > $countryId) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }
        $statesArr = $this->getStates($countryId, 0, true);
        $states = array();
        foreach ($statesArr as $key => $val) {
            $states[]=array("id"=>$key,'name'=>$val);
        }
        $this->set('states', $states);
        $this->_template->render();
    }
}
