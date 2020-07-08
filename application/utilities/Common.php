<?php
class Common
{
    public static function headerWishListAndCartSummary($template)
    {
        $cartObj = new Cart();
        $siteLangId = CommonHelper::getLangId();
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        $wislistPSrchObj = new UserWishListProductSearch();
        $wislistPSrchObj->joinWishLists();
        $wislistPSrchObj->doNotLimitRecords();
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $loggedUserId);
        $wislistPSrchObj->addGroupBy('uwlp_selprod_id');
        $wislistPSrchObj->addMultipleFields(array('uwlp_uwlist_id'));
        $rs = $wislistPSrchObj->getResultSet();
        $totalWishListItems = $wislistPSrchObj->recordCount();

        $productsArr = $cartObj->getProducts($siteLangId);

        $cartSummary = $cartObj->getCartFinancialSummary($siteLangId);

        $template->set('siteLangId', $siteLangId);
        $template->set('products', $productsArr);
        $template->set('cartSummary', $cartSummary);
        $template->set('totalWishListItems', $totalWishListItems);
        $template->set('totalCartItems', $cartObj->countProducts());
    }

    public static function countWishList()
    {
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        $wislistPSrchObj = new UserWishListProductSearch();
        $wislistPSrchObj->joinSellerProducts();
        $wislistPSrchObj->joinProducts();
        $wislistPSrchObj->joinSellers();
        $wislistPSrchObj->joinShops();
        $wislistPSrchObj->joinProductToCategory();
        $wislistPSrchObj->joinSellerSubscription();
        $wislistPSrchObj->addSubscriptionValidCondition();
        $wislistPSrchObj->joinWishLists();
        $wislistPSrchObj->doNotLimitRecords();
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $loggedUserId);
        $wislistPSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $wislistPSrchObj->addCondition('selprod_active', '=', applicationConstants::YES);
        $wislistPSrchObj->addGroupBy('uwlp_selprod_id');
        $wislistPSrchObj->addMultipleFields(array('uwlp_uwlist_id'));
        $rs = $wislistPSrchObj->getResultSet();
        $totalWishListItems = $wislistPSrchObj->recordCount();

        return $totalWishListItems;
    }

    public static function setHeaderBreadCrumb($template)
    {
        $controllerName = FatApp::getController();
        $action = FatApp::getAction();

        $controller = new $controllerName('');
        $template->set('siteLangId', CommonHelper::getLangId());
        $template->set('nodes', $controller->getBreadcrumbNodes($action));
    }

    public static function headerUserArea($template)
    {
        $template->set('siteLangId', CommonHelper::getLangId());
        $isUserLogged = UserAuthentication::isUserLogged();
        $template->set('isUserLogged', $isUserLogged);
        if ($isUserLogged) {
            $userId = UserAuthentication::getLoggedUserId();
            $userImgUpdatedOn = User::getAttributesById($userId, 'user_img_updated_on');
            $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
            $profileImage = FatCache::getCachedUrl(CommonHelper::generateUrl('Account', 'userProfileImage', array($userId,'croped',true)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
            $template->set('userName', ucfirst(CommonHelper::getUserFirstName(UserAuthentication::getLoggedUserAttribute('user_name'))));
            $template->set('userEmail', UserAuthentication::getLoggedUserAttribute('user_email'));
            $template->set('profilePicUrl', $profileImage);
        }
    }

    public static function headerSearchFormArea($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $headerSrchFrm = static::getSiteSearchForm();
        $headerSrchFrm->setFormTagAttribute('onSubmit', 'submitSiteSearch(this); return(false);');

        /* to fill the posted data to form[ */
        $paramsArr = FatApp::getParameters();
        $paramsAssocArr = CommonHelper::arrayToAssocArray($paramsArr);
        $headerSrchFrm->fill($paramsAssocArr);
        /* ] */

        $headerRootCatArr =  FatCache::get('headerRootCatArr'.$siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($headerRootCatArr) {
            $categoriesArr = unserialize($headerRootCatArr);
        } else {
            /* SubQuery, Category have products[ */
            $prodSrchObj = new ProductSearch();
            $prodSrchObj->setDefinedCriteria(0 , 0, array('doNotJoinSpecialPrice'=>true));
            $prodSrchObj->joinProductToCategory($siteLangId);
            $prodSrchObj->doNotCalculateRecords();
            $prodSrchObj->doNotLimitRecords();
            $prodSrchObj->joinSellerSubscription($siteLangId, true);
            $prodSrchObj->addSubscriptionValidCondition();
            $prodSrchObj->addGroupBy('prodcat_id');
            $prodSrchObj->addMultipleFields(array('prodcat_code AS prodrootcat_code','count(selprod_id) as productCounts', 'prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_parent'));

            $rs = $prodSrchObj->getResultSet();

            $productRows = FatApp::getDb()->fetchAll($rs);

            $mainRootCategories = FatUtility::int(array_column($productRows, 'prodrootcat_code'));

            $categoriesMainRootArr = array();

            if ($productRows) {
                $categoriesMainRootArr = array_unique($mainRootCategories);
                array_flip($categoriesMainRootArr);
            }
            /* ] */

            $catSrch = ProductCategory::getSearchObject(false, $siteLangId);
            $catSrch->addMultipleFields(array('prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as category_name'));
            $catSrch->addOrder('category_name');
            $catSrch->doNotCalculateRecords();
            $catSrch->addCondition('prodcat_active', '=', applicationConstants::YES);
            $catSrch->addCondition('prodcat_deleted', '=', applicationConstants::NO);
            if ($categoriesMainRootArr) {
                $catSrch->addCondition('prodcat_id', 'in', $categoriesMainRootArr);
            }
            $catSrch->setPageSize(25);
            $catRs = $catSrch->getResultSet();
            $categoriesArr = [];
            while ($row = FatApp::getDb()->fetch($catRs)) {
                $categoriesArr[$row['prodcat_id']] = strip_tags($row['category_name']);
            }
        }

        FatCache::set('headerRootCatArr'.$siteLangId, serialize($categoriesArr), '.txt');

        $template->set('categoriesArr', $categoriesArr);
        $template->set('headerSrchFrm', $headerSrchFrm);
        $template->set('siteLangId', $siteLangId);
        //$productRootCategoriesArr = $prodCatObj->getCategoriesForSelectBox($siteLangId, 0, true); */
        //ProductCategory::getRootProdCatAssocArr($siteLangId, 0);
        /* ob_end_clean();
        CommonHelper::printArray($data);
        die(); */
        //$template->set( 'productRootCategoriesArr', $productRootCategoriesArr );
    }

    public static function getSiteSearchForm()
    {
        $siteLangId = CommonHelper::getLangId();
        $frm = new Form('frmSiteSearch');
        $frm->setFormTagAttribute('class', 'main-search-form');
        $frm->setFormTagAttribute('autocomplete', 'off');
        /* $frm->addSelectBox('', 'category', $categoriesArr, '', array(), Labels::getLabel('LBL_All', CommonHelper::getLangId()) ); */
        $frm->addTextBox('', 'keyword');
        $frm->addHiddenField('', 'category');
        $frm->addSubmitButton('', 'btnSiteSrchSubmit', Labels::getLabel('LBL_Search', CommonHelper::getLangId()));
        return $frm;
    }

    public static function headerLanguageArea($template)
    {
        $template->set('siteLangId', CommonHelper::getLangId());
        $template->set('siteCurrencyId', CommonHelper::getCurrencyId());
        $template->set('languages', Language::getAllNames(false));
        $template->set('currencies', Currency::getCurrencyAssoc(CommonHelper::getLangId()));
    }

    public static function footerNewsLetterForm($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $frm = static::getNewsLetterForm($siteLangId);
        $template->set('frm', $frm);
        $template->set('siteLangId', $siteLangId);
    }

    public static function footerTopBrands($template)
    {
        $siteLangId = CommonHelper::getLangId();

        $brandSrch = Brand::getSearchObject($siteLangId);
        $brandSrch->joinTable(Product::DB_TBL, 'INNER JOIN', 'brand_id = p.product_brand_id', 'p');
        $brandSrch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'sp');
        $brandSrch->doNotCalculateRecords();
        $brandSrch->addMultipleFields(array( 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name', 'SUM(IFNULL(selprod_sold_count, 0)) as totSoldQty'));
        $brandSrch->addCondition('brand_status', '=', Brand::BRAND_REQUEST_APPROVED);
        $brandSrch->addCondition('brand_active', '=', applicationConstants::YES);
        $brandSrch->addGroupBy('brand_id');
        $brandSrch->addHaving('totSoldQty', '>', 0);
        $brandSrch->addOrder('totSoldQty', 'DESC');
        $brandSrch->addOrder('brand_name');
        $brandSrch->setPageSize(25);

        $brandRs = $brandSrch->getResultSet();
        $topBrands = FatApp::getDb()->fetchAll($brandRs);
        $template->set('topBrands', $topBrands);
        $template->set('siteLangId', $siteLangId);
    }

    public static function footerTopCategories($template)
    {
        $siteLangId = CommonHelper::getLangId();

        $catSrch = new ProductCategorySearch($siteLangId, true, true, false, false);
        $catSrch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'ptc');
        $catSrch->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_product_id = ptc.ptc_product_id', 'sp');
        $catSrch->doNotCalculateRecords();
        $catSrch->addMultipleFields(array( 'c.prodcat_id', 'IFNULL(c_l.prodcat_name, c.prodcat_identifier) as prodcat_name', 'SUM(IFNULL(selprod_sold_count, 0)) as totSoldQty'));
        $catSrch->addCondition('prodcat_active', '=', applicationConstants::YES);
        $catSrch->addCondition('prodcat_deleted', '=', applicationConstants::NO);
        $catSrch->addGroupBy('prodcat_id');
        $catSrch->addHaving('totSoldQty', '>', 0);
        $catSrch->addOrder('totSoldQty', 'DESC');
        $catSrch->addOrder('prodcat_name');
        $catSrch->setPageSize(25);

        $catRs = $catSrch->getResultSet();
        $topCategories = FatApp::getDb()->fetchAll($catRs);
        $template->set('topCategories', $topCategories);
        $template->set('siteLangId', $siteLangId);
    }

    public static function footerTrustBanners($template)
    {
        $siteLangId = CommonHelper::getLangId();

        $obj = new Extrapage();
        $footerData = $obj->getContentByPageType(Extrapage::FOOTER_TRUST_BANNERS, $siteLangId);
        $template->set('footerData', $footerData);
    }

    public static function getNewsLetterForm($langId)
    {
        $frm = new Form('frmNewsLetter');
        $frm->setRequiredStarWith('');
        $fld1 = $frm->addEmailField('', 'email');
        $fld2 = $frm->addSubmitButton('', 'btnSubmit', Labels::getLabel('LBL_Subscribe', $langId));
        $fld1->attachField($fld2);
        $frm->setJsErrorDisplay('afterfield');
        return $frm;
    }

    public static function brandFilters($template)
    {
        $brandSrch = clone $prodSrchObj;
        $brandSrch->addGroupBy('brand_id');
        $brandSrch->addOrder('brand_name');
        $brandSrch->addMultipleFields(array( 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'));
        /* if needs to show product counts under brands[ */
        //$brandSrch->addFld('count(selprod_id) as totalProducts');
        /* ] */
        //echo $brandSrch->getQuery(); die();
        $brandRs = $brandSrch->getResultSet();
        $brandsArr = $db->fetchAll($brandRs);
        $template->set('brandsArr', $brandsArr);
    }

    public static function userMessages($template)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $srch = new MessageSearch();
        $srch->joinThreadMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->addMultipleFields(array('tth.*','ttm.message_id','ttm.message_text','ttm.message_date','ttm.message_is_unread'));
        $srch->addCondition('ttm.message_deleted', '=', 0);
        //$cnd = $srch->addCondition('ttm.message_from','=',$userId);
        $srch->addCondition('ttm.message_to', '=', $userId);
        $srch->addOrder('message_id', 'DESC');
        $srch->setPageSize(3);
        $rs = $srch->getResultSet();
        $messages = FatApp::getDb()->fetchAll($rs);
        $template->set('messages', $messages);
        $template->set('siteLangId', CommonHelper::getLangId());
    }

    public static function footerSocialMedia($template)
    {
        $siteLangId = CommonHelper::getLangId();

        $srch = SocialPlatform::getSearchObject($siteLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('splatform_user_id', '=', 0);
        $rs = $srch->getResultSet();
        $rows = FatApp::getDb()->fetchAll($rs);

        $template->set('rows', $rows);
        $template->set('siteLangId', $siteLangId);
    }

    public static function homePageBelowSlider($template)
    {
        $siteLangId = CommonHelper::getLangId();
    }

    public static function productDetailPageBanner($template)
    {
        $siteLangId = CommonHelper::getLangId();
    }

    public static function blogSidePanelArea($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $blogSrchFrm = static::getBlogSearchForm();
        $blogSrchFrm->setFormTagAttribute('action', CommonHelper::generateUrl('Blog'));

        /* to fill the posted data into form[ */
        $postedData = FatApp::getPostedData();
        $blogSrchFrm->fill($postedData);
        /* ] */

        /* Right Side Categories Data[ */
        $categoriesArr = BlogPostCategory::getBlogPostCatParentChildWiseArr($siteLangId);
        $template->set('categoriesArr', $categoriesArr);
        /* ] */

        $template->set('blogSrchFrm', $blogSrchFrm);
        $template->set('siteLangId', $siteLangId);
    }

    public static function blogTopFeaturedCategories($template)
    {
        $siteLangId = CommonHelper::getLangId();

        $bpCatObj = new BlogPostCategory();
        $arrCategories = $bpCatObj->getFeaturedCategories($siteLangId);
        $categories = $bpCatObj->makeAssociativeArray($arrCategories);
        $template->set('featuredBlogCategories', $categories);
        $template->set('siteLangId', $siteLangId);
    }

    public static function getBlogSearchForm()
    {
        $frm = new Form('frmBlogSearch');
        $frm->setFormTagAttribute('autocomplete', 'off');
        $frm->addTextBox('', 'keyword', '');
        $frm->addHiddenField('', 'page', 1);
        $frm->addSubmitButton('', 'btn_submit', '');
        return $frm;
    }

    public static function getPollForm($pollId, $langId)
    {
        $frm = new Form('frmPoll');
        $frm->addHiddenField('', 'pollfeedback_polling_id', $pollId);
        $frm->addRadioButtons('', 'pollfeedback_response_type', Polling::getPollingResponseTypeArr($langId), '1', array('class'=>'listing--vertical listing--vertical-chcek'), array());
        $submitBtn = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('Lbl_Vote', $langId), array('class'=>'btn btn--primary poll--link-js'));
        /* $viewResultsFld = $frm->addHTML('View Results', 'btn_view_results','<a href="javascript:void(0)" class="link--underline view--link-js" >'.Labels::getLabel('Lbl_View_Results',$langId).'</a>');
        $submitBtn->attachField($viewResultsFld); */
        return $frm;
    }

    public static function pollForm($template)
    {
        $action = FatApp::getAction();
        $controller = FatApp::getController();
        $params = FatApp::getParameters();

        if ($controller == 'ProductsController' && $action == 'view' && !empty($params)) {
            $productId = FatUtility::int($params[0]);
            $selProd = SellerProduct::getAttributesById($productId, array('selprod_product_id'), false);
            $pollQuest = Polling::getProductPoll($selProd['selprod_product_id'], CommonHelper::getLangId());
        } elseif ($controller == 'CategoryController' && $action == 'view' && !empty($params)) {
            $categoryId = FatUtility::int($params[0]);
            $pollQuest = Polling::getCategoryPoll($categoryId, CommonHelper::getLangId());
        }

        if (empty($pollQuest)) {
            $pollQuest = Polling::getGeneraicPoll(CommonHelper::getLangId());
        }

        $template->set('pollQuest', $pollQuest);
        $pollForm = static::getPollForm($pollQuest['polling_id'], CommonHelper::getLangId());
        $template->set('pollForm', $pollForm);
        $template->set('siteLangId', CommonHelper::getLangId());
    }
}
