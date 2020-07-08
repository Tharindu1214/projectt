<?php
class OrderReturnRequestSearch extends SearchBase
{
    private $langId;
    private $isJoinedOrderProducts;
    private $isOrdersJoined;
    private $commonLangId;
    public function __construct($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;
        $this->isJoinedOrderProducts = false;
        $this->isOrdersJoined = false;
        $this->commonLangId = CommonHelper::getLangId();
        parent::__construct(OrderReturnRequest::DB_TBL, 'orrequest');
    }

    public function joinOrderProducts($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->joinTable(Orders::DB_TBL_ORDER_PRODUCTS, 'LEFT OUTER JOIN', 'orrequest.orrequest_op_id = op.op_id', 'op');

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

    public function joinOrderProductSettings()
    {
        $this->joinTable(OrderProduct::DB_TBL_SETTINGS, 'LEFT OUTER JOIN', 'op.op_id = opst.opsetting_op_id', 'opst');
    }

    public function joinOrderReturnReasons($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(OrderReturnReason::DB_TBL, 'LEFT OUTER JOIN', 'orrequest.orrequest_returnreason_id = orreason.orreason_id', 'orreason');

        if ($langId) {
            $this->joinTable(OrderReturnReason::DB_TBL_LANG, 'LEFT OUTER JOIN', 'orreason.orreason_id = orreason_l.orreasonlang_orreason_id AND  	orreasonlang_lang_id = ' .$langId, 'orreason_l');
        }
    }

    public function joinOrders($langId = 0)
    {
        if (!$this->isJoinedOrderProducts) {
            trigger_error(Labels::getLabel('MSG_joinOrders_can_be_joined_only,_if_joinOrderProducts_is_Joined,_So,_Please_Use_joinOrderProducts()_first,_then_try_to_join_joinOrders', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(Orders::DB_TBL, 'LEFT OUTER JOIN', 'op_order_id = order_id', 'o');
        if ($langId) {
            $this->joinTable(Orders::DB_TBL_LANG, 'LEFT OUTER JOIN', 'order_id = orderlang_order_id AND orderlang_lang_id = '.$langId, 'o_l');
        }
        $this->isOrdersJoined = true;
    }

    public function joinOrderBuyerUser()
    {
        if (!$this->isOrdersJoined) {
            trigger_error(Labels::getLabel('MSG_joinOrderBuyerUser_can_be_joined_only,_if_joinOrders_is_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_join_joinOrderBuyerUser', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'o.order_user_id = buyer.user_id', 'buyer');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'buyer.user_id = buyer_cred.credential_user_id', 'buyer_cred');
    }

    public function joinOrderSellerUser()
    {
        if (!$this->isJoinedOrderProducts) {
            trigger_error(Labels::getLabel('MSG_Please_first_use_joinOrderProducts(),_then_try_to_use_joinOrderSellerUser', $this->commonLangId), E_USER_ERROR);
        }

        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'op.op_selprod_user_id = seller.user_id', 'seller');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'seller.user_id = seller_cred.credential_user_id', 'seller_cred');
    }

    public function joinSellerProducts()
    {
        if (!$this->isJoinedOrderProducts) {
            trigger_error(Labels::getLabel('MSG_joinSellerProducts_cannot_be_joined,_Please_first_use_joinOrderProducts()', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_id = op.op_selprod_id and op.op_is_batch = 0', 'sp');
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

    public function addDateFromCondition($dateFrom)
    {
        $dateFrom = FatDate::convertDatetimeToTimestamp($dateFrom);
        $dateFrom = date('Y-m-d', strtotime($dateFrom));

        if ($dateFrom != '') {
            $this->addCondition('orrequest_date', '>=', $dateFrom. ' 00:00:00');
        }
    }

    public function addDateToCondition($dateTo)
    {
        $dateTo = FatDate::convertDatetimeToTimestamp($dateTo);
        $dateTo = date('Y-m-d', strtotime($dateTo));

        if ($dateTo != '') {
            $this->addCondition('orrequest_date', '<=', $dateTo. ' 23:59:59');
        }
    }
}
