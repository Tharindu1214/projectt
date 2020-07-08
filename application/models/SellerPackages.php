<?php
class SellerPackages extends MyAppModel
{
    const DB_TBL = 'tbl_seller_packages';
    const DB_TBL_PREFIX = 'spackage_';
    const DB_TBL_LANG = 'tbl_seller_packages_lang';
    const DB_TBL_LANG_PREFIX = 'spackagelang_';
    const FREE_TYPE = 1;
    const PAID_TYPE = 2;

    const TYPE_FREQUENCY= 1;
    const TYPE_UNLIMITED = 2;

    const CLASS_ONE ='';
    const CLASS_TWO ='two';
    const CLASS_THREE ='three';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getPackageClass()
    {
        return array(
        '1'=>SellerPackages::CLASS_ONE,
        '2'=>SellerPackages::CLASS_TWO,
        '3'=>SellerPackages::CLASS_THREE,
        '4'=>SellerPackages::CLASS_ONE,
        '5'=>SellerPackages::CLASS_TWO,
        '6'=>SellerPackages::CLASS_THREE,
        '7'=>SellerPackages::CLASS_ONE,
        '8'=>SellerPackages::CLASS_TWO,
        '9'=>SellerPackages::CLASS_THREE,
        );
    }
    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'sp');

        if ($langId) {
            $srch->joinTable(
                SellerPackages::DB_TBL . '_lang',
                'LEFT OUTER JOIN',
                'spl.spackagelang_spackage_id = sp.spackage_id AND spl.spackagelang_lang_id = ' . $langId,
                'spl'
            );
        }

        return $srch;
    }
    public static function getSellerPackages($langId = 0)
    {
        $srch = self::getSearchObject($langId);
        $srch->addMultipleFields(array( "sp.spackage_id", "IFNULL( spl.spackage_name, sp.spackage_identifier ) as spackage_name"));

        $rs = $srch->getResultSet();
        $records = array();
        $records = FatApp::getDb()->fetchAllAssoc($rs);

        return $records;
    }

    public static function getSellerVisiblePackages($langId = 0, $includeFreePackages = true)
    {
        $srch = new PackagesSearch($langId);
        $srch->joinTable(SellerPackagePlans::DB_TBL, 'INNER JOIN', 'sp.spackage_id =spp.spplan_spackage_id', 'spp');
        $srch->addMultipleFields(
            array(
            "sp.spackage_id", "IFNULL( spl.spackage_name, sp.spackage_identifier ) as spackage_name","spackage_text","spackage_products_allowed","spackage_inventory_allowed","spackage_images_per_product","spackage_commission_rate","spackage_type")
        );
        $srch->addGroupBy('sp.spackage_id');
        $srch->addCondition('sp.spackage_active', '=', applicationConstants::YES);
        $srch->addOrder('sp.spackage_display_order');
        if (!$includeFreePackages) {
            $srch->addCondition('sp.spackage_type', '=', SellerPackages::PAID_TYPE);
        }
        $rs = $srch->getResultSet();
        $records = array();
        $records = FatApp::getDb()->fetchAll($rs);

        return $records;
    }

    public static function getPackageTypes()
    {
        return array(
        ''=>Labels::getLabel('LBL_Select_Plan', CommonHelper::getLangId()),
        SellerPackages::FREE_TYPE=>Labels::getLabel('LBL_Free_Plan', CommonHelper::getLangId()),
        SellerPackages::PAID_TYPE=>Labels::getLabel('LBL_Paid_Plan', CommonHelper::getLangId()),
        );
    }
    public static function getAllowedLimit($userId, $langId, $key = '')
    {
        $columns = array("spackage_products_allowed","spackage_inventory_allowed","spackage_images_per_product");
        $currentActivePlan = OrderSubscription:: getUserCurrentActivePlanDetails($langId, $userId, $columns);
        
        if (!empty($key)) {
            return $currentActivePlan[$key];
        }

        return $currentActivePlan;
    }
}
