<?php
class OrderCancelRequestSearch extends SearchBase
{
    private $langId;
    private $isOrdersJoined;
    private $isJoinedOrderProducts;
    private $isJoinedShops;
    private $commonLangId;
    public function __construct($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;
        $this->isOrdersJoined = false;
        $this->isJoinedOrderProducts = false;
        $this->isJoinedShops = false;
        $this->commonLangId = CommonHelper::getLangId();
        parent::__construct(OrderCancelRequest::DB_TBL, 'ocrequest');
    }

    public function joinOrderProducts($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->joinTable(Orders::DB_TBL_ORDER_PRODUCTS, 'LEFT OUTER JOIN', 'ocrequest.ocrequest_op_id = op.op_id', 'op');

        if ($this->langId) {
            $langId = $this->langId;
        }

        if ($langId) {
            $this->joinTable(
                Orders::DB_TBL_ORDER_PRODUCTS_LANG,
                'LEFT OUTER JOIN',
                'op.op_id = op_l.oplang_op_id
			AND oplang_lang_id = ' . $langId,
                'op_l'
            );
        }
        $this->isJoinedOrderProducts = true;
    }

    public function joinOrders($langId = 0)
    {
        if (!$this->isJoinedOrderProducts) {
            trigger_error(Labels::getLabel('MSG_joinOrders_cannot_be_applied_until_joinOrderProducts_is_not_applied.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        $this->joinTable(Orders::DB_TBL, 'LEFT OUTER JOIN', 'op.op_order_id = o.order_id', 'o');
        $this->isOrdersJoined = true;
    }

    public function joinOrderProductStatus($langId = 0)
    {
        if (!$this->isJoinedOrderProducts) {
            trigger_error(Labels::getLabel('MSG_joinOrderProductStatus_cannot_be_applied_until_joinOrderProducts_is_not_applied.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(Orders::DB_TBL_ORDERS_STATUS, 'LEFT OUTER JOIN', 'os.orderstatus_id = op.op_status_id', 'os');
        if ($langId) {
            $this->joinTable(Orders::DB_TBL_ORDERS_STATUS_LANG, 'LEFT OUTER JOIN', 'os_l.orderstatuslang_orderstatus_id = os.orderstatus_id AND os_l.orderstatuslang_lang_id = '.$langId, 'os_l');
        }
    }

    public function joinOrderCancelReasons($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(OrderCancelReason::DB_TBL, 'LEFT OUTER JOIN', 'ocrequest.ocrequest_ocreason_id = ocreason.ocreason_id', 'ocreason');

        if ($langId) {
            $this->joinTable(OrderCancelReason::DB_TBL_LANG, 'LEFT OUTER JOIN', 'ocreason.ocreason_id = ocreason_l.ocreasonlang_ocreason_id AND  	ocreasonlang_lang_id = ' .$langId, 'ocreason_l');
        }
    }

    public function joinOrderBuyerUser()
    {
        if (!$this->isOrdersJoined) {
            trigger_error(Labels::getLabel('MSG_joinOrderBuyerUser_cannot_be_applied_untill_joinOrders_is_not_applied.', $this->commonLangId), E_USER_ERROR);
        }

        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'o.order_user_id = buyer.user_id', 'buyer');
        $this->joinTable(user::DB_TBL_CRED, 'LEFT OUTER JOIN', 'buyer.user_id = buyer_cred.credential_user_id', 'buyer_cred');
    }

    public function joinOrderSellerUser()
    {
        if (!$this->isJoinedOrderProducts) {
            trigger_error(Labels::getLabel('MSG_joinOrderSellerUser_cannot_be_applied_untill_joinOrderProducts_is_not_applied.', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'op.op_selprod_user_id = seller.user_id', 'seller');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'seller.user_id = seller_cred.credential_user_id', 'seller_cred');
    }

    public function joinOrderProductChargesByType($type, $alias = 'opct')
    {
        $type = FatUtility::int($type);
        if (!$this->isJoinedOrderProducts) {
            trigger_error(Labels::getLabel('MSG_joinOrderProductChargesByType_cannot_be_applied_until_joinOrderProducts_is_not_applied.', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(OrderProduct::DB_TBL_CHARGES, 'LEFT OUTER JOIN', 'op.op_id = '.$alias.'.opcharge_op_id and '.$alias.'.opcharge_type = '.$type, $alias);
    }

    public function addOrderProductCharges()
    {
        if (!$this->isJoinedOrderProducts) {
            trigger_error(Labels::getLabel('MSG_addOrderProductCharges_cannot_be_applied_until_joinOrderProducts_is_not_applied.', $this->commonLangId), E_USER_ERROR);
        }
        $srch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $srch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $srch->getQuery();
        $this->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
    }

    /* public function joinShops( $langId = 0 ){
    $this->isJoinedShops = true;
    $langId = FatUtility::int( $langId );
    if( $this->langId ){
    $langId = $this->langId;
    }
    if( !$this->isJoinedOrderProducts ){
    trigger_error("joinShops cannot be applied untill joinOrderProducts is not applied.", E_USER_ERROR );
    }
    $this->joinTable( Shop::DB_TBL, 'LEFT OUTER JOIN', 'op.op_shop_id = shop.shop_id', 'shop' );

    if( $langId ){
    $this->joinTable( Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = shop_l.shoplang_shop_id AND shop_l.shoplang_lang_id = '.$langId , 'shop_l' );
    }
    } */

    public function addDateFromCondition($dateFrom)
    {
        $dateFrom = FatDate::convertDatetimeToTimestamp($dateFrom);
        $dateFrom = date('Y-m-d', strtotime($dateFrom));

        if ($dateFrom != '') {
            $this->addCondition('ocrequest_date', '>=', $dateFrom. ' 00:00:00');
        }
    }

    public function addDateToCondition($dateTo)
    {
        $dateTo = FatDate::convertDatetimeToTimestamp($dateTo);
        $dateTo = date('Y-m-d', strtotime($dateTo));

        if ($dateTo != '') {
            $this->addCondition('ocrequest_date', '<=', $dateTo. ' 23:59:59');
        }
    }
}
