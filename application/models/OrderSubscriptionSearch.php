<?php
class OrderSubscriptionSearch extends SearchBase
{
    private $langId;
    private $isOrdersTableJoined;
    private $isOrderUserTableJoined;
    private $isOrderSubscriptionStatusJoined;
    private $commonLangId;

    public function __construct($langId = 0, $joinOrders = false, $joinOrderSuscriptionStatus = false)
    {
        parent::__construct(OrderSubscription::DB_TBL, 'oss');
        $this->langId = FatUtility::int($langId);
        $this->isOrdersTableJoined = false;
        $this->isOrderUserTableJoined = false;
        $this->isOrderSubscriptionStatusJoined = false;
        $this->commonLangId = CommonHelper::getLangId();
        if ($this->langId > 0) {
            $this->joinTable(
                OrderSubscription::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'oss_l.'.OrderSubscription::DB_TBL_LANG_PREFIX.'ossubs_id = oss.'.OrderSubscription::DB_TBL_PREFIX.'id
			AND oss_l.'.OrderSubscription::DB_TBL_LANG_PREFIX.'lang_id = ' . $langId,
                'oss_l'
            );
        }

        if ($joinOrders) {
            $this->joinOrders();
        }

        if ($joinOrderSuscriptionStatus) {
            $this->joinOrderSuscriptionStatus($this->langId);
        }
    }

    public function joinOrderPaymentMethod($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(PaymentMethods::DB_TBL, 'LEFT OUTER JOIN', 'o.order_pmethod_id = pm.pmethod_id', 'pm');

        if ($langId) {
            $this->joinTable(PaymentMethods::DB_LANG_TBL, 'LEFT OUTER JOIN', 'pm.pmethod_id = pm_l.pmethodlang_pmethod_id AND pm_l.pmethodlang_lang_id = '. $langId, 'pm_l');
        }
    }
    public function joinOrders()
    {
        if ($this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Orders_Table_is_already_joined', $this->commonLangId), E_USER_ERROR);
        }
        $this->isOrdersTableJoined = true;
        $this->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_id = oss.'.OrderSubscription::DB_TBL_PREFIX.'order_id', 'o');
    }
    public function addOrderProductCharges()
    {
        $srch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $srch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $srch->getQuery();
        $this->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'oss.ossubs_id = opcc.opcharge_op_id', 'opcc');
    }
    public function joinOrderSuscriptionStatus($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        if ($this->isOrderSubscriptionStatusJoined) {
            trigger_error(Labels::getLabel('MSG_OrderProduct_Status_is_already_joined', $this->commonLangId), E_USER_ERROR);
        }
        $this->isOrderSubscriptionStatusJoined = true;
        $this->joinTable(Orders::DB_TBL_ORDERS_STATUS, 'LEFT OUTER JOIN', 'os.orderstatus_id = oss.'.OrderSubscription::DB_TBL_PREFIX.'status_id', 'os');
        if ($langId) {
            $this->joinTable(Orders::DB_TBL_ORDERS_STATUS_LANG, 'LEFT OUTER JOIN', 'os_l.orderstatuslang_orderstatus_id = os.orderstatus_id AND os_l.orderstatuslang_lang_id = '.$langId, 'os_l');
        }
    }

    public function joinOrderUser()
    {
        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_joinOrderUser_can_be_joined_only,_if_joinOrders_is_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_join_joinOrderUser', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(User::DB_TBL, 'INNER JOIN', 'ou.user_id = o.order_user_id', 'ou');
        $this->joinTable(User::DB_TBL_CRED, 'INNER JOIN', 'ou.user_id = ouc.credential_user_id', 'ouc');
        $this->isOrderUserTableJoined = true;
    }

    public function joinSubscription($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(SellerPackagePlans::DB_TBL, 'LEFT OUTER JOIN', 'spp.'.SellerPackagePlans::DB_TBL_PREFIX.'id = oss.'.OrderSubscription::DB_TBL_PREFIX.'plan_id ', 'spp');
    }
    public function addStatusCondition($op_status)
    {
        if (is_array($op_status)) {
            if (!empty($op_status)) {
                $this->addCondition('oss.ossubs_status_id', 'IN', $op_status);
            } else {
                $this->addCondition('oss.ossubs_status_id', '=', 0);
            }
        } else {
            $op_status_id = FatUtility::int($op_status);
            $this->addCondition('oss.ossubs_status_id', '=', $op_status_id);
        }
    }
    public function addKeywordSearch($keyword)
    {
        $cnd = $this->addCondition('oss.ossubs_order_id', 'like', '%' . $keyword . '%');
        $cnd->attachCondition('oss.ossubs_invoice_number', 'like', '%' . $keyword . '%', 'OR');
        if ($this->isOrderUserTableJoined) {
            $cnd->attachCondition('ou.user_name', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('ouc.credential_email', 'like', '%' . $keyword . '%', 'OR');
        }
        if ($this->langId) {
            $cnd->attachCondition('ossubs_subscription_name', 'like', '%' . $keyword . '%', 'OR');
        }
    }

    public function addDateFromCondition($dateFrom)
    {
        $dateFrom = FatDate::convertDatetimeToTimestamp($dateFrom);
        $dateFrom = date('Y-m-d', strtotime($dateFrom));

        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Order_Date_Condition_cannot_be_applied,_as_Orders_Table_is_not_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_add_Order_date_from_condition', $this->commonLangId), E_USER_ERROR);
        }
        if ($dateFrom != '') {
            $this->addCondition('o.order_date_added', '>=', $dateFrom. ' 00:00:00');
        }
    }

    public function addDateToCondition($dateTo)
    {
        $dateTo = FatDate::convertDatetimeToTimestamp($dateTo);
        $dateTo = date('Y-m-d', strtotime($dateTo));

        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Order_Date_Condition_cannot_be_applied,_as_Orders_Table_is_not_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_add_Order_date_to_condition', $this->commonLangId), E_USER_ERROR);
        }
        if ($dateTo != '') {
            $this->addCondition('o.order_date_added', '<=', $dateTo. ' 23:59:59');
        }
    }
    public function addMinPriceCondition($priceFrom)
    {
        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Order_Price_Condition_cannot_be_applied,_as_Orders_Table_is_not_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_add_Order_Price_condition', $this->commonLangId), E_USER_ERROR);
        }
        $this->addCondition('o.order_net_amount', '>=', $priceFrom);
    }

    public function addMaxPriceCondition($priceTo)
    {
        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Order_Price_Condition_cannot_be_applied,_as_Orders_Table_is_not_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_add_Order_Price_condition', $this->commonLangId), E_USER_ERROR);
        }
        $this->addCondition('o.order_net_amount', '<=', $priceTo);
    }
}
