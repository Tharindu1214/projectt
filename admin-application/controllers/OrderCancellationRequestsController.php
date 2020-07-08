<?php
class OrderCancellationRequestsController extends AdminBaseController
{
    public function __construct($action) 
    {
        $ajaxCallArray = array();
        if(!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewOrderCancellationRequests($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditOrderCancellationRequests($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index() 
    {
        $this->objPrivilege->canViewOrderCancellationRequests();
        $frmSearch = $this->getOrderCancellationRequestSearchForm($this->adminLangId);
        $data = FatApp::getPostedData();
        if($data) {
            $data['ocrequest_id'] = FatUtility::int($data['id']);
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewOrderCancellationRequests();
        $frmSearch = $this->getOrderCancellationRequestSearchForm($this->adminLangId);

        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);

        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : FatUtility::int($data['page']);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $srch = new OrderCancelRequestSearch($this->adminLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderSellerUser();
        $srch->joinOrderProductStatus();
        $srch->joinOrderCancelReasons();
        $srch->addOrderProductCharges();
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addOrder('ocrequest_date', 'DESC');
        $srch->addMultipleFields(
            array('ocrequest_id', 'ocrequest_message', 'ocrequest_date', 'ocrequest_status',
            'buyer.user_name as buyer_name', 'buyer_cred.credential_username as buyer_username', 'buyer_cred.credential_email as buyer_email', 'buyer.user_phone as buyer_phone', 'seller.user_name as seller_name', 'seller_cred.credential_username as seller_username','seller_cred.credential_email as seller_email', 'seller.user_phone as seller_phone', 'op_invoice_number',
            'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'IFNULL(ocreason_title, ocreason_identifier) as ocreason_title', 'op_qty' , 'op_unit_price','order_tax_charged','op_other_charges')
        );

        $keyword = FatApp::getPostedData('keyword', null, '');
        if(!empty($keyword) ) {
            $cnd = $srch->addCondition('op_invoice_number', '=', $keyword);
            $cnd->attachCondition('op_order_id', '=', $keyword);
            $cnd->attachCondition('ocrequest_message', 'LIKE', "%".$keyword."%");
        }

        if(isset($post['ocrequest_status']) && $post['ocrequest_status'] != '' ) {
            $ocrequest_status = FatUtility::int($post['ocrequest_status']);
            $srch->addCondition('ocrequest_status', '=', $ocrequest_status);
        }

        if(isset($post['op_status_id']) && $post['op_status_id'] != '' ) {
            $op_status_id = FatUtility::int($post['op_status_id']);
            $srch->addCondition('op_status_id', '=', $op_status_id);
        }
        if(isset($post['ocrequest_id']) && $post['ocrequest_id'] > 0 ) {
            $srch->addCondition('ocrequest_id', '=', $post['ocrequest_id']);
        }

        if(isset($post['ocrequest_ocreason_id']) && $post['ocrequest_ocreason_id'] != '' ) {
            $ocrequest_ocreason_id = FatUtility::int($post['ocrequest_ocreason_id']);
            $srch->addCondition('ocrequest_ocreason_id', '=', $ocrequest_ocreason_id);
        }

        if(isset($post['buyer']) && $post['buyer'] != '' ) {
            $buyer = $post['buyer'];
            $cnd = $srch->addCondition('buyer.user_name', 'LIKE', "%". $buyer. "%");
            $cnd->attachCondition('buyer_cred.credential_username', 'LIKE', "%". $buyer. "%");
            $cnd->attachCondition('buyer_cred.credential_email', 'LIKE', "%". $buyer. "%");
            $cnd->attachCondition('buyer.user_phone', 'LIKE', "%". $buyer. "%");
        }

        if(isset($post['seller']) && $post['seller'] != '' ) {
            $seller = $post['seller'];
            $cnd = $srch->addCondition('seller.user_name', '=', $seller);
            $cnd->attachCondition('seller_cred.credential_username', '=', $seller);
            $cnd->attachCondition('seller_cred.credential_email', '=', $seller);
            $cnd->attachCondition('seller.user_phone', '=', $seller);
        }

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if(!empty($dateFrom) ) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if(!empty($dateTo) ) {
            $srch->addDateToCondition($dateTo);
        }

        $rs = $srch->getResultSet();
        $arrListing = FatApp::getDb()->fetchAll($rs);

        $this->set("arrListing", $arrListing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('requestStatusArr', OrderCancelRequest::getRequestStatusArr($this->adminLangId));
        $this->set('statusClassArr', OrderCancelRequest::getStatusClassArr());
        $this->_template->render(false, false);
    }

    public function updateStatusForm($ocrequest_id) 
    {

        $srch = new OrderCancelRequestSearch();
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->addCondition('ocrequest_id', '=', $ocrequest_id);
        //$srch->joinOrderProductChargesByType(OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addMultipleFields(array('order_reward_point_used'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $orderRewardUsed = 0;
        if(!empty($row) && $row['order_reward_point_used'] > 0) {
            $orderRewardUsed = $row['order_reward_point_used'];
        }

        $this->set('orderRewardUsed', $orderRewardUsed);
        $this->objPrivilege->canEditOrderCancellationRequests();
        $this->set('frm', $this->getUpdateStatusForm($ocrequest_id, $this->adminLangId));
        $this->_template->render(false, false);
    }

    public function setupUpdateStatus()
    {
        $this->objPrivilege->canEditOrderCancellationRequests();

        $ocrequest_id = FatApp::getPostedData('ocrequest_id', FatUtility::VAR_INT, 0);
        $frm = $this->getUpdateStatusForm($ocrequest_id, $this->adminLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if(false == $post) {
            Message::addErrorMessage($frm->getValidationErrors());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new OrderCancelRequestSearch($this->adminLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->addCondition('ocrequest_id', '=', $ocrequest_id);
        $srch->addCondition('ocrequest_status', '=', OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array( 'ocrequest_id', 'ocrequest_status', 'ocrequest_op_id', 'o.order_language_id','op_status_id'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if(!$row ) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_or_Status_is_already_Approved_or_Declined!', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $msgString = Labels::getLabel('LBL_Cancellation_request_has_been_{updatedStatus}_successfully.', $this->adminLangId);
        switch ( $post['ocrequest_status'] ){
        case OrderCancelRequest::CANCELLATION_REQUEST_STATUS_APPROVED:
            $notAllowedStatusChangeArr = array_merge(
                unserialize(FatApp::getConfig("CONF_PROCESSING_ORDER_STATUS")),
                unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")),
                (array)FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")
            );
            $status = Orders::getOrderStatusArr($this->adminLangId);
            if(in_array($row['op_status_id'], $notAllowedStatusChangeArr) ) {
                Message::addErrorMessage(Labels::getLabel(str_replace('{currentStatus}', $status[$row['op_status_id']], 'LBL_This_order_is_{currentStatus}_now,_so_not_eligible_for_cancellation'), $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            $dataToUpdate = array( 'ocrequest_status' => OrderCancelRequest::CANCELLATION_REQUEST_STATUS_APPROVED ,'ocrequest_refund_in_wallet' => $post['ocrequest_refund_in_wallet'],'ocrequest_admin_comment' => $post['ocrequest_admin_comment'] );
            $successMsgString = str_replace(strToLower('{updatedStatus}'), OrderCancelRequest::getRequestStatusArr($this->adminLangId)[OrderCancelRequest::CANCELLATION_REQUEST_STATUS_APPROVED], $msgString);
            $oObj = new Orders();
            $oObj->addChildProductOrderHistory($row['ocrequest_op_id'], $row['order_language_id'], FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"), Labels::getLabel('MSG_Your_Cancellation_Request_Approved', $row['order_language_id']), true, '', 0, $post['ocrequest_refund_in_wallet']);
            break;
        case OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED:
            $successMsgString = str_replace(strToLower('{updatedStatus}'), OrderCancelRequest::getRequestStatusArr($this->adminLangId)[OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED], $msgString);
            $dataToUpdate = array( 'ocrequest_status' => OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED );
            break;
        case OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING:
            $successMsgString = str_replace(strToLower('{updatedStatus}'), OrderCancelRequest::getRequestStatusArr($this->adminLangId)[OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING], $msgString);
            $dataToUpdate = array( 'ocrequest_status' => OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING );
            break;
        }
        $whereArr = array( 'smt' => 'ocrequest_id = ?', 'vals' => array( $row['ocrequest_id'] ) );
        $db = FatApp::getDb();
        if(!empty($dataToUpdate) ) {
            if(!$db->updateFromArray(OrderCancelRequest::DB_TBL, $dataToUpdate, $whereArr) ) {
                Message::addErrorMessage($db->getError());
                CommonHelper::redirectUserReferer();
            }
        }
        $emailObj = new EmailHandler();
        if(!$emailObj->sendOrderCancellationRequestUpdateNotification($row['ocrequest_id'], $this->adminLangId) ) {
            Message::addErrorMessage(Labels::getLabel('LBL_Email_Sending_Error', $this->adminLangId). " " . $emailObj->getError());
            CommonHelper::redirectUserReferer();
        }
        FatUtility::dieJsonSuccess($successMsgString);
    }

    private function getOrderCancellationRequestSearchForm( $langId )
    {
        $frm = new Form('frmRequestSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('id'=>'keyword','autocomplete'=>'off'));

        $frm->addSelectBox(Labels::getLabel('LBL_Request_Status', $this->adminLangId), 'ocrequest_status', OrderCancelRequest::getRequestStatusArr($langId), '', array(), 'All Request Status');

        $frm->addSelectBox(Labels::getLabel('LBL_Order_Payment_Status', $this->adminLangId), 'op_status_id', Orders::getOrderProductStatusArr($langId), '', array(), 'All Order Payment Status');
        $frm->addSelectBox(Labels::getLabel('LBL_Cancel_Reason', $this->adminLangId), 'ocrequest_ocreason_id', OrderCancelReason::getOrderCancelReasonArr($langId), '', array(), 'All Order Cancel Reason');
        $frm->addTextBox(Labels::getLabel('LBL_Buyer_Details', $this->adminLangId), 'buyer');
        $frm->addTextBox(Labels::getLabel('LBL_Seller_Details', $this->adminLangId), 'seller');
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly'));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly'));

        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'ocrequest_id', 0);
        $fld_submit=$frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getUpdateStatusForm( $ocrequest_id , $langId )
    {

        $frm = new Form('frmUpdateStatus');
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'ocrequest_status', OrderCancelRequest::getRequestStatusArr($langId), '', array(), '');
        $frm->addCheckBox(Labels::getLabel('LBL_Transfer_Refund_to_Wallet', $this->adminLangId), 'ocrequest_refund_in_wallet', 1, array('checked'=>'checked'), false, 0);
        $frm->addTextarea(Labels::getLabel('LBL_Comment', $this->adminLangId), 'ocrequest_admin_comment');
        $frm->addHiddenField('', 'ocrequest_id', $ocrequest_id);
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }
}
