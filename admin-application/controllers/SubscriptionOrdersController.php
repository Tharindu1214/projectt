<?php
class SubscriptionOrdersController extends AdminBaseController
{
    public function __construct($action) 
    {
        $ajaxCallArray = array();
        if(!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }     
        parent::__construct($action);        
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewSubscriptionOrders($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditSubscriptionOrders($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }
    
    public function index() 
    {
        $this->objPrivilege->canViewSubscriptionOrders();
        $this->set('frmSearch', $this->getOrderSearchForm($this->adminLangId));        
        $this->_template->render();
    }
    
    public function search() 
    {
        $this->objPrivilege->canViewSubscriptionOrders();
        $frmSearch = $this->getOrderSearchForm($this->adminLangId);
        
        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);
        
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : FatUtility::int($data['page']);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $srch = new OrderSearch();
        $srch->joinOrderBuyerUser();
        $srch->addOrder('order_date_added', 'DESC');
        $srch->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addMultipleFields(array('order_id','order_date_added', 'order_is_paid', 'buyer.user_id', 'buyer.user_name as buyer_user_name', 'buyer_cred.credential_email as buyer_email', 'order_net_amount'));
        
        $keyword = FatApp::getPostedData('keyword', null, '');
        if(!empty($keyword) ) {
            $srch->addKeywordSearch($keyword);
        }
        
        $user_id = FatApp::getPostedData('user_id', '', -1);
        if($user_id ) {
            $srch->addCondition('buyer.user_id', '=', $user_id);
        }
        
        if(isset($post['order_is_paid']) && $post['order_is_paid'] != '' ) {
            $order_is_paid = FatUtility::int($post['order_is_paid']);
            $srch->addCondition('order_is_paid', '=', $order_is_paid);
        }
        
        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if(!empty($dateFrom) ) {
            $srch->addDateFromCondition($dateFrom);
        }
        
        $dateTo = FatApp::getPostedData('date_to', null, '');
        if(!empty($dateTo) ) {
            $srch->addDateToCondition($dateTo);
        }
        
        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if(!empty($priceFrom) ) {
            $srch->addMinPriceCondition($priceFrom);
        }
        
        $priceTo = FatApp::getPostedData('price_to', null, '');
        if(!empty($priceTo) ) {
            $srch->addMaxPriceCondition($priceTo);
        }
        
        $rs = $srch->getResultSet();
        $ordersList = FatApp::getDb()->fetchAll($rs);
        
        $this->set("ordersList", $ordersList);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);                        
        $this->set('recordCount', $srch->recordCount());
        
        $this->set('canViewSellerOrders', $this->objPrivilege->canViewSellerOrders($this->admin_id, true));
        $this->set('canViewUsers', $this->objPrivilege->canViewUsers($this->admin_id, true));
        
        $this->_template->render(false, false);
    }
    
