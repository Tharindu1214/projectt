<?php
class SellerController extends SellerBaseController
{
    // use Attributes;
    use Options;
    use CustomProducts;
    use SellerProducts;
    use SellerCollections;
    use CustomCatalogProducts;

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $user = new User($userId);
        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'S';

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS", FatUtility::VAR_STRING, '')));
        $srch->joinSellerProducts();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        //$srch->addSellerOrderCounts(date('Y-m-d',strtotime("-1 days")),date('Y-m-d'),'yesterdayOrder');
        $srch->addCondition('op_selprod_user_id', '=', $userId);

        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber(1);
        $srch->setPageSize(2);

        $srch->addMultipleFields(
            array('order_id', 'order_user_id','op_selprod_id','op_is_batch','selprod_product_id', 'order_date_added', 'order_net_amount', 'op_invoice_number','totCombinedOrders as totOrders', 'op_selprod_title', 'op_product_name', 'op_id','op_qty','op_selprod_options','op_status_id', 'op_brand_name', 'op_shop_name','op_other_charges','op_unit_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name','op_tax_collected_by_seller','op_selprod_user_id','opshipping_by_seller_user_id')
        );

        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
        }

        /* Orders Counts [*/
        $orderSrch = new OrderProductSearch($this->siteLangId, true, true);
        $orderSrch->doNotCalculateRecords();
        $orderSrch->doNotLimitRecords();
        /* $orderSrch->addSellerOrdersCounts( date('Y-m-d',strtotime("-1 days") ), date('Y-m-d'), 'yesterdayOrder');
        $orderSrch->addSellerCompletedOrdersStats( date('Y-m-d', strtotime("-1 days")),date('Y-m-d'), 'yesterdaySold' ); */

        /* $orderSrch->addSellerOrdersCounts( date('Y-m-d',strtotime("-1 days") ), date('Y-m-d',strtotime("-1 days") ), 'todayOrder'); */
        $orderSrch->addSellerOrdersCounts(date('Y-m-d'), date('Y-m-d'), 'todayOrder');
        /* $orderSrch->addSellerCompletedOrdersStats( date('Y-m-d', strtotime("-1 days")),date('Y-m-d',strtotime("-1 days") ), 'yesterdaySold' ); */
        $orderSrch->addSellerCompletedOrdersStats(date('Y-m-d'), date('Y-m-d'), 'todaySold');

        $orderSrch->addSellerCompletedOrdersStats(false, false, 'totalSold');
        $orderSrch->addSellerInprocessOrdersStats(false, false, 'totalInprocess');
        $orderSrch->addSellerRefundedOrdersStats();
        $orderSrch->addSellerCancelledOrdersStats();
        $orderSrch->addGroupBy('order_user_id');
        $orderSrch->addCondition('op_selprod_user_id', '=', $userId);
        $orderSrch->addMultipleFields(array('todayOrderCount', 'totalInprocessSales', 'totalSoldSales', 'totalSoldCount', 'refundedOrderCount', 'refundedOrderAmount', 'cancelledOrderCount', 'cancelledOrderAmount' ));
        $rs = $orderSrch->getResultSet();
        $ordersStats = FatApp::getDb()->fetch($rs);
        /* ]*/

        /*$threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        $unreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD);
        $totalMessageCount = $threadObj->getMessageCount($userId);*/
        /*]*/
        $orderObj = new Orders();
        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();

        /* Remaining Products and Days Count [*/
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $products = new Product();

            $latestOrder = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $userId, array('ossubs_till_date','ossubs_id','ossubs_products_allowed', 'ossubs_subscription_name'));
            $pendingDaysForCurrentPlan = 0;
            $remainingAllowedProducts = 0;
            if ($latestOrder) {
                $pendingDaysForCurrentPlan = FatDate::diff(date("Y-m-d"), $latestOrder['ossubs_till_date']);
                $totalProducts  =  $products->getTotalProductsAddedByUser($userId);
                $remainingAllowedProducts = $latestOrder['ossubs_products_allowed'] - $totalProducts;
                $this->set('subscriptionTillDate', $latestOrder['ossubs_till_date']);
                $this->set('subscriptionName', $latestOrder['ossubs_subscription_name']);
            }

            $this->set('pendingDaysForCurrentPlan', $pendingDaysForCurrentPlan);
            $this->set('remainingAllowedProducts', $remainingAllowedProducts);
        }
        /*]*/

        /*
        * Return Request Listing
        */
        $srchReturnReq = $this->returnReuestsListingObj();
        $srchReturnReq->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $srchReturnReq->getResultSet();
        $returnRequests = FatApp::getDb()->fetchAll($rs);

        /*
        * Transactions Listing
        */
        $transSrch = Transactions::getUserTransactionsObj($userId);
        $transSrch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $transSrch->getResultSet();
        $transactions = FatApp::getDb()->fetchAll($rs, 'utxn_id');
        /*
        * Cancellation Request Listing
        */
        $canSrch = $this->cancelRequestListingObj();
        $canSrch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $canSrch->getResultSet();
        $cancellationRequests = FatApp::getDb()->fetchAll($rs);
        $this->set('returnRequestsCount', $srchReturnReq->recordCount());

        $txnObj = new Transactions();
        $txnsSummary = $txnObj->getTransactionSummary($userId, date('Y-m-d'));

        $this->set('transactions', $transactions);
        $this->set('returnRequests', $returnRequests);
        $this->set('OrderReturnRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->set('cancellationRequests', $cancellationRequests);
        $this->set('txnStatusArr', Transactions::getStatusArr($this->siteLangId));
        $this->set('OrderCancelRequestStatusArr', OrderCancelRequest::getRequestStatusArr($this->siteLangId));
        $this->set('txnsSummary', $txnsSummary);

        $this->set('notAllowedStatues', $notAllowedStatues);
        $this->set('orders', $orders);
        $this->set('ordersCount', $srch->recordCount());
        $this->set('data', $user->getProfileData());
        $this->set('userBalance', User::getUserBalance($userId));
        $this->set('ordersStats', $ordersStats);
        $this->set('dashboardStats', Stats::getUserSales($userId));

        $this->_template->addJs(array('js/chartist.min.js'));
        $this->_template->addCss(array('css/chartist.css'));
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render(true, true);
    }

    public function sales()
    {
        $frmOrderSrch = $this->getOrderSearchForm($this->siteLangId);
        $this->set('frmOrderSrch', $frmOrderSrch);
        $this->_template->render(true, true);
    }

    public function orderProductSearchListing()
    {
        $frm = $this->getOrderSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $userId = UserAuthentication::getLoggedUserId();

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinSellerProducts();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addMultipleFields(
            array( 'order_id', 'order_user_id','op_selprod_id','op_is_batch','selprod_product_id','order_date_added', 'order_net_amount', 'op_invoice_number','totCombinedOrders as totOrders', 'op_selprod_title', 'op_product_name', 'op_id','op_qty','op_selprod_options', 'op_brand_name', 'op_shop_name','op_other_charges','op_unit_price','op_tax_collected_by_seller','op_selprod_user_id','opshipping_by_seller_user_id', 'orderstatus_id', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name' )
        );

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');

        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")))) {
            $srch->addStatusCondition($op_status_id);
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        }

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if (!empty($priceFrom)) {
            $srch->addMinPriceCondition($priceFrom);
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addMaxPriceCondition($priceTo);
        }

        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
        }

        $this->set('orders', $orders);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    private function getOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code','currency_symbol_left','currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];
        $frm = new Form('frmOrderSrch');
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $langId) ));
        $frm->addSelectBox('', 'status', Orders::getOrderProductStatusArr($langId, unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS"))), '', array(), Labels::getLabel('LBL_Status', $langId));
        $frm->addTextBox('', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Price_Min', $langId).' ['.$currencySymbol.']' ));
        $frm->addTextBox('', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Price_Max', $langId).' ['.$currencySymbol.']' ));
        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $langId) ,'readonly'=>'readonly' ));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $langId)  ,'readonly'=>'readonly'));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    public function orderSearchListing()
    {
        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getSubscriptionOrderSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $userId = UserAuthentication::getLoggedUserId();

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addCondition('opcharge_order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderSubscriptionSearch($this->siteLangId, true, true);
        $srch->joinSubscription();
        $srch->joinOrderUser();
        //$srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'oss.ossubs_id = opcc.opcharge_op_id', 'opcc');
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $srch->addOrder("ossubs_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addMultipleFields(
            array('order_id', 'order_user_id','user_autorenew_subscription','ossubs_id','ossubs_type','ossubs_plan_id','order_date_added', 'order_net_amount', 'ossubs_invoice_number','ossubs_subscription_name',  'ossubs_id', 'op_other_charges','ossubs_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name','ossubs_interval','ossubs_frequency','ossubs_till_date','ossubs_status_id','ossubs_from_date','order_language_id')
        );

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');

        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_SELLER_SUBSCRIPTION_STATUS")))) {
            $srch->addStatusCondition($op_status_id);
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_SELLER_SUBSCRIPTION_STATUS")));
        }

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if (!empty($priceFrom)) {
            $srch->addHaving('totOrders', '=', '1');
            $srch->addMinPriceCondition($priceFrom);
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addHaving('totOrders', '=', '1');
            $srch->addMaxPriceCondition($priceTo);
        }
        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['ossubs_id']);
            $order['charges'] = $charges;
        }
        $orderStatuses = Orders::getOrderSubscriptionStatusArr($this->siteLangId);
        $this->set('orders', $orders);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function viewOrder($op_id, $print = false)
    {
        $op_id =  FatUtility::int($op_id);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();

        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
        $userId = UserAuthentication::getLoggedUserId();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addOrderProductCharges();
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $codOrder = false;
        if (strtolower($orderDetail['pmethod_code']) == 'cashondelivery') {
            $codOrder = true;
        }

        if ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(true, $codOrder);
        } elseif ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder);
        } else {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder);
        }

        /*[ if shipping not handled by seller then seller can not update status to ship and delived */
        if (!CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id'])) {
            $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
            $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
        }
        /*]*/

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $orderDetail['charges'] = $charges;
        $address = $orderObj->getOrderAddresses($orderDetail['op_order_id']);
        $orderDetail['billingAddress'] = (isset($address[Orders::BILLING_ADDRESS_TYPE]))?$address[Orders::BILLING_ADDRESS_TYPE]:array();
        $orderDetail['shippingAddress'] = (isset($address[Orders::SHIPPING_ADDRESS_TYPE]))?$address[Orders::SHIPPING_ADDRESS_TYPE]:array();

        $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("op_id"=>$op_id,'seller_id'=>$userId));

        $data = array('op_id'=>$op_id , 'op_status_id' => $orderDetail['op_status_id']);
        $frm = $this->getOrderCommentsForm($orderDetail, $processingStatuses);
        $frm->fill($data);

        $shippedBySeller = applicationConstants::NO;
        if (CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id'])) {
            $shippedBySeller = applicationConstants::YES;
        }

        $digitalDownloads = array();
        if ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $digitalDownloads = Orders::getOrderProductDigitalDownloads($op_id);
        }

        $digitalDownloadLinks = array();
        if ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $digitalDownloadLinks = Orders::getOrderProductDigitalDownloadLinks($op_id);
        }

        $this->set('orderDetail', $orderDetail);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('shippedBySeller', $shippedBySeller);
        $this->set('digitalDownloads', $digitalDownloads);
        $this->set('digitalDownloadLinks', $digitalDownloadLinks);
        $this->set('languages', Language::getAllNames());
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));
        $this->set('frm', $frm);
        $this->set('displayForm', (in_array($orderDetail['op_status_id'], $processingStatuses)));

        if ($print) {
            $print = true;
        }
        $this->set('print', $print);
        $urlParts = array_filter(FatApp::getParameters());
        $this->set('urlParts', $urlParts);
        $this->_template->render(true, true);
    }

    public function viewSubscriptionOrder($ossubs_id)
    {
        $op_id =  FatUtility::int($ossubs_id);
        if (1 > $ossubs_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();

        $orderStatuses = Orders::getOrderSubscriptionStatusArr($this->siteLangId);
        $userId = UserAuthentication::getLoggedUserId();

        $srch = new OrderSubscriptionSearch($this->siteLangId, true, true);

        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('ossubs_id', '=', $op_id);
        $rs = $srch->getResultSet();

        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $orderDetail['charges'] = $charges;

        $data = array('ossubs_id'=>$ossubs_id , 'ossubs_status_id' => $orderDetail['ossubs_status_id']);
        //    $frm = $this->getOrderCommentsForm($orderDetail,$processingStatuses);
        //$frm->fill($data);

        $this->set('orderDetail', $orderDetail);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));
        //$this->set('frm', $frm);
        //    $this->set('displayForm',(in_array($orderDetail['op_status_id'],$processingStatuses)));
        $this->_template->render(true, true);
    }

    public function changeOrderStatus()
    {
        $post = FatApp::getPostedData();
        if (!isset($post['op_id'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $op_id = FatUtility::int($post['op_id']);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $oCancelRequestSrch = new OrderCancelRequestSearch();
        $oCancelRequestSrch->doNotCalculateRecords();
        $oCancelRequestSrch->doNotLimitRecords();
        $oCancelRequestSrch->addCondition('ocrequest_op_id', '=', $op_id);
        $oCancelRequestSrch->addCondition('ocrequest_status', '!=', OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED);
        $oCancelRequestRs = $oCancelRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oCancelRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Cancel_request_is_submitted_for_this_order', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $loggedUserId = UserAuthentication::getLoggedUserId();

        $orderObj = new Orders();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinOrderCancellationRequest();
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->addCondition('op_selprod_user_id', '=', $loggedUserId);
        $srch->addCondition('op_id', '=', $op_id);
        $rs = $srch->getResultSet();

        $orderDetail = FatApp::getDb()->fetch($rs);

        if (empty($orderDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($orderDetail["op_status_id"]!= $post['op_status_id'] && $orderDetail['ocrequest_status'] != '' && $orderDetail['ocrequest_status'] == OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Buyer_Order_Cancellation_request_is_pending', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* if( strtolower($orderDetail['pmethod_code']) == 'cashondelivery' ){
        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false,true);
        } else {
        $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses();
        } */

        $codOrder = false;
        if (strtolower($orderDetail['pmethod_code']) == 'cashondelivery') {
            $codOrder = true;
        }

        if ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(true, $codOrder);
        } elseif ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder);
        } else {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder);
        }


        /*[ if shipping not handled by seller then seller can not update status to ship and delived*/
        $opshipping_by_seller_user_id = isset($orderDetail['opshipping_by_seller_user_id'])?$orderDetail['opshipping_by_seller_user_id']:0;
        if (!CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $opshipping_by_seller_user_id)) {
            $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
            $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
        }
        /*]*/

        $frm =  $this->getOrderCommentsForm($orderDetail, $processingStatuses);
        $post = $frm->getFormDataFromArray($post);

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (in_array($orderDetail["op_status_id"], $processingStatuses) && in_array($post["op_status_id"], $processingStatuses)) {
            if (!$orderObj->addChildProductOrderHistory($op_id, $orderDetail["order_language_id"], $post["op_status_id"], $post["comments"], $post["customer_notified"], $post["tracking_number"])) {
                Message::addErrorMessage(Labels::getLabel('M_ERROR_INVALID_REQUEST', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        } else {
            Message::addErrorMessage(Labels::getLabel('M_ERROR_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('op_id', $op_id);
        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function cancelOrder($op_id)
    {
        $userId = UserAuthentication::getLoggedUserId();

        $op_id = FatUtility::int($op_id);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();
        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $op_id);
        $rs = $srch->getResultSet();

        $orderDetail = FatApp::getDb()->fetch($rs);

        if (empty($orderDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $orderDetail['charges'] = $charges;

        $address = $orderObj->getOrderAddresses($orderDetail['op_order_id']);
        $orderDetail['billingAddress'] = (isset($address[Orders::BILLING_ADDRESS_TYPE]))?$address[Orders::BILLING_ADDRESS_TYPE]:array();
        $orderDetail['shippingAddress'] = (isset($address[Orders::SHIPPING_ADDRESS_TYPE]))?$address[Orders::SHIPPING_ADDRESS_TYPE]:array();
        $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("op_id"=>$op_id,'seller_id'=>$userId));

        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);

        $notEligible = false;
        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();

        if (in_array($orderDetail["op_status_id"], $notAllowedStatues)) {
            $notEligible = true;
            Message::addErrorMessage(sprintf(Labels::getLabel('LBL_this_order_already', $this->siteLangId), $orderStatuses[$orderDetail["op_status_id"]]));
        }

        $frm = $this->getOrderCancelForm($this->siteLangId);
        $frm->fill(array('op_id'=>$op_id));

        $this->set('notEligible', $notEligible);
        $this->set('frm', $frm);
        $this->set('orderDetail', $orderDetail);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));
        $this->_template->render(true, true);
    }

    public function cancelReason()
    {
        $frm = $this->getOrderCancelForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $op_id = FatUtility::int($post['op_id']);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userId = UserAuthentication::getLoggedUserId();

        $orderObj = new Orders();
        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $op_id);
        $rs = $srch->getResultSet();

        $orderDetail = FatApp::getDb()->fetch($rs);

        if (empty($orderDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();
        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);

        if (in_array($orderDetail["op_status_id"], $notAllowedStatues)) {
            Message::addErrorMessage(sprintf(Labels::getLabel('LBL_this_order_already', $this->siteLangId), $orderStatuses[$orderDetail["op_status_id"]]));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$orderObj->addChildProductOrderHistory($op_id, $this->siteLangId, FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"), $post["comments"], true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        Message::addMessage(Labels::getLabel("MSG_Updated_Successfully", $this->siteLangId));
        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function catalog($displayDefaultListing = false)
    {
        $displayDefaultListing = FatUtility::int($displayDefaultListing);

        if (!$this->isShopActive(UserAuthentication::getLoggedUserId(), 0, true)) {
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'shop'));
        }
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'Packages'));
        }

        $frmSearchCatalogProduct = $this->getCatalogProductSearchForm();
        $this->set("frmSearchCatalogProduct", $frmSearchCatalogProduct);
        $this->set("displayDefaultListing", $displayDefaultListing);
        $this->set('canRequestProduct', User::canRequestProduct());
        $this->set('canAddCustomProduct', User::canAddCustomProduct());
        $this->set('canAddCustomProductAvailableToAllSellers', User::canAddCustomProductAvailableToAllSellers());
        $this->_template->render(true, true);
    }

    public function requestedCatalog()
    {
        if (!$this->isShopActive(UserAuthentication::getLoggedUserId(), 0, true)) {
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'shop'));
        }
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'catalog'));
        }
        $this->_template->render(true, true);
    }

    public function searchRequestedCatalog()
    {
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $cRequestObj = new User(UserAuthentication::getLoggedUserId());
        $srch = $cRequestObj->getUserCatalogRequestsObj();
        $srch->addMultipleFields(
            array(
            'scatrequest_id',
            'scatrequest_user_id',
            'scatrequest_reference',
            'scatrequest_title',
            'scatrequest_comments',
            'scatrequest_status',
            'scatrequest_date'
                            )
        );
        $srch->addOrder('scatrequest_date', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();

        $arr_listing = $db->fetchAll($rs);

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('catalogReqStatusArr', User::getCatalogReqStatusArr($this->siteLangId));
        $this->_template->render(false, false);
    }

    public function addCatalogRequest()
    {
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = $this->addNewCatalogRequestForm();
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setUpCatalogRequest()
    {
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $userId = UserAuthentication::getLoggedUserId();

        $frm =  $this->addNewCatalogRequestForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new User($userId);
        $reference_number = $userId.'-'.time();

        $db = FatApp::getDb();
        $db->startTransaction();

        $data = array(
        'scatrequest_user_id'=>$userId,
        'scatrequest_reference'=>$reference_number,
        'scatrequest_title'=>$post['scatrequest_title'],
        'scatrequest_content'=>$post['scatrequest_content'],
        'scatrequest_date'=>date('Y-m-d H:i:s'),
        );

        if (!$obj->addCatalogRequest($data)) {
            $db->rollbackTransaction();
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $scatrequest_id = FatApp::getDb()->getInsertId();
        if (!$scatrequest_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* attach file with request [ */

        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $uploadedFile = $_FILES['file']['tmp_name'];
            $uploadedFileExt = pathinfo($uploadedFile, PATHINFO_EXTENSION);

            if (filesize($uploadedFile) > 10240000) {
                Message::addErrorMessage(Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }

            $fileHandlerObj = new AttachedFile();
            if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_SELLER_CATALOG_REQUEST, $scatrequest_id, 0, $_FILES['file']['name'], -1, true)) {
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        /* ] */

        if (!$obj->notifyAdminCatalogRequest($data, $this->siteLangId)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        //send notification to admin
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_CATALOG,
        'notification_record_id' => $scatrequest_id,
        'notification_user_id' => $userId,
        'notification_label_key' => Notification::NEW_CATALOG_REQUEST_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('MSG_CATALOG_REQUESTED_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function viewRequestedCatalog($scatrequest_id)
    {
        $scatrequest_id = FatUtility::int($scatrequest_id);
        if (1 > $scatrequest_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $cRequestObj = new User(UserAuthentication::getLoggedUserId());
        $srch = $cRequestObj->getUserCatalogRequestsObj($scatrequest_id);
        $srch->addCondition('tucr.scatrequest_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addMultipleFields(array('scatrequest_id','scatrequest_title','scatrequest_content','scatrequest_comments','scatrequest_reference'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();
        $row =  FatApp::getDb()->fetch($rs);
        if (!$row) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $this->set("data", $row);
        $this->_template->render(false, false);
    }

    public function catalogRequestMsgForm($requestId = 0)
    {
        $requestId = FatUtility::int($requestId);
        $frm = $this->getCatalogRequestMessageForm($requestId);

        if (0 >= $requestId) {
            FatUtility::dieWithError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $userObj = new User();
        $srch = $userObj->getUserSupplierRequestsObj($requestId);
        $srch->addFld('tusr.*');

        $rs = $srch->getResultSet();

        if (!$rs || FatApp::getDb()->fetch($rs) === false) {
            FatUtility::dieWithError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        $this->set('requestId', $requestId);

        $this->set('frm', $frm);
        $this->set('logged_user_id', UserAuthentication::getLoggedUserId());
        $this->set('logged_user_name', UserAuthentication::getLoggedUserAttribute('user_name'));

        $searchFrm = $this->getCatalogRequestMessageSearchForm();
        $searchFrm->getField('requestId')->value = $requestId;
        $this->set('searchFrm', $searchFrm);

        $this->_template->render(false, false);
    }

    public function catalogRequestMessageSearch()
    {
        $frm = $this->getCatalogRequestMessageSearchForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = 1;

        $requestId = isset($post['requestId']) ? FatUtility::int($post['requestId']) : 0;

        $srch = new CatalogRequestMessageSearch();
        $srch->joinCatalogRequests();
        $srch->joinMessageUser();
        $srch->joinMessageAdmin();
        $srch->addCondition('scatrequestmsg_scatrequest_id', '=', $requestId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addOrder('scatrequestmsg_id', 'DESC');
        $srch->addMultipleFields(
            array( 'scatrequestmsg_id','scatrequestmsg_from_user_id', 'scatrequestmsg_from_admin_id',
            'admin_name', 'admin_username', 'admin_email', 'scatrequestmsg_msg',
            'scatrequestmsg_date', 'msg_user.user_name as msg_user_name', 'msg_user_cred.credential_username as msg_username',
            'msg_user_cred.credential_email as msg_user_email',
            'scatrequest_status' )
        );

        $rs = $srch->getResultSet();
        $messagesList = FatApp::getDb()->fetchAll($rs, 'scatrequestmsg_id');
        ksort($messagesList);

        $this->set('messagesList', $messagesList);
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);

        $startRecord = ($page-1)*$pageSize + 1 ;
        $endRecord = $page * $pageSize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }
        $json['totalRecords'] = $totalRecords;
        $json['startRecord'] = $startRecord;
        $json['endRecord'] = $endRecord;

        $json['html'] = $this->_template->render(false, false, 'seller/catalog-request-messages-list.php', true, false);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, 'seller/catalog-request-messages-list-load-more-btn.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    public function setUpCatalogRequestMessage()
    {
        $requestId = FatApp::getPostedData('requestId', null, '0');
        $frm = $this->getCatalogRequestMessageForm($requestId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $requestId = FatUtility::int($requestId);

        $srch = new CatalogRequestSearch($this->siteLangId);
        $srch->addCondition('scatrequest_id', '=', $requestId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('scatrequest_id', 'scatrequest_status'));
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* save catalog request message[ */
        $dataToSave = array(
        'scatrequestmsg_scatrequest_id'    =>    $requestRow['scatrequest_id'],
        'scatrequestmsg_from_user_id'    =>    UserAuthentication::getLoggedUserId(),
        'scatrequestmsg_from_admin_id'    =>    0,
        'scatrequestmsg_msg'            =>    $post['message'],
        'scatrequestmsg_date'            =>    date('Y-m-d H:i:s'),
        );
        $catRequestMsgObj = new CatalogRequestMessage();
        $catRequestMsgObj->assignValues($dataToSave, true);
        if (!$catRequestMsgObj->save()) {
            Message::addErrorMessage($catRequestMsgObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $scatrequestmsg_id = $catRequestMsgObj->getMainTableRecordId();
        if (!$scatrequestmsg_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_Technical_team', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendCatalogRequestMessageNotification($scatrequestmsg_id, $this->siteLangId)) {
            Message::addErrorMessage($emailNotificationObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        /* send notification to admin [*/
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_CATALOG_REQUEST,
        'notification_record_id' => $scatrequestmsg_id,
        'notification_user_id' => UserAuthentication::getLoggedUserId(),
        'notification_label_key' => Notification::CATALOG_REQUEST_MESSAGE_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        $this->set('scatrequestmsg_scatrequest_id', $requestId);
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRequestedCatalog()
    {
        $post = FatApp::getPostedData();
        $scatrequest_id = FatUtility::int($post['scatrequest_id']);

        if (1 > $scatrequest_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $cRequestObj = new User(UserAuthentication::getLoggedUserId());
        $srch = $cRequestObj->getUserCatalogRequestsObj($scatrequest_id);
        $srch->addCondition('tucr.scatrequest_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addCondition('tucr.scatrequest_status', '=', 0);
        $srch->addMultipleFields(array('scatrequest_id','scatrequest_status'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();

        $row = FatApp::getDb()->fetch($rs);

        if ($row == false || ($row != false && $row['scatrequest_status'] != User::CATALOG_REQUEST_PENDING)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$cRequestObj->deleteCatalogRequest($row['scatrequest_id'])) {
            Message::addErrorMessage(Labels::getLabel($cRequestObj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('scatrequest_id', $row['scatrequest_id']);
        $this->set('msg', Labels::getLabel('LBL_Record_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function searchCatalogProduct()
    {
        $frmSearchCatalogProduct = $this->getCatalogProductSearchForm();
        $post = $frmSearchCatalogProduct->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        /* echo $page; die; */
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        //$srch = Product::getSearchObject($this->siteLangId);
        $srch = new ProductSearch($this->siteLangId, null, null, false, false);
        $srch->joinProductShippedBySeller(UserAuthentication::getLoggedUserId());
        $srch->joinTable(AttributeGroup::DB_TBL, 'LEFT OUTER JOIN', 'product_attrgrp_id = attrgrp_id', 'attrgrp');
        $srch->joinTable(UpcCode::DB_TBL, 'LEFT OUTER JOIN', 'upc_product_id = product_id', 'upc');

        /* $cnd = $srch->addCondition( 'product_seller_id', '=',0);
        $cnd->attachCondition( 'product_added_by_admin_id', '=', applicationConstants::YES,'OR');

        if( User::canAddCustomProduct() ){
        $cnd->attachCondition('product_seller_id', '=', UserAuthentication::getLoggedUserId(),'OR');
        } */
        $srch->addDirectCondition(
            '((CASE
                    WHEN product_seller_id = 0 THEN product_active = 1
                    WHEN product_seller_id > 0 THEN product_active IN (1, 0)
                    END ) )'
        );
        if (User::canAddCustomProduct()) {
            $srch->addDirectCondition('((product_seller_id = 0 AND product_added_by_admin_id = '.applicationConstants::YES.') OR product_seller_id = '.UserAuthentication::getLoggedUserId().')');
        } else {
            $cnd = $srch->addCondition('product_seller_id', '=', 0);
            $cnd->attachCondition('product_added_by_admin_id', '=', applicationConstants::YES, 'AND');
        }

        $srch->addCondition('product_deleted', '=', applicationConstants::NO);

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_identifier', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('attrgrp_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_model', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('upc_code', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_upc', 'like', '%' . $keyword . '%');
        }

        if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT')) {
            $is_custom_or_catalog = FatApp::getPostedData('type', FatUtility::VAR_INT, -1);
            if ($is_custom_or_catalog > -1) {
                if ($is_custom_or_catalog > 0) {
                    $srch->addCondition('product_seller_id', '>', 0);
                } else {
                    $srch->addCondition('product_seller_id', '=', 0);
                }
            }
        }

        $product_type = FatApp::getPostedData('product_type', FatUtility::VAR_INT, -1);
        if ($product_type != -1) {
            $srch->addCondition('product_type', '=', $product_type);
        }

        $srch->addMultipleFields(
            array(
            'product_id',
            'product_identifier',
            'IFNULL(product_name, product_identifier) as product_name',
            'product_added_on',
            'product_model',
            'product_attrgrp_id',
            'attrgrp_name',
            'psbs_user_id',
            'product_seller_id ',
            'product_added_by_admin_id',
            'product_type',
            'product_active',
            'product_approved',
            )
        );
        $srch->addOrder('product_active', 'DESC');
        $srch->addOrder('product_added_on', 'DESC');
        $srch->addGroupBy('product_id');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs);

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('siteLangId', $this->siteLangId);

        unset($post['page']);
        $frmSearchCatalogProduct->fill($post);
        $this->set("frmSearchCatalogProduct", $frmSearchCatalogProduct);
        $this->_template->render(false, false);
    }

    public function setUpshippedBy()
    {
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $product_id = FatUtility::int($post['product_id']);
        $shippedBy = $post['shippedBy'];
        $userId = UserAuthentication::getLoggedUserId();

        if (1 > $product_id && 1 > $userId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $db = FatApp::getDb();
        if ($shippedBy == 'admin') {
            $whr = array('smt'=>'psbs_product_id = ? and psbs_user_id = ?', 'vals'=>array($product_id, $userId));
            if (!$db->deleteRecords(Product::DB_PRODUCT_SHIPPED_BY_SELLER, $whr)) {
                Message::addErrorMessage($db->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        } elseif ($shippedBy == 'seller') {
            $data = array('psbs_product_id'=>$product_id,'psbs_user_id'=>$userId);
            if (!$db->insertFromArray(Product::DB_PRODUCT_SHIPPED_BY_SELLER, $data)) {
                Message::addErrorMessage($db->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        } else {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function taxCategories()
    {
        $frmSearch = $this->getTaxCatSearchForm($this->siteLangId);
        $this->set("frmSearch", $frmSearch);
        $this->_template->render(true, true);
    }

    public function searchTaxCategories()
    {
        //echo UserAuthentication::getLoggedUserId();
        $userId = UserAuthentication::getLoggedUserId();
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $frmSearch = $this->getTaxCatSearchForm($this->siteLangId);

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $frmSearch->getFormDataFromArray($data);
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);

        $srch = Tax::getSearchObject($this->siteLangId);

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('t.taxcat_identifier', 'like', '%'.$post['keyword'].'%');
            $cnd->attachCondition('t_l.taxcat_name', 'like', '%'.$post['keyword'].'%');
        }

        $srch->addMultipleFields(array('taxcat_id','IFNULL(taxcat_name,taxcat_identifier) as taxcat_name'));
        $srch->addCondition('taxcat_deleted', '=', 0);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('taxcat_name', 'ASC');
        /* $srch->addMultipleFields(array('taxcat_id','IFNULL(taxcat_name,taxcat_identifier) as taxcat_name','taxval_seller_user_id','taxval_is_percent','taxval_value'));
        $srch->joinTable(Tax::DB_TBL_VALUES, 'LEFT OUTER JOIN','tv.taxval_taxcat_id = t.taxcat_id','tv');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('taxval_seller_user_id','DESC'); */
        $taxCatData = array();

        $rs =  $srch->getResultSet();
        $taxCatData = FatApp::getDb()->fetchAll($rs, 'taxcat_id');

        $records = array();
        if (!empty($taxCatData)) {
            $taxObj = new Tax();
            foreach ($taxCatData as $tcatId => $val) {
                $defaultTaxValues = $taxObj->getTaxValuesByCatId($tcatId, $userId, true);
                $taxValues = $taxObj->getTaxValuesByCatId($tcatId, $userId);

                $records[$tcatId] = $taxValues;
                $records[$tcatId]['default'] = $defaultTaxValues;
                $records[$tcatId]['taxcat_name'] = $val['taxcat_name'];
                $records[$tcatId]['taxcat_id'] = $val['taxcat_id'];
                //$records[$tcatId]['taxval_seller_user_id'] = $userId;
            }
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('userId', $userId);
        $this->_template->render(false, false);
    }

    public function changeTaxRates($taxcat_id)
    {
        $taxcat_id = FatUtility::int($taxcat_id);
        $taxObj = new Tax();

        $taxValues = $taxObj->getTaxValuesByCatId($taxcat_id, UserAuthentication::getLoggedUserId());

        $frm = $this->getchangeTaxRatesForm($this->siteLangId);
        $frm->fill($taxValues+array('taxcat_id'=>$taxcat_id));

        $this->set('frm', $frm);
        $this->set('userId', UserAuthentication::getLoggedUserId());
        $this->_template->render(false, false);
    }

    public function setUpTaxRates()
    {
        $frm = $this->getchangeTaxRatesForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $taxcat_id = $post['taxcat_id'];
        if (1 > $taxcat_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access'));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = array(
        'taxval_taxcat_id' =>$taxcat_id,
        'taxval_seller_user_id'=>UserAuthentication::getLoggedUserId(),
        'taxval_is_percent'=>$post['taxval_is_percent'],
        'taxval_value'=>$post['taxval_value']
        );

        $taxObj = new Tax();
        if (!$taxObj->addUpdateTaxValues($data, $data)) {
            Message::addErrorMessage($taxObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('taxcatId', $taxcat_id);
        $this->set('msg', Labels::getLabel('MSG_Setup_Successfull', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function shop($tab = '', $subTab = '')
    {
        if (!UserPrivilege::isUserHasValidSubsription(UserAuthentication::getLoggedUserId())) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'Packages'));
        }
        $this->_template->addJs('js/jscolor.js');
        $userId = UserAuthentication::getLoggedUserId();
        $shopDetails = Shop::getAttributesByUserId($userId, array('shop_id'), false);

        $shop_id = 0;
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }
        $this->set('tab', $tab);
        $this->set('subTab', $subTab);
        $this->set('shop_id', $shop_id);
        $this->set('language', Language::getAllNames());
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(true, true);
    }

    public function shopForm()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = 0;
        $stateId = 0;

        if (!false == $shopDetails) {
            $shop_id =  $shopDetails['shop_id'];
            $stateId = $shopDetails['shop_state_id'];
        }

        $shopLayoutTemplateId =  $shopDetails['shop_ltemplate_id'];
        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }
        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);

        $shopFrm =  $this->getShopInfoForm($shop_id);

        /* url data[ */
        $urlSrch = UrlRewrite::getSearchObject();
        $urlSrch->doNotCalculateRecords();
        $urlSrch->doNotLimitRecords();
        $urlSrch->addFld('urlrewrite_custom');
        $urlSrch->addCondition('urlrewrite_original', '=', Shop::SHOP_VIEW_ORGINAL_URL.$shop_id);
        $rs = $urlSrch->getResultSet();
        $urlRow = FatApp::getDb()->fetch($rs);
        if ($urlRow) {
            $shopDetails['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
        }
        /* ] */

        $shopFrm->fill($shopDetails);

        $this->set('shopFrm', $shopFrm);
        $this->set('stateId', $stateId);
        $this->set('shop_id', $shop_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function shopMediaForm()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shopLayoutTemplateId =  $shopDetails['shop_ltemplate_id'];
        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }

        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);

        $shop_id = 0;
        $stateId = 0;

        if (!false == $shopDetails) {
            $shop_id =  $shopDetails['shop_id'];
            $stateId = $shopDetails['shop_state_id'];
        }

        $shopLogoFrm =  $this->getShopLogoForm($shop_id, $this->siteLangId);
        $shopBannerFrm =  $this->getShopBannerForm($shop_id, $this->siteLangId);
        $shopBackgroundImageFrm =  $this->getBackgroundImageForm($shop_id, $this->siteLangId);

        $this->set('shopDetails', $shopDetails);
        $this->set('shopLogoFrm', $shopLogoFrm);
        $this->set('shopBannerFrm', $shopBannerFrm);
        $this->set('shopBackgroundImageFrm', $shopBackgroundImageFrm);
        $this->set('language', Language::getAllNames());
        $this->set('shop_id', $shop_id);
        $this->_template->render(false, false);
    }

    public function shopImages($imageType, $lang_id = 0, $slide_screen = 0)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = 0;
        $stateId = 0;
        $bannerAttachments = array();
        $logoAttachments = array();
        $backgroundAttachments = array();

        if (!false == $shopDetails) {
            $shop_id =  $shopDetails['shop_id'];
            $stateId = $shopDetails['shop_state_id'];

            if ($imageType=='logo') {
                $logoAttachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_LOGO, $shop_id, 0, $lang_id, false);
                $this->set('images', $logoAttachments);
                $this->set('imageFunction', 'shopLogo');
            } elseif ($imageType=='banner') {
                $bannerAttachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_BANNER, $shop_id, 0, $lang_id, false, $slide_screen);
                // CommonHelper::printArray($bannerAttachments); die;
                $this->set('images', $bannerAttachments);
                $this->set('imageFunction', 'shopBanner');
            } else {
                $backgroundAttachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE, $shop_id, 0, $lang_id, false);
                $this->set('images', $backgroundAttachments);
                $this->set('imageFunction', 'shopBackgroundImage');
            }
        }
        $this->set('imageType', $imageType);
        $this->set('shopDetails', $shopDetails);
        $this->set('shop_id', $shop_id);
        $this->set('languages', applicationConstants::bannerTypeArr());
        $this->_template->render(false, false);
    }

    public function shopLangForm($shopId, $langId)
    {
        $shop_id = FatUtility::int($shopId);
        $lang_id = FatUtility::int($langId);

        if ($shop_id == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request_Id', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $userId = UserAuthentication::getLoggedUserId();

        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $shopLayoutTemplateId =  $shopDetails['shop_ltemplate_id'];
        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }
        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);

        if (!$this->isShopActive($userId, $shop_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $langData = Shop::getAttributesByLangId($lang_id, $shop_id);

        $shopLangFrm =  $this->getShopLangInfoForm($shop_id, $lang_id);
        $shopLangFrm->fill($langData);

        $this->set('shopLangFrm', $shopLangFrm);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('formLangId', $lang_id);
        $this->set('shop_id', $shop_id);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function shopThemeColor()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (false == $shopDetails) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id =  $shopDetails['shop_id'];
        $themeColorFrm = $this->getThemeColorFrm($shopDetails['shop_ltemplate_id']);
        $themeDetails = ShopTheme::getAttributesByShopId($shop_id, array('stt_bg_color','stt_header_color','stt_text_color'));
        if (!$themeDetails['stt_bg_color']  && !$themeDetails['stt_header_color'] && !$themeDetails['stt_text_color']) {
            $templateId = $shopDetails['shop_ltemplate_id'];
            $themeDetails = ShopTheme::getDefaultShopThemeColor($shopDetails['shop_ltemplate_id']);
        }
        $themeDetails['shop_custom_color_status'] = $shopDetails['shop_custom_color_status'];
        $themeColorFrm->fill($themeDetails);
        $this->set('themeColorFrm', $themeColorFrm);
        $this->set('shop_id', $shop_id);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    private function getThemeColorFrm($shopTemplateId = 0)
    {
        $onOffArr = applicationConstants::getOnOffArr($this->siteLangId);
        $frm = new Form('shopThemeColor');

        $frm->addSelectBox(Labels::getLabel('Lbl_Use_Custom_Color', $this->siteLangId), 'shop_custom_color_status', $onOffArr, applicationConstants::OFF, array(), '');

        if ($shopTemplateId == Shop::TEMPLATE_ONE || $shopTemplateId == Shop::TEMPLATE_TWO) {
            $fld = $frm->addTextBox(Labels::getLabel('LBL_Template_Theme_Background_Color', $this->siteLangId), 'stt_bg_color');
            $fld->addFieldTagAttribute('class', 'jscolor');
        }
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Template_Header_Color', $this->siteLangId), 'stt_header_color');
        $fld->addFieldTagAttribute('class', 'jscolor');


        $fld = $frm->addTextBox(Labels::getLabel('LBL_Template_Text_Link_Color', $this->siteLangId), 'stt_text_color');
        $fld->addFieldTagAttribute('class', 'jscolor');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        $frm->addButton('', 'btn_reset', Labels::getLabel('LBL_Reset_Default_Color', $this->siteLangId));
        return $frm;
    }

    public function setupThemeColor()
    {
        $userId = UserAuthentication::getLoggedUserId();

        if (!$this->isShopActive($userId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $post = FatApp::getPostedData();

        $frm =  $this->getThemeColorFrm();
        /* $post = $frm->getFormDataFromArray($post); */

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shopDetails = Shop::getAttributesByUserId($userId, null, false);
        $data_to_save_arr = array();
        $data_to_save_arr['shop_custom_color_status'] = $post['shop_custom_color_status'];
        $shop_id  = $shopDetails['shop_id'];
        $shopObj = new Shop($shop_id);
        $shopObj->assignValues($data_to_save_arr);
        if (!$shopObj->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $to_save_arr = array();
        $shopTemplateId = $shopDetails['shop_ltemplate_id'];
        /* echo $shopTemplateId; die; */
        if ($shopTemplateId == Shop::TEMPLATE_ONE || $shopTemplateId == Shop::TEMPLATE_TWO) {
            $to_save_arr['stt_bg_color'] = $post['stt_bg_color'];
        }
        $to_save_arr['stt_header_color'] = $post['stt_header_color'];
        $to_save_arr['stt_text_color'] = $post['stt_text_color'];
        $to_save_arr['stt_shop_id'] = $shop_id;
        $record = new TableRecord(Shop::DB_TBL_SHOP_THEME_COLOR);
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function resetDefaultThemeColor()
    {
        $userId = UserAuthentication::getLoggedUserId();

        if (!$this->isShopActive($userId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);
        $shop_id = $shopDetails['shop_id'];
        FatApp::getDb()->deleteRecords(Shop::DB_TBL_SHOP_THEME_COLOR, array( 'smt' => 'stt_shop_id = ?', 'vals' => array($shop_id)));


        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function shopTemplate()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (false == $shopDetails) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id =  $shopDetails['shop_id'];
        $shopLayoutTemplateId =  $shopDetails['shop_ltemplate_id'];

        $shopTemplateLayouts = LayoutTemplate::getMultipleLayouts(LayoutTemplate::LAYOUTTYPE_SHOP);

        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }

        $this->set('shop_id', $shop_id);
        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);
        $this->set('shopTemplateLayouts', $shopTemplateLayouts);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setTemplate($ltemplate_id)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $ltemplate_id = FatUtility::int($ltemplate_id);
        if (1 > $ltemplate_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data =  LayoutTemplate::getAttributesById($ltemplate_id);
        if (false == $data) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shopDetails = Shop::getAttributesByUserId($userId, null, false);
        if (false == $shopDetails) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id =     $shopDetails['shop_id'];

        $shopObj = new Shop($shop_id);
        $data = array('shop_ltemplate_id'=>$ltemplate_id);
        $shopObj->assignValues($data);

        if (!$shopObj->save()) {
            Message::addErrorMessage($shopObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupShop()
    {
        $userId = UserAuthentication::getLoggedUserId();

        if (!$this->isShopActive($userId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $state_id = FatUtility::int($post['shop_state']);

        $frm =  $this->getShopInfoForm();
        $post = $frm->getFormDataFromArray($post);

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = FatUtility::int($post['shop_id']);
        unset($post['shop_id']);

        $post['shop_user_id'] = $userId;
        $post['shop_state_id'] = $state_id;


        if ($shop_id > 0) {
            $post['shop_updated_on'] = date('Y-m-d H:i:s');
        } else {
            $post['shop_created_on'] = date('Y-m-d H:i:s');
        }


        $shopObj = new Shop($shop_id);
        $shopObj->assignValues($post);

        if (!$shopObj->save()) {
            Message::addErrorMessage($shopObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $shop_id = $shopObj->getMainTableRecordId();

        /* $userObj = new User( $userId );
        $vendorReturnAddress = $userObj->getUserReturnAddress( $this->siteLangId );

        if( !$vendorReturnAddress ){
        $dataToSave = array(
        'ura_country_id'=>$post['shop_country_id'],
        'ura_state_id'=> $state_id,
        'ura_zip'=>$post['shop_postalcode'],
        'ura_phone'=>$post['shop_phone'],
        );
        if ( !$userObj->updateUserReturnAddress($dataToSave) ) {
        Message::addErrorMessage(Labels::getLabel($userObj->getError(),$this->siteLangId));
        FatUtility::dieJsonError( Message::getHtml() );
        }
        } */

        /* url data[ */
        $shopOriginalUrl = Shop::SHOP_TOP_PRODUCTS_ORGINAL_URL.$shop_id;
        if ($post['urlrewrite_custom'] == '') {
            FatApp::getDb()->deleteRecords(UrlRewrite::DB_TBL, array( 'smt' => 'urlrewrite_original = ?', 'vals' => array($shopOriginalUrl)));
        } else {
            $shopObj->rewriteUrlShop($post['urlrewrite_custom']);
            $shopObj->rewriteUrlReviews($post['urlrewrite_custom']);
            $shopObj->rewriteUrlTopProducts($post['urlrewrite_custom']);
            $shopObj->rewriteUrlContact($post['urlrewrite_custom']);
            $shopObj->rewriteUrlpolicy($post['urlrewrite_custom']);
        }
        /* ] */


        $newTabLangId = 0;
        if ($shop_id>0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId =>$langName) {
                if (!$row = Shop::getAttributesByLangId($langId, $shop_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $shop_id = $shopObj->getMainTableRecordId();
            $newTabLangId =  $this->siteLangId;
        }

        /* if( $newTabLangId == 0 && !$this->isMediaUploaded($shop_id))
        {
        $this->set('openMediaForm', true);
        } */

        $this->set('shopId', $shop_id);
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupShopLang()
    {
        $userId = UserAuthentication::getLoggedUserId();

        if (!$this->isShopActive($userId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm =  $this->getShopLangInfoForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $shop_id = $shopDetails['shop_id'];
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);

        if ($lang_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request_id', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }


        $shopObj = new Shop($shop_id);
        $data = array(
        'shoplang_shop_id' => $shop_id,
        'shoplang_lang_id' => $lang_id,
        'shop_name' => $post['shop_name'],
        'shop_city'=>$post['shop_city'],
        'shop_contact_person'=>$post['shop_contact_person'],
        'shop_description'=>$post['shop_description'],
        'shop_payment_policy'=>$post['shop_payment_policy'],
        'shop_delivery_policy'=>$post['shop_delivery_policy'],
        'shop_refund_policy'=>$post['shop_refund_policy'],
        'shop_additional_info'=>$post['shop_additional_info'],
        'shop_seller_info'=>$post['shop_seller_info'],
        );

        if (!$shopObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($shopObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }


        /* saving address data to user's return address, if return address is blank[ */
        /* $userObj = new User( $userId );
        $srch = new SearchBase( User::DB_TBL_USR_RETURN_ADDR_LANG );
        $srch->addCondition( 'uralang_user_id', '=', $userId );
        $srch->addCondition( 'uralang_lang_id', '=', $lang_id );
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $vendorReturnAddress = FatApp::getDb()->fetch( $rs );
        if( !$vendorReturnAddress ){
        $dataToSave = array(
        'lang_id'    =>    $lang_id,
        'ura_name'    =>    $post['shop_name'],
        'ura_city'    =>    $post['shop_city'],
        'ura_address_line_1'    =>    '',
        'ura_address_line_2'    =>    ''
        );
        if ( !$userObj->updateUserReturnAddressLang( $dataToSave ) ) {
        Message::addErrorMessage( Labels::getLabel($userObj->getError(),$this->siteLangId) );
        FatUtility::dieJsonError( Message::getHtml() );
        }
        } */
        /* ] */


        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId =>$langName) {
            if (!$row = Shop::getAttributesByLangId($langId, $shop_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        /* if( $newTabLangId == 0 && !$this->isMediaUploaded($shop_id))
        {
        $this->set('openMediaForm', true);
        } */

        $this->set('shopId', $shop_id);
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function uploadShopImages()
    {
        $userId = UserAuthentication::getLoggedUserId();

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        if (1 > $shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $slide_screen = FatApp::getPostedData('slide_screen', FatUtility::VAR_INT, 0);
        if (!$file_type) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $allowedFileTypeArr = array(AttachedFile::FILETYPE_SHOP_LOGO,AttachedFile::FILETYPE_SHOP_BANNER,AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE);

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $unique_record = true;
        /* if ($file_type != AttachedFile::FILETYPE_SHOP_BANNER) {
            $unique_record = true;
        } */

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            $file_type,
            $shop_id,
            0,
            $_FILES['file']['name'],
            -1,
            $unique_record,
            $lang_id,
            $slide_screen
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('file', $_FILES['file']['name']);
        $this->set('shopId', $shop_id);
        /* Message::addMessage(  Labels::getLabel('MSG_File_uploaded_successfully' ,$this->siteLangId) );
        FatUtility::dieJsonSuccess(Message::getHtml()); */
        $this->set('msg', Labels::getLabel('MSG_File_uploaded_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
        /* $this->set('msg', Message::getHtml() );
        $this->_template->render(false, false, 'json-success.php'); */
    }

    public function removeShopImage($banner_id, $langId, $imageType, $slide_screen = 0)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $langId = FatUtility::int($langId);

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        if (!$shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($imageType=='logo') {
            $fileType = AttachedFile::FILETYPE_SHOP_LOGO;
        } elseif ($imageType=='banner') {
            $fileType = AttachedFile::FILETYPE_SHOP_BANNER;
        } else {
            $fileType = AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE;
        }


        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile($fileType, $shop_id, $banner_id, 0, $langId, $slide_screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_File_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function CategoryBanners(){
    $this->_template->render(true,false);
    }  */

    public function addCategoryBanner($prodCatId)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $prodCatId = FatUtility::int($prodCatId);

        if (1 > $prodCatId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        if (1 > $shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = $this->getSellerProdCategoriesObj($userId, $shop_id, $prodCatId, $this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();

        $arr_listing = $db->fetchAll($rs, 'prodcat_id');

        if (empty($arr_listing) || (!empty($arr_listing) && !array_key_exists($prodCatId, $arr_listing))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $attachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CATEGORY_BANNER_SELLER, $shop_id, $prodCatId, -1);
        $mediaFrm =  $this->getCategoryMediaForm($prodCatId);

        $this->set('mediaFrm', $mediaFrm);
        /* $this->set('mode', $mode);         */
        $this->set('userId', $userId);
        $this->set('shop_id', $shop_id);
        $this->set('prodCatId', $prodCatId);
        $this->set('attachments', $attachments);
        $this->set('bannerTypeArr', applicationConstants::bannerTypeArr());
        $this->_template->render(false, false);
    }

    /* public function categoryBannerLangForm( $prodCatId, $langId ){
    $userId = UserAuthentication::getLoggedUserId();
    $prodCatId = FatUtility::int($prodCatId);
    $langId = FatUtility::int($langId);

    if( !$prodCatId || !$langId ){
    Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access',$this->siteLangId));
    FatUtility::dieWithError( Message::getHtml() );
    }

    if( !$shopDetails = $this->isShopActive($userId,0,true) ){
    Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin',$this->siteLangId));
    FatUtility::dieJsonError( Message::getHtml() );
    }

    $shop_id = $shopDetails['shop_id'];
    if( !$shop_id ){
    Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID',$this->siteLangId));
    FatUtility::dieJsonError( Message::getHtml() );
    }

    $srch = $this->getSellerProdCategoriesObj( $userId, $shop_id, $prodCatId, $this->siteLangId );
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $db = FatApp::getDb();
    $rs = $srch->getResultSet();
    $catData = $db->fetchAll( $rs, 'prodcat_id' );

    if( empty( $catData ) || ( !empty( $catData ) && !array_key_exists( $prodCatId, $catData )) ){
    Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access',$this->siteLangId));
    FatUtility::dieWithError( Message::getHtml() );
    }

    $mediaFrm =  $this->getCategoryMediaLangForm( $prodCatId, $langId );

    $this->set('mediaFrm', $mediaFrm);
    $this->set('catData', array_shift($catData) );
    $this->set( 'shop_id', $shop_id );
    $this->set( 'prodCatId', $prodCatId );
    $this->set( 'languages', Language::getAllNames() );
    $this->set( 'formLangId', $langId );
    $this->_template->render( false, false );
    } */

    public function setUpCategoryBanner()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();

        $prodCatId = FatApp::getPostedData('prodcat_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        if (!$prodCatId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        if (!$shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = $this->getSellerProdCategoriesObj($userId, $shop_id, $prodCatId, $this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs, 'prodcat_id');

        if (empty($arr_listing) || (!empty($arr_listing) && !array_key_exists($prodCatId, $arr_listing))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            AttachedFile::FILETYPE_CATEGORY_BANNER_SELLER,
            $shop_id,
            $prodCatId,
            $_FILES['file']['name'],
            -1,
            $unique_record = true,
            $lang_id
        )
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('file', $_FILES['file']['name']);
        $this->set('prodCatId', $prodCatId);
        $this->set('shop_id', $shop_id);

        Message::addMessage(Labels::getLabel('MSG_File_uploaded_successfully', $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
        /* $this->set('msg', Labels::getLabel('MSG_File_uploaded_successfully',$this->siteLangId));
        $this->_template->render(false, false, 'json-success.php'); */
    }

    public function removeCategoryBanner($prodCatId, $langId)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $prodCatId = FatUtility::int($prodCatId);
        $langId = FatUtility::int($langId);

        if (!$prodCatId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        if (!$shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_CATEGORY_BANNER_SELLER, $shop_id, 0, $prodCatId, $langId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_File_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function searchCategoryBanners()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = $this->getSellerProdCategoriesObj($userId, 0, 0, $this->siteLangId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs, 'prodcat_id');

        $this->set('arr_listing', $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        $shopDetails = Shop::getAttributesByUserId($userId, null, false);
        $shopLayoutTemplateId =  $shopDetails['shop_ltemplate_id'];
        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }
        $shop_id = 0;
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);
        $this->set('shop_id', $shop_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function orderCancellationRequests()
    {
        $frm = $this->getOrderCancellationRequestsSearchForm($this->siteLangId);
        $this->set('frmOrderCancellationRequestsSrch', $frm);
        $this->_template->render(true, true);
    }

    public function orderCancellationRequestSearch()
    {
        $frm = $this->getOrderCancellationRequestsSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $srch = $this->cancelRequestListingObj();
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $op_invoice_number = $post['op_invoice_number'];
        if (!empty($op_invoice_number)) {
            $srch->addCondition('op_invoice_number', '=', $op_invoice_number);
        }

        $ocrequest_date_from = $post['ocrequest_date_from'];
        if (!empty($ocrequest_date_from)) {
            $srch->addCondition('ocrequest_date', '>=', $ocrequest_date_from. ' 00:00:00');
        }

        $ocrequest_date_to = $post['ocrequest_date_to'];
        if (!empty($ocrequest_date_to)) {
            $srch->addCondition('ocrequest_date', '<=', $ocrequest_date_to. ' 23:59:59');
        }

        //$ocrequest_status = $post['ocrequest_status'];
        $ocrequest_status = FatApp::getPostedData('ocrequest_status', null, -1);
        if ($ocrequest_status > -1) {
            $ocrequest_status = FatUtility::int($ocrequest_status);
            $srch->addCondition('ocrequest_status', '=', $ocrequest_status);
        }

        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        $this->set('requests', $requests);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('OrderCancelRequestStatusArr', OrderCancelRequest::getRequestStatusArr($this->siteLangId));
        $this->_template->render(false, false, 'buyer/order-cancellation-request-search.php');
    }

    private function cancelRequestListingObj()
    {
        $srch = new OrderCancelRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrderCancelReasons();
        $srch->joinOrders();
        $srch->addCondition('op_selprod_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addMultipleFields(array( 'ocrequest_id', 'ocrequest_date', 'ocrequest_status', 'order_id', 'op_invoice_number', 'op_id', 'IFNULL(ocreason_title, ocreason_identifier) as ocreason_title', 'ocrequest_message', 'op_selprod_title', 'op_product_name','op_selprod_id', 'op_is_batch'));
        $srch->addOrder('ocrequest_date', 'DESC');
        return $srch;
    }

    public function orderReturnRequests()
    {
        $frm = $this->getOrderReturnRequestsSearchForm($this->siteLangId);
        $this->set('frmOrderReturnRequestsSrch', $frm);
        $this->_template->render(true, true);
    }
    
    public function shippingSettings()
    {
        $frm = $this->getShippingSettingsForm($this->siteLangId);
        $this->set('frmShippingSettings', $frm);

        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new SearchBase('tbl_shipping_settings', 'ss');
        $srch->addCondition('ship_set_user_id', '=', $user_id);
        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);
        
        $this->set('recordCount', count($requests));
        $this->_template->render(true, true);
    }

    public function orderReturnRequestSearch()
    {
        $frm = $this->getOrderReturnRequestsSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $user_id = UserAuthentication::getLoggedUserId();
        $keyword = $post['keyword'];
        $orrequest_date_from = $post['orrequest_date_from'];
        $orrequest_date_to = $post['orrequest_date_to'];

        $page = (empty($page) || $page <= 0) ? 1 : FatUtility::int($page);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $srch = $this->returnReuestsListingObj();

        $orrequest_status = FatApp::getPostedData('orrequest_status', null, '-1');
        if ($orrequest_status > -1) {
            $orrequest_status = FatUtility::int($orrequest_status);
            $srch->addCondition('orrequest_status', '=', $orrequest_status);
        }

        $orrequest_type = FatApp::getPostedData('orrequest_type', null, '-1');
        if ($orrequest_type > -1) {
            $orrequest_type = FatUtility::int($orrequest_type);
            $srch->addCondition('orrequest_type', '=', $orrequest_type);
        }

        if (!empty($orrequest_date_from)) {
            $srch->addCondition('orrequest_date', '>=', $orrequest_date_from. ' 00:00:00');
        }

        if (!empty($orrequest_date_to)) {
            $srch->addCondition('orrequest_date', '<=', $orrequest_date_to. ' 23:59:59');
        }

        if (!empty($keyword)) {
            $cnd = $srch->addCondition('op_invoice_number', '=', $keyword);
            $cnd->attachCondition('op_order_id', '=', $keyword);
            $cnd->attachCondition('op_selprod_title', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_product_name', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_brand_name', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_selprod_options', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_selprod_sku', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_product_model', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('orrequest_reference', 'LIKE', '%'.$keyword.'%', 'OR');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        //echo $srch->getQuery(); die();
        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        $this->set('sellerPage', true);
        $this->set('buyerPage', false);

        $this->set('requests', $requests);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('returnRequestTypeArr', OrderReturnRequest::getRequestTypeArr($this->siteLangId));
        $this->set('OrderReturnRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->_template->render(false, false, 'buyer/order-return-request-search.php');
    }

    public function shippingSettingsListing()
    {
        $frm = $this->getShippingSettingsForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $user_id = UserAuthentication::getLoggedUserId();
        $city_list = $post['city_list'];
        $shipping_company = $post['shipping_company'];
        $businessdays = $post['businessdays'];

        $page = (empty($page) || $page <= 0) ? 1 : FatUtility::int($page);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $srch = new SearchBase('tbl_shipping_settings', 'ss');
        if ($this->siteLangId > 0) {
            $srch->joinTable('tbl_cities','LEFT OUTER JOIN','ct.city_id = ss.ship_set_city','ct');
            $srch->joinTable('tbl_shipping_company','LEFT OUTER JOIN','sc.scompany_id = ss.ship_set_company','sc');
            $srch->joinTable('tbl_shipping_durations','LEFT OUTER JOIN','sd.sduration_id = ss.ship_set_duration ','sd');
        }
        $srch->addMultipleFields(array( 'ship_set_id ', 'scompany_identifier', 'city_identifier','sduration_from','sduration_to','cost_for_1st_kg','each_additional_kg'));
        $srch->addOrder('ship_set_id', 'ASC');

        $srch->addCondition('ship_set_user_id', '=', $user_id);
       
        if ($city_list != -1) {
            $srch->addCondition('ship_set_city', '=', $city_list);
        }

        if ($shipping_company != -1) {
            $srch->addCondition('ship_set_company', '=', $shipping_company);
        }

        if ($businessdays != -1) {
            $srch->addCondition('ship_set_duration', '=', $businessdays);
        }
        

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        //echo $srch->getQuery(); die();
        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);
        // echo '<pre>';
        // print_r($requests);
        // exit();
        $this->set('sellerPage', true);
        $this->set('buyerPage', false);

        $this->set('requests', $requests);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'seller/shipping-settings-listing.php');
    }

    public function importShippingSettings(){
        $this->_template->render(false, false, 'seller/shipping-settings-import.php');
    }

    public function importProductShippingRate($productId){
        $this->set('productId', $productId);
        $this->_template->render(false, false, 'seller/product-shipping-rate-import.php');
    }

    public function importShippingSettingsData($actionType){
        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $obj = new Importexport();
        if (!$obj->isUploadedFileValidMimes($_FILES['import_file'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $sheetType = FatApp::getPostedData('sheet_type', FatUtility::VAR_INT, 0);
        $userId = UserAuthentication::getLoggedUserId();
        $obj->import($actionType, $langId, $sheetType, $userId);
    }

    public function importProductShippingRateData($actionType){
        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $obj = new Importexport();
        if (!$obj->isUploadedFileValidMimes($_FILES['import_file'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $sheetType = FatApp::getPostedData('sheet_type', FatUtility::VAR_INT, 0);
        $userId = UserAuthentication::getLoggedUserId();
        $obj->import($actionType, $langId, $sheetType, $userId);
    }


    public function deleteShippingSetting(){
        $id = $_POST['id'];
        $user_id = UserAuthentication::getLoggedUserId();
        $db = FatApp::getDb();
        $smt = 'ship_set_id = ? AND ship_set_user_id = ? ';
        $smtValues = array($id, $user_id);
        if (!$db->deleteRecords('tbl_shipping_settings', array( 'smt' => $smt, 'vals' => $smtValues))) {
            $this->error = $db->getError();
            echo json_encode(array('status'=>0,'msg'=>$this->error));
            exit();
        }
        echo json_encode(array('status'=>1,'msg'=>"Record Deleted Successfully."));
    }


    public function shippingSettingExport(){
        //echo UserAuthentication::getLoggedUserId();exit();
        $srch = new SearchBase('tbl_shipping_settings');
        $srch->addCondition('ship_set_user_id', '=', UserAuthentication::getLoggedUserId(true));
        $rs = $srch->getResultSet();
        $result = FatApp::getDb()->fetchAll($rs);

        $filename = "shipping-settings-export.csv";
        $fp = fopen('php://output', 'w');
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);
        $header = ['City Name','Shipping Company','Duration From','Duration To','Cost for 1st Kg','Each additional Kg'];
        fputcsv($fp, $header);

       if($result){
           foreach($result as $res){
                $cities = Cities::getAttributesById($res['ship_set_city'], array('city_identifier'));
                $shComp = ShippingCompanies::getAttributesById($res['ship_set_company'], array('scompany_identifier'));
                $surationDt = ShippingDurations::getAttributesById($res['ship_set_duration'], array('sduration_from','sduration_to'));

                $row = [$cities['city_identifier'],$shComp['scompany_identifier'],$surationDt['sduration_from'],$surationDt['sduration_to'],$res['cost_for_1st_kg'],$res['each_additional_kg']];
                fputcsv($fp, $row);
            }
       }else{
           $row1 = ['Colombo 01','DHL',1,5,10,0];
           $row2 = ['Dehiwala','DHL',1,7,8,0];
           $row3 = ['Kolonnawa','Fedex',2,5,6,0];
           $row4 = ['Maharagama','UPS',2,8,7,0];
           $row5 = ['Mount Lavinia','Fedex',3,6,10,0];
           $row6 = ['Mulleriyawa New Town','DHL',7,12,3,0];
           $row7 = ['Mutwal','Fedex',6,7,9,0];
           $row8 = ['Nugegoda','DHL',2,9,11,0];
           $row9 = ['Sri Jayawardenepura','UPS',3,4,7,0];
           $row10 = ['Kolonnawa','Fedex',10,12,2,0];
            fputcsv($fp, $row1);
            fputcsv($fp, $row2);
            fputcsv($fp, $row3);
            fputcsv($fp, $row4);
            fputcsv($fp, $row5);
            fputcsv($fp, $row6);
            fputcsv($fp, $row7);
            fputcsv($fp, $row8);
            fputcsv($fp, $row9);
            fputcsv($fp, $row10);
       }
        
    }

    public function productShippingRateExport($productId){
        //echo UserAuthentication::getLoggedUserId();exit();
        $srch = new SearchBase('tbl_product_shipping_rates');
        $srch->addCondition('pship_prod_id', '=', $productId);
        $srch->addCondition('pship_user_id', '=', UserAuthentication::getLoggedUserId(true));
        $rs = $srch->getResultSet();
        $result = FatApp::getDb()->fetchAll($rs);

        // echo '<pre>';
        // print_r($result);
        // exit(); 
        $filename = "product-shipping-rate-export.csv";
        $fp = fopen('php://output', 'w');
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);
        $header = ['City Name','Shipping Company','Duration From','Duration To','Shipping Charge','Additional Shipping Charge'];
        fputcsv($fp, $header);

       if($result){
           foreach($result as $res){
                $cities = Cities::getAttributesById($res['pship_city'], array('city_identifier'));
                $shComp = ShippingCompanies::getAttributesById($res['pship_company'], array('scompany_identifier'));
                $surationDt = ShippingDurations::getAttributesById($res['pship_duration'], array('sduration_from','sduration_to'));

                $row = [$cities['city_identifier'],$shComp['scompany_identifier'],$surationDt['sduration_from'],$surationDt['sduration_to'],$res['pship_charges'],$res['pship_additional_charges']];
                fputcsv($fp, $row);
            }
       }else{
           $row1 = ['Colombo 01','DHL',1,5,10,0];
           $row2 = ['Dehiwala','DHL',1,7,8,0];
           $row3 = ['Kolonnawa','Fedex',2,5,6,0];
           $row4 = ['Maharagama','UPS',2,8,7,0];
           $row5 = ['Mount Lavinia','Fedex',3,6,10,0];
           $row6 = ['Mulleriyawa New Town','DHL',7,12,3,0];
           $row7 = ['Mutwal','Fedex',6,7,9,0];
           $row8 = ['Nugegoda','DHL',2,9,11,0];
           $row9 = ['Sri Jayawardenepura','UPS',3,4,7,0];
           $row10 = ['Kolonnawa','Fedex',10,12,2,0];
            fputcsv($fp, $row1);
            fputcsv($fp, $row2);
            fputcsv($fp, $row3);
            fputcsv($fp, $row4);
            fputcsv($fp, $row5);
            fputcsv($fp, $row6);
            fputcsv($fp, $row7);
            fputcsv($fp, $row8);
            fputcsv($fp, $row9);
            fputcsv($fp, $row10);
       }
        
    }

    private function returnReuestsListingObj()
    {
        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->addCondition('op_selprod_user_id', '=', UserAuthentication::getLoggedUserId());

        $srch->addMultipleFields(
            array( 'orrequest_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type', 'orrequest_reference', 'orrequest_date', 'orrequest_status',
            'op_invoice_number', 'op_selprod_title', 'op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_selprod_id', 'op_is_batch', 'op_id')
        );
        $srch->addOrder('orrequest_date', 'DESC');

        return $srch;
    }

    public function downloadAttachedFileForReturn($recordId, $recordSubid=0)
    {
        $recordId = FatUtility::int($recordId);

        if (1 > $recordId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'ViewOrderReturnRequest', array($recordId)));
        }

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $recordId, $recordSubid);

        if (false == $file_row) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'ViewOrderReturnRequest', array($recordId)));
        }
        if (!file_exists(CONF_UPLOADS_PATH.$file_row['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'ViewOrderReturnRequest', array($recordId)));
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

    public function viewOrderReturnRequest($orrequest_id)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrderProductSettings();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderReturnReasons();
        $srch->addOrderProductCharges();

        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('op_selprod_user_id', '=', $user_id);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array( 'orrequest_id','orrequest_op_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type',
            'orrequest_date', 'orrequest_status','orrequest_reference',  'op_invoice_number', 'op_selprod_title', 'op_product_name',
            'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_qty',
            'op_unit_price', 'op_selprod_user_id', 'IFNULL(orreason_title, orreason_identifier) as orreason_title','op_shop_id', 'op_shop_name', 'op_shop_owner_name', 'buyer.user_name as buyer_name', 'order_tax_charged','op_other_charges','op_refund_shipping','op_refund_amount','op_commission_percentage','op_affiliate_commission_percentage','op_commission_include_tax','op_commission_include_shipping','op_free_ship_upto','op_actual_shipping_charges')
        );

        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);

        if (!$request) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'orderReturnRequests'));
        }

        $oObj = new Orders();
        $charges = $oObj->getOrderProductChargesArr($request['orrequest_op_id']);
        $request['charges'] = $charges;

        $sellerUserObj = new User($request['op_selprod_user_id']);
        $vendorReturnAddress = $sellerUserObj->getUserReturnAddress($this->siteLangId);

        $returnRequestMsgsForm = $this->getOrderReturnRequestMessageSearchForm($this->siteLangId);
        $returnRequestMsgsForm->fill(array( 'orrequest_id' => $request['orrequest_id'] ));

        $frm = $this->getOrderReturnRequestMessageForm($this->siteLangId);
        $frm->fill(array( 'orrmsg_orrequest_id' => $request['orrequest_id'] ));

        $canEscalateRequest = false;
        $canApproveReturnRequest = false;
        if ($request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING) {
            $canEscalateRequest = true;
        }

        if (($request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING) || $request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED) {
            $canApproveReturnRequest = true;
        }

        if ($attachedFile = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $orrequest_id)) {
            if (file_exists(CONF_UPLOADS_PATH.$attachedFile['afile_physical_path'])) {
                $this->set('attachedFile', $attachedFile);
            }
        }

        $this->set('frmMsg', $frm);
        $this->set('canEscalateRequest', $canEscalateRequest);
        $this->set('canApproveReturnRequest', $canApproveReturnRequest);
        $this->set('returnRequestMsgsForm', $returnRequestMsgsForm);
        $this->set('request', $request);
        $this->set('vendorReturnAddress', $vendorReturnAddress);
        $this->set('returnRequestTypeArr', OrderReturnRequest::getRequestTypeArr($this->siteLangId));
        $this->set('requestRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->set('logged_user_name', UserAuthentication::getLoggedUserAttribute('user_name'));
        $this->set('logged_user_id', UserAuthentication::getLoggedUserId());
        $this->_template->render(true, true);
    }

    public function approveOrderReturnRequest($orrequest_id)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderReturnReasons();

        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('op_selprod_user_id', '=', $user_id);

        $cnd = $srch->addCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING);
        $cnd->attachCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id'));

        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'viewOrderReturnRequest', array($requestRow['orrequest_id'])));
        }

        $orrObj = new OrderReturnRequest();
        if (!$orrObj->approveRequest($requestRow['orrequest_id'], $user_id, $this->siteLangId)) {
            Message::addErrorMessage(Labels::getLabel($orrObj->getError(), $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'viewOrderReturnRequest', array($requestRow['orrequest_id'])));
        }

        /* email notification handling[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification($requestRow['orrequest_id'], $this->siteLangId)) {
            Message::addErrorMessage(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'viewOrderReturnRequest', array($requestRow['orrequest_id'])));
        }
        /* ] */

        Message::addMessage(Labels::getLabel('MSG_Request_Approved_Refund', $this->siteLangId));
        FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'viewOrderReturnRequest', array($requestRow['orrequest_id'])));
    }

    public function setUpReturnOrderRequestMessage()
    {
        $orrmsg_orrequest_id = FatApp::getPostedData('orrmsg_orrequest_id', null, '0');

        $frm = $this->getOrderReturnRequestMessageForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $orrmsg_orrequest_id = FatUtility::int($orrmsg_orrequest_id);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->addCondition('orrequest_id', '=', $orrmsg_orrequest_id);
        $srch->addCondition('op_selprod_user_id', '=', $user_id);
        $srch->joinOrderProducts();
        $srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'orrequest_status', ));
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED || $requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_WITHDRAWN) {
            Message::addErrorMessage(Labels::getLabel('MSG_Message_cannot_be_posted_now,_as_order_is_refunded_or_withdrawn.', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* save return request message[ */
        $returnRequestMsgDataToSave = array(
        'orrmsg_orrequest_id'    =>    $requestRow['orrequest_id'],
        'orrmsg_from_user_id'    =>    $user_id,
        'orrmsg_msg'            =>    $post['orrmsg_msg'],
        'orrmsg_date'            =>    date('Y-m-d H:i:s'),
        );
        $oReturnRequestMsgObj = new OrderReturnRequestMessage();
        $oReturnRequestMsgObj->assignValues($returnRequestMsgDataToSave);
        if (!$oReturnRequestMsgObj->save()) {
            Message::addErrorMessage($oReturnRequestMsgObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $orrmsg_id = $oReturnRequestMsgObj->getMainTableRecordId();
        if (!$orrmsg_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendReturnRequestMessageNotification($orrmsg_id, $this->siteLangId)) {
            Message::addErrorMessage($emailNotificationObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $this->set('orrmsg_orrequest_id', $orrmsg_orrequest_id);
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function socialPlatforms()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(true, true);
    }

    public function socialPlatformSearch()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $srch = SocialPlatform::getSearchObject($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('splatform_user_id', '=', $userId);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("arr_listing", $records);
        $this->_template->render(false, false, 'seller/social-platform-search.php');
    }

    public function socialPlatformForm($splatform_id = 0)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $splatform_id = FatUtility::int($splatform_id);
        $frm = $this->getSocialPlatformForm();

        if (0 < $splatform_id) {
            $data = SocialPlatform::getAttributesById($splatform_id);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('splatform_id', $splatform_id);
        $this->set('frm', $frm);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function socialPlatformSetup()
    {
        $frm = $this->getSocialPlatformForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $splatform_id = $post['splatform_id'];
        unset($post['splatform_id']);
        $data_to_be_save = $post;
        $data_to_be_save['splatform_user_id'] = UserAuthentication::getLoggedUserId();

        $recordObj = new SocialPlatform($splatform_id);
        $recordObj->assignValues($data_to_be_save, true);
        if (!$recordObj->save()) {
            Message::addErrorMessage($recordObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $splatform_id = $recordObj->getMainTableRecordId();

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = SocialPlatform::getAttributesByLangId($langId, $splatform_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->siteLangId));
        $this->set('splatformId', $splatform_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function socialPlatformLangForm($splatform_id = 0, $lang_id = 0)
    {
        $splatform_id = FatUtility::int($splatform_id);
        $lang_id = FatUtility::int($lang_id);

        if ($splatform_id==0 || $lang_id==0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getSocialPlatformLangForm($splatform_id, $lang_id);
        $langData = SocialPlatform::getAttributesByLangId($lang_id, $splatform_id);

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('splatform_id', $splatform_id);
        $this->set('splatform_lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function socialPlatformLangSetup()
    {
        $post = FatApp::getPostedData();
        $splatform_id = FatUtility::int($post['splatform_id']);
        $lang_id = $post['lang_id'];

        if ($splatform_id == 0 || $lang_id == 0) {
            Message::addErrorMessage('Invalid Request');
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getSocialPlatformLangForm($splatform_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['splatform_id']);
        unset($post['lang_id']);
        $data_to_update = array(
        'splatformlang_splatform_id'    =>    $splatform_id,
        'splatformlang_lang_id'        =>    $lang_id,
        'splatform_title'                =>    $post['splatform_title'],
        );

        $socialObj = new SocialPlatform($splatform_id);
        if (!$socialObj->updateLangData($lang_id, $data_to_update)) {
            Message::addErrorMessage($socialObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = SocialPlatform::getAttributesByLangId($langId, $splatform_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->siteLangId));
        $this->set('splatformId', $splatform_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSocialPlatform()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $splatformId = FatApp::getPostedData('splatformId', FatUtility::VAR_INT, 0);
        if ($splatformId < 1) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = SocialPlatform::getSearchObject($this->siteLangId);
        $srch->addCondition('splatform_user_id', '=', $userId);
        $srch->addCondition('splatform_id', '=', $splatformId);
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new SocialPlatform($splatformId);
        if (!$obj->deleteRecord(true)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess(Labels::getLabel("MSG_Social_Platform_deleted!", $this->siteLangId));
    }

    public function sellerProductsAutoComplete()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE');
        $db = FatApp::getDb();
        $json = array();
        $post = FatApp::getPostedData();

        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.$this->siteLangId, 'p_l');
        $srch->addCondition('selprod_user_id', '=', $userId);
        $srch->addCondition('sp.selprod_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_approved', '=', Product::APPROVED);
        $srch->addOrder('product_name');
        $srch->addOrder('selprod_title');
        $srch->addOrder('selprod_id');
        $srch->addMultipleFields(array( 'selprod_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_price'));
        //$srch->setPageSize( $pageSize );
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
            //$cnd->attachCondition('option_identifier', 'LIKE', '%'. $post['keyword'] . '%', 'OR');
        }

        $rs = $srch->getResultSet();
        $products = $db->fetchAll($rs, 'selprod_id');

        if ($products) {
            foreach ($products as $selprod_id => $product) {
                $options = SellerProduct::getSellerProductOptions($product['selprod_id'], true, $this->siteLangId);

                $variantStr = $product['product_name'];
                //$variantStr .= ( $product['selprod_title'] != '') ? $product['selprod_title'] : $product['product_name'];

                if (is_array($options) && count($options)) {
                    $variantStr .= ' (';
                    $counter = 1;
                    foreach ($options as $op) {
                        $variantStr .= $op['option_name'].': '.$op['optionvalue_name'];
                        if ($counter != count($options)) {
                            $variantStr .= ', ';
                        }
                        $counter++;
                    }
                    $variantStr .= ' )';
                }
                $json[] = array(
                'id'    =>    $selprod_id,
                'value'    =>    strip_tags(html_entity_decode($variantStr, ENT_QUOTES, 'UTF-8')),
                );
            }
        }

        echo json_encode(array( 'suggestions' => $json ));
        exit;
        //die(json_encode($json));
    }

    public function InventoryUpdate()
    {
        if (!$this->isShopActive(UserAuthentication::getLoggedUserId(), 0, true)) {
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'shop'));
        }
        $extraPage = new Extrapage();
        $pageData = $extraPage->getContentByPageType(Extrapage::PRODUCT_INVENTORY_UPDATE_INSTRUCTIONS, $this->siteLangId);

        $this->set('pageData', $pageData);
        $this->_template->render(true, true);
    }

    public function InventoryUpdateForm()
    {
        $frm = $this->getInventoryUpdateForm($this->siteLangId);

        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function updateInventory()
    {
        $frm = $this->getInventoryUpdateForm($this->siteLangId);
        $post = FatApp::getPostedData();
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        if (!isset($_FILES['file'])) {
            /* Message::addErrorMessage(Labels::getLabel('MSG_Invalid_File_Upload',$this->siteLangId)); */
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_File_Upload', $this->siteLangId));
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            /* Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file',$this->siteLangId)); */
            FatUtility::dieJsonError(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
        }

        $uploadedFile = $_FILES['file']['tmp_name'];
        $fileHandle = fopen($uploadedFile, 'r');
        if ($fileHandle == false) {
            /* Message::addErrorMessage(Labels::getLabel('MSG_Invalid_File_Upload',$this->siteLangId)); */
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_File_Upload', $this->siteLangId));
        }

        /* validate file extension[ */
        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','application/octet-stream');
        if (!in_array($_FILES['file']['type'], $mimes)) {
            /* Message::addErrorMessage(Labels::getLabel('MSG_Invalid_File_Upload',$this->siteLangId)); */
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_File_Upload', $this->siteLangId));
        }
        /* ] */

        $firstLine = fgetcsv($fileHandle);
        $defaultColArr = $this->getInventorySheetColoum($this->siteLangId);
        if ($firstLine != $defaultColArr) {
            /* Message::addErrorMessage(Labels::getLabel('LBL_Sheet_seems_to_be_empty', $this->siteLangId )); */
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Coloum_CSV_File', $this->siteLangId));
        }
        $processFile = false;
        $db = FatApp::getDb();

        while (($dataArray = fgetcsv($fileHandle)) !== false) {
            //
            $selprod_id = FatUtility::int($dataArray[0]);
            $selprod_sku = $dataArray[1];
            $selprod_cost_price = FatUtility::float($dataArray[3]);
            $selprod_price = FatUtility::float($dataArray[4]);
            $selprod_stock = FatUtility::int($dataArray[5]);

            $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id', false);
            $prodData = Product::getAttributesById($productId, array('product_min_selling_price'));
            if ($selprod_price < $prodData['product_min_selling_price']) {
                $selprod_price = $prodData['product_min_selling_price'];
            }

            $assignValues = array();
            if ($selprod_price != '') {
                $assignValues['selprod_price'] = $selprod_price;
            }
            if ($selprod_stock < 0 || $selprod_price < 0 || $selprod_cost_price <= 0) {
                continue;
            }
            $assignValues['selprod_cost'] = $selprod_cost_price;
            $assignValues['selprod_stock'] = $selprod_stock;
            if ($selprod_id > 0) {
                $whereSmt = array( 'smt'=>'selprod_user_id = ? and selprod_id = ?', 'vals'=>array( $loggedUserId, $selprod_id ) );
                $db->updateFromArray(SellerProduct::DB_TBL, $assignValues, $whereSmt);
            }
            $processFile = true;
        }

        if (!$processFile) {
            /* Message::addErrorMessage(Labels::getLabel('MSG_Uploaded_file_seems_to_be_empty,_please_upload_a_valid_file_or_records_skipped',$this->siteLangId)); */
            FatUtility::dieJsonError(Labels::getLabel('MSG_Uploaded_file_seems_to_be_empty,_please_upload_a_valid_file_or_records_skipped', $this->siteLangId));
        }

        Product::updateMinPrices();
        /* Message::addMessage(  Labels::getLabel('MSG_Inventory_has_been_updated_successfully',$this->siteLangId) ); */
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Inventory_has_been_updated_successfully', $this->siteLangId));
    }

    public function exportInventory()
    {
        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.$this->siteLangId, 'p_l');
        $srch->addCondition('selprod_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('selprod_active', '=', applicationConstants::ACTIVE);
        $srch->addOrder('product_name');
        $srch->addOrder('selprod_active', 'DESC');
        $srch->addMultipleFields(array('selprod_id','selprod_sku','selprod_price', 'selprod_cost', 'selprod_stock','IFNULL(product_name, product_identifier) as product_name', 'selprod_title'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $inventoryData  = FatApp::getDb()->fetchAll($rs, 'selprod_id');

        /* if( count($data) ){
        //$data['options'] = SellerProduct::getSellerProductOptions(0,true,$this->siteLangId);
        foreach( $data as & $arr ){
        $options = SellerProduct::getSellerProductOptions( $arr['selprod_id'], true, $this->siteLangId );
        }
        }  */

        $sheetData = array();
        /* $arr = array('selprod_id','selprod_sku','selprod_title', 'selprod_price','selprod_stock'); */
        $arr = $this->getInventorySheetColoum($this->siteLangId);
        array_push($sheetData, $arr);

        foreach ($inventoryData as $key => $val) {
            $title = $val['product_name'];
            if ($val['selprod_title'] != "") {
                $title .= "-[" . $val['selprod_title'] . "]";
            }
            $arr = array($val['selprod_id'],$val['selprod_sku'], $title, $val['selprod_cost'], $val['selprod_price'],$val['selprod_stock']);
            array_push($sheetData, $arr);
        }

        CommonHelper::convertToCsv($sheetData, str_replace(' ', '_', Labels::getLabel('LBL_Inventory_Report', $this->siteLangId)).'_'.date("Y-m-d").'.csv', ',');
        exit;
    }

    private function getInventorySheetColoum($langId)
    {
        $arr = array(
        Labels::getLabel("LBL_Seller_Product_Id", $langId),
        Labels::getLabel("LBL_SKU", $langId),
        Labels::getLabel("LBL_Product", $langId),
        Labels::getLabel('LBL_Cost_Price', $langId),
        Labels::getLabel("LBL_Price", $langId),
        Labels::getLabel("LBL_Stock/Quantity", $langId)
        );
        return $arr;
    }

    /* private function isMediaUploaded($shopId){
    if($attachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_BANNER , $shopId, 0 )){
    return true;
    }
    return false;
    } */

    private function getCatalogRequestMessageSearchForm()
    {
        $frm = new Form('frmCatalogRequestMsgsSrch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'requestId');
        return $frm;
    }

    private function getCatalogRequestMessageForm($requestId)
    {
        $frm = new Form('catalogRequestMsgForm');

        $frm->addHiddenField('', 'requestId', $requestId);
        $frm->addTextArea(Labels::getLabel('LBL_Message', $this->siteLangId), 'message');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send', $this->siteLangId));
        return $frm;
    }

    private function getchangeTaxRatesForm($langId)
    {
        $frm = new Form('frmChangeTaxRates');
        $frm->addHiddenField('', 'taxcat_id');
        $typeArr = applicationConstants::getYesNoArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Tax_in_percent', $langId), 'taxval_is_percent', $typeArr, '', array(), '');
        $fld = $frm->addFloatField(Labels::getLabel('LBL_Value', $langId), 'taxval_value');
        $fld->requirements()->setFloatPositive(true);
        $fld->requirements()->setRange('0', '100');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    private function getTaxCatSearchForm($langId)
    {
        $frm = new Form('frmSearchTaxCat');
        $frm->addTextBox('', 'keyword');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick'=>'clearSearch();'));
        return $frm;
    }

    private function getInventoryUpdateForm($langId = 0)
    {
        $frm = new Form('frmInventoryUpdate');
        $frm->addHiddenField('', 'lang_id', $langId);

        $fld = $frm->addButton('', 'csvfile', Labels::getLabel('Lbl_Upload_Csv_File', $this->siteLangId), array('class'=>'csvFile-Js','id'=>'csvFile-Js'));
        return $frm;
    }

    private function getSocialPlatformLangForm($splatform_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmSocialPlatformLang');
        $frm->addHiddenField('', 'splatform_id', $splatform_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->siteLangId), 'splatform_title');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->siteLangId));
        return $frm;
    }

    private function getSocialPlatformForm()
    {
        $frm = new Form('frmSocialPlatform');
        $frm->addHiddenField('', 'splatform_id', 0);
        $frm->addRequiredField(Labels::getLabel('Lbl_Identifier', $this->siteLangId), 'splatform_identifier');
        $frm->addRequiredField(Labels::getLabel('Lbl_URL', $this->siteLangId), 'splatform_url');
        $frm->addSelectBox(Labels::getLabel('Lbl_Icon_Type_from_CSS', $this->siteLangId), 'splatform_icon_class', SocialPlatform::getIconArr($this->siteLangId), '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId));
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('Lbl_Status', $this->siteLangId), 'splatform_active', $activeInactiveArr, '', array(), '');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function isShopActive($userId, $shopId = 0, $returnResult = false)
    {
        return Shop::isShopActive($userId, $shopId, $returnResult);
    }

    private function getShopInfoForm($shop_id = 0)
    {
        $frm = new Form('frmShop');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $frm->addRequiredField(Labels::getLabel('Lbl_Identifier', $this->siteLangId), 'shop_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Shop_SEO_Friendly_URL', $this->siteLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();
        $zipFld = $frm->addTextBox(Labels::getLabel('Lbl_Postalcode', $this->siteLangId), 'shop_postalcode');
        $zipFld->requirements()->setRegularExpressionToValidate(ValidateElement::ZIP_REGEX);
        $zipFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_alphanumeric_value_is_allowed.', $this->siteLangId));

        $phnFld = $frm->addTextBox(Labels::getLabel('Lbl_phone', $this->siteLangId), 'shop_phone', '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        // $phnFld->htmlAfterField='<small class="text--small">'.Labels::getLabel('LBL_e.g.', $this->siteLangId).': '.implode(', ', ValidateElement::PHONE_FORMATS).'</small>';
        $phnFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $this->siteLangId));

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->siteLangId);
        $fld = $frm->addSelectBox(Labels::getLabel('Lbl_Country', $this->siteLangId), 'shop_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 223), array(), Labels::getLabel('Lbl_Select', $this->siteLangId));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('Lbl_State', $this->siteLangId), 'shop_state', array(), '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId))->requirement->setRequired(true);

        $onOffArr = applicationConstants::getOnOffArr($this->siteLangId);

        $frm->addSelectBox(Labels::getLabel('Lbl_Display_Status', $this->siteLangId), 'shop_supplier_display_status', $onOffArr, '', array(), '');

        $fld = $frm->addTextBox(Labels::getLabel('LBL_Free_Shipping_On', $this->siteLangId), 'shop_free_ship_upto');
        $fld->requirements()->setInt();
        $fld->requirements()->setPositive();



        /* if($shop_id > 0){
        $fld = $frm->addButton(Labels::getLabel('Lbl_Logo',$this->siteLangId),'shop_logo',Labels::getLabel('LBL_Upload_File',$this->siteLangId),
        array('class'=>'shopFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_SHOP_LOGO));
        $fld->htmlAfterField='<span id="input-field'.AttachedFile::FILETYPE_SHOP_LOGO.'"></span>
        <div><img src="'.CommonHelper::generateUrl('Image','shopLogo',array($shop_id, $this->siteLangId, 'THUMB')).'"></div>';

        $fld1 = $frm->addButton(Labels::getLabel('Lbl_Banner',$this->siteLangId),'shop_banner',Labels::getLabel('LBL_Upload_File',$this->siteLangId),
        array('class'=>'shopFile-Js','id'=>'shop_banner','data-file_type'=>AttachedFile::FILETYPE_SHOP_BANNER));
        $fld1->htmlAfterField='<span id="input-field'.AttachedFile::FILETYPE_SHOP_BANNER.'"></span>
        <div><img src="'.CommonHelper::generateUrl('Image','shopBanner',array($shop_id, $this->siteLangId, 'THUMB')).'"></div>';
        } */

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function getShopLogoForm($shop_id, $langId)
    {
        $frm = new Form('frmShopLogo');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('Lbl_Language', $langId), 'lang_id', $bannerTypeArr, '', array('class'=>'logo-language-js'), '');
        $fld = $frm->addButton(
            Labels::getLabel('Lbl_Logo', $langId),
            'shop_logo',
            Labels::getLabel('LBL_Upload_Logo', $this->siteLangId),
            array('class'=>'shopFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_SHOP_LOGO,'data-frm'=>'frmShopLogo')
        );
        return $frm;
    }

    private function getBackgroundImageForm($shop_id, $langId)
    {
        $frm = new Form('frmBackgroundImage');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('Lbl_Language', $langId), 'lang_id', $bannerTypeArr, '', array('class'=>'bg-language-js'), '');
        $fld = $frm->addButton(
            Labels::getLabel('Lbl_Background_Image', $langId),
            'shop_background_image',
            Labels::getLabel('LBL_Upload_Background_Image', $this->siteLangId),
            array('class'=>'shopFile-Js','id'=>'shop_background_image','data-file_type'=>AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE,'data-frm'=>'frmBackgroundImage')
        );
        return $frm;
    }

    private function getShopBannerForm($shop_id, $langId)
    {
        $frm = new Form('frmShopBanner');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('Lbl_Language', $langId), 'lang_id', $bannerTypeArr, '', array('class'=>'banner-language-js'), '');
        $screenArr = applicationConstants::getDisplaysArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel("LBL_Display_For", $this->siteLangId), 'slide_screen', $screenArr, '', array(), '');
        $fld1 =  $frm->addButton(
            Labels::getLabel('Lbl_Banner', $this->siteLangId),
            'shop_banner',
            Labels::getLabel('LBL_Upload_Banner', $this->siteLangId),
            array('class'=>'shopFile-Js','id'=>'shop_banner','data-file_type'=>AttachedFile::FILETYPE_SHOP_BANNER,'data-frm'=>'frmShopBanner')
        );
        return $frm;
    }

    private function getShopLangInfoForm($shop_id =0, $lang_id = 0)
    {
        $frm = new Form('frmShopLang');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $frm->addHiddenField('', 'lang_id', $lang_id);
        $frm->addRequiredField(Labels::getLabel('LbL_Shop_Name', $lang_id), 'shop_name');
        $frm->addTextBox(Labels::getLabel('Lbl_Shop_City', $lang_id), 'shop_city');
        $frm->addTextBox(Labels::getLabel('Lbl_Contact_Person', $lang_id), 'shop_contact_person');
        $frm->addTextarea(Labels::getLabel('Lbl_Description', $lang_id), 'shop_description');
        $frm->addTextarea(Labels::getLabel('Lbl_Payment_Policy', $lang_id), 'shop_payment_policy');
        $frm->addTextarea(Labels::getLabel('Lbl_Delivery_Policy', $lang_id), 'shop_delivery_policy');
        $frm->addTextarea(Labels::getLabel('Lbl_Refund_Policy', $lang_id), 'shop_refund_policy');
        $frm->addTextarea(Labels::getLabel('Lbl_Additional_Information', $lang_id), 'shop_additional_info');
        $frm->addTextarea(Labels::getLabel('Lbl_Seller_Information', $lang_id), 'shop_seller_info');
        /* $fld = $frm->addButton(Labels::getLabel('Lbl_Logo',$this->siteLangId),'shop_logo',Labels::getLabel('LBL_Upload_Logo',$this->siteLangId),
        array('class'=>'shopFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_SHOP_LOGO));

        $fld1 =  $frm->addButton(Labels::getLabel('LBL_Banner',$this->siteLangId),'shop_banner',Labels::getLabel('LBL_Upload_Banner',$this->siteLangId),array('class'=>'shopFile-Js','id'=>'shop_banner','data-file_type'=>AttachedFile::FILETYPE_SHOP_BANNER)); */
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $lang_id));
        return $frm;
    }

    private function getCatalogProductSearchForm()
    {
        $frm = new Form('frmSearchCatalogProduct');
        $frm->addTextBox(Labels::getLabel('LBL_Search_By', $this->siteLangId), 'keyword');

        /* if( !User::canAddCustomProductAvailableToAllSellers() ){ */
        if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT')) {
            $frm->addSelectBox(Labels::getLabel('LBL_Product', $this->siteLangId), 'type', array( -1 =>Labels::getLabel('LBL_All', $this->siteLangId) )+ applicationConstants::getCatalogTypeArrForFrontEnd($this->siteLangId), '-1', array('id'=>'type'), '');
        }

        $frm->addSelectBox(Labels::getLabel('LBL_Product_Type', $this->siteLangId), 'product_type', array( -1 =>Labels::getLabel('LBL_All', $this->siteLangId) )+Product::getProductTypes($this->siteLangId), '-1', array(), '');
        /* }  */

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Submit', $this->siteLangId));

        /* if( !User::canAddCustomProductAvailableToAllSellers() ){ */
        $frm->addButton('&nbsp;', 'btn_clear', Labels::getLabel('LBL_Clear', $this->siteLangId));
        /* } */
        //$fldSubmit->attachField($fldCancel);
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function addNewCatalogRequestForm()
    {
        $frm = new Form('frmAddCatalogRequest', array('enctype' => "multipart/form-data"));
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->siteLangId), 'scatrequest_title');
        /* $fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Content',$this->siteLangId),'scatrequest_content');
        $fld->htmlBeforeField = '<div class="editor-bar">';
        $fld->htmlAfterField = '</div>'; */
        $frm->addTextArea(Labels::getLabel('LBL_Content', $this->siteLangId), 'scatrequest_content');
        $fileFld = $frm->addFileUpload(Labels::getLabel('LBL_Upload_File', $this->siteLangId), 'file', array('accept'=>'image/*,.zip' , 'enctype' => "multipart/form-data" ));
        $fileFld->htmlBeforeField='<div class="filefield"><span class="filename"></span>';
        $fileFld->htmlAfterField = '<label class="filelabel">'.Labels::getLabel('LBL_Browse_File', $this->siteLangId).'</label></div><span class="text--small">' .Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->siteLangId) .'</span>' ;
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function getSellerProdCategoriesObj($userId, $shopId = 0, $prodcat_id = 0, $lang_id = 0)
    {
        return Shop::getUserShopProdCategoriesObj($userId, $lang_id, $shopId, $prodcat_id);
    }

    private function getCategoryMediaForm($prodCatId)
    {
        $frm = new Form('frmCategoryMedia');
        $frm->addHiddenField('', 'prodcat_id', $prodCatId);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('Lbl_Language', $this->siteLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $fld1 =  $frm->addButton('', 'category_banner', Labels::getLabel('LBL_Upload_File', $this->siteLangId), array('class'=>'catFile-Js','id'=>'category_banner'));
        return $frm;
    }

    private function getOrderCommentsForm($orderData = array(), $processingOrderStatus)
    {
        $frm = new Form('frmOrderComments');
        $frm->addTextArea(Labels::getLabel('LBL_Your_Comments', $this->siteLangId), 'comments');
        $orderStatusArr = Orders::getOrderProductStatusArr($this->siteLangId, $processingOrderStatus, $orderData['op_status_id']);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->siteLangId), 'op_status_id', $orderStatusArr, '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId));
        $fld->requirements()->setRequired();

        $frm->addSelectBox(Labels::getLabel('LBL_Notify_Customer', $this->siteLangId), 'customer_notified', applicationConstants::getYesNoArr($this->siteLangId), '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId))->requirements()->setRequired();

        $frm->addTextBox(Labels::getLabel('LBL_Tracking_Number', $this->siteLangId), 'tracking_number');

        $trackingUnReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->siteLangId));
        $trackingUnReqObj->setRequired(false);

        $trackingReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->siteLangId));
        $trackingReqObj->setRequired(true);

        $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'eq', 'tracking_number', $trackingReqObj);
        $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'ne', 'tracking_number', $trackingUnReqObj);
        $frm->addHiddenField('', 'op_id', 0);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function getSubscriptionOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code','currency_symbol_left','currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];

        $frm = new Form('frmOrderSrch');
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $langId) ));
        /* $frm->addSelectBox('','status', Orders::getOrderSubscriptionStatusArr( $langId, unserialize(FatApp::getConfig("CONF_SUBSCRIPTION_ORDER_STATUS")) ), '', array(), Labels::getLabel('LBL_Status', $langId) ); */
        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $langId) ,'readonly'=>'readonly' ));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $langId)  ,'readonly'=>'readonly'));
        /* $frm->addTextBox( '', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Order_From', $langId).' ['.$currencySymbol.']' ) );
        $frm->addTextBox( '', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Order_to', $langId).' ['.$currencySymbol.']' ) ); */
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearSearch();'));
        $frm->addHiddenField('', 'page');
        //$fldSubmit->attachField($fldCancel);
        return $frm;
    }

    private function getOrderCancelForm($langId)
    {
        $frm = new Form('frmOrderCancel');
        $frm->addHiddenField('', 'op_id');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Comments', $langId), 'comments');
        $fld->requirements()->setRequired(true);
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('ERR_Reason_cancellation', $langId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    /* -- - --   Packges  ----- */
    public function packages()
    {
        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl());
        }
        $this->_template->addCss(array('css/packages.css'), false);
        $includeFreeSubscription = OrderSubscription:: canUserBuyFreeSubscription($this->siteLangId, UserAuthentication::getLoggedUserId());
        $packagesArr = SellerPackages::getSellerVisiblePackages($this->siteLangId, $includeFreeSubscription);

        $currentActivePlanId = 0;
        $currentActivePlanId = OrderSubscription:: getUserCurrentActivePlanDetails($this->siteLangId, UserAuthentication::getLoggedUserId(), array(OrderSubscription::DB_TBL_PREFIX.'plan_id'));

        foreach ($packagesArr as $key => $package) {
            $packagesArr[$key]['plans'] =  SellerPackagePlans::getSellerVisiblePackagePlans($package[SellerPackages::DB_TBL_PREFIX.'id']);
            $packagesArr[$key]['cheapPlan'] = SellerPackagePlans:: getCheapestPlanByPackageId($package[SellerPackages::DB_TBL_PREFIX.'id']);
        }
        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::SUBSCRIPTION_PAGE_BLOCK, $this->siteLangId);
        $this->set('pageData', $pageData);

        $this->set('includeFreeSubscription', $includeFreeSubscription);
        $this->set('currentActivePlanId', $currentActivePlanId);
        $this->set('packagesArr', $packagesArr);
        $this->_template->render(true, true);
    }

    /*  Subscription Orders */
    public function subscriptions()
    {
        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
            FatApp::redirectUser(CommonHelper::generateUrl('account'));
        }
        $currentActivePlan = OrderSubscription:: getUserCurrentActivePlanDetails($this->siteLangId, UserAuthentication::getLoggedUserId(), array(OrderSubscription::DB_TBL_PREFIX.'till_date',OrderSubscription::DB_TBL_PREFIX.'price',OrderSubscription::DB_TBL_PREFIX.'type'));

        $frmOrderSrch = $this->getSubscriptionOrderSearchForm($this->siteLangId);
        $userId = UserAuthentication::getLoggedUserId();
        $autoRenew = User::getAttributesById($userId, 'user_autorenew_subscription');

        $this->set('currentActivePlan', $currentActivePlan);
        $this->set('frmOrderSrch', $frmOrderSrch);
        $this->set('autoRenew', $autoRenew);
        $this->_template->render(true, true);
    }

    public function addCatalogPopup()
    {
        $this->_template->render(false, false);
    }

    public function sellerShippingForm($productId)
    {
        $productId = FatUtility::int($productId);
        $srch = Product::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(
            array('product_id', 'product_seller_id', 'product_added_by_admin_id',
            'IFNULL(product_name,product_identifier)as product_name')
        );
        $srch->addCondition('product_id', '=', $productId);
        $rs = $srch->getResultSet();
        $productDetails = FatApp::getDb()->fetch($rs);
        if ($productDetails['product_seller_id']>0) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
            FatApp::dieJsonError(Message::getHtml());
        }
        $shipping_rates = array();
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId();

        //$shipping_rates = Products::getProductShippingRates();
        $this->set('siteLangId', $this->siteLangId);
        $shipping_rates = array();

        $shipping_rates = Product::getProductShippingRates($productId, $this->siteLangId, 0, $userId);
        $shippingDetails = Product::getProductShippingDetails($productId, $this->siteLangId, $userId);
        if (isset($shippingDetails['ps_from_country_id']) && $shippingDetails['ps_from_country_id']) {
            $shippingDetails['shipping_country'] = Countries::getCountryById($shippingDetails['ps_from_country_id'], $this->siteLangId, 'country_name');
        }
        $shippingDetails['ps_product_id']= $productId;
        $shippingFrm = $this->getShippingForm();
        $shippingFrm->fill($shippingDetails);
        $this->set('shippingFrm', $shippingFrm);

        $this->set('productDetails', $productDetails);
        $this->set('product_id', $productId);
        $this->set('shipping_rates', $shipping_rates);
        $this->_template->render(false, false);
    }

    public function getShippingForm()
    {
         $frm = new Form('frmCustomProduct');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Shipping_country', $this->siteLangId), 'shipping_country');

        $fld=$frm->addCheckBox(Labels::getLabel('LBL_Free_Shipping', $this->siteLangId), 'ps_free', 1);
        $frm->addHtml('', '', '<div id="tab_shipping"></div>');

        $frm->addHiddenField('', 'ps_from_country_id');
        $frm->addHiddenField('', 'ps_product_id');
        $frm->addHtml('', '', '<div id="tab_shipping"></div>');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $this->siteLangId));
        return $frm;
    }

    public function setupSellerShipping()
    {
        $frm = $this->getShippingForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $productShiping = FatApp::getPostedData('product_shipping');



        if (false === $post) {
            FatUtility::dieWithError(current($frm->getValidationErrors()));
        }
        $product_id = FatUtility::int($post['ps_product_id']);

        /* Validate product belongs to current logged seller[ */
        if ($product_id) {
            $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
            if ($productRow['product_seller_id'] != 0) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        unset($post['product_id']);
        unset($post['product_shipping']);
        $prodObj = new Product($product_id);
        $data_to_be_save = $post;
        $data_to_be_save['ps_product_id'] = $product_id;


        /*Save Prodcut Shipping  [*/
        if (!$this->addUpdateProductSellerShipping($product_id, $data_to_be_save)) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /*]*/

        /*Save Prodcut Shipping Details [*/
        if (!$this->addUpdateProductShippingRates($product_id, $productShiping)) {
            Message::addErrorMessage($taxObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /*]*/

        $this->set('msg', Labels::getLabel('LBL_Shipping_Setup_Successful', $this->siteLangId));
        $this->set('product_id', $product_id);

        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleAutoRenewalSubscription()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $status = User::getAttributesById($userId, 'user_autorenew_subscription');
        if ($status) {
            $status = applicationConstants::OFF;
        } else {
            $status = applicationConstants::ON;
        }
        $dataToUpdate = array('user_autorenew_subscription'=>$status);
        $record = new User($userId);
        $record->assignValues($dataToUpdate);

        if (!$record->save()) {
            Message::addErrorMessage(Labels::getLabel('M_Unable_to_Process_the_request,Please_try_later', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('M_Settings_updated_successfully', $this->siteLangId));
        $this->set('autoRenew', $status);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function productLinks($product_id)
    {
        //$this->objPrivilege->canViewProducts();
        $product_id = FatUtility::int($product_id);
        if ($product_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $prodCatObj = new ProductCategory();
        $arr_options = $prodCatObj->getProdCatTreeStructure(0, $this->siteLangId);
        $prodObj = new Product();
        $product_categories = $prodObj->getProductCategories($product_id);

        $this->set('selectedCats', $product_categories);
        $this->set('arr_options', $arr_options);
        $this->set('product_id', $product_id);
        $this->_template->render(false, false);
    }

    public function updateProductLink()
    {
        //$this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        if (!$product_id || !$option_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodObj = new Product();
        if (!$prodObj->addUpdateProductCategory($product_id, $option_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Record_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeProductCategory()
    {
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        if (!$product_id || !$option_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodObj = new Product();
        if (!$prodObj->removeProductCategory($product_id, $option_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Category_Removed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getCustomProductForm($type = 'CUSTOM_PRODUCT', $prodcat_id = 0)
    { 
        $langId = $this->siteLangId;
        $frm = new Form('frmCustomProduct');
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Product_Identifier', $langId), 'product_identifier');
        $fld->htmlAfterField = '<br/><small class="text--small">'. Labels::getLabel('LBL_Product_Identifier_can_be_same_as_of_Product_Name', $langId).'</small>';
        $pTypeFld = $frm->addSelectBox(Labels::getLabel('LBL_Product_Type', $langId), 'product_type', Product::getProductTypes($langId), '', array('id'=>'product_type'), '');

        $fld_model = $frm->addTextBox(Labels::getLabel('LBL_Model', $langId), 'product_model');
        if (FatApp::getConfig("CONF_PRODUCT_MODEL_MANDATORY", FatUtility::VAR_INT, 1)) {
            $fld_model->requirements()->setRequired();
        }

        /* if($type == 'CATALOG_PRODUCT'){ */
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Brand/Manfacturer', $this->siteLangId), 'brand_name');
        $fld->htmlAfterField = '<br/><small class="text--small"><a href="javascript:void(0)" onClick="addBrandReqForm(0);">'. Labels::getLabel('LBL_Brand_not_found?_select_other_and_request_for_brand', $this->siteLangId).'</a></small>';
        $frm->addHiddenField('', 'product_brand_id');
        /* } */

        $fld = $frm->addFloatField(Labels::getLabel('LBL_Minimum_Selling_Price', $this->siteLangId).' ['.CommonHelper::getCurrencySymbol(true).']', 'product_min_selling_price', '');
        $fld->requirements()->setPositive();
        $taxCategories =  Tax::getSaleTaxCatArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Tax_Category', $this->siteLangId), 'ptt_taxcat_id', $taxCategories, '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId))->requirements()->setRequired(true);

        if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) {
            /* dimension unit[ */
            $lengthUnitsArr = applicationConstants::getLengthUnitsArr($langId);
            $frm->addSelectBox(Labels::getLabel('LBL_Dimensions_Unit', $langId), 'product_dimension_unit', $lengthUnitsArr, '', array(), Labels::getLabel('LBL_Select', $langId))->requirements()->setRequired();
            $pDimensionUnitUnReqObj = new FormFieldRequirement('product_dimension_unit', Labels::getLabel('LBL_Dimensions_Unit', $langId));
            $pDimensionUnitUnReqObj->setRequired(false);

            $pDimensionUnitReqObj = new FormFieldRequirement('product_dimension_unit', Labels::getLabel('LBL_Dimensions_Unit', $langId));
            $pDimensionUnitReqObj->setRequired(true);
            /* ] */

            /* length [ */
            $pLengthFld =  $frm->addFloatField(Labels::getLabel('LBL_Length', $langId), 'product_length', '0.00');
            $pLengthUnReqObj = new FormFieldRequirement('product_length', Labels::getLabel('LBL_Length', $langId));
            $pLengthUnReqObj->setRequired(false);

            $pLengthReqObj = new FormFieldRequirement('product_length', Labels::getLabel('LBL_Length', $langId));
            $pLengthReqObj->setRequired(true);
            $pLengthReqObj->setFloatPositive();
            $pLengthReqObj->setRange('0.00001', '9999999999');
            /* ] */

            /* width[ */
            $pWidthFld =  $frm->addFloatField(Labels::getLabel('LBL_Width', $langId), 'product_width', '0.00');
            $pWidthUnReqObj = new FormFieldRequirement('product_width', Labels::getLabel('LBL_Width', $langId));
            $pWidthUnReqObj->setRequired(false);

            $pWidthReqObj = new FormFieldRequirement('product_width', Labels::getLabel('LBL_Width', $langId));
            $pWidthReqObj->setRequired(true);
            $pWidthReqObj->setFloatPositive();
            $pWidthReqObj->setRange('0.00001', '9999999999');
            /* ] */

            /* height[ */
            $pHeightFld =  $frm->addFloatField(Labels::getLabel('LBL_Height', $langId), 'product_height', '0.00');
            $pHeightUnReqObj = new FormFieldRequirement('product_height', Labels::getLabel('LBL_Height', $langId));
            $pHeightUnReqObj->setRequired(false);

            $pHeightReqObj = new FormFieldRequirement('product_height', Labels::getLabel('LBL_Height', $langId));
            $pHeightReqObj->setRequired(true);
            $pHeightReqObj->setFloatPositive();
            $pHeightReqObj->setRange('0.00001', '9999999999');
            /* ] */

            /* weight unit[ */
            $weightUnitsArr = applicationConstants::getWeightUnitsArr($langId);
            $pWeightUnitsFld = $frm->addSelectBox(Labels::getLabel('LBL_Weight_Unit', $langId), 'product_weight_unit', $weightUnitsArr, '', array(), Labels::getLabel('LBL_Select', $langId))->requirements()->setRequired();
            ;

            $pWeightUnitUnReqObj = new FormFieldRequirement('product_weight_unit', Labels::getLabel('LBL_Weight_Unit', $langId));
            $pWeightUnitUnReqObj->setRequired(false);

            $pWeightUnitReqObj = new FormFieldRequirement('product_weight_unit', Labels::getLabel('LBL_Weight_Unit', $langId));
            $pWeightUnitReqObj->setRequired(true);
            /* ] */

            /* weight[ */
            $pWeightFld = $frm->addFloatField(Labels::getLabel('LBL_Weight', $langId), 'product_weight', '0.00');
            $pWeightUnReqObj = new FormFieldRequirement('product_weight', Labels::getLabel('LBL_Weight', $langId));
            $pWeightUnReqObj->setRequired(false);

            $pWeightReqObj = new FormFieldRequirement('product_weight', Labels::getLabel('LBL_Weight', $langId));
            $pWeightReqObj->setRequired(true);
            /* ] */

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_length', $pLengthUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_length', $pLengthReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_width', $pWidthUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_width', $pWidthReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_height', $pHeightUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_height', $pHeightReqObj);


            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_dimension_unit', $pDimensionUnitUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_dimension_unit', $pDimensionUnitReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_weight', $pWeightUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_weight', $pWeightReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_weight_unit', $pWeightUnitUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_weight_unit', $pWeightUnitReqObj);
        }

        /* $frm->addFloatField( Labels::getLabel('LBL_Minimum_Selling_Price', $langId).' ['.CommonHelper::getCurrencySymbol(true).']', 'product_min_selling_price', ''); */

        $frm->addTextBox(Labels::getLabel('LBL_EAN/UPC_code', $this->siteLangId), 'product_upc');

        $frm->addCheckBox(Labels::getLabel('LBL_Product_Featured', $this->siteLangId), 'product_featured', 1, array(), false, 0);

        /* $frm->addSelectBox(Labels::getLabel('LBL_Shipped_by_me',$langId), 'product_shipped_by_me', $yesNoArr, applicationConstants::YES, array(), ''); */



        $activeInactiveArr = applicationConstants::getActiveInactiveArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Product_Status', $langId), 'product_active', $activeInactiveArr, applicationConstants::ACTIVE, array(), '');

        $yesNoArr = applicationConstants::getYesNoArr($langId);
        $codFld = $frm->addSelectBox(Labels::getLabel('LBL_Available_for_COD', $langId), 'product_cod_enabled', $yesNoArr, applicationConstants::NO, array(), '');
        $paymentMethod = new PaymentMethods;
        if (!$paymentMethod->cashOnDeliveryIsActive()) {
            $codFld->addFieldTagAttribute('disabled', 'disabled');
            $codFld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_COD_option_is_disabled_in_payment_gateway_settings', $langId).'</small>';
        }
        $fld=$frm->addCheckBox(Labels::getLabel('LBL_Free_Shipping', $langId), 'ps_free', 1);

        $fld = $frm->addTextBox(Labels::getLabel('LBL_Shipping_country', $langId), 'shipping_country');

        if ($type == 'CATALOG_PRODUCT') {
            $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Option_Groups', $this->siteLangId), 'option_name');
            $fld1->htmlAfterField='<div class=""><small> <a class="" href="javascript:void(0);" onClick="optionForm(0);">' .Labels::getLabel('LBL_Add_New_Option', $this->siteLangId).'</a></small></div><div class="col-md-12"><ul class="list--vertical" id="product_options_list"></ul></div>';

            $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Tag', $this->siteLangId), 'tag_name');
            $fld1->htmlAfterField= '<div class=""><small><a href="javascript:void(0);" onClick="addTagForm(0);">'.Labels::getLabel('LBL_Tag_Not_Found?_Click_here_to_', $this->siteLangId).' '.Labels::getLabel('LBL_Add_New_Tag', $this->siteLangId).'</a></small></div><div class="col-md-12"><ul class="list--vertical" id="product-tag-js"></ul></div>';
        }

        $frm->addHiddenField('', 'ps_from_country_id');
        $frm->addHiddenField('', 'product_id');
        $frm->addHiddenField('', 'preq_id');
        $frm->addHiddenField('', 'product_options');
        $frm->addHiddenField('', 'preq_prodcat_id', $prodcat_id);

        $fld1 = $frm->addHtml('', 'shipping_info_html', '<div class="heading4 not-digital-js">'.Labels::getLabel('LBL_Shipping_Info/Charges', $langId).'</div><div class="divider not-digital-js"></div>');
        $fld2 =$frm->addHtml('', '', '<div id="tab_shipping"></div>');
        $fld1->attachField($fld2);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));

        return $frm;
    }

    private function getSellerProductForm($product_id, $type = 'SELLER_PRODUCT')
    {
        /*Type is used when we called this form for custom catalog request with selprod data*/

        $defaultProductCond = '';
        $frm = new Form('frmSellerProduct');

        if ($type == 'CUSTOM_CATALOG') {
            $reqData = ProductRequest::getAttributesById($product_id, array('preq_content'));
            $productData = array_merge($reqData, json_decode($reqData['preq_content'], true));
            $optionArr = isset($productData['product_option'])?$productData['product_option']:array();
            /*if(!empty($optionArr)) {
                $frm->addHtml('', 'optionSectionHeading', '');
            }*/
            foreach ($optionArr as $val) {
                $optionSrch = Option::getSearchObject($this->siteLangId);
                $optionSrch->addMultipleFields(array('IFNULL(option_name,option_identifier) as option_name','option_id'));
                $optionSrch->doNotCalculateRecords();
                $optionSrch->setPageSize(1);
                $optionSrch->addCondition('option_id', '=', $val);
                $rs = $optionSrch->getResultSet();
                $option = FatApp::getDb()->fetch($rs);
                if ($option == false) {
                    continue;
                }
                $optionValues = Product::getOptionValues($option['option_id'], $this->siteLangId);
                $option_name = ($option['option_name'] != '') ? $option['option_name'] : $option['option_identifier'];
                $fld = $frm->addSelectBox($option_name, 'selprodoption_optionvalue_id['.$option['option_id'].']', $optionValues, '', array('class' => 'selprodoption_optionvalue_id'), Labels::getLabel('LBL_Select', $this->siteLangId));
                $fld->requirements()->setRequired();
            }
        } else {
            $productData = Product::getAttributesById($product_id, array('product_type','product_min_selling_price','product_cod_enabled'));
            if ($productData['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
                $defaultProductCond = Product::CONDITION_NEW;
            }

            $productOptions = Product::getProductOptions($product_id, $this->siteLangId, true);
            if ($productOptions) {
                /*$frm->addHtml('', 'optionSectionHeading', '');*/
                foreach ($productOptions as $option) {
                    $option_name = ($option['option_name'] != '') ? $option['option_name'] : $option['option_identifier'];
                    $fld = $frm->addSelectBox($option_name, 'selprodoption_optionvalue_id['.$option['option_id'].']', $option['optionValues'], '', array('class' => 'selprodoption_optionvalue_id'), Labels::getLabel('LBL_Select', $this->siteLangId));
                    $fld->requirements()->setRequired();
                }
            }
        }

        $frm->addTextBox(Labels::getLabel('LBL_Url_Keyword', $this->siteLangId), 'selprod_url_keyword')->requirements()->setRequired();

        $costPrice = $frm->addFloatField(Labels::getLabel('LBL_Cost_Price', $this->siteLangId).' ['.CommonHelper::getCurrencySymbol(true).']', 'selprod_cost');
        $costPrice->requirements()->setPositive();

        $fld = $frm->addFloatField(Labels::getLabel('LBL_Price', $this->siteLangId).' ['.CommonHelper::getCurrencySymbol(true).']', 'selprod_price');
        $fld->requirements()->setPositive();
        if (isset($productData['product_min_selling_price'])) {
            $fld->requirements()->setRange($productData['product_min_selling_price'], 9999999999);
            // $fld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Minimum_selling_price_for_this_product_is', $this->siteLangId).' '.CommonHelper::displayMoneyFormat($productData['product_min_selling_price'], true, true));

            $fld->htmlAfterField='<small class="text--small">'.Labels::getLabel('LBL_This_price_is_excluding_the_tax_rates', $this->siteLangId).'</small> <br><small class="text--small">'.Labels::getLabel('LBL_Min_Selling_price', $this->siteLangId). CommonHelper::displayMoneyFormat($productData['product_min_selling_price'], true, true).'</small>';
        }

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Quantity', $this->siteLangId), 'selprod_stock');
        $fld->requirements()->setPositive();
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Minimum_Quantity', $this->siteLangId), 'selprod_min_order_qty');
        $fld->requirements()->setPositive();
        $frm->addSelectBox(Labels::getLabel('LBL_Subtract_Stock', $this->siteLangId), 'selprod_subtract_stock', applicationConstants::getYesNoArr($this->siteLangId), applicationConstants::YES, array(), '');
        $selprod_track_inventoryFld =  $frm->addSelectBox(Labels::getLabel('LBL_Track_Inventory', $this->siteLangId), 'selprod_track_inventory', Product::getInventoryTrackArr($this->siteLangId), Product::INVENTORY_NOT_TRACK, array(), '');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Alert_Stock_Level', $this->siteLangId), 'selprod_threshold_stock_level');
        $fld->requirements()->setInt();

        $fld_sku = $frm->addTextBox(Labels::getLabel('LBL_Product_SKU', $this->siteLangId), 'selprod_sku');
        if (FatApp::getConfig("CONF_PRODUCT_SKU_MANDATORY", FatUtility::VAR_INT, 1)) {
            $fld_sku->requirements()->setRequired();
        }
        $fld_sku->htmlAfterField='<br/><small class="text--small">'.Labels::getLabel('LBL_Stock_Keeping_Unit', $this->siteLangId).'</small>';

        if ($productData['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $fld = $frm->addIntegerField(Labels::getLabel('LBL_Max_Download_Times', $this->siteLangId), 'selprod_max_download_times');
            $fld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_-1_for_unlimited', $this->siteLangId).'</small>';

            $fld1 = $frm->addIntegerField(Labels::getLabel('LBL_Validity_(days)', $this->siteLangId), 'selprod_download_validity_in_days');
            $fld1->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_-1_for_unlimited', $this->siteLangId).'</small>';
            $frm->addHiddenField('', 'selprod_condition', $defaultProductCond);
        } else {
            $fld = $frm->addSelectBox(Labels::getLabel('LBL_Product_Condition', $this->siteLangId), 'selprod_condition', Product::getConditionArr($this->siteLangId), $defaultProductCond, array(), Labels::getLabel('LBL_Select_Condition', $this->siteLangId));
            $fld->requirements()->setRequired();
        }

        $frm->addDateField(Labels::getLabel('LBL_Date_Available', $this->siteLangId), 'selprod_available_from', '', array('readonly' => 'readonly'))->requirements()->setRequired();

        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->siteLangId), 'selprod_active', applicationConstants::getActiveInactiveArr($this->siteLangId), applicationConstants::ACTIVE, array(), '');

        if ($type != 'CUSTOM_CATALOG') {
            $yesNoArr = applicationConstants::getYesNoArr($this->siteLangId);
            $codFld = $frm->addSelectBox(Labels::getLabel('LBL_Available_for_COD', $this->siteLangId), 'selprod_cod_enabled', $yesNoArr, '0', array(), '');

            $paymentMethod = new PaymentMethods;
            if (!$paymentMethod->cashOnDeliveryIsActive() || $productData['product_cod_enabled'] != applicationConstants::YES) {
                $codFld->addFieldTagAttribute('disabled', 'disabled');
                if ($productData['product_cod_enabled'] != applicationConstants::YES) {
                    $codFld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_COD_option_is_disabled_in_Product', $this->siteLangId).'</small>';
                } else {
                    $codFld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_COD_option_is_disabled_in_payment_gateway_settings', $this->siteLangId).'</small>';
                }
            }
        }

        $frm->addHiddenField('', 'selprod_product_id', $product_id);
        $frm->addHiddenField('', 'selprod_urlrewrite_id');
        $frm->addHiddenField('', 'selprod_id');
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        if ($type != 'CUSTOM_CATALOG') {
            $fld2 = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $this->siteLangId), array('onClick' => 'cancelForm(this)'));
            $fld1->attachField($fld2);
        }
        return $frm;
    }

    public function catalogInfo($product_id = 0)
    {
        $product_id = FatUtility::int($product_id);
        $prodSrchObj = new ProductSearch($this->siteLangId, null, null, false, false);
        /* fetch requested product[ */
        $prodSrch = clone $prodSrchObj;
        $prodSrch->joinProductToCategory(0, false, false, false);
        $prodSrch->joinProductToTax();
        $prodSrch->joinBrands(0, false, false, false);
        $prodSrch->addCondition('product_id', '=', $product_id);
        $prodSrch->doNotLimitRecords();


        $prodSrch->addMultipleFields(
            array(
            'product_id','product_identifier', 'IFNULL(product_name,product_identifier) as product_name', 'product_seller_id', 'product_model', 'product_type', 'product_short_description', 'prodcat_id', 'IFNULL(prodcat_name,prodcat_identifier) as prodcat_name', 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name','product_min_selling_price', 'ptt_taxcat_id ')
        );
        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        /* ] */

        $taxData = Tax::getTaxCatByProductId($product_id, UserAuthentication::getLoggedUserId(), $this->siteLangId, array('ptt_taxcat_id'));
        if (!empty($taxData)) {
            $product = array_merge($product, $taxData);
        }

        if (!$product) {
            FatUtility::exitWithErrorCode(404);
        }

        /* Get Product Specifications [*/
        $specSrchObj = clone $prodSrchObj;
        $specSrchObj->doNotCalculateRecords();
        $specSrchObj->doNotLimitRecords();
        $specSrchObj->joinTable(Product::DB_PRODUCT_SPECIFICATION, 'LEFT OUTER JOIN', 'product_id = tcps.prodspec_product_id', 'tcps');
        $specSrchObj->joinTable(Product::DB_PRODUCT_LANG_SPECIFICATION, 'INNER JOIN', 'tcps.prodspec_id = tcpsl.prodspeclang_prodspec_id and   prodspeclang_lang_id  = '.$this->siteLangId, 'tcpsl');
        $specSrchObj->addMultipleFields(array('prodspec_id','prodspec_name','prodspec_value'));
        $specSrchObj->addGroupBy('prodspec_id');
        $specSrchObj->addCondition('prodspec_product_id', '=', $product['product_id']);
        $specSrchObjRs = $specSrchObj->getResultSet();
        $productSpecifications = FatApp::getDb()->fetchAll($specSrchObjRs);
        /* ] */

        $this->set('product', $product);
        $this->set('productSpecifications', $productSpecifications);
        $this->_template->render(false, false);
    }


    public function returnAddress()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $userObj = new User($userId);
        $data = $userObj->getUserReturnAddress($this->siteLangId);
        $this->set('info', $data);
        $this->_template->render(false, false);
    }

    public function returnAddressForm()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $frm = $this->getReturnAddressForm();
        $stateId = 0;

        $userObj = new User($userId);
        $data = $userObj->getUserReturnAddress();

        if ($data != false) {
            $frm->fill($data);
            $stateId = $data['ura_state_id'];
        }


        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = 0;

        if (!false == $shopDetails) {
            $shop_id =  $shopDetails['shop_id'];
        }

        $this->set('shop_id', $shop_id);
        $this->set('language', Language::getAllNames());
        $this->set('siteLangId', $this->siteLangId);
        $this->set('frm', $frm);
        $this->set('stateId', $stateId);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setReturnAddress()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $post = FatApp::getPostedData();
        $ura_state_id = FatUtility::int($post['ura_state_id']);
        $frm = $this->getReturnAddressForm();
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $post['ura_state_id'] = $ura_state_id;

        $userObj = new User($userId);
        if (!$userObj->updateUserReturnAddress($post)) {
            Message::addErrorMessage(Labels::getLabel($userObj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $newTabLangId = $this->siteLangId;
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_Setup_successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function returnAddressLangForm($langId)
    {
        $langId = FatUtility::int($langId);
        $userId = UserAuthentication::getLoggedUserId();
        $userId = FatUtility::int($userId);

        if (1 > $langId || 1 > $userId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getReturnAddressLangForm($langId);
        $stateId = 0;

        $userObj = new User($userId);
        $data = $userObj->getUserReturnAddress($langId);

        if ($data != false) {
            $frm->fill($data);
        }


        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = 0;

        if (!false == $shopDetails) {
            $shop_id =  $shopDetails['shop_id'];
        }

        $this->set('shop_id', $shop_id);
        $this->set('language', Language::getAllNames());
        $this->set('siteLangId', $this->siteLangId);
        $this->set('frm', $frm);
        $this->set('stateId', $stateId);
        $this->set('formLangId', $langId);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->_template->render(false, false);
    }

    public function setReturnAddressLang()
    {
        $post = FatApp::getPostedData();
        $lang_id = $post['lang_id'];
        $userId = UserAuthentication::getLoggedUserId();

        if ($userId == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getReturnAddressLangForm($lang_id);
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($userId);
        if (!$userObj->updateUserReturnAddressLang($post)) {
            Message::addErrorMessage(Labels::getLabel($userObj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $newTabLangId     = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            $userObj = new User($userId);
            $srch = new SearchBase(User::DB_TBL_USR_RETURN_ADDR_LANG);
            $srch->addCondition('uralang_user_id', '=', $userId);
            $srch->addCondition('uralang_lang_id', '=', $langId);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $vendorReturnAddress = FatApp::getDb()->fetch($rs);


            if (!$vendorReturnAddress) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_Setup_successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getReturnAddressForm()
    {
        $frm = new Form('frmReturnAddress');

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->siteLangId);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->siteLangId), 'ura_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY'), array(), Labels::getLabel('LBL_Select', $this->siteLangId));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->siteLangId), 'ura_state_id', array(), '', array(), Labels::getLabel('LBL_Select', $this->siteLangId))->requirement->setRequired(true);
        /* $frm->addTextBox(Labels::getLabel('LBL_City',$this->siteLangId), 'ura_city');     */
        $zipFld = $frm->addTextBox(Labels::getLabel('LBL_Postalcode', $this->siteLangId), 'ura_zip');
        $zipFld->requirements()->setRegularExpressionToValidate(ValidateElement::ZIP_REGEX);
        $zipFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_alphanumeric_value_is_allowed.', $this->siteLangId));

        $phnFld = $frm->addTextBox(Labels::getLabel('LBL_Phone', $this->siteLangId), 'ura_phone', '', array('class'=>'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        // $phnFld->htmlAfterField='<small class="text--small">'.Labels::getLabel('LBL_e.g.', $this->siteLangId).': '.implode(', ', ValidateElement::PHONE_FORMATS).'</small>';

        $phnFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $this->siteLangId));

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $this->siteLangId));
        return $frm;
    }

    private function getReturnAddressLangForm($formLangId)
    {
        $formLangId = FatUtility::int($formLangId);

        $frm = new Form('frmReturnAddressLang');
        $frm->addHiddenField('', 'lang_id', $formLangId);
        $frm->addTextBox(Labels::getLabel('LBL_Name', $formLangId), 'ura_name')->requirement->setRequired(true);
        ;
        $frm->addTextBox(Labels::getLabel('LBL_City', $formLangId), 'ura_city')->requirement->setRequired(true);
        ;
        $frm->addTextarea(Labels::getLabel('LBL_Address1', $formLangId), 'ura_address_line_1')->requirement->setRequired(true);
        ;
        $frm->addTextarea(Labels::getLabel('LBL_Address2', $formLangId), 'ura_address_line_2');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $this->siteLangId));
        return $frm;
    }

    public function sellerOffers()
    {
        $this->_template->render(true, true);
    }

    public function searchSellerOffers()
    {
        $offers = DiscountCoupons::getUserCoupons(UserAuthentication::getLoggedUserId(), $this->siteLangId, DiscountCoupons::TYPE_SELLER_PACKAGE);

        if ($offers) {
            $this->set('offers', $offers);
        } else {
            $this->set('noRecordsHtml', $this->_template->render(false, false, '_partial/no-record-found.php', true));
        }
        $this->_template->render(false, false);
    }

    public function productTooltipInstruction($type)
    {
        $this->set('type', $type);
        $this->_template->render(false, false);
    }

    public function specialPrice($selProd_id = 0)
    {
        $selProd_id = FatUtility::int($selProd_id);

        if (0 < $selProd_id || 0 > $selProd_id) {
            $selProd_id = SellerProduct::getAttributesByID($selProd_id, 'selprod_id', false);
            if (empty($selProd_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
                FatApp::redirectUser(CommonHelper::generateUrl('SellerProducts', 'specialPrice'));
            }
        }

        $srchFrm = $this->getSpecialPriceSearchForm();
        $selProdIdsArr = FatApp::getPostedData('selprod_ids', FatUtility::VAR_INT, 0);

        $dataToEdit = array();
        if (!empty($selProdIdsArr) || 0 < $selProd_id) {
            $selProdIdsArr = (0 < $selProd_id) ? array($selProd_id) : $selProdIdsArr;
            $productsTitle = SellerProduct::getProductDisplayTitle($selProdIdsArr, $this->siteLangId);
            foreach ($selProdIdsArr as $selProdId) {
                $dataToEdit[] = array(
                    'product_name' => html_entity_decode($productsTitle[$selProdId], ENT_QUOTES, 'UTF-8'),
                    'splprice_selprod_id' => $selProdId
                );
            }
        } else {
            $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

            if (false === $post) {
                FatUtility::dieJsonError(current($frm->getValidationErrors()));
            } else {
                unset($post['btn_submit'], $post['btn_clear']);
                $srchFrm->fill($post);
            }
        }
        if (0 < $selProd_id) {
            $srchFrm->addHiddenField('', 'selprod_id', $selProd_id);
            $srchFrm->fill(array('keyword'=>$productsTitle[$selProd_id]));
        }

        $this->set("dataToEdit", $dataToEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("selProd_id", $selProd_id);
        $this->_template->render();
    }

    public function searchSpecialPriceProducts()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');

        $srch = SellerProduct::searchSpecialPriceProductsObj($this->siteLangId, $selProdId, $keyword, $userId);
        $srch->setPageNumber($page);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);

        $this->set("arrListing", $arrListing);

        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    private function getSpecialPriceSearchForm()
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $this->siteLangId) ));

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId), array('onclick'=>'clearSearch();'));
        return $frm;
    }

    public function updateSpecialPriceRow()
    {
        $data = FatApp::getPostedData();
        if (empty($data)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $splPriceId = $this->updateSelProdSplPrice($data, true);
        if (!$splPriceId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        // last Param of getProductDisplayTitle function used to get title in html form.
        $productName = SellerProduct::getProductDisplayTitle($data['splprice_selprod_id'], $this->siteLangId, true);
        $data['product_name'] = $productName;
        $this->set('data', $data);
        $this->set('splPriceId', $splPriceId);
        $json = array(
            'status'=> true,
            'msg'=>Labels::getLabel('LBL_Special_Price_Setup_Successful', $this->siteLangId),
            'data'=>$this->_template->render(false, false, 'seller/update-special-price-row.php', true)
        );
        Product::updateMinPrices();
        FatUtility::dieJsonSuccess($json);
    }

    private function updateSelProdSplPrice($post, $return = false)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $selprod_id = !empty($post['splprice_selprod_id']) ? FatUtility::int($post['splprice_selprod_id']) : 0;
        $splprice_id = !empty($post['splprice_id']) ? FatUtility::int($post['splprice_id']) : 0;

        if (1 > $selprod_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if (strtotime($post['splprice_start_date']) > strtotime($post['splprice_end_date'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Dates', $this->siteLangId));
        }

        $prodSrch = new ProductSearch($this->siteLangId);
        $prodSrch->joinSellerProducts($userId, '', array(), false);
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->addMultipleFields(array('product_min_selling_price', 'selprod_price', 'selprod_available_from'));
        $prodSrch->setPageSize(1);
        $rs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($rs);

        if (strtotime($post['splprice_start_date']) < strtotime($product['selprod_available_from'])) {
            $str = Labels::getLabel('MSG_Special_Price_Date_Must_Be_Greater_Or_Than_Equal_To_{availablefrom}', $this->siteLangId);
            $message = CommonHelper::replaceStringData($str, array('{availablefrom}' => date('Y-m-d', strtotime($product['selprod_available_from']))));
            FatUtility::dieJsonError($message);
        }

        if (!isset($post['splprice_price']) || $post['splprice_price'] < $product['product_min_selling_price'] || $post['splprice_price'] >= $product['selprod_price']) {
            $str = Labels::getLabel('MSG_Price_must_between_min_selling_price_{minsellingprice}_and_selling_price_{sellingprice}', $this->siteLangId);
            $minSellingPrice = CommonHelper::displayMoneyFormat($product['product_min_selling_price'], false, true, true);
            $sellingPrice = CommonHelper::displayMoneyFormat($product['selprod_price'], false, true, true);

            $message = CommonHelper::replaceStringData($str, array('{minsellingprice}' => $minSellingPrice, '{sellingprice}' => $sellingPrice));
            FatUtility::dieJsonError($message);
        }

        /* Check if same date already exists [ */
        $tblRecord = new TableRecord(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE);

        $smt = 'splprice_selprod_id = ? AND ';
        $smt .= '(
                                ((splprice_start_date between ? AND ?) OR (splprice_end_date between ? AND ?))
                                OR
                                ((? BETWEEN splprice_start_date AND splprice_end_date) OR (? BETWEEN  splprice_start_date AND splprice_end_date))
                            )';
        $smtValues = array(
            $selprod_id,
            $post['splprice_start_date'],
            $post['splprice_end_date'],
            $post['splprice_start_date'],
            $post['splprice_end_date'],
            $post['splprice_start_date'],
            $post['splprice_end_date'],
        );

        if (0 < $splprice_id) {
            $smt .= ' AND splprice_id != ?';
            $smtValues[] = $splprice_id;
        }
        $condition = array(
            'smt' => $smt,
            'vals' => $smtValues
        );
        // CommonHelper::printArray($condition, true);
        if ($tblRecord->loadFromDb($condition)) {
            $specialPriceRow = $tblRecord->getFlds();
            if ($specialPriceRow['splprice_id'] != $splprice_id) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Special_price_for_this_date_already_added', $this->siteLangId));
            }
        }
        /* ] */

        $data_to_save = array(
        'splprice_selprod_id'    =>    $selprod_id,
        'splprice_start_date'    =>    $post['splprice_start_date'],
        'splprice_end_date'    =>    $post['splprice_end_date'],
        'splprice_price'        =>    $post['splprice_price'],
        );

        if (0 < $splprice_id) {
            $data_to_save['splprice_id'] = $splprice_id;
        }

        $sellerProdObj = new SellerProduct();

        // Return Special Price ID if $return is true else it will return bool value.
        $splPriceId = $sellerProdObj->addUpdateSellerProductSpecialPrice($data_to_save, $return);
        if (false === $splPriceId) {
            FatUtility::dieJsonError(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
        }

        return $splPriceId;
    }

    public function updateSpecialPriceColValue()
    {
        $splPriceId = FatApp::getPostedData('splprice_id', FatUtility::VAR_INT, 0);
        if (1 > $splPriceId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $attribute = FatApp::getPostedData('attribute', FatUtility::VAR_STRING, '');

        $columns = array('splprice_start_date', 'splprice_end_date', 'splprice_price');
        if (!in_array($attribute, $columns)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $otherColumns = array_values(array_diff($columns, [$attribute]));

        $otherColumnsValue = SellerProductSpecialPrice::getAttributesById($splPriceId, $otherColumns);
        if (empty($otherColumnsValue)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $value = FatApp::getPostedData('value');
        $selProdId = FatApp::getPostedData('selProdId', FatUtility::VAR_INT, 0);

        $dataToUpdate = array(
            'splprice_selprod_id' => $selProdId,
            'splprice_id' => $splPriceId,
            $attribute => $value,
        );

        $dataToUpdate += $otherColumnsValue;

        if (!$this->updateSelProdSplPrice($dataToUpdate)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Something_went_wrong._Please_Try_Again.', $this->siteLangId));
        }

        if ('splprice_price' == $attribute) {
            $value = CommonHelper::displayMoneyFormat($value, true, true);
        }
        $json = array(
            'status'=> true,
            'msg'=>Labels::getLabel('MSG_Success', $this->siteLangId),
            'data'=> array('value'=>$value)
        );
        FatUtility::dieJsonSuccess($json);
    }

    public function deleteSellerProductSpecialPrice()
    {
        $splPriceId = FatApp::getPostedData('splprice_id', FatUtility::VAR_INT, 0);
        if (1 > $splPriceId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $specialPriceRow = SellerProduct::getSellerProductSpecialPriceById($splPriceId);
        if (empty($specialPriceRow) || 1 > count($specialPriceRow)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Already_Deleted', $this->siteLangId));
        }
        $this->deleteSpecialPrice($splPriceId, $specialPriceRow['selprod_id']);
        $this->set('selprod_id', $specialPriceRow['selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Special_Price_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSpecialPriceRows()
    {
        $splpriceIdArr = FatApp::getPostedData('selprod_ids');
        $splpriceIds = FatUtility::int($splpriceIdArr);
        foreach ($splpriceIds as $splPriceId => $selProdId) {
            $specialPriceRow = SellerProduct::getSellerProductSpecialPriceById($splPriceId);
            $this->deleteSpecialPrice($splPriceId, $specialPriceRow['selprod_id']);
        }
        $this->set('selprod_id', $specialPriceRow['selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Special_Price_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function deleteSpecialPrice($splPriceId, $selProdId)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $sellerProdObj = new SellerProduct($selProdId);
        if (!$sellerProdObj->deleteSellerProductSpecialPrice($splPriceId, $selProdId, $userId)) {
            FatUtility::dieWithError(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
        }
        return true;
    }

    public function checkIfAvailableForInventory($productId)
    {
        $productId = FatUtility::int($productId);
        $userId = UserAuthentication::getLoggedUserId();
        if (0 == $productId) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $available = Product::availableForAddToStore($productId, $userId);
        if (!$available) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Inventory_for_all_possible_product_options_have_been_added._Please_access_the_shop_inventory_section_to_update', $this->siteLangId));
        }
        FatUtility::dieJsonSuccess(array());
    }
}
