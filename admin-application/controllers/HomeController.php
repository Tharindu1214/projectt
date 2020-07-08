<?php
class HomeController extends AdminBaseController
{
    function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewAdminDashboard($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditAdminDashboard($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
        include_once CONF_INSTALLATION_PATH . 'library/phpfastcache.php';
    }

    public function index()
    {
        $accountId = false;
        $this->set('configuredAnalytics', false);
        $this->set('objPrivilege', $this->objPrivilege);

        $analyticArr = array(
        'clientId' => FatApp::getConfig("CONF_ANALYTICS_CLIENT_ID", FatUtility::VAR_STRING, ''),
        'clientSecretKey' => FatApp::getConfig("CONF_ANALYTICS_SECRET_KEY", FatUtility::VAR_STRING, ''),
        'redirectUri' => CommonHelper::generateFullUrl('configurations', 'redirect', array(), '', false),
        'googleAnalyticsID' => FatApp::getConfig("CONF_ANALYTICS_ID", FatUtility::VAR_STRING, '')
        );


        // simple Caching with:
        phpFastCache::setup("storage", "files");
        phpFastCache::setup("path", CONF_UPLOADS_PATH."caching");
        $cache = phpFastCache();
        $dashboardInfo = $cache->get("dashboardInfo".$this->adminLangId);

        $dashboardInfo = array();
        if($dashboardInfo == null) {

            include_once CONF_INSTALLATION_PATH . 'library/analytics/analyticsapi.php';
            try{
                $analytics = new Ykart_analytics($analyticArr);
                $token = $analytics->getRefreshToken(FatApp::getConfig("CONF_ANALYTICS_ACCESS_TOKEN"));

                $analytics->setAccessToken((isset($token['accessToken']))?$token['accessToken']:'');

                $accountId = $analytics->setAccountId(FatApp::getConfig("CONF_ANALYTICS_ID"));
                if(!$accountId) {
                    Message::addErrorMessage(Labels::getLabel('LBL_Analytic_Id_does_not_exist_with_Configured_Account', $this->adminLangId));
                }else{
                    $this->set('configuredAnalytics', true);
                }
            }catch(exception $e){
                /* Message::addErrorMessage(Labels::getLabel('LBL_Analytic_Id_does_not_exist_with_Configured_Account',$this->adminLangId)); */
                //Message::addErrorMessage($e->getMessage());
            }

            $statsObj = new Statistics();

            if($accountId) {
                $statsInfo = $analytics->getVisitsByDate();

                $chatStats=array();
                if(!empty($statsInfo['stats'])) {
                    $chatStats = "[['".Labels::getLabel('LBL_Year', $this->adminLangId)."', '".Labels::getLabel('LBL_Today', $this->adminLangId)."','".Labels::getLabel('LBL_Weekly', $this->adminLangId)."','".Labels::getLabel('LBL_Last_Month', $this->adminLangId)."','".Labels::getLabel('LBL_Last_3_Month', $this->adminLangId)."'],";
                    foreach($statsInfo['stats'] as $key => $val){
                        if($key == '') { continue;
                        }

                        $chatStats.="['".FatDate::format($key)."',";
                        $chatStats.= isset($val['today']['visit'])?FatUtility::int($val['today']['visit']):0;
                        $chatStats.=',';
                        $chatStats.= isset($val['weekly']['visit'])?FatUtility::int($val['weekly']['visit']):0;
                        $chatStats.=',';
                        $chatStats.= isset($val['lastMonth']['visit'])?FatUtility::int($val['lastMonth']['visit']):0;
                        $chatStats.=',';
                        $chatStats.= isset($val['last3Month']['visit'])?FatUtility::int($val['last3Month']['visit']):0;
                        $chatStats.=',';
                    }
                }
                $chatStats = rtrim($chatStats, ',');
                $visits_chart_data = $chatStats.="]";
                $visitCount = $statsInfo['result'];
                foreach($statsInfo['result'] as $key=>$val){
                    $visitCount[$key] = $val['totalsForAllResults'];
                }
                $socialVisits = $analytics->getSocialVisits();

            }

            $conversionStats = $statsObj->getConversionStats();
            $conversionChatData = "['Type','user',{ role: 'style' }],";
            foreach($conversionStats as $key=>$val ){
                $key = Labels::getLabel('LBL_'.ucwords($key), $this->adminLangId);
                $conversionChatData.="['".$key."', ".$val["count"].",'#AEC785'],";
            }

            $conversionChatData = rtrim($conversionChatData, ',');

            $salesData = $statsObj->getDashboardLast12MonthsSummary($this->adminLangId, 'sales', array(), 6);
            $salesChartData = array();
            foreach($salesData as $key => $val ){
                $salesChartData[$val["duration"]] = $val["value"];
            }


            $salesEarningsData = $statsObj->getDashboardLast12MonthsSummary($this->adminLangId, 'earnings', array(), 6);
            $salesEarningsChartData = [];
            foreach($salesEarningsData as $key => $val ){
                $salesEarningsChartData[$val["duration"]] = $val["value"];
            }

            $signupsData = $statsObj->getDashboardLast12MonthsSummary($this->adminLangId, 'signups', array('user_is_buyer' => 1, 'user_is_supplier' => 1), 6);
            $signupsChartData = [];
            foreach($signupsData as $key => $val ){
                $signupsChartData[$val["duration"]] = $val["value"];
            }

            $affiliateSignupsData = $statsObj->getDashboardLast12MonthsSummary($this->adminLangId, 'signups', array( 'user_is_affiliate' => 1 ), 6);
            $affiliateSignupsChartData = array();
            foreach($affiliateSignupsData as $key => $val ){
                $affiliateSignupsChartData[$val["duration"]] = $val["value"];
            }
            $productsData = $statsObj->getDashboardLast12MonthsSummary($this->adminLangId, 'products', array(), 6);
            $productsChartData = [];
            foreach($productsData as $key => $val ){
                $productsChartData[$val["duration"]] = $val["value"];
            }
            //CommonHelper::printArray($affiliateSignupsChartData);


            $dashboardInfo["summary"]["sales"] = $statsObj->getDashboardSummary('sales');
            $dashboardInfo["summary"]["orders"] = $statsObj->getDashboardSummary('orders');
            $dashboardInfo["summary"]["users"] = $statsObj->getDashboardSummary('signups');
            $dashboardInfo["summary"]["shops"] = $statsObj->getDashboardSummary('shops');
            //$dashboardInfo["summary"]["products"] = $statsObj->getDashboardSummary('products');
            //$dashboardInfo['topSearchKeyword'] = $statsObj->getTopSearchKeywords('YEARLY');
            $dashboardInfo["stats"]["totalUsers"] = $statsObj->getStats('total_members');
            $dashboardInfo["stats"]["totalShops"] = $statsObj->getStats('total_shops');
            $dashboardInfo["stats"]["totalSales"] = $statsObj->getStats('total_sales');


            if($this->layoutDirection!='rtl') {

                $dashboardInfo['productsChartData'] = array_reverse($productsChartData);
                $dashboardInfo['salesChartData'] =  array_reverse($salesChartData);
                $dashboardInfo['salesEarningsChartData']= array_reverse($salesEarningsChartData);
                $dashboardInfo['signupsChartData'] =  array_reverse($signupsChartData);
                $dashboardInfo['affiliateSignupsChartData'] =  array_reverse($affiliateSignupsChartData);
            }else{
                $dashboardInfo['productsChartData'] = $productsChartData;
                $dashboardInfo['salesChartData'] =   $salesChartData;
                $dashboardInfo['salesEarningsChartData']= $salesEarningsChartData;
                $dashboardInfo['signupsChartData'] =  $signupsChartData;
                $dashboardInfo['affiliateSignupsChartData'] =  $affiliateSignupsChartData;

            }

            $dashboardInfo['topProducts'] = $statsObj->getTopProducts('YEARLY', $this->adminLangId, 10);
            $dashboardInfo['visits_chart_data'] = isset($visits_chart_data)?rtrim($visits_chart_data, ','):'';
            $dashboardInfo['visitsCount'] = (isset($visitCount))?$visitCount:'';
            $dashboardInfo['socialVisits'] = isset($socialVisits)?$socialVisits:'';
            $dashboardInfo['conversionChatData'] = $conversionChatData;
            $dashboardInfo['conversionStats'] = $conversionStats;

            $cache->set("dashboardInfo".$this->adminLangId, $dashboardInfo, 24*60*60);
        }

        //$saleStats = Stats::getTotalSalesStats();
        $this->_template->addJs(array('js/chartist.min.js','js/jquery.counterup.js','js/slick.min.js','js/enscroll-0.6.2.min.js'));
        $this->_template->addCss(array('css/chartist.css'));

        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false) {
            $this->_template->addCss('css/ie.css');
        }
        $this->set('dashboardInfo', $dashboardInfo);
        $this->_template->render();
    }

    public function searchStatistics()
    {
        $post = FatApp::getPostedData();
        $type = $post['type'];

        $statsObj = new Statistics();
        $dashboardInfo = array();

        switch(strtolower($type)){
            case 'statistics':
                $dashboardInfo["stats"]["totalUsers"] = $statsObj->getStats('total_members');
                $dashboardInfo["stats"]["totalSellerProducts"] = $statsObj->getStats('total_seller_products');
                $dashboardInfo["stats"]["totalShops"] = $statsObj->getStats('total_shops');
                $dashboardInfo["stats"]["totalOrders"] = $statsObj->getStats('total_orders');
                $dashboardInfo["stats"]["totalSales"] = $statsObj->getStats('total_sales');
                $dashboardInfo["stats"]["totalWithdrawalRequests"] = $statsObj->getStats('total_withdrawal_requests');
                $dashboardInfo["stats"]["totalAffiliateCommission"] = $statsObj->getStats('total_affiliate_commission');
                $dashboardInfo["stats"]["totalPpc"] = $statsObj->getStats('total_ppc_earnings');
                $dashboardInfo["stats"]["subscriptionEarnings"] = $statsObj->getStats('total_subscription_earnings');
                $dashboardInfo["stats"]["affiliateWithdrawalRequest"] = $statsObj->getStats('total_affiliate_withdrawal_requests');
                $dashboardInfo["stats"]["productReviews"] = $statsObj->getStats('total_product_reviews');
                break;
            case 'sellerproducts':
                $srch = new ProductSearch($this->adminLangId);
                $srch->doNotCalculateRecords();
                $srch->setPageNumber(1);
                $srch->setPageSize(10);
                $srch->setDefinedCriteria(0);
                $srch->joinProductToCategory();
                $srch->addMultipleFields(array('selprod_title', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(brand_name, brand_identifier) as brand_name', 'IFNULL(shop_name, shop_identifier) as shop_name', 'theprice', 'selprod_stock'));
                /* groupby added, because if same product is linked with multiple categories, then showing in repeat for each category[ */
                $srch->addGroupBy('selprod_id');
                $srch->addOrder('selprod_added_on', 'DESC');
                /* ] */
                $rs = $srch->getResultSet();
                $sellerProductsList = FatApp::getDb()->fetchAll($rs);
                $dashboardInfo['sellerProductsList'] = $sellerProductsList;
                break;
            case 'shops':
                $srch = new ShopSearch($this->adminLangId);
                $srch->setDefinedCriteria($this->adminLangId, 0);
                $srch->doNotCalculateRecords();
                $srch->setPageNumber(1);
                $srch->setPageSize(10);
                $srch->addOrder('shop_created_on', 'DESC');
                $srch->addMultipleFields(
                    array('IFNULL(shop_name, shop_identifier) as shop_name',
                    'credential_username as shop_owner_username', 'shop_created_on', 'shop_active')
                );

                $rs = $srch->getResultSet();
                $dashboardInfo['shopsList'] = FatApp::getDb()->fetchAll($rs);
                break;
            case 'signups':
                $userObj = new User();
                $srch = $userObj->getUserSearchObj();
                $srch->doNotCalculateRecords();
                $srch->addOrder('u.user_id', 'DESC');
                $cnd = $srch->addCondition('u.user_is_supplier', '=', 1);
                $cnd->attachCondition('u.user_is_buyer', '=', 1);
                $srch->addMultipleFields(
                    array('user_name', 'credential_username', 'credential_email', 'user_phone',
                    'user_regdate', 'user_is_buyer', 'user_is_supplier')
                );
                $srch->setPageNumber(1);
                $srch->setPageSize(10);
                $rs = $srch->getResultSet();
                $buyerSellerList = FatApp::getDb()->fetchAll($rs);
                $dashboardInfo['buyerSellerList'] = $buyerSellerList;
                break;
            case 'advertisers':
                $userObj = new User();
                $srch = $userObj->getUserSearchObj();
                $srch->doNotCalculateRecords();
                $srch->addOrder('u.user_id', 'DESC');
                $srch->addCondition('u.user_is_advertiser', '=', 1);
                $srch->addMultipleFields(array('user_name', 'credential_username', 'credential_email', 'user_phone', 'user_regdate'));
                $srch->setPageNumber(1);
                $srch->setPageSize(10);
                $rs = $srch->getResultSet();
                $advertisersList = FatApp::getDb()->fetchAll($rs);
                $dashboardInfo['advertisersList'] = $advertisersList;
                break;
            case 'affiliates':
                $userObj = new User();
                $srch = $userObj->getUserSearchObj();
                $srch->doNotCalculateRecords();
                $srch->addOrder('u.user_id', 'DESC');
                $srch->addCondition('u.user_is_affiliate', '=', 1);
                $srch->addMultipleFields(array('user_name', 'credential_username', 'credential_email', 'user_phone', 'user_regdate'));
                $srch->setPageNumber(1);
                $srch->setPageSize(10);
                $rs = $srch->getResultSet();
                $affiliatesList = FatApp::getDb()->fetchAll($rs);
                $dashboardInfo['affiliatesList'] = $affiliatesList;
                break;
        }

        $this->set('type', $type);
        $this->set('dashboardInfo', $dashboardInfo);
        $this->_template->render(false, false);
    }

    public function latestOrders($limit = 5)
    {
        $dashboardInfo = array();
        $srch = new OrderSearch();
        $srch->joinOrderBuyerUser();
        $srch->addOrder('order_date_added', 'DESC');
        $srch->addCondition('order_type', '=', Orders::ORDER_PRODUCT);
        $srch->setPageSize($limit);
        $srch->addMultipleFields(array('order_id','order_date_added', 'order_is_paid', 'buyer.user_name as buyer_user_name',  'order_net_amount'));
        $rs = $srch->getResultSet();
        $ordersList = FatApp::getDb()->fetchAll($rs);
        $dashboardInfo['recentOrders'] = $ordersList;
        $dashboardInfo['orderPaymentStatusArr'] = Orders::getOrderPaymentStatusArr($this->adminLangId);
        $this->set('dashboardInfo', $dashboardInfo);
        $this->_template->render(false, false);
    }

    public function dashboardStats()
    {
        $post = FatApp::getPostedData();
        $type = $post['rtype'];
        //$type = 'visitors_stats';
        $interval = isset($post['interval'])?$post['interval']:'';
        //$interval = 'yearly';

        include_once CONF_INSTALLATION_PATH . 'library/analytics/analyticsapi.php';
        $analyticArr = array(
        'clientId'=>FatApp::getConfig("CONF_ANALYTICS_CLIENT_ID"),
        'clientSecretKey'=>FatApp::getConfig("CONF_ANALYTICS_SECRET_KEY"),
        'redirectUri'=>CommonHelper::generateFullUrl('configurations', 'redirect', array(), '', false),
        'googleAnalyticsID'=>FatApp::getConfig("CONF_ANALYTICS_ID")
        );
        phpFastCache::setup("storage", "files");
        phpFastCache::setup("path", CONF_UPLOADS_PATH."caching");
        $cache = phpFastCache();

        $result = $cache->get("dashboardInfo_".$type.'_'.$interval.'_'.$this->adminLangId);
        if($result == null) {
            if(strtoupper($type) == 'TOP_PRODUCTS') {
                $statsObj = new Statistics();
                $result = $statsObj->getTopProducts($interval, $this->adminLangId, 10);
            }else{
                try{
                    $analytics = new Ykart_analytics($analyticArr);
                    $token = $analytics->getRefreshToken(FatApp::getConfig("CONF_ANALYTICS_ACCESS_TOKEN"));
                    if(isset($token['accessToken'])) {
                        $analytics->setAccessToken($token['accessToken']);
                    }
                    $accountId = $analytics->setAccountId(FatApp::getConfig("CONF_ANALYTICS_ID"));
                    switch(strtoupper($type)){
                    case 'TOP_COUNTRIES':
                        $result = $analytics->getTopCountries($interval, 9);

                        break;
                    case 'TOP_REFERRERS':
                        $result = $analytics->getTopReferrers($interval, 9);
                        break;
                    case 'TOP_SEARCH_KEYWORD':
                        //$result=$analytics->getSearchTerm($interval,9);
                        $statsObj = new Statistics();
                        $result = $statsObj->getTopSearchKeywords($interval, 10);
                        break;
                    case 'TRAFFIC_SOURCE':
                        $result = $analytics->getTrafficSource($interval);
                        break;
                    case 'VISITORS_STATS':
                        $result = $analytics->getVisitsByDate();
                        break;
                    case 'TOP_PRODUCTS':
                        $statsObj = new Statistics();
                        $result = $statsObj->getTopProducts($interval, $this->adminLangId, 10);
                        break;
                    }
                }catch(exception $e){
                    echo $e->getMessage();
                }
            }

            $cache->set("dashboardInfo_".$type.'_'.$interval.'_'.$this->adminLangId, $result, 6*60*60);
        }
        $this->set('stats_type', strtoupper($type));
        $this->set('stats_info', $result);
        $this->_template->render(false, false);
    }

    function clear()
    {
        CommonHelper::recursiveDelete(CONF_UPLOADS_PATH."caching");
        FatCache::clearAll();
        Message::addMessage(Labels::getLabel('LBL_Cache_has_been_cleared', $this->adminLangId));
        if(Labels::isAPCUcacheAvailable()) {
            apcu_clear_cache();
        }
        Product::updateMinPrices();
        //FatApp::redirectUser(CommonHelper::generateUrl("home"));
    }
    public function setLanguage($langId = 0)
    {

        $langId =  FatUtility::int($langId);
        if(0 < $langId ) {
            $languages = Language::getAllNames();
            if(array_key_exists($langId, $languages)) {
                setcookie('defaultAdminSiteLang', $langId, time()+3600*24*10, CONF_WEBROOT_FRONT_URL);
            }
            $this->set('msg', Labels::getLabel('Msg_Please_Wait_We_are_redirecting_you...', $this->adminLangId));
            $this->_template->render(false, false, 'json-success.php');
        }
        Message::addErrorMessage(Labels::getLabel('MSG_Please_select_any_language', $this-adminLangId));
        FatUtility::dieWithError(Message::getHtml());
    }
}
