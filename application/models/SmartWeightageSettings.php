<?php
class SmartWeightageSettings extends MyAppModel
{
    const DB_TBL = 'tbl_smart_weightage_settings';
    const DB_TBL_PREFIX = 'swsetting_';
    private $db;

    const PRODUCT_VIEW = 1;
    const PRODUCT_ORDER_PAID = 2;
    const PRODUCT_TIME_SPENT = 3;
    /* const PRODUCT_CART = 2;
    const PRODUCT_CART_REMOVE = 3;
    const PRODUCT_FAVORITE = 4;
    const PRODUCT_UNFAVORITE = 5;
    const PRODUCT_ORDER_CANCELLED = 6;
    const PRODUCT_ORDER_COMPLETED = 7;
    const PRODUCT_ORDER_PAID = 8;
    const PRODUCT_RATING_MULTIPLY_FACTOR = 9;
    const PRODUCT_TIME_SPENT = 10;
    const PRODUCT_WISHLIST = 11;
    const PRODUCT_UNWISHLIST = 12; */

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'key', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'sws');
        return $srch;
    }

    public static function getWeightageAssoc()
    {
        $srch = static::getSearchObject();
        $srch->addMultipleFields(array('swsetting_key','swsetting_weightage'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAllAssoc($rs);
        if ($row == false) {
            return array();
        }
        return $row;
    }

    public static function getWeightageKeyArr()
    {
        $arr = array(
        static::PRODUCT_VIEW => 'products#view',
        static::PRODUCT_ORDER_PAID => 'products#order_paid',
        static::PRODUCT_TIME_SPENT => 'products#time_spent',
        /* static::PRODUCT_CART => 'products#cart',
        static::PRODUCT_CART_REMOVE => 'products#cart_remove',
        static::PRODUCT_FAVORITE => 'products#favorite',
        static::PRODUCT_UNFAVORITE => 'products#unfavorite',
        static::PRODUCT_ORDER_CANCELLED => 'products#order_cancelled',
        static::PRODUCT_ORDER_COMPLETED => 'products#order_completed',
        static::PRODUCT_ORDER_PAID => 'products#order_paid',
        static::PRODUCT_RATING_MULTIPLY_FACTOR => 'products#rating_multiply_factor',
        static::PRODUCT_TIME_SPENT => 'products#time_spent',
        static::PRODUCT_WISHLIST => 'products#wishlist',
        static::PRODUCT_UNWISHLIST => 'products#unwishlist', */
        );
        return $arr;
    }
}
