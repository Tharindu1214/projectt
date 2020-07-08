<?php
class Navigation
{
    public static function headerTopNavigation($template)
    {
        $db = FatApp::getDb();
        $siteLangId = CommonHelper::getLangId();

        $headerTopNavigationCache =  FatCache::get('headerTopNavigation_'.$siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');

        if ($headerTopNavigationCache) {
            $headerTopNavigation  = unserialize($headerTopNavigationCache);
        } else {
            $headerTopNavigation = self::getNavigation(Navigations::NAVTYPE_TOP_HEADER);
            FatCache::set('headerTopNavigationCache_'.$siteLangId, serialize($headerTopNavigation), '.txt');
        }
        $template->set('top_header_navigation', $headerTopNavigation);
    }

    public static function headerNavigation($template)
    {
        $db = FatApp::getDb();
        $siteLangId = CommonHelper::getLangId();
        $template->set('siteLangId', $siteLangId);
        $headerNavigationCache =  FatCache::get('headerNavigation_'.$siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($headerNavigationCache) {
            $headerNavigation  = unserialize($headerNavigationCache);
        } else {
            $headerNavigation = self::getNavigation(Navigations::NAVTYPE_HEADER, true);
            FatCache::set('headerNavigation_'.$siteLangId, serialize($headerNavigation), '.txt');
        }

        $isUserLogged = UserAuthentication::isUserLogged();
        if ($isUserLogged) {
            $template->set('userName', ucfirst(CommonHelper::getUserFirstName(UserAuthentication::getLoggedUserAttribute('user_name'))));
        }

        $headerTopNavigationCache =  FatCache::get('headerTopNavigation_'.$siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');

        if ($headerTopNavigationCache) {
            $headerTopNavigation  = unserialize($headerTopNavigationCache);
        } else {
            $headerTopNavigation = self::getNavigation(Navigations::NAVTYPE_TOP_HEADER);
            FatCache::set('headerTopNavigationCache_'.$siteLangId, serialize($headerTopNavigation), '.txt');
        }
        $template->set('top_header_navigation', $headerTopNavigation);

        $template->set('isUserLogged', $isUserLogged);
        $template->set('headerNavigation', $headerNavigation);
    }

    public static function buyerDashboardNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();
        $userId = UserAuthentication::getLoggedUserId();
        /* Unread Message Count [*/
        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        /*]*/
        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
        $template->set('todayUnreadMessageCount', $todayUnreadMessageCount);
    }

    public static function topHeaderDashboard($template)
    {
        $userId = UserAuthentication::getLoggedUserId();
        /* Unread Message Count [*/
        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        /*]*/
        $shopDetails = Shop::getAttributesByUserId($userId, array('shop_id'), false);
        $shop_id = 0;
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $controller = str_replace('Controller', '', FatApp::getController());
        $activeTab = 'B';
        $sellerActiveTabControllers = array('Seller');
        $buyerActiveTabControllers = array('Buyer');

        if (in_array($controller, $sellerActiveTabControllers)) {
            $activeTab = 'S';
        } elseif (in_array($controller, $buyerActiveTabControllers)) {
            $activeTab = 'B';
        } elseif (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'])) {
            $activeTab = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'];
        }

        $template->set('activeTab', $activeTab);
        $template->set('shop_id', $shop_id);
        $template->set('isShopActive', Shop::isShopActive($userId));
        $template->set('todayUnreadMessageCount', $todayUnreadMessageCount);
    }

    public static function advertiserDashboardNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();

        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
    }

    public static function sellerDashboardNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $userId = UserAuthentication::getLoggedUserId();
        /* Unread Message Count [*/
        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        /*]*/
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();

        $shopDetails = Shop::getAttributesByUserId($userId, array('shop_id'), false);

        $shop_id = 0;
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $template->set('shop_id', $shop_id);
        $template->set('isShopActive', Shop::isShopActive($userId));
        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
        $template->set('todayUnreadMessageCount', $todayUnreadMessageCount);
    }

