<?php
class OrderSubscription extends MyAppModel
{
    const DB_TBL = 'tbl_order_seller_subscriptions';
    const DB_TBL_PREFIX = 'ossubs_';

    const DB_TBL_LANG = 'tbl_order_seller_subscriptions_lang';
    const DB_TBL_LANG_PREFIX = 'ossubslang_';

    const ACTIVE_SUBSCRIPTION = 11;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'oss');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'oss_l.'.static::DB_TBL_LANG_PREFIX.'oss_id = o.ossub_id
			AND opssub_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = ' . $langId,
                'ossub_l'
            );
        }

        return $srch;
    }


    public static function canUserBuyFreeSubscription($langId = 0, $userId = 0)
    {
        $srch = new  OrderSearch($langId);
        $srch->joinTableOrderSellerSubscription();
        $srch ->addCondition(Orders::DB_TBL_PREFIX.'type', '=', Orders::ORDER_SUBSCRIPTION);
        $srch->addCondition(Orders::DB_TBL_PREFIX.'is_paid', '=', Orders::ORDER_IS_PAID);
        $srch->addCondition(Orders::DB_TBL_PREFIX.'user_id', '=', $userId);
        $srch->setPageSize(1);

        $srch->addOrder(Orders::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();

        $rowCount =  $srch->recordCount();
        if ($rowCount == 0) {
            return true;
        }
        return false;
    }
    public static function getUserCurrentActivePlanDetails($langId = 0, $userId = 0, $flds = array(OrderSubscription::DB_TBL_PREFIX.'id'))
    {
        $srch = new  OrderSearch($langId);
        $srch->joinTableOrderSellerSubscription($langId);
        $srch->joinTableSubscriptionPlan();
        $srch->joinPackage($langId);

        //$srch->addSubscriptionValidCondition();
        $srch ->addCondition(Orders::DB_TBL_PREFIX.'type', '=', Orders::ORDER_SUBSCRIPTION);
        $srch->addCondition(Orders::DB_TBL_PREFIX.'is_paid', '=', Orders::ORDER_IS_PAID);
        $srch->addCondition(Orders::DB_TBL_PREFIX.'user_id', '=', $userId);
        $srch->addCondition('ossubs_status_id', 'IN ', Orders::getActiveSubscriptionStatusArr());
        $srch->addMultipleFields($flds);
        $srch->setPageSize(1);
        $srch->doNotCalculateRecords(true);
        $srch->addOrder(Orders::DB_TBL_PREFIX.'id', 'desc');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs, $flds);

        if ($row==false) {
            return '';
        }
        if (count($flds)==1) {
            return $row[$flds[0]];
        } else {
            return $row;
        }
    }

    public static function getOSSubIdArrByOrderId($orderId)
    {
        $opSrch = static::getSearchObject();
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $opSrch->addMultipleFields(array(OrderSubscription::DB_TBL_PREFIX.'id'));
        $opSrch->addCondition(OrderSubscription::DB_TBL_PREFIX.'order_id', '=', $orderId);

        $rs = $opSrch->getResultSet();
        return $row = FatApp::getDb()->fetchAll($rs, OrderSubscription::DB_TBL_PREFIX.'id');
    }
    public static function getChargeTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(

        OrderProduct::CHARGE_TYPE_DISCOUNT =>    Labels::getLabel('LBL_Order_Product_Discount_Charges', $langId),

        OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT    =>    Labels::getLabel('LBL_Order_Product_Reward_Point', $langId),

        );
    }

    public function getOrderSubscriptionByOssubId($ossubs_id, $langId)
    {
        $ossubs_id = FatUtility::int($ossubs_id);
        $langId = FatUtility::int($langId);
        $srch = new OrderSubscriptionSearch($langId);
        $srch->joinTable(Orders::DB_TBL, 'LEFT OUTER JOIN', 'o.'.Orders::DB_TBL_PREFIX.'id = oss.'.OrderSubscription::DB_TBL_PREFIX.'order_id', 'o');
        $srch->joinTable(Orders::DB_TBL_CHARGES, 'LEFT OUTER JOIN', 'oc.'.Orders::DB_TBL_CHARGES_PREFIX.'op_id = oss.'.OrderSubscription::DB_TBL_PREFIX.'id', 'oc');
        $srch->addMultipleFields(array('oss.*','oss_l.*','o.'.Orders::DB_TBL_PREFIX.'is_paid','o.'.Orders::DB_TBL_PREFIX.'language_id','o.'.Orders::DB_TBL_PREFIX.'user_id','sum('.OrderProduct::DB_TBL_CHARGES_PREFIX.'amount) as op_other_charges'));
        $srch->addCondition(OrderSubscription::DB_TBL_PREFIX.'id', '=', $ossubs_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $records = array();
        $row = FatApp::getDb()->fetch($srch->getResultSet());
        $charges = $this->getOrderSubscriptionChargesArr($ossubs_id);
        if (!empty($row)) {
            $records = $row;
            $records['charges'] = $charges;
        }
        return $records;
    }

    public function getOrderSubscriptionChargesArr($ossubs_id)
    {
        $ossubs_id = FatUtility::int($ossubs_id);
        $srch = new SearchBase(Orders::DB_TBL_CHARGES, 'oc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array(Orders::DB_TBL_CHARGES_PREFIX.'type',Orders::DB_TBL_CHARGES_PREFIX.'amount'));
        $srch->addCondition(Orders::DB_TBL_CHARGES_PREFIX.'op_id', '=', $ossubs_id);
        $srch->addCondition(Orders::DB_TBL_CHARGES_PREFIX.'type', '=', Orders::ORDER_SUBSCRIPTION);
        $rs = $srch->getResultSet();
        return $row = FatApp::getDb()->fetchAll($rs, Orders::DB_TBL_CHARGES_PREFIX.'type');
    }

    public static function searchOrderSubscription($criteria = array(), $langId = 0)
    {
        $srch = new OrderSubscriptionSearch();


        foreach ($criteria as $key => $val) {
            if (strval($val)=='') {
                continue;
            }
            switch ($key) {
                case 'id':
                case OrderSubscription::DB_TBL_PREFIX.'id':
                    $ossubs_id = FatUtility::int($val);
                    $srch->addCondition('oss.'.OrderSubscription::DB_TBL_PREFIX.'id', '=', $ossubs_id);
                    break;
                case 'order':
                case Orders::DB_TBL_PREFIX.'id':
                    $srch->addCondition(OrderSubscription::DB_TBL_PREFIX.'order_id', '=', $val);
                    break;
            }
        }
        return $srch;
    }
    public static function getAdjustedAmount($currentPlanDetails = array(), $userId = 0)
    {
        if (empty($currentPlanDetails)) {
            die(Labels::getLabel('MSG_Please_Provide_plan_details', CommonHelper::getLangId()));
        }
        if ($currentPlanDetails[SellerPackages::DB_TBL_PREFIX.'type']==SellerPackages::FREE_TYPE) {
            return 0;
        }
        $planDetails = SellerPackagePlans::getAttributesById($currentPlanDetails[OrderSubscription::DB_TBL_PREFIX.'plan_id']);

        $activePlanInterval = $planDetails[SellerPackagePlans::DB_TBL_PREFIX.'interval'];
        $activePlanFrequency = $planDetails[SellerPackagePlans::DB_TBL_PREFIX.'frequency'];

        $pendingDaysForCurrentPlan=FatDate::diff(date("Y-m-d"), $currentPlanDetails[OrderSubscription::DB_TBL_PREFIX.'till_date'])-1;
        if ($pendingDaysForCurrentPlan<0) {
            $pendingDaysForCurrentPlan =0;
        }
        $totalPlanDays = FatDate::diff($currentPlanDetails[OrderSubscription::DB_TBL_PREFIX.'from_date'], $currentPlanDetails[OrderSubscription::DB_TBL_PREFIX.'till_date']);
        $perDayPrice  = $currentPlanDetails[OrderSubscription::DB_TBL_PREFIX.'price']/$totalPlanDays;
        $adjustableAmount = $perDayPrice * $pendingDaysForCurrentPlan;
        return $adjustableAmount;
    }
    public static function getSubscriptionEndingList($lastUserIdCond = false)
    {
        $statusArr  = Orders::getActiveSubscriptionStatusArr();
        $endDate  = date("Y-m-d");
        $srch = new OrderSubscriptionSearch();
        $srch->joinOrders();

        $srch->joinOrderUser();
        $srch->addCondition('order_is_paid', '=', ORDERS::ORDER_IS_PAID);
        $srch->addCondition('ossubs_status_id', 'in', $statusArr);
        $srch->addCondition('ossubs_till_date', '=', date('Y-m-d', strtotime('+'.FatApp::getConfig('CONF_BEFORE_EXIPRE_SUBSCRIPTION_REMINDER_EMAIL_DAYS', FatUtility::VAR_INT, 2).' days')));

        $srch->addCondition('ossubs_till_date', '!=', '0000-00-00');
        /* $srch->addCondition('user_autorenew_subscription','=',1); */
        $srch->addMultipleFields(array('user_id','ossubs_id','ossubs_type','user_name','credential_email','order_language_id'));
        /* $srch->addGroupBy('order_user_id');  */
        $srch->addOrder('ossubs_id', 'desc');
        if ($lastUserIdCond) {
            $srch->addCondition('user_id', '>', FatApp::getConfig("CONF_CRON_SUBSCRIPTION_REMINDER_LAST_EXECUTED_USERID", FatUtility::VAR_INT, 0));
        }

        $rs = $srch->getResultSet();
        $activeSusbscriptions = FatApp::getDb()->fetchAll($rs, 'ossubs_id');

        return $activeSusbscriptions;
    }

    public static function getSubscriptionTitle($plan, $langId = 0)
    {
        if (!$langId) {
            $langId = CommonHelper::getLangId();
        }
        $price = $plan['ossubs_price'];

        $subcriptionPeriodArr = SellerPackagePlans::getSubscriptionPeriods($langId);

        if ($plan['ossubs_frequency']==SellerPackagePlans::SUBSCRIPTION_PERIOD_UNLIMITED) {
            return CommonHelper::displayMoneyFormat($price)." /  ". $subcriptionPeriodArr[$plan['ossubs_frequency']];
        }
        $planText = ($plan['ossubs_type']==SellerPackages::PAID_TYPE)?" /"." ".Labels::getLabel("LBL_Per", $langId):Labels::getLabel("LBL_For", $langId);

        return $plan['ossubs_subscription_name']." - ".CommonHelper::displayMoneyFormat($price).$planText." ".(($plan['ossubs_interval']>0)?$plan['ossubs_interval']:'')
        ."  ". $subcriptionPeriodArr[$plan['ossubs_frequency']];
    }
}
