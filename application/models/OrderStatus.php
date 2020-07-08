<?php
class orderStatus extends MyAppModel
{
    const DB_TBL = 'tbl_orders_status';
    const DB_TBL_PREFIX = 'orderstatus_';

    const DB_TBL_LANG = 'tbl_orders_status_lang';
    const DB_TBL_LANG_PREFIX = 'orderstatuslang_';

    const ORDER_SHIPPED = 4;
    const ORDER_DELIVERED = 5;
    const ORDER_RETURN_REQUESTED = 6;
    const ORDER_COMPLETED = 7;
    const ORDER_CANCELLED = 8;
    const ORDER_REFUNDED = 9;


    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getSearchObject($isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'ostatus');

        if ($isActive==true) {
            $srch->addCondition('ostatus.'.static::DB_TBL_PREFIX.'is_active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'ostatus_l.'.static::DB_TBL_LANG_PREFIX.'orderstatus_id = ostatus.'.static::tblFld('id').' and
			ostatus_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'ostatus_l'
            );
        }

        return $srch;
    }

    public static function nonCancellableStatuses()
    {
        return array(
            static::ORDER_SHIPPED,
            static::ORDER_DELIVERED,
            static::ORDER_RETURN_REQUESTED,
            static::ORDER_COMPLETED,
            static::ORDER_CANCELLED,
            static::ORDER_REFUNDED
        );
    }

    public static function getOrderStatusTypeArr($langId)
    {
        return array(
            Orders::ORDER_PRODUCT=>Labels::getLabel('LBL_Product', $langId),
            Orders::ORDER_SUBSCRIPTION=>Labels::getLabel('LBL_Subscriptions', $langId),
        );
    }

    public function updateOrder($order)
    {
        if (is_array($order) && sizeof($order) > 0) {
            foreach ($order as $i => $id) {
                if (FatUtility::int($id) < 1) {
                    continue;
                }

                FatApp::getDb()->updateFromArray(
                    static::DB_TBL,
                    array(
                    static::DB_TBL_PREFIX . 'priority' => $i
                    ),
                    array(
                    'smt' => static::DB_TBL_PREFIX . 'id = ?',
                    'vals' => array($id)
                    )
                );
            }
            return true;
        }
        return false;
    }
}