    public static function affiliateDashboardNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();

        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
    }

    public static function dashboardTop($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());

        $activeTab = 'B';
        $sellerActiveTabControllers = array('Seller');
        $buyerActiveTabControllers = array('Buyer');

        if (in_array($controller, $sellerActiveTabControllers)) {
            $activeTab = 'S';
        } elseif (in_array($controller, $buyerActiveTabControllers)) {
            $activeTab = 'B';
        } elseif (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'])) {
            $activeTab = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'];
        }

        $jsVariables = array(
        'confirmDelete' =>Labels::getLabel('LBL_Do_you_want_to_delete', $siteLangId),
        'confirmDefault' =>Labels::getLabel('LBL_Do_you_want_to_set_default', $siteLangId),
        );

        $template->set('jsVariables', $jsVariables);
        $template->set('siteLangId', $siteLangId);
        $template->set('activeTab', $activeTab);
    }

    public static function customPageLeft($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $contentBlockUrlArr = array(Extrapage::CONTACT_US_CONTENT_BLOCK => CommonHelper::generateUrl('Custom', 'ContactUs'));

        $srch = Extrapage::getSearchObject($siteLangId);
        $srch->addCondition('epage_default', '=', 1);
        $srch->addMultipleFields(
            array('epage_id as id','epage_type as pageType','IFNULL(epage_label,epage_identifier) as pageTitle ')
        );

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $pagesArr = FatApp::getDb()->fetchAll($rs);

        $srch = ContentPage::getSearchObject($siteLangId);
        $srch->addCondition('cpagelang_cpage_id', 'is not', 'mysql_func_null', 'and', true);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $cpagesArr = FatApp::getDb()->fetchAll($rs);

        $template->set('pagesArr', $pagesArr);
        $template->set('cpagesArr', $cpagesArr);
        $template->set('contentBlockUrlArr', $contentBlockUrlArr);
        $template->set('siteLangId', $siteLangId);
    }

    public static function getNavigation($type = 0, $includeChildCategories = false)
    {
        $siteLangId = CommonHelper::getLangId();
        $headerNavCache =  FatCache::get('headerNavCache'.$siteLangId.'-'.$type, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($headerNavCache) {
            return  unserialize($headerNavCache);
        }

        /* SubQuery, Category have products[ */
        $prodSrchObj = new ProductSearch();
        $prodSrchObj->setDefinedCriteria(0, 0, array('doNotJoinSpecialPrice'=>true));
        $prodSrchObj->joinProductToCategory($siteLangId);
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->doNotLimitRecords();
        $prodSrchObj->joinSellerSubscription($siteLangId, true);
        $prodSrchObj->addSubscriptionValidCondition();
        $prodSrchObj->addGroupBy('prodcat_id');
        $prodSrchObj->addMultipleFields(array('prodcat_code AS prodrootcat_code','count(selprod_id) as productCounts', 'prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_parent'));
        $prodSrchObj->addOrder('prodcat_display_order', 'asc');
        $navigationCatCache =  FatCache::get('navigationCatCache'.$siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($navigationCatCache) {
            $categoriesMainRootArr  = unserialize($navigationCatCache);
        } else {
            $rs = $prodSrchObj->getResultSet();
            $productRows = FatApp::getDb()->fetchAll($rs);
            $categoriesMainRootArr = array_column($productRows, 'prodrootcat_code');
            array_walk(
                $categoriesMainRootArr,
                function (&$n) {
                    $n = FatUtility::int(substr($n, 0, 6));
                }
            );
            $categoriesMainRootArr = array_unique($categoriesMainRootArr);
            array_flip($categoriesMainRootArr);
            FatCache::set('navigationCatCache'.$siteLangId, serialize($categoriesMainRootArr), '.txt');
        }

        $catWithProductConditoon ='';
        if ($categoriesMainRootArr) {
            $catWithProductConditoon = " and nlink_category_id in(".implode($categoriesMainRootArr, ",").")";
        }

        /* ] */

        $srch = new NavigationLinkSearch($siteLangId);
        $srch->joinTable('('.$prodSrchObj->getQuery().')', 'LEFT OUTER JOIN', 'qryProducts.prodcat_id = nlink_category_id', 'qryProducts');
        //$srch->joinTable( '('.$navCatSrch->getQuery().')', 'LEFT OUTER JOIN', 'catr.product_code like substr(GETCATCODE(prodcat_id),1,6)%', 'catr' );
        //$srch->joinTable( '('.$prodSrchObj->getQuery().')', 'LEFT OUTER JOIN', 'qryProducts.prodcat_id = nlink_category_id', 'qryProducts' );
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->joinNavigation();
        $srch->joinProductCategory();
        $srch->joinContentPages();

        $srch->addOrder('nav_id');
        $srch->addOrder('nlink_display_order');

        $srch->addCondition('nav_type', '=', $type);
        $srch->addCondition('nlink_deleted', '=', applicationConstants::NO);
        $srch->addCondition('nav_active', '=', applicationConstants::ACTIVE);
        $srch->addDirectCondition("((nlink_type = ". NavigationLinks::NAVLINK_TYPE_CATEGORY_PAGE ." AND nlink_category_id > 0 $catWithProductConditoon ) OR (nlink_type = ". NavigationLinks::NAVLINK_TYPE_CMS ." AND nlink_cpage_id > 0 ) OR  ( nlink_type = " . NavigationLinks::NAVLINK_TYPE_EXTERNAL_PAGE . " ))");

        $srch->addHaving('filtered_cpage_deleted', '=', applicationConstants::NO);
        $srch->addHaving('filtered_prodcat_active', '=', applicationConstants::ACTIVE);
        $srch->addHaving('filtered_prodcat_deleted', '=', applicationConstants::NO);

        $srch->addMultipleFields(
            array('nav_id', 'IFNULL( nav_name, nav_identifier ) as nav_name',
            'IFNULL( nlink_caption, nlink_identifier ) as nlink_caption', 'nlink_type', 'nlink_cpage_id', 'nlink_category_id', 'IFNULL( prodcat_active, '. applicationConstants::ACTIVE .' ) as filtered_prodcat_active', 'IFNULL(prodcat_deleted, ' . applicationConstants::NO . ') as filtered_prodcat_deleted', 'IFNULL( cpage_deleted, ' . applicationConstants::NO . ' ) as filtered_cpage_deleted', 'nlink_target', 'nlink_url', 'nlink_login_protected', '(qryProducts.productCounts) as totProductCounts' )
        );

        $isUserLogged = UserAuthentication::isUserLogged();
        if ($isUserLogged) {
            $cnd = $srch->addCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_BOTH);
            $cnd->attachCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_YES, 'OR');
        }
        if (!$isUserLogged) {
            $cnd = $srch->addCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_BOTH);
            $cnd->attachCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_NO, 'OR');
        }

        $rs = $srch->getResultSet();
        $rows = FatApp::getDb()->fetchAll($rs);
        $navigation = array();
        $previous_nav_id = 0;
        $productCategory = new productCategory;
        if ($rows) {
            foreach ($rows as $key => $row) {
                if ($key == 0 || $previous_nav_id != $row['nav_id']) {
                    $previous_nav_id = $row['nav_id'];
                }
                $navigation[$previous_nav_id]['parent'] = $row['nav_name'];
                $navigation[$previous_nav_id]['pages'][$key] = $row;

                $childrenCats = array();
                if ($row['nlink_category_id'] > 0) {
                    $catObj = clone $prodSrchObj;
                    $catObj->addCategoryCondition($row['nlink_category_id']);
                    $categoriesDataArr = ProductCategory::getProdCatParentChildWiseArr($siteLangId, $row['nlink_category_id'], false, false, false, $catObj, false);
                    $childrenCats = $productCategory ->getCategoryTreeArr($siteLangId, $categoriesDataArr);
                    $childrenCats = ($childrenCats) ? $childrenCats[$row['nlink_category_id']]['children']: array() ;
                }
                $navigation[$previous_nav_id]['pages'][$key]['children'] = $childrenCats;
            }
        }
        FatCache::set('headerNavCache'.$siteLangId.'-'.$type, serialize($navigation), '.txt');
        return $navigation;
    }

    public static function footerNavigation($template)
    {
        $db = FatApp::getDb();
        $siteLangId = CommonHelper::getLangId();
        $footerNavigationCache =  FatCache::get('footerNavigation'.$siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($footerNavigationCache) {
            $footerNavigation  = unserialize($footerNavigationCache);
        } else {
            $footerNavigation = self::getNavigation(Navigations::NAVTYPE_FOOTER);
            FatCache::set('footerNavigationCache'.$siteLangId, serialize($footerNavigation), '.txt');
        }
        $template->set('footer_navigation', $footerNavigation);
    }

    public static function sellerNavigationLeft($template)
    {
        $db = FatApp::getDb();
        $siteLangId = CommonHelper::getLangId();
        $seller_navigation_left = self::getNavigation(Navigations::NAVTYPE_SELLER_LEFT);
        $template->set('seller_navigation_left', $seller_navigation_left);
    }

    public static function sellerNavigationRight($template)
    {
        $db = FatApp::getDb();
        $siteLangId = CommonHelper::getLangId();
        $seller_navigation_right=self::getNavigation(Navigations::NAVTYPE_SELLER_RIGHT);
        $template->set('seller_navigation_right', $seller_navigation_right);
    }

    public static function blogNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $blog = new BlogController();
        $srchFrm = $blog->getBlogSearchForm();
        $categoriesArr = BlogPostCategory::getRootBlogPostCatArr($siteLangId);
        $template->set('srchFrm', $srchFrm);
        $template->set('categoriesArr', $categoriesArr);
        $template->set('siteLangId', $siteLangId);
    }
}
