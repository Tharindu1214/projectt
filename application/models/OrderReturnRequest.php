<?php
class OrderReturnRequest extends MyAppModel
{
    const DB_TBL = 'tbl_order_return_requests';
    const DB_TBL_PREFIX = 'orrequest_';

    const DB_TBL_RETURN_REQUEST_MESSAGE = 'tbl_order_return_request_messages';

    const RETURN_REQUEST_TYPE_REPLACE = 1;
    const RETURN_REQUEST_TYPE_REFUND = 2;

    const RETURN_REQUEST_STATUS_PENDING = 0;
    const RETURN_REQUEST_STATUS_ESCALATED = 1;
    const RETURN_REQUEST_STATUS_REFUNDED = 2;
    const RETURN_REQUEST_STATUS_WITHDRAWN = 3;
    const RETURN_REQUEST_STATUS_CANCELLED = 4;

    const CLASS_REQUEST_STATUS_PENDING = 'warning';
    const CLASS_REQUEST_STATUS_ESCALATED = 'info';
    const CLASS_REQUEST_STATUS_REFUNDED = 'green';
    const CLASS_REQUEST_STATUS_WITHDRAWN = 'purple';
    const CLASS_REQUEST_STATUS_CANCELLED = 'danger';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'orr');
        return $srch;
    }

    public static function getRequestTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        /* static::RETURN_REQUEST_TYPE_REPLACE => Labels::getLabel( 'LBL_Order_Request_Type_Replace', $langId ), */
        static::RETURN_REQUEST_TYPE_REFUND => Labels::getLabel('LBL_Order_Request_Type_Refund', $langId),
        );
    }

    public static function getRequestStatusClass()
    {
        return array(
        static::RETURN_REQUEST_STATUS_PENDING => static::CLASS_REQUEST_STATUS_PENDING,
        static::RETURN_REQUEST_STATUS_ESCALATED => static::CLASS_REQUEST_STATUS_ESCALATED,
        static::RETURN_REQUEST_STATUS_REFUNDED => static::CLASS_REQUEST_STATUS_REFUNDED,
        static::RETURN_REQUEST_STATUS_WITHDRAWN => static::CLASS_REQUEST_STATUS_WITHDRAWN,
        static::RETURN_REQUEST_STATUS_CANCELLED => static::CLASS_REQUEST_STATUS_CANCELLED,
        );
    }


    public static function getRequestStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::RETURN_REQUEST_STATUS_PENDING => Labels::getLabel('LBL_Return_Request_Status_Pending', $langId),
        static::RETURN_REQUEST_STATUS_ESCALATED => Labels::getLabel('LBL_Return_Request_Status_Escalated', $langId),
        static::RETURN_REQUEST_STATUS_REFUNDED => Labels::getLabel('LBL_Return_Request_Status_Refunded', $langId),
        static::RETURN_REQUEST_STATUS_WITHDRAWN => Labels::getLabel('LBL_Return_Request_Status_Withdrawn', $langId),
        static::RETURN_REQUEST_STATUS_CANCELLED => Labels::getLabel('LBL_Return_Request_Status_Cancelled', $langId),
        );
    }

    public function escalateRequest($orrequest_id, $user_id, $langId)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $langId = FatUtility::int($langId);
        $user_id = FatUtility::int($user_id);
        if ($orrequest_id < 1 || $langId < 1 || $user_id < 1) {
            trigger_error(Labels::getLabel('MSG_Invalid_Argument_Passed', $this->commonLangId), E_USER_ERROR);
        }
        $db = FatApp::getDb();
        $dataToUpdate = array( 'orrequest_status' => static::RETURN_REQUEST_STATUS_ESCALATED );
        $whereArr = array( 'smt' => 'orrequest_id = ?', 'vals' => array($orrequest_id) );
        if (!$db->updateFromArray(static::DB_TBL, $dataToUpdate, $whereArr)) {
            $this->error = $db->getError();
            return false;
        }
        $orrmsg_msg = str_replace('{website_name}', FatApp::getConfig('CONF_WEBSITE_NAME_'.$langId), Labels::getLabel('LBL_Return_Request_Escalated_to', $langId));
        $dataToSave = array(
        'orrmsg_orrequest_id' => $orrequest_id,
        'orrmsg_from_user_id' => $user_id,
        'orrmsg_msg' => $orrmsg_msg,
        'orrmsg_date' => date('Y-m-d H:i:s'),
        'orrmsg_deleted'    => 0,
        );
        if (!$db->insertFromArray(OrderReturnRequestMessage::DB_TBL, $dataToSave)) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function withdrawRequest($orrequest_id, $user_id, $langId, $op_id, $orderLangId)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $langId = FatUtility::int($langId);
        $user_id = FatUtility::int($user_id);
        $op_id = FatUtility::int($op_id);
        $orderLangId = FatUtility::int($orderLangId);

        if ($orrequest_id < 1 || $langId < 1 || $op_id < 1 || $orderLangId < 1) {
            trigger_error(Labels::getLabel('MSG_Invalid_Argument_Passed', $this->commonLangId), E_USER_ERROR);
        }
        $db = FatApp::getDb();

        $dataToUpdate = array( 'orrequest_status' => static::RETURN_REQUEST_STATUS_WITHDRAWN );
        $whereArr = array( 'smt' => 'orrequest_id = ?', 'vals' => array($orrequest_id) );
        if (!$db->updateFromArray(static::DB_TBL, $dataToUpdate, $whereArr)) {
            $this->error = $db->getError();
            return false;
        }

        $orrmsg_msg = Labels::getLabel('LBL_Return_Request_Withdrawn', $this->commonLangId);
        $dataToSave = array(
        'orrmsg_orrequest_id' => $orrequest_id,
        'orrmsg_from_user_id' => $user_id,
        'orrmsg_msg' => $orrmsg_msg,
        'orrmsg_date' => date('Y-m-d H:i:s'),
        'orrmsg_deleted'    => 0,
        );

        if (!$user_id && AdminAuthentication::isAdminLogged()) {
            $dataToSave['orrmsg_from_admin_id'] = AdminAuthentication::getLoggedAdminId();
        }
        if (!$db->insertFromArray(OrderReturnRequestMessage::DB_TBL, $dataToSave)) {
            $this->error = $db->getError();
            return false;
        }

        $oObj = new Orders();
        $oObj->addChildProductOrderHistory($op_id, $orderLangId, FatApp::getConfig("CONF_RETURN_REQUEST_WITHDRAWN_ORDER_STATUS"), Labels::getLabel('MSG_Buyer_Withdrawn_Return_Request', $orderLangId), 1);
        return true;
    }

    public function approveRequest($orrequest_id, $user_id, $langId, $moveRefundInWallet = true, $adminComment = '')
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $langId = FatUtility::int($langId);
        $user_id = FatUtility::int($user_id);

        if ($orrequest_id < 1 || $langId < 1) {
            trigger_error(Labels::getLabel('MSG_Invalid_Argument_Passed!', $this->commonLangId), E_USER_ERROR);
        }
        $db = FatApp::getDb();

        $srch = new OrderReturnRequestSearch();
        $srch->joinOrderProducts();
        $srch->joinOrderProductSettings();
        $srch->joinOrders();
        $srch->addOrderProductCharges();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addMultipleFields(array('orrequest_id', 'orrequest_op_id', 'orrequest_qty','orrequest_type', 'op_commission_percentage', 'op_affiliate_commission_percentage', 'op_qty','order_language_id', 'op_shop_owner_name', 'op_unit_price','op_other_charges','op_commission_include_shipping','op_tax_collected_by_seller','op_commission_include_tax','op_free_ship_upto','op_actual_shipping_charges'));
        $rs = $srch->getResultSet();
        $requestRow = $db->fetch($rs);

        if (!$requestRow) {
            $this->error = Labels::getLabel("MSG_Invalid_Request", $this->commonLangId);
            return false;
        }

        $oObj = new Orders();
        $charges = $oObj->getOrderProductChargesArr($requestRow['orrequest_op_id']);
        $requestRow['charges'] = $charges;

        $orderLangId = $requestRow['order_language_id'];

        $db->startTransaction();
        $dataToUpdate = array( 'orrequest_status' => static::RETURN_REQUEST_STATUS_REFUNDED ,'orrequest_refund_in_wallet' => $moveRefundInWallet,'orrequest_admin_comment' => $adminComment);
        $whereArr = array( 'smt' => 'orrequest_id = ?', 'vals' => array( $requestRow['orrequest_id'] ) );
        if (!$db->updateFromArray(static::DB_TBL, $dataToUpdate, $whereArr)) {
            $this->error = $db->getError();
            $db->rollbackTransaction();
            return false;
        }

        $approved_by_person_name = $requestRow['op_shop_owner_name'];
        if (!$user_id && AdminAuthentication::isAdminLogged()) {
            $approved_by_person_name = FatApp::getConfig('CONF_WEBSITE_NAME_'.$orderLangId);
        }
        $orrmsg_msg = str_replace("{approved_by_person_name}", $approved_by_person_name, Labels::getLabel('LBL_Return_Request_Approved_By', $orderLangId));
        $dataToSave = array(
        'orrmsg_orrequest_id' => $orrequest_id,
        'orrmsg_from_user_id' => $user_id,
        'orrmsg_msg' => $orrmsg_msg,
        'orrmsg_date' => date('Y-m-d H:i:s'),
        'orrmsg_deleted'    => 0,
        );

        if (!$user_id && AdminAuthentication::isAdminLogged()) {
            $dataToSave['orrmsg_from_admin_id'] = AdminAuthentication::getLoggedAdminId();
        }

        if (!$db->insertFromArray(OrderReturnRequestMessage::DB_TBL, $dataToSave)) {
            $this->error = $db->getError();
            $db->rollbackTransaction();
            return false;
        }

        if ($requestRow['orrequest_type'] == static::RETURN_REQUEST_TYPE_REFUND) {
            $opDataToUpdate = CommonHelper::getOrderProductRefundAmtArr($requestRow);
            unset($opDataToUpdate['op_cart_amount']);
            unset($opDataToUpdate['op_prod_price']);
            unset($opDataToUpdate['op_refund_tax']);
            $whereArr = array( 'smt' => 'op_id = ?', 'vals' => array( $requestRow['orrequest_op_id'] ) );
            if (!$db->updateFromArray(OrderProduct::DB_TBL, $opDataToUpdate, $whereArr)) {
                $this->error = $db->getError();
                $db->rollbackTransaction();
                return false;
            }
        }

        if ($requestRow['orrequest_type'] == static::RETURN_REQUEST_TYPE_REPLACE) {
            $moveRefundInWallet = false;
        }
        $approvedByLabel = sprintf(Labels::getLabel('MSG_Approved_Return_Request', $orderLangId), $requestRow['op_shop_owner_name']);
        if (!$user_id && AdminAuthentication::isAdminLogged()) {
            $approvedByLabel = sprintf(Labels::getLabel('MSG_Approved_Return_Request', $orderLangId), FatApp::getConfig('CONF_WEBSITE_NAME_'.$orderLangId));
        }
        $oObj->addChildProductOrderHistory($requestRow['orrequest_op_id'], $orderLangId, FatApp::getConfig("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"), $approvedByLabel, 1, '', 0, $moveRefundInWallet);
        $db->commitTransaction();
        return true;
    }
}
