<?php
class OrderReturnRequestsController extends AdminBaseController
{
    
    public function __construct($action) 
    {
        $ajaxCallArray = array();
        if(!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        } 
        parent::__construct($action);        
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewOrderReturnRequests($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditOrderReturnRequests($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);    
    }
    
    public function index() 
    {
        $this->objPrivilege->canViewOrderReturnRequests();
        $frmSearch = $this->getOrderReturnRequestSearchForm($this->adminLangId);
        $data = FatApp::getPostedData();
        if($data) {
            $data['orrequest_id'] = FatUtility::int($data['id']);
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);        
        $this->_template->render();
    }
    
    public function search()
    {
        $this->objPrivilege->canViewOrderReturnRequests();
        $frmSearch = $this->getOrderReturnRequestSearchForm($this->adminLangId);
        
        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);
        
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : FatUtility::int($data['page']);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        
        $srch = new OrderReturnRequestSearch();
        $srch->joinOrderProducts($this->adminLangId);
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderSellerUser();
        $srch->addOrderProductCharges();
        $srch->addMultipleFields(
            array( 'orrequest_id', 'orrequest_qty', 'orrequest_type', 'orrequest_returnreason_id', 
            'orrequest_date', 'orrequest_status', 'orrequest_reference', 'buyer.user_name as buyer_name', 'buyer_cred.credential_username as buyer_username',
            'buyer_cred.credential_email as buyer_email', 'buyer.user_phone as buyer_phone', 'seller.user_name as seller_name', 
            'seller.user_phone as seller_phone', 'seller_cred.credential_username as seller_username', 'seller_cred.credential_email as seller_email',
            'op_product_name','op_selprod_title', 'op_selprod_options', 'op_brand_name', 'op_shop_name',  'op_qty', 'op_unit_price','order_tax_charged','op_other_charges','op_refund_shipping' ) 
        );
        $srch->addOrder('orrequest_date', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        
        if(isset($post['buyer']) && $post['buyer'] != '' ) {
            $buyer = $post['buyer'];
            $cnd = $srch->addCondition('buyer.user_name', 'LIKE', "%". $buyer. "%");
            $cnd->attachCondition('buyer_cred.credential_username', 'LIKE', "%". $buyer. "%");
            $cnd->attachCondition('buyer_cred.credential_email', 'LIKE', "%". $buyer. "%");
            $cnd->attachCondition('buyer.user_phone', 'LIKE', "%". $buyer. "%");
        }
        
        if(isset($post['seller']) && $post['seller'] != '' ) {
            $seller = $post['seller'];
            $cnd = $srch->addCondition('seller.user_name', 'LIKE', "%" . $seller. "%");
            $cnd->attachCondition('seller_cred.credential_username', 'LIKE', "%" . $seller. "%");
            $cnd->attachCondition('seller_cred.credential_email', 'LIKE', "%" . $seller. "%");
            $cnd->attachCondition('seller.user_phone', 'LIKE', "%" . $seller. "%");
        }
        
        if(isset($post['product']) && $post['product'] != '' ) {
            $product = $post['product'];
            $cnd = $srch->addCondition('op_product_name', 'LIKE', "%" . $product. "%");
            $cnd->attachCondition('op_selprod_title', 'LIKE', "%" . $product. "%");
            $cnd->attachCondition('op_selprod_options', 'LIKE', "%" . $product. "%");
            $cnd->attachCondition('op_brand_name', 'LIKE', "%" . $product. "%");
            $cnd->attachCondition('op_shop_name', 'LIKE', "%" . $product. "%");
        }
        
        if(isset($post['ref_no']) && $post['ref_no'] != '' ) {
            $ref_no = FatUtility::convertToType($post['ref_no'], FatUtility::VAR_STRING);
            $srch->addCondition('orrequest_reference', 'like', "%$ref_no%");
        }
        
        if(isset($post['orrequest_status']) && $post['orrequest_status'] != '' ) {
            $orrequest_status = FatUtility::int($post['orrequest_status']);
            $srch->addCondition('orrequest_status', '=', $orrequest_status);
        }
        
        if(isset($post['orrequest_type']) && $post['orrequest_type'] != '' ) {
            $orrequest_type = FatUtility::int($post['orrequest_type']);
            $srch->addCondition('orrequest_type', '=', $orrequest_type);
        }

        if(isset($post['orrequest_id']) && $post['orrequest_id'] > 0 ) {
            $srch->addCondition('orrequest_id', '=', $post['orrequest_id']);
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
        $this->set('requestStatusArr', OrderReturnRequest::getRequestStatusArr($this->adminLangId));
        $this->set('requestTypeArr', OrderReturnRequest::getRequestTypeArr($this->adminLangId));
        $this->set('requestTypeClassArr', OrderReturnRequest::getRequestStatusClass());
        $this->_template->render(false, false);
    }
    
    public function downloadAttachedFileForReturn($recordId, $recordSubid =0) 
    {
        
        $recordId = FatUtility::int($recordId);
        
        if(1 > $recordId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $recordId, $recordSubid);
        
        if(false == $file_row) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);        
    }
    
    public function view( $orrequest_id )
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        if(!$orrequest_id) {
            $data = FatApp::getPostedData();
            if($data) {
                $orrequest_id = $data['id'];
            }
        }
        $srch = new OrderReturnRequestSearch();
        $srch->joinOrderProducts($this->adminLangId);
        $srch->joinOrderProductSettings();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderSellerUser();
        $srch->joinOrderReturnReasons($this->adminLangId);
        $srch->addOrderProductCharges();
        $srch->addMultipleFields(
            array( 'orrequest_id','orrequest_op_id', 'orrequest_qty', 'orrequest_type', 'orrequest_returnreason_id', 
            'orrequest_date', 'orrequest_status','orrequest_reference', 'buyer.user_name as buyer_name', 'buyer_cred.credential_username as buyer_username',
            'buyer_cred.credential_email as buyer_email', 'buyer.user_phone as buyer_phone', 'seller.user_name as seller_name', 
            'seller.user_phone as seller_phone', 'seller_cred.credential_username as seller_username', 'seller_cred.credential_email as seller_email',
            'op_product_name','op_selprod_title', 'op_selprod_options', 'op_brand_name', 'op_shop_name', 'op_qty', 'op_unit_price',  'IFNULL(orreason_title, orreason_identifier) as orreason_title','order_tax_charged','op_other_charges','op_refund_shipping','op_refund_amount','op_commission_percentage','op_affiliate_commission_percentage','op_commission_include_shipping','op_commission_include_tax','op_free_ship_upto','op_actual_shipping_charges') 
        );
        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if(!$requestRow ) {
            Message::addErrorMessage($this->str_invalid_request);
            FatApp::redirectUser(CommonHelper::generateUrl('OrderReturnRequests'));
        }
        
        $oObj = new Orders();
        $charges = $oObj->getOrderProductChargesArr($requestRow['orrequest_op_id']);
        $requestRow['charges'] = $charges; 
        
        $this->set('requestRow', $requestRow);
        $this->set('requestStatusArr', OrderReturnRequest::getRequestStatusArr($this->adminLangId));
        $this->set('requestTypeArr', OrderReturnRequest::getRequestTypeArr($this->adminLangId));
        
        $returnRequestMsgsSrchForm = $this->getOrderReturnRequestMessageSearchForm($this->adminLangId);
        $returnRequestMsgsSrchForm->fill(array( 'orrequest_id' => $requestRow['orrequest_id'] ));
        $this->set('returnRequestMsgsSrchForm', $returnRequestMsgsSrchForm);
        
        $frmMsg = $this->getOrderReturnRequestMessageForm($this->adminLangId);
        $frmMsg->fill(array( 'orrmsg_orrequest_id' => $requestRow['orrequest_id'] ));
        $this->set('frmMsg', $frmMsg);
        
        if($attachedFile = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $orrequest_id)) {
            $this->set('attachedFile', $attachedFile);
        }
        $this->set('frmUpdateStatus', $this->getUpdateStatusForm($orrequest_id, $this->adminLangId));
        $this->_template->render();
    }
    
    public function messageSearch()
    {
        $frm = $this->getOrderReturnRequestMessageSearchForm($this->adminLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        
        $orrequest_id = isset($post['orrequest_id']) ? FatUtility::int($post['orrequest_id']) : 0;

        $srch = new OrderReturnRequestMessageSearch();
        $srch->joinOrderReturnRequests();
        $srch->joinMessageUser();
        $srch->joinMessageAdmin();
        $srch->addCondition('orrmsg_orrequest_id', '=', $orrequest_id);
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addOrder('orrmsg_id', 'DESC');
        $srch->addMultipleFields(
            array( 'orrmsg_id','orrmsg_from_user_id', 'orrmsg_from_admin_id', 
            'admin_name', 'admin_username', 'admin_email', 'orrmsg_msg', 
            'orrmsg_date', 'msg_user.user_name as msg_user_name', 'msg_user_cred.credential_username as msg_username', 
            'msg_user_cred.credential_email as msg_user_email',
            'orrequest_status' ) 
        );
        
        $rs = $srch->getResultSet();
        $messagesList = FatApp::getDb()->fetchAll($rs, 'orrmsg_id');
        ksort($messagesList);
        
        $this->set('messagesList', $messagesList);
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);

        $startRecord = ($page-1)*$pageSize + 1 ;
        $endRecord = $page * $pageSize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) { $endRecord = $totalRecords; 
        }
        $json['totalRecords'] = $totalRecords;
        $json['startRecord'] = $startRecord;
        $json['endRecord'] = $endRecord;
        
        $json['html'] = $this->_template->render(false, false, 'order-return-requests/return-request-messages-list.php', true);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, 'order-return-requests/return-request-messages-list-load-more-btn.php', true);
        FatUtility::dieJsonSuccess($json);
    }
    
    public function setUpReturnOrderRequestMessage()
    {
        $this->objPrivilege->canEditOrderReturnRequests();
        $orrmsg_orrequest_id = FatApp::getPostedData('orrmsg_orrequest_id', null, '0');
        
        $frm = $this->getOrderReturnRequestMessageForm($this->adminLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post ) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $orrmsg_orrequest_id = FatUtility::int($orrmsg_orrequest_id);
        $admin_id = AdminAuthentication::getLoggedAdminId();
        
        $srch = new OrderReturnRequestSearch($this->adminLangId);
        $srch->addCondition('orrequest_id', '=', $orrmsg_orrequest_id);
        $srch->joinOrderProducts();
        $srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'orrequest_status','orrequest_user_id' ));
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if(!$requestRow ) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* save return request message[ */
        $returnRequestMsgDataToSave = array(
        'orrmsg_orrequest_id'    =>    $requestRow['orrequest_id'],
        'orrmsg_from_user_id'    =>    0,
        'orrmsg_from_admin_id'    =>    $admin_id,
        'orrmsg_msg'            =>    $post['orrmsg_msg'],
        'orrmsg_date'            =>    date('Y-m-d H:i:s'),
        );
        $oReturnRequestMsgObj = new OrderReturnRequestMessage();
        $oReturnRequestMsgObj->assignValues($returnRequestMsgDataToSave);
        if (!$oReturnRequestMsgObj->save() ) {
            Message::addErrorMessage($oReturnRequestMsgObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $orrmsg_id = $oReturnRequestMsgObj->getMainTableRecordId();
        if(!$orrmsg_id ) {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_Technical_team', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */
        
        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if(!$emailNotificationObj->sendReturnRequestMessageNotification($orrmsg_id, $this->adminLangId) ) {
            Message::addErrorMessage($emailNotificationObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */
        
        //send notification to admin			
        /*$notificationData = array(
        'notification_record_type' => Notification::TYPE_ORDER_RETURN_REQUEST_MESSAGE,
        'notification_record_id' => $orrmsg_orrequest_id,
        'notification_user_id' =>  $requestRow['orrequest_user_id'],
        'notification_label_key' => Notification::RETURN_REQUEST_MESSAGE_TO_USER_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if(!Notification::saveNotifications($notificationData)){
        Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT",$this->adminLangId));	
        FatUtility::dieWithError( Message::getHtml() );
        }	*/
        
        
        $this->set('orrmsg_orrequest_id', $orrmsg_orrequest_id);
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function setupUpdateStatus()
    {
        $this->objPrivilege->canEditOrderReturnRequests();
        
        $orrequest_id = FatApp::getPostedData('orrequest_id', FatUtility::VAR_INT, 0);
        $frm = $this->getUpdateStatusForm($orrequest_id, $this->adminLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if(false == $post) {
            Message::addErrorMessage($frm->getValidationErrors());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $srch = new OrderReturnRequestSearch($this->adminLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $cnd = $srch->addCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING);
        $cnd->attachCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'op_id', 'order_language_id','orrequest_user_id'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if(!$row ) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_or_Status_is_already_Approved_or_Declined!', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $orrObj = new OrderReturnRequest();
        $user_id = 0;
        $successMsg='';
        switch ( $post['orrequest_status'] ){
        case OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED:
            if(!$orrObj->approveRequest($row['orrequest_id'], $user_id, $this->adminLangId, $post['orrequest_refund_in_wallet'], $post['orrequest_admin_comment']) ) {
                Message::addErrorMessage($orrObj->getError());
                FatApp::redirectUser(CommonHelper::generateUrl('orderReturnRequests'));
            }
            $successMsg = Labels::getLabel('LBL_Return_request_has_been_refunded_successfully.', $this->adminLangId);
            break;
            
        case OrderReturnRequest::RETURN_REQUEST_STATUS_WITHDRAWN:
            if(!$orrObj->withdrawRequest($row['orrequest_id'], $user_id, $this->adminLangId, $row['op_id'], $row['order_language_id']) ) {
                Message::addErrorMessage(Labels::getLabel($orrObj->getError(), $this->adminLangId));
                FatApp::redirectUser(CommonHelper::generateUrl('orderReturnRequests'));
            }
            $successMsg = Labels::getLabel('LBL_Return_request_has_been_withdrawn_successfully.', $this->adminLangId);
            break;
        }
        
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification($row['orrequest_id'], $this->adminLangId) ) {
            Message::addErrorMessage(Labels::getLabel($emailNotificationObj->getError(), $this->adminLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('orderReturnRequests'));
        }

        //send notification to admin			
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_ORDER_RETURN_REQUEST,
        'notification_record_id' => $row['orrequest_id'],
        'notification_user_id' => $row['orrequest_user_id'],
        'notification_label_key' => Notification::RETURN_REQUEST_STATUS_CHANGE_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );
        
        if(!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->adminLangId));    
            FatApp::redirectUser(CommonHelper::generateUrl('orderReturnRequests'));
        }        
        
        FatUtility::dieJsonSuccess($successMsg);
    }
    
    // public function approve( $orrequest_id ){
    // $this->objPrivilege->canEditOrderReturnRequests();
    // $orrequest_id = FatUtility::int( $orrequest_id );
        
    // $srch = new OrderReturnRequestSearch( );
    // $srch->joinOrderProducts();
    // $srch->joinOrders();
    // $srch->joinSellerProducts();
    // $srch->joinOrderReturnReasons();
    // $srch->addCondition( 'orrequest_id', '=', $orrequest_id );
    // $cnd = $srch->addCondition( 'orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING );
    // $cnd->attachCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED );
    // $srch->doNotCalculateRecords();
    // $srch->doNotLimitRecords();
    // $srch->addMultipleFields( array('orrequest_id', 'op_id', 'order_language_id') );
    // $rs = $srch->getResultSet();
    // $requestRow = FatApp::getDb()->fetch( $rs );
    // if( !$requestRow ){
    // Message::addErrorMessage( Labels::getLabel('MSG_Invalid_Access', $this->adminLangId) );
    // FatApp::redirectUser( CommonHelper::generateUrl('orderReturnRequests'));
    // }
        
    // $orrObj = new OrderReturnRequest();
    // $user_id = 0;
    // if( !$orrObj->approveRequest( $requestRow['orrequest_id'], $user_id, $this->adminLangId ) ){
    // Message::addErrorMessage( $orrObj->getError() );
    // FatApp::redirectUser( CommonHelper::generateUrl('orderReturnRequests'));
    // }
        
    // /* email notification handling[ */
    // $emailNotificationObj = new EmailHandler();
    // if ( !$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification( $requestRow['orrequest_id'], $this->adminLangId ) ){
    // Message::addErrorMessage( Labels::getLabel($emailNotificationObj->getError(),$this->adminLangId) );
    // FatApp::redirectUser( CommonHelper::generateUrl('orderReturnRequests'));
    // }
    // /* ] */
        
    // Message::addMessage( Labels::getLabel('MSG_Request_Approved_Refund', $this->adminLangId) );
    // FatApp::redirectUser( CommonHelper::generateUrl('orderReturnRequests'));
    // }
    
    // public function cancel( $orrequest_id ){
    // $this->objPrivilege->canEditOrderReturnRequests();
        
    // $srch = new OrderReturnRequestSearch( );
    // $srch->joinOrderProducts();
    // $srch->joinOrders();
    // $srch->joinSellerProducts();
    // $srch->joinOrderReturnReasons();
    // $srch->addCondition( 'orrequest_id', '=', $orrequest_id );
    // $cnd = $srch->addCondition( 'orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING );
    // $cnd->attachCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED );
    // $srch->doNotCalculateRecords();
    // $srch->doNotLimitRecords();
    // $srch->addMultipleFields( array('orrequest_id', 'op_id', 'order_language_id') );
    // $rs = $srch->getResultSet();
    // $request = FatApp::getDb()->fetch( $rs );
    // if( !$request ){
    // Message::addErrorMessage( Labels::getLabel('MSG_Invalid_Access', $this->adminLangId) );
    // FatApp::redirectUser( CommonHelper::generateUrl('orderReturnRequests'));
    // }
    // $orrObj = new OrderReturnRequest();
    // $user_id = 0;
    // if( !$orrObj->withdrawRequest( $request['orrequest_id'], $user_id, $request['order_language_id'], $request['op_id'], $request['order_language_id'] ) ){
    // Message::addErrorMessage( Labels::getLabel($orrObj->getError(), $this->adminLangId) );
    // FatApp::redirectUser( CommonHelper::generateUrl('orderReturnRequests'));
    // }
        
    // /* email notification handling[ */
    // $emailNotificationObj = new EmailHandler();
    // if ( !$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification( $request['orrequest_id'], $request['order_language_id'] ) ){
    // Message::addErrorMessage( Labels::getLabel( $emailNotificationObj->getError(),$this->adminLangId ) );
    // CommonHelper::redirectUserReferer();
    // }
    // /* ] */
        
    // Message::addMessage( Labels::getLabel('MSG_Request_Withdrawn', $this->adminLangId) );
    // FatApp::redirectUser( CommonHelper::generateUrl('orderReturnRequests'));
    // }
    
    /* public function updateStatusForm($orrequest_id) {
    $this->objPrivilege->canEditOrderReturnRequests();
    $this->set('frm', $this->getUpdateStatusForm( $orrequest_id , $this->adminLangId ));
    $this->_template->render(false , false);
    } */
    
    private function getOrderReturnRequestSearchForm( $langId )
    {
        $frm = new Form('frmRequestSearch');
        
        $frm->addTextBox(Labels::getLabel('LBL_Buyer_Details', $this->adminLangId), 'buyer');
        $frm->addTextBox(Labels::getLabel('LBL_Reference_No.', $this->adminLangId), 'ref_no');
        $frm->addTextBox(Labels::getLabel('LBL_Vender_Details', $this->adminLangId), 'seller');
        $frm->addTextBox(Labels::getLabel('LBL_Product', $this->adminLangId), 'product');
        $frm->addSelectBox(Labels::getLabel('LBL_Request_Status', $this->adminLangId), 'orrequest_status', OrderReturnRequest::getRequestStatusArr($langId), '', array(), 'All Request Status');
        $requestType = OrderReturnRequest::getRequestTypeArr($langId);
        if(count($requestType)>1) {
            $frm->addSelectBox(Labels::getLabel('LBL_Request_Type', $this->adminLangId), 'orrequest_type', OrderReturnRequest::getRequestTypeArr($langId), '', array(), 'All Request Types');
        }
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly'));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly'));
        
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'orrequest_id');
        $fld_submit=$frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Submit', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);            
        return $frm;
    }
    
    private function getOrderReturnRequestMessageSearchForm( $langId )
    {
        $frm = new Form('frmOrderReturnRequestMsgsSrch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'orrequest_id');
        return $frm;
    }
    
    private function getOrderReturnRequestMessageForm( $langId )
    {
        $frm = new Form('frmOrderReturnRequestMessge');
        $frm->setRequiredStarPosition('');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Comment', $this->adminLangId), 'orrmsg_msg');
        $fld->requirements()->setRequired();
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('MSG_Message_is_mandatory', $langId));
        $frm->addHiddenField('', 'orrmsg_orrequest_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $langId));
        return $frm;
    }
    
    private function getUpdateStatusForm( $orrequest_id , $langId )
    {
        
        $frm = new Form('frmUpdateStatus');
        
        $statusArr = OrderReturnRequest::getRequestStatusArr($langId);
        unset($statusArr[OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED]);
        unset($statusArr[OrderReturnRequest::RETURN_REQUEST_STATUS_CANCELLED]);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'orrequest_status', $statusArr, '', array(), '');
        $frm->addCheckBox(Labels::getLabel('LBL_Transfer_Refund_to_Wallet', $this->adminLangId), 'orrequest_refund_in_wallet', 1, array('checked'=>'checked'), false, 0);
        $frm->addTextarea(Labels::getLabel('LBL_Comment', $this->adminLangId), 'orrequest_admin_comment');
        $frm->addHiddenField('', 'orrequest_id', $orrequest_id);
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }
}