<?php
class CouponHistory extends MyAppModel
{
    const DB_TBL = 'tbl_coupons_history';
    const DB_TBL_PREFIX = 'couponhistory_';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'ch');
        return $srch;
    }
}
