<?php
class OrderReturnRequestMessageSearch extends SearchBase
{
    private $langId;
    private $isOrdersJoined;
    private $isOrderReturnRequestJoined;
    private $isOrderProductsJoined;
    private $commonLangId;
    public function __construct($langId = 0, $isDeleted = true)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;
        $this->isOrderReturnRequestJoined = false;
        $this->isOrdersJoined = false;
        $this->isOrderProductsJoined = false;
        $this->commonLangId = CommonHelper::getLangId();
        parent::__construct(OrderReturnRequestMessage::DB_TBL, 'orrequestmsg');

        if ($isDeleted == true) {
            $this->addCondition('orrmsg_deleted', '=', applicationConstants::NO);
        }
    }

    public function joinMessageUser()
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'orrequestmsg.orrmsg_from_user_id = msg_user.user_id', 'msg_user');
        $this->joinTable(User::DB_TBL_CRED, 'LEFt OUTER JOIN', 'msg_user.user_id = msg_user_cred.credential_user_id', 'msg_user_cred');
        $this->joinTable('tbl_shops', 'LEFT OUTER JOIN', 'msg_user.user_id = s.shop_user_id', 's');
    }

    public function joinMessageAdmin()
    {
        $this->joinTable('tbl_admin', 'LEFT OUTER JOIN', 'orrequestmsg.orrmsg_from_admin_id = msg_admin.admin_id', 'msg_admin');
    }

    public function joinOrderReturnRequests()
    {
        $this->joinTable(OrderReturnRequest::DB_TBL, 'LEFT OUTER JOIN', 'orrequest_id = orrmsg_orrequest_id', 'orrequest');
        $this->isOrderReturnRequestJoined = true;
    }

    public function joinOrderProducts($langId = 0)
    {
        if (!$this->isOrderReturnRequestJoined) {
            trigger_error(Labels::getLabel('MSG_joinOrderProducts_can_be_joined_only,_if_joinOrderReturnRequests_is_Joined,_So,_Please_Use_joinOrderReturnRequests()_first,_then_try_to_join_joinOrderProducts', $this->commonLangId), E_USER_ERROR);
        }

        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(OrderProduct::DB_TBL, 'LEFT OUTER JOIN', 'orrequest_op_id = op_id', 'op');

        if ($langId) {
            $this->joinTable(OrderProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'op_id = oplang_op_id AND oplang_lang_id = ' . $langId, 'op_l');
        }
        $this->isOrderProductsJoined = true;
    }

    public function joinOrders($langId = 0)
    {
        if (!$this->isOrderProductsJoined) {
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
        $this->joinTable(user::DB_TBL_CRED, 'LEFT OUTER JOIN', 'buyer.user_id = buyer_cred.credential_user_id', 'buyer_cred');
    }

    public function joinReturnReason($langId = 0)
    {
        if (!$this->isOrderReturnRequestJoined) {
            trigger_error(Labels::getLabel('MSG_joinReturnReason_can_be_joined_only,_if_joinOrderReturnRequests_is_Joined,_So,_Please_Use_joinOrderReturnRequests()_first,_then_try_to_join_joinReturnReason', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(OrderReturnReason::DB_TBL, 'LEFT OUTER JOIN', 'orreason.orreason_id = orrequest_returnreason_id', 'orreason');

        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        if ($langId) {
            $this->joinTable(OrderReturnReason::DB_TBL_LANG, 'LEFT OUTER JOIN', 'orreason.orreason_id = orreason_l.orreasonlang_orreason_id AND orreason_l.orreasonlang_lang_id = '.$langId, 'orreason_l');
        }
    }

    /* public function joinSellerProducts( $langId = 0 ){
    if( !$this->isOrderProductsJoined ){
    trigger_error("joinSellerProducts can be joined only, if joinOrderProducts is Joined, So, Please use joinOrderProducts() first, then try to join joinSellerProducts", E_USER_ERROR);
    }
    $this->joinTable( SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'op.', 'sp' );
    } */
}
