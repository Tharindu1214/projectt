<?php
class SellerProductVolumeDiscountSearch extends SearchBase
{
    public function __construct($langId = 0)
    {
        parent::__construct(SellerProductVolumeDiscount::DB_TBL, 'vd');
    }

    public static function getSearchObject($volDiscountId = '', $selprod_id = '', $userId = '', $attr = '')
    {
        $srch = new SearchBase(SellerProductVolumeDiscount::DB_TBL, 'vd');
        if (!empty($attr)) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } else {
                $srch->addfld($attr);
            }
        }
        if (!empty($volDiscountId) && 0 < $volDiscountId) {
            $srch->addCondition('voldiscount_id', '=', $volDiscountId);
            $srch->setPageSize(1);
        }
        if (!empty($selprod_id) && 0 < $selprod_id) {
            $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'sp.selprod_id = vd.voldiscount_selprod_id', 'sp');
            $srch->addCondition('voldiscount_selprod_id', '=', $selprod_id);
        }

        if (!empty($userId) && 0 < $userId) {
            $srch->addCondition('selprod_user_id', '=', $userId);
        }
        return $srch;
    }
}
