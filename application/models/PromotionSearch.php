<?php
class PromotionSearch extends SearchBase
{
    private $langId;

    public function __construct($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;

        parent::__construct(Promotion::DB_TBL, 'pr');

        if ($langId > 0) {
            $this->joinTable(
                Promotion::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'pr_l.promotionlang_promotion_id = pr.promotion_id
			AND pr_l.promotionlang_lang_id = ' . $langId,
                'pr_l'
            );
        }
    }

    public function setDefinedCriteria()
    {
        $this->addCondition('promotion_deleted', '=', applicationConstants::NO);
        $this->addCondition('promotion_active', '=', applicationConstants::YES);
    }

    public function joinSlides($langId = 0, $type = Promotion::TYPE_SLIDES, $alias = 'sl')
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(
            Slides::DB_TBL,
            'LEFT OUTER JOIN',
            'pr.promotion_type = '.$type.' and '.$alias.'.slide_type = '.Slides::TYPE_PPC.' and '.$alias.'.slide_record_id = pr.promotion_id',
            $alias
        );
    }

    public function joinBannersAndLocation($langId = 0, $type = Promotion::TYPE_BANNER, $alias = 'b', $deviceType = 0)
    {
        $langId = FatUtility::int($langId);
        $deviceType = FatUtility::int($deviceType);

        $deviceType = ($deviceType > 0) ? $deviceType : applicationConstants::SCREEN_DESKTOP;
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(
            Banner::DB_TBL,
            'LEFT OUTER JOIN',
            'pr.promotion_type = '.$type.' and '.$alias.'.banner_type = '.Banner::TYPE_PPC.' and '.$alias.'.banner_record_id = pr.promotion_id',
            $alias
        );

        if ($langId > 0) {
            $this->joinTable(
                Banner::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                $alias.'_l.bannerlang_banner_id = '.$alias.'.banner_id and '.$alias.'_l.bannerlang_lang_id = '.$langId,
                $alias.'_l'
            );
        }

        $this->joinTable(
            BannerLocation::DB_TBL,
            'LEFT OUTER JOIN',
            $alias.'bl.blocation_id = '.$alias.'.banner_blocation_id',
            $alias.'bl'
        );

        $this->joinTable(
            BannerLocation::DB_DIMENSIONS_TBL,
            'LEFT OUTER JOIN',
            $alias.'bld.bldimension_blocation_id = '.$alias.'.banner_blocation_id
			AND bldimension_device_type = ' . $deviceType,
            $alias.'bld'
        );

        if ($langId > 0) {
            $this->joinTable(
                BannerLocation::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                $alias.'bl_l.blocationlang_blocation_id = '.$alias.'bl.blocation_id and '.$alias.'bl_l.blocationlang_lang_id = '.$langId,
                $alias.'bl_l'
            );
        }
    }

    public function joinPromotionsLogForCount($fromDate = '', $todate = '', $groupBy = 'plog_promotion_id')
    {
        $srch = new SearchBase(Promotion::DB_TBL_LOGS, 'i');
        $srch->addMultipleFields(array('i.plog_promotion_id','sum(i.plog_impressions) as impressions','sum(i.plog_clicks) as clicks','sum(i.plog_orders) as orders','plog_date'));
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();

        if ($fromDate != '') {
            $fromDate = FatDate::convertDatetimeToTimestamp($fromDate);
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $srch->addCondition('i.plog_date', '>=', $fromDate.' 00:00:00');
        }

        if ($todate != '') {
            $toDate = FatDate::convertDatetimeToTimestamp($toDate);
            $toDate = date('Y-m-d', strtotime($toDate));
            $srch->addCondition('i.plog_date', '<=', $todate.' 23:59:59');
        }

        $srch->addGroupBy('i.'.$groupBy);

        $this->joinTable('(' . $srch->getQuery() . ')', 'LEFT OUTER JOIN', 'pri.plog_promotion_id = pr.promotion_id', 'pri');
    }

    public function joinShops($langId = 0, $isActive = true, $isDisplayStatus = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 's.shop_user_id = pr.promotion_user_id', 's');
        if ($langId > 0) {
            $this->joinTable(
                SHOP::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                's_l.'.SHOP::DB_TBL_LANG_PREFIX.'shop_id = s.'.SHOP::tblFld('id').' and
			s_l.'.SHOP::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                's_l'
            );
        }
        if (!$isActive && !$isDisplayStatus) {
            return ;
        }

        $and = $displayStatusCondition = $activeCondition = '';

        if ($isActive) {
            $activeCondition = 's.shop_active ='. applicationConstants::ACTIVE;
            $and = ' and ';
        }

        if ($isDisplayStatus) {
            $displayStatusCondition = $and.'s.shop_supplier_display_status ='. applicationConstants::ON;
        }
        $this->addDirectCondition(
            '(
                                        CASE
                                            WHEN u.user_is_advertiser = "'. applicationConstants::YES .'" AND (pr.promotion_type = "'. Promotion::TYPE_BANNER .'" OR pr.promotion_type = "'. Promotion::TYPE_SLIDES .'")
                                            THEN TRUE
                                            ELSE ' . $activeCondition . $displayStatusCondition .'
                                        END
                                    )'
        );
    }

    public function joinProducts($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_id = pr.promotion_record_id and pr.promotion_type = '.Promotion::TYPE_PRODUCT, 'sp');
        $this->joinTable(Product::DB_TBL, 'LEFT OUTER JOIN', 'tp.product_id = sp.selprod_product_id', 'tp');

        if ($langId) {
            $this->joinTable(
                SellerProduct::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'sp_l.'.SellerProduct::DB_LANG_TBL_PREFIX.'selprod_id = sp.'.SellerProduct::tblFld('id').' and
			sp_l.'.SellerProduct::DB_LANG_TBL_PREFIX.'lang_id = '.$langId,
                'sp_l'
            );

            $this->joinTable(
                Product::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'productlang_product_id = tp.product_id
			AND productlang_lang_id = ' . $langId,
                'tp_l'
            );
        }
    }

    public function addDateCondition($dateFrom, $dateTo)
    {
        if (!empty($dateTo)) {
            $this->addCondition('pr.promotion_start_date', '<=', $dateTo);
        }

        if (!empty($dateFrom)) {
            $this->addCondition('pr.promotion_end_date', '>=', $dateFrom);
        }
    }

    /* public function addDateFromCondition($dateFrom, $dateTo){

    $dateFrom = FatDate::convertDatetimeToTimestamp($dateFrom);
    $dateFrom = date('Y-m-d', strtotime( $dateFrom ));
    $dateTo = FatDate::convertDatetimeToTimestamp($dateTo);
    $dateTo = date('Y-m-d', strtotime( $dateTo ));

    if( $dateFrom != '' && $dateTo == ''){
    $cnd = $this->addCondition('pr.promotion_start_date', '>=', $dateFrom. ' 00:00:00');
    $cnd->attachCondition('pr.promotion_end_date', '>=', $dateFrom. ' 00:00:00');
    }else if( $dateFrom != ''){
    $this->addCondition('pr.promotion_start_date', '>=', $dateFrom. ' 00:00:00');
    }

    }

    public function addDateToCondition($dateTo, $dateFrom){

    $dateTo = FatDate::convertDatetimeToTimestamp($dateTo);
    $dateTo = date('Y-m-d', strtotime( $dateTo ));
    $dateFrom = FatDate::convertDatetimeToTimestamp($dateFrom);
    $dateFrom = date('Y-m-d', strtotime( $dateFrom ));

    if( $dateTo != '' && $dateFrom == ''){
    $cnd = $this->addCondition('pr.promotion_end_date', '<=', $dateTo. ' 23:59:59');
    $cnd->attachCondition('pr.promotion_start_date', '<=', $dateTo. ' 23:59:59');

    }else if( $dateTo != ''){
    $this->addCondition('pr.promotion_end_date', '<=', $dateTo. ' 23:59:59');
    }
    } */

    public function addPromotionTypeCondition($type)
    {
        if ($type != '') {
            $this->addCondition('pr.promotion_type', '=', $type);
        }
    }

    public function addShopActiveExpiredCondition()
    {
        $this->addCondition('shop_active', '=', applicationConstants::ACTIVE);

        //$this->addDirectCondition('(CONCAT(pr.promotion_start_date," ",pr.promotion_start_time) <= NOW()) AND ( CONCAT(pr.promotion_end_date," ",pr.promotion_end_time) >= NOW())');



        $this->addCondition('pr.promotion_start_date', '<=', date('Y-m-d 00:00:00'));

        $this->addCondition('pr.promotion_end_date', '>=', date('Y-m-d 00:00:00'));

        /* $this->addDirectCondition("case when
        (pr.promotion_start_time <= NOW() and  pr.promotion_end_time >= NOW())
        Then pr.promotion_start_time <= NOW() and pr.promotion_end_time >= NOW()
        when ( pr.promotion_start_time >= NOW() and pr.promotion_end_time <= NOW())
        then
        pr.promotion_end_time <= NOW() and pr.promotion_start_time >= NOW()

        when ( pr.promotion_start_time <= NOW() and pr.promotion_end_time >= NOW())
        then
        pr.promotion_end_time >= NOW() and pr.promotion_start_time <= NOW() end ");  */


        $this->addCondition('pr.promotion_start_time', '<=', date('H:i'));

        $this->addCondition('pr.promotion_end_time', '>=', date('H:i'));


        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->addActiveSubscriptionCondition();
        }
    }

    public function addActiveSubscriptionCondition()
    {
        $this->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_user_id=s.shop_user_id and o.order_type='.ORDERS::ORDER_SUBSCRIPTION, 'o');
        $this->joinTable(OrderSubscription::DB_TBL, 'INNER JOIN', 'o.order_id = oss.ossubs_order_id', 'oss');
        $this->addCondition('oss.ossubs_till_date', '>=', date("Y-m-d"));
        $this->addCondition('ossubs_status_id', 'IN ', Orders::getActiveSubscriptionStatusArr());
    }

    public function joinUserWallet()
    {
        $this->joinedUserWallet = true;
        $txnObj = new Transactions();
        $srch = $txnObj -> getSearchObject();
        $srch->addMultipleFields(array('IFNULL(SUM(utxn.utxn_credit)-SUM(utxn.utxn_debit),0) AS userBalance','utxn_user_id'));
        $srch->doNotCalculateRecords();
        $srch->doNotlimitRecords();
        $srch->addCondition('utxn_status', '=', applicationConstants::ACTIVE);
        $srch->addGroupBy('utxn_user_id');

        $this->joinTable('(' . $srch->getQuery() . ')', 'LEFT OUTER JOIN', 'pr.promotion_user_id = uw.utxn_user_id ', 'uw');

        $this->addCondition('userBalance', '>=', FatApp::getConfig('CONF_PPC_MIN_WALLET_BALANCE'));
    }

    public function joinBudget()
    {
        $srch = new SearchBase(Promotion::DB_TBL_ITEM_CHARGES, 'tpic');
        $srch->joinTable(Promotion::DB_TBL_CLICKS, 'LEFT OUTER JOIN', 'tpc.'.Promotion::DB_TBL_CLICKS_PREFIX.'id = tpic.'.Promotion::DB_TBL_ITEM_CHARGES_PREFIX.'pclick_id', 'tpc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('tpc.'.Promotion::DB_TBL_CLICKS_PREFIX.'promotion_id');
        $srch->addMultipleFields(
            array(
            'tpc.pclick_promotion_id',
            "SUM(IF(date(`picharge_datetime`)>CURRENT_DATE - INTERVAL 1 DAY,`picharge_cost`,0)) daily_cost,
			SUM(IF(date(`picharge_datetime`)>CURRENT_DATE - INTERVAL 1 WEEK,`picharge_cost`,0)) weekly_cost,
			SUM(IF(date(`picharge_datetime`)>CURRENT_DATE - INTERVAL 1 MONTH,`picharge_cost`,0)) monthly_cost",
            "SUM(picharge_cost) as total_cost"
            )
        );

        $this->joinTable('(' . $srch->getQuery() . ')', 'LEFT OUTER JOIN', 'pr.promotion_id =pclick_promotion_id', 'pcb');
    }

    public function joinPromotionItemClick()
    {
        $this->joinTable(Promotion::DB_TBL_ITEM_CHARGES, 'Inner OUTER JOIN', 'pc.'.Promotion::DB_TBL_CLICKS_PREFIX.'id = tpic.'.Promotion::DB_TBL_ITEM_CHARGES_PREFIX.'pclick_id', 'tpic');
    }

    public function joinPromotionItemLastClick()
    {
        $this->joinTable(Promotion::DB_TBL_ITEM_CHARGES, 'LEFT OUTER JOIN', 'pc.'.Promotion::DB_TBL_CLICKS_PREFIX.'id = tpic.'.Promotion::DB_TBL_ITEM_CHARGES_PREFIX.'pclick_id and case when pcharge_end_piclick_id then  picharge_id>pcharge_end_piclick_id else picharge_id>0 end', 'tpic');
    }

    public function addBudgetCondition()
    {
        $this->addDirectCondition(
            '((CASE
			WHEN promotion_duration='.Promotion::DAILY.' THEN promotion_budget > COALESCE(daily_cost,0)
			WHEN promotion_duration='.Promotion::WEEKLY.' THEN promotion_budget > COALESCE(weekly_cost,0)
			WHEN promotion_duration='.Promotion::MONTHLY.' THEN promotion_budget > COALESCE(monthly_cost,0)

		  END ) )'
        );
    }

    public function joinShopCountry($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 's.shop_country_id = shop_country.country_id', 'shop_country');

        if ($langId) {
            $this->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop_country.country_id = shop_country_l.countrylang_country_id AND shop_country_l.countrylang_lang_id = '.$langId, 'shop_country_l');
        }
        if ($isActive) {
            $this->addCondition('shop_country.country_active', '=', applicationConstants::ACTIVE);
        }
    }

    public function joinShopState($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 's.shop_state_id = shop_state.state_id', 'shop_state');

        if ($langId) {
            $this->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop_state.state_id = shop_state_l.statelang_state_id AND shop_state_l.statelang_lang_id = '.$langId, 'shop_state_l');
        }
        if ($isActive) {
            $this->addCondition('shop_state.state_active', '=', applicationConstants::ACTIVE);
        }
    }

    public function joinPromotionClick()
    {
        $this ->joinTable(Promotion::DB_TBL_CLICKS, 'LEFT OUTER JOIN', 'pr.promotion_id = pc.pclick_promotion_id', 'pc');
    }

    public function joinPromotionCharge()
    {
        $this->joinTable(Promotion::DB_TBL_CHARGES, 'LEFT OUTER JOIN', 'tpc.'.Promotion::DB_TBL_CHARGES_PREFIX.'promotion_id = pr.'.Promotion::DB_TBL_PREFIX.'id', 'tpc');
    }

    public function addLastChargeCondition()
    {
        $Cnd = $this->addHaving('charge_till_date', '<=', date('Y-m-d', strtotime('-'.FatAPP::getConfig('CONF_PPC_WALLET_CHARGE_DAYS_INTERVAL').' days')));
        $Cnd->attachCondition('charge_till_date', '=', '0000-00-00', 'OR');
    }
    public function addLastChargeClickItemCondition()
    {
        $srch = new SearchBase(Promotion::DB_TBL_CHARGES, 'tpc');

        $srch->addOrder('tpc.'.Promotion::DB_TBL_CHARGES_PREFIX.'id', 'desc');

        $this->joinTable('(' . $srch->getQuery() . ')', 'LEFT OUTER JOIN', 'pr.promotion_id =pclick_promotion_id', 'pcb');
    }

    public function joinActiveUser($isActive = true)
    {
        $this->joinTable(User::DB_TBL, 'LEFT JOIN', 'pr.promotion_user_id = u.user_id', 'u');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT JOIN', 'cu.credential_user_id = u.user_id', 'cu');
        if ($isActive) {
            $this->addCondition('cu.credential_active', '=', applicationConstants::ACTIVE);
        }
    }
}
