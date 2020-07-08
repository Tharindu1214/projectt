<?php
class SellerPackagePlans extends MyAppModel
{
    const DB_TBL = 'tbl_seller_packages_plan';
    const DB_TBL_PREFIX = 'spplan_';

    const SUBSCRIPTION_PERIOD_DAYS = 'D';
    const SUBSCRIPTION_PERIOD_WEEKS = 'W';
    const SUBSCRIPTION_PERIOD_MONTH = 'M';
    const SUBSCRIPTION_PERIOD_YEAR = 'Y';
    const SUBSCRIPTION_PERIOD_UNLIMITED = 'U';



    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'spp');

        return $srch;
    }


    public static function getSellerVisiblePackagePlans($packageId = 0)
    {
        $srch = new SellerPackagePlansSearch();
        $srch ->joinPackage();
        $srch->addMultipleFields(array( "spp.*","spackage_type"));
        if ($packageId>0) {
            $srch->addCondition(SellerPackagePlans::DB_TBL_PREFIX.'spackage_id', '=', $packageId);
        }
        $srch->addCondition('spp.spplan_active', '=', applicationConstants::YES);
        $rs = $srch->getResultSet();
        $records = array();
        $records = FatApp::getDb()->fetchAll($rs);

        return $records;
    }
    public static function getSubscriptionPeriods($langId = 0)
    {
        $langId = FatUtility::convertToType($langId, FatUtility::VAR_INT);
        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', CommonHelper::getLangId), E_USER_ERROR);
            return false;
        }
        return array(
        self::SUBSCRIPTION_PERIOD_DAYS => Labels::getLabel('LBL_Days', $langId),
        /* self::SUBSCRIPTION_PERIOD_WEEKS => Labels::getLabel('LBL_Weeks', $langId), */
        self::SUBSCRIPTION_PERIOD_MONTH => Labels::getLabel('LBL_Months', $langId),
        self::SUBSCRIPTION_PERIOD_YEAR => Labels::getLabel('LBL_Years', $langId),
        self::SUBSCRIPTION_PERIOD_UNLIMITED => Labels::getLabel('LBL_Unlimited', $langId),
        );
    }
    public static function getSubscriptionPeriodValues()
    {
        return array(
        self::SUBSCRIPTION_PERIOD_DAYS => 'DAY' ,
        /* self::SUBSCRIPTION_PERIOD_WEEKS => self::SUBSCRIPTION_PERIOD_WEEKS, */
        self::SUBSCRIPTION_PERIOD_MONTH => 'MONTHS' ,
        self::SUBSCRIPTION_PERIOD_YEAR => 'YEAR',
        self::SUBSCRIPTION_PERIOD_UNLIMITED => 'YEAR',
        );
    }
    public static function getPlanPeriod($plan)
    {
        $subcriptionPeriodArr = self::getSubscriptionPeriods(CommonHelper::getLangId());
        return ($plan[SellerPackagePlans::DB_TBL_PREFIX.'interval']>1)?$plan[SellerPackagePlans::DB_TBL_PREFIX.'interval']:''." ".Labels::getLabel("LBL_Per", CommonHelper::getLangId())." "
        . $subcriptionPeriodArr[$plan[SellerPackagePlans::DB_TBL_PREFIX.'frequency']];
    }
    public static function getPlanTrialPeriod($plan)
    {
        $subcriptionPeriodArr = self::getSubscriptionPeriods(CommonHelper::getLangId());
        if ($plan[SellerPackagePlans::DB_TBL_PREFIX.'trial_interval']<1) {
            return Labels::getLabel("LBL_N/A", CommonHelper::getLangId());
        }
        return (($plan[SellerPackagePlans::DB_TBL_PREFIX.'trial_interval']>0)?$plan[SellerPackagePlans::DB_TBL_PREFIX.'trial_interval']:'')." "
        . $subcriptionPeriodArr[$plan[SellerPackagePlans::DB_TBL_PREFIX.'trial_frequency']];
    }
    public static function getPlanPriceWithPeriod($plan, $price)
    {
        $subcriptionPeriodArr = self::getSubscriptionPeriods(CommonHelper::getLangId());
        if ($plan[SellerPackagePlans::DB_TBL_PREFIX.'frequency']==SellerPackagePlans::SUBSCRIPTION_PERIOD_UNLIMITED) {
            return CommonHelper::displayMoneyFormat($price)." /  ". $subcriptionPeriodArr[$plan[SellerPackagePlans::DB_TBL_PREFIX.'frequency']];
        }

        $planText = ($plan[SellerPackages::DB_TBL_PREFIX.'type']==SellerPackages::PAID_TYPE)?" /"." ".Labels::getLabel("LBL_Per", CommonHelper::getLangId()):Labels::getLabel("LBL_For", CommonHelper::getLangId());
        return CommonHelper::displayMoneyFormat($price).$planText." ".(($plan[SellerPackagePlans::DB_TBL_PREFIX.'interval']>0)?$plan[SellerPackagePlans::DB_TBL_PREFIX.'interval']:'')
        ."  ". $subcriptionPeriodArr[$plan[SellerPackagePlans::DB_TBL_PREFIX.'frequency']];
    }

    public static function getCheapPlanPriceWithPeriod($plan, $price)
    {
        $subcriptionPeriodArr = self::getSubscriptionPeriods(CommonHelper::getLangId());
        return CommonHelper::displayMoneyFormat($price, false, false, true, true)." <br/>"." ".Labels::getLabel("LBL_Per", CommonHelper::getLangId())." ".(($plan[SellerPackagePlans::DB_TBL_PREFIX.'interval']>1)?$plan[SellerPackagePlans::DB_TBL_PREFIX.'interval']:'')
        ."  ". $subcriptionPeriodArr[$plan[SellerPackagePlans::DB_TBL_PREFIX.'frequency']];
    }
    public static function getPlanByPackageId($spackageId = 0)
    {
        $spackageId = FatUtility::convertToType($spackageId, FatUtility::VAR_INT);
        if (!$spackageId) {
            trigger_error(Labels::getLabel('ERR_Package_Id_Not_Specified', CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }
        $srch = new SellerPackagePlansSearch();
        $srch->joinPackage(CommonHelper::getLangId());
        $srch->addMultipleFields(array( "spackage_type","spp.*","spp.".SellerPackagePlans::DB_TBL_PREFIX."price"));
        $srch->addCondition(SellerPackagePlans::DB_TBL_PREFIX.'spackage_id', '=', $spackageId);

        $srch->addOrder(SellerPackagePlans::DB_TBL_PREFIX.'display_order');

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        return $records;
    }

    public static function getCheapestPlanByPackageId($spackageId = 0)
    {
        $spackageId = FatUtility::convertToType($spackageId, FatUtility::VAR_INT);
        if (!$spackageId) {
            trigger_error(Labels::getLabel('ERR_Package_Id_Not_Specified', CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }
        $srch = new SellerPackagePlansSearch();
        $srch->addCondition(SellerPackagePlans::DB_TBL_PREFIX.'spackage_id', '=', $spackageId);
        $srch->addMultipleFields(array(SellerPackagePlans::DB_TBL_PREFIX.'price',SellerPackagePlans::DB_TBL_PREFIX.'interval',SellerPackagePlans::DB_TBL_PREFIX.'frequency'));
        $srch->addOrder(SellerPackagePlans::DB_TBL_PREFIX.'price', 'asc');
        $srch->addCondition('spp.spplan_active', '=', applicationConstants::YES);
        $srch->setPageSize(1);
        $srch->doNotCalculateRecords(true);

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if ($row==false) {
            return array();
        } else {
            return $row;
        }
    }

    public static function getSubscriptionPlanDataByPlanId($spplan_id = 0, $siteLangId = 0)
    {
        $spplan_id = FatUtility::convertToType($spplan_id, FatUtility::VAR_INT);
        if (!$spplan_id) {
            trigger_error(Labels::getLabel('ERR_Package_Id_Not_Specified', $siteLangId), E_USER_ERROR);
            return false;
        }
        $srch = new SellerPackageSearch($siteLangId);

        $srch->joinPlan();
        $srch->addCondition(SellerPackagePlans::DB_TBL_PREFIX.'id', '=', $spplan_id);
        $srch->addMultipleFields(array(SellerPackagePlans::DB_TBL_PREFIX.'price',SellerPackages::DB_TBL_PREFIX.'products_allowed',SellerPackages::DB_TBL_PREFIX.'inventory_allowed',SellerPackages::DB_TBL_PREFIX.'images_per_product',SellerPackagePlans::DB_TBL_PREFIX.'interval',SellerPackages::DB_TBL_PREFIX.'type',SellerPackagePlans::DB_TBL_PREFIX.'frequency',SellerPackages::DB_TBL_PREFIX.'name',SellerPackagePlans::DB_TBL_PREFIX.'trial_interval',SellerPackagePlans::DB_TBL_PREFIX.'trial_frequency'));
        $srch->addOrder(SellerPackagePlans::DB_TBL_PREFIX.'price', 'asc');
        $srch->addCondition('spp.spplan_active', '=', applicationConstants::YES);
        $srch->setPageSize(1);
        $srch->doNotCalculateRecords(true);

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if ($row==false) {
            return array();
        } else {
            return $row;
        }
    }
}