    public function View( $order_id ) 
    {
        $this->objPrivilege->canViewSubscriptionOrders();
        
        $srch = new OrderSubscriptionSearch($this->adminLangId);
        $srch->joinOrders();
        $srch->joinOrderPaymentMethod();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->joinOrderUser();
        $srch->addMultipleFields(
            array('order_id','order_user_id', 'order_date_added', 'order_is_paid','order_tax_charged', 'order_site_commission',
            'ou.user_name as buyer_user_name', 'ouc.credential_email as buyer_email','ou.user_phone as buyer_phone', 'order_net_amount',   'order_pmethod_id', 'pmethod_name','order_discount_total')
        );
        $srch->addCondition('order_id', '=', $order_id);
        $srch->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $rs = $srch->getResultSet();
        $order = FatApp::getDb()->fetch($rs);
        if(!$order ) {
            Message::addErrorMessage(Labels::getLabel('LBL_Order_data_not_found', $this->adminLangId));
            FatApp::redirectUser(CommonHelper::generateUrl("SubscriptionOrders"));
        }
        
        
        $opSrch = new OrderSubscriptionSearch($this->adminLangId, false, true);
        //$opSrch->addCountsOfOrderedProducts();
        //$opSrch->joinOrderSuscriptionStatus();
        
        $opSrch->addOrderProductCharges();
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $opSrch->addCondition('oss.ossubs_order_id', '=', $order['order_id']);
        
        $opSrch->addMultipleFields(
            array('ossubs_id', 'ossubs_invoice_number',
         
            'ossubs_price','ossubs_type', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name',
            'ossubs_frequency','ossubs_subscription_name,ossubs_interval','ossubs_status_id','ossubs_till_date','ossubs_from_date'/* ,'op_other_charges' */ )
        );
        
        $opRs = $opSrch->getResultSet();
        
        $order['products'] = FatApp::getDb()->fetchAll($opRs, 'ossubs_id');
    
        $orderObj = new Orders($order['order_id']);
        
        $charges = $orderObj->getOrderProductChargesByOrderId($order['order_id']);
        
    
        
        $addresses = $orderObj->getOrderAddresses($order['order_id']);
        
        
        $order['comments'] = $orderObj->getOrderComments($this->adminLangId, array("order_id"=>$order['order_id']));
        $order['payments'] = $orderObj->getOrderPayments(array("order_id"=>$order['order_id']));
        
        $frm = $this->getPaymentForm($this->adminLangId, $order['order_id']);
        $this->set('frm', $frm);
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->adminLangId));
        $this->set('order', $order);
        $orderStatuses = Orders::getOrderSubscriptionStatusArr($this->adminLangId);
        $this->set('orderStatuses', $orderStatuses);
        $this->_template->render();
    }
    
    public function updatePayment()
    {
        $this->objPrivilege->canEditSubscriptionOrders();
        $frm = $this->getPaymentForm($this->adminLangId);
    
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {            
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $orderId = $post['opayment_order_id'];
        if($orderId == '' || $orderId == null) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $orderPaymentObj = new OrderPayment($orderId, $this->adminLangId);
        if(!$orderPaymentObj->addOrderPayment($post["opayment_method"], $post['opayment_gateway_txn_id'], $post["opayment_amount"], $post["opayment_comments"])) {
            Message::addErrorMessage($orderPaymentObj->getError());
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        $this->set('msg', Labels::getLabel('LBL_Payment_Details_Added_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function Cancel($order_id)
    {
        $this->objPrivilege->canEditSubscriptionOrders();
        
        $orderObj =  new Orders();
        $order = $orderObj->getOrderById($order_id);
        
        if($order==false) {
            Message::addErrorMessage(Labels::getLabel('LBL_Error:_Please_perform_this_action_on_valid_record.', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());    
        }
        
        if (!$order["order_is_paid"] ) {
            if(!$orderObj->addOrderPaymentHistory($order_id, Orders::ORDER_IS_CANCELLED, Labels::getLabel('MSG_Order_Cancelled', $order['order_language_id']), 1)) {
                Message::addErrorMessage($orderObj->getError());
                FatUtility::dieJsonError(Message::getHtml());    
            }
            
            if(!$orderObj->refundOrderPaidAmount($order_id, $order['order_language_id'])) {
                Message::addErrorMessage($orderObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }    
        }
        
        $this->set('msg', Labels::getLabel('LBL_Payment_Details_Added_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    private function getPaymentForm($langId, $orderId = '')
    {
        $frm = new Form('frmPayment');
        $frm->addHiddenField('', 'opayment_order_id', $orderId);
        $frm->addTextArea(Labels::getLabel('LBL_Comments', $this->adminLangId), 'opayment_comments', '')->requirements()->setRequired();
        $frm->addRequiredField(Labels::getLabel('LBL_Payment_Method', $this->adminLangId), 'opayment_method');
        $frm->addRequiredField(Labels::getLabel('LBL_Txn_ID', $this->adminLangId), 'opayment_gateway_txn_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Amount', $this->adminLangId), 'opayment_amount')->requirements()->setFloatPositive(true);
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
    
    private function getOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id,    array('currency_code','currency_symbol_left','currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];
        
        $frm = new Form('frmSubscriptionOrderSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('id'=>'keyword','autocomplete'=>'off'));
        
        $frm->addTextBox(Labels::getLabel('LBL_Buyer', $this->adminLangId), 'buyer', '');
        
        $frm->addSelectBox(Labels::getLabel('LBL_Payment_Status', $this->adminLangId), 'order_is_paid', Orders::getOrderPaymentStatusArr($langId), '', array(), 'Select Payment Status');
        
        $frm->addDateField('', 'date_from', '', array('placeholder' => 'Date From', 'readonly' => 'readonly' ));
        $frm->addDateField('', 'date_to', '', array('placeholder' => 'Date To', 'readonly' => 'readonly' ));
        $frm->addTextBox('', 'price_from', '', array('placeholder' => 'Order From'.' ['.$currencySymbol.']' ));
        $frm->addTextBox('', 'price_to', '', array('placeholder' => 'Order To ['.$currencySymbol.']' ));
        
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'user_id');
        $fld_submit=$frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);            
        return $frm;
    }
}