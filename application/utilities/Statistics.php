<?php 
class Statistics
{
    public static function sellerSalesGraph($template)
    {
        $loggedUserId = 0;
        if(UserAuthentication::isUserLogged() ) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }
        
        $dashboardStats = Stats::getUserSales($loggedUserId, STATS::SELLER_DASHBOARD_SALES_MONTH);
        $sales_earnings_chart_data = array();
        foreach($dashboardStats as $saleskey=>$salesval ){
            $sales_earnings_chart_data[$saleskey]=round($salesval, 2);
        }
        
        $dashboardInfo['sales_earnings_chart_data'] = array_reverse($sales_earnings_chart_data);
        
        $template->set('siteLangId', CommonHelper::getLangId());
        $template->set('dashboardInfo', $dashboardInfo);
    }
}
