<?php
class SellerProductSpecialPrice extends MyAppModel
{
    const DB_TBL = 'tbl_product_special_prices';
    const DB_TBL_PREFIX = 'splprice_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($splPrice = '', $selprod_id = '', $userId = '', $attr = '')
    {
        $srch = new SearchBase(self::DB_TBL, 'splp');
        if (!empty($attr)) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } else {
                $srch->addfld($attr);
            }
        }
        if (!empty($splPrice) && 0 < $splPrice) {
            $srch->addCondition('splprice_id', '=', $splPrice);
            $srch->setPageSize(1);
        }
        if (!empty($selprod_id) && 0 < $selprod_id) {
            $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'sp.selprod_id = splp.splprice_selprod_id', 'sp');
            $srch->addCondition('splprice_selprod_id', '=', $selprod_id);
        }

        if (!empty($userId) && 0 < $userId) {
            $srch->addCondition('selprod_user_id', '=', $userId);
        }
        return $srch;
    }
}
