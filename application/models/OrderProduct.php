<?php
class OrderProduct extends MyAppModel
{
    const DB_TBL = 'tbl_order_products';
    const DB_TBL_PREFIX = 'op_';

    const DB_TBL_LANG = 'tbl_order_products_lang';
    const DB_TBL_CHARGES = 'tbl_order_product_charges';
    const DB_TBL_CHARGES_PREFIX=     'opcharge_';
    const DB_TBL_OP_TO_SHIPPING_USERS = 'tbl_order_product_to_shipping_users';

    const DB_TBL_SETTINGS =     'tbl_order_product_settings';
    const DB_TBL_SETTINGS_PREFIX = 'opsetting_';

    const CHARGE_TYPE_TAX = 1;
    const CHARGE_TYPE_DISCOUNT = 2;
    const CHARGE_TYPE_SHIPPING = 3;
    /* const CHARGE_TYPE_BATCH_DISCOUNT = 4; */
    const CHARGE_TYPE_REWARD_POINT_DISCOUNT = 5;
    const CHARGE_TYPE_VOLUME_DISCOUNT = 6;
    const CHARGE_TYPE_ADJUST_SUBSCRIPTION_PRICE = 7;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'op');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'op_l.oplang_op_id = o.op_id
			AND op_l.oplang_lang_id = ' . $langId,
                'op_l'
            );
        }

        return $srch;
    }

    public static function getChargeTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::CHARGE_TYPE_TAX =>    Labels::getLabel('LBL_Order_Product_Tax_Charges', $langId),
        static::CHARGE_TYPE_DISCOUNT =>    Labels::getLabel('LBL_Order_Product_Discount_Charges', $langId),
        static::CHARGE_TYPE_SHIPPING    =>    Labels::getLabel('LBL_Order_Product_Shipping_Charges', $langId),
        /* static::CHARGE_TYPE_BATCH_DISCOUNT=>Labels::getLabel('LBL_Order_Product_Batch_Discount', $langId), */
        static::CHARGE_TYPE_REWARD_POINT_DISCOUNT    =>    Labels::getLabel('LBL_Order_Product_Reward_Point', $langId),
        static::CHARGE_TYPE_VOLUME_DISCOUNT    =>    Labels::getLabel('LBL_Order_Product_Volume_Discount', $langId),
        );
    }

    public static function getOpIdArrByOrderId($orderId)
    {
        $opSrch = static::getSearchObject();
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $opSrch->addMultipleFields(array('op_id'));
        $opSrch->addCondition('op_order_id', '=', $orderId);
        $rs = $opSrch->getResultSet();
        return $row = FatApp::getDb()->fetchAll($rs, 'op_id');
    }

    public static function getOpArrByOrderId($orderId, $checkNotCancelled = true)
    {
        $opSrch = OrderProduct::getSearchObject();
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $opSrch->addMultipleFields(array('op_id','op_selprod_id','op_selprod_user_id','op_unit_price','op_qty','op_actual_shipping_charges','op_refund_qty'));
        $opSrch->addCondition('op_order_id', '=', $orderId);

        if ($checkNotCancelled) {
            $opSrch->joinTable(OrderCancelRequest::DB_TBL, 'LEFT OUTER JOIN', 'ocr.'.OrderCancelRequest::DB_TBL_PREFIX.'op_id = op.op_id', 'ocr');
            $cnd = $opSrch->addCondition(OrderCancelRequest::DB_TBL_PREFIX . 'status', '!=', OrderCancelRequest::CANCELLATION_REQUEST_STATUS_APPROVED);
            $cnd->attachCondition(OrderCancelRequest::DB_TBL_PREFIX . 'status', 'IS', 'mysql_func_null', 'OR', true);
        }
        $rs = $opSrch->getResultSet();
        return $rows = FatApp::getDb()->fetchAll($rs);
    }

    public function setupSettings()
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $data = array(
        'opsetting_op_id'=>$this->mainTableRecordId,
        'op_tax_collected_by_seller'=>FatApp::getConfig('CONF_TAX_COLLECTED_BY_SELLER', FatUtility::VAR_INT, 0),
        'op_commission_include_tax'=>FatApp::getConfig('CONF_COMMISSION_INCLUDING_SHIPPING', FatUtility::VAR_INT, 0),
        'op_commission_include_shipping'=>FatApp::getConfig('CONF_COMMISSION_INCLUDING_TAX', FatUtility::VAR_INT, 0)
        );

        if (FatApp::getDb()->insertFromArray(static::DB_TBL_SETTINGS, $data, false, array(), $data)) {
            return true;
        }
        return false;
    }

    public static function pendingForReviews($userId, $langId = 0)
    {
        $srch = new OrderProductSearch(0, true);
        $srch->joinSellerProducts($langId);
        $srch->addStatusCondition(SelProdReview::getBuyerAllowedOrderReviewStatuses());
        $srch->joinTable('tbl_seller_product_reviews', 'left outer join', 'o.order_id = spr.spreview_order_id and ((op.op_selprod_id = spr.spreview_selprod_id and op.op_is_batch = 0) || (op.op_batch_selprod_id = spr.spreview_selprod_id and op.op_is_batch = 1))', 'spr');
        $srch->addCondition('o.order_user_id', '=', $userId);
        $srch->addCondition('spr.spreview_id', 'is', 'mysql_func_null', 'and', true);
        $srch->addMultipleFields(array('op_id', 'op_selprod_id', 'op_order_id', 'selprod_title', 'selprod_product_id', 'order_id', 'order_user_id'));
        $rows = FatApp::getDb()->fetchAll($srch->getResultSet());
        return $rows;
    }
}
