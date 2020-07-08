<?php
require_once CONF_INSTALLATION_PATH . 'library/APIs/twitteroauth-master/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

class BuyerController extends BuyerBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $user = new User($userId);
        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'B';

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinSellerProducts();
        $srch->joinSellerProductGroup();
        $srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        //$srch->addBuyerOrdersCounts(date('Y-m-d',strtotime("-1 days")),date('Y-m-d'),'yesterdayOrder');
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS", null, '')));
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber(1);
        $srch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);

        $srch->addMultipleFields(
            array('order_id', 'order_user_id','op_selprod_id','op_is_batch','selprod_product_id','order_date_added', 'order_net_amount', 'op_invoice_number','totCombinedOrders as totOrders', 'op_selprod_title', 'op_product_name', 'op_product_type', 'op_status_id', 'op_id','op_qty','op_selprod_options', 'op_brand_name', 'op_shop_name','op_other_charges','op_unit_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name')
        );
        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);
        /* CommonHelper::printArray($orders); die; */

        /* $getPurchasedsrch = clone $srch;
        $getPurchasedsrch->addCondition('order_is_paid', '=', 1);
        $getPurchasedsrch->addfld('count(order_id) as totalPurchasedItems');
        $countPurchasedItemsRs = $getPurchasedsrch->getResultSet();
        $totalPurchasedItems = FatApp::getDb()->fetch($countPurchasedItemsRs, 'totalPurchasedItems'); */

        /* if(FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $totalFavouriteItems = UserFavorite::getUserFavouriteItemCount($userId, $this->siteLangId);
        }else{
            $totalFavouriteItems = UserWishList::getUserWishlistItemCount($userId);
        } */



        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
        }

        /* Orders Counts [*/
        $orderSrch = new OrderProductSearch($this->siteLangId, true, true);
        $orderSrch->doNotCalculateRecords();
        $orderSrch->doNotLimitRecords();
        /* $orderSrch->addBuyerOrdersCounts(date('Y-m-d',strtotime("-1 days")),date('Y-m-d',strtotime("-1 days")),'yesterdayOrder'); */
        $orderSrch->addBuyerOrdersCounts(false, false, 'pendingOrder');
        $completedOrderStatus = unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS", FatUtility::VAR_STRING, ''));
        if (!empty($completedOrderStatus)) {
            $orderSrch->addCondition('op_status_id', 'NOT IN', $completedOrderStatus);
        }
        $orderSrch->addGroupBy('order_user_id');
        $orderSrch->addCondition('order_user_id', '=', $userId);
        $orderSrch->addMultipleFields(array('pendingOrderCount'));
        $rs = $orderSrch->getResultSet();
        $ordersStats = FatApp::getDb()->fetch($rs);
        /* ]*/

        /* Unread Message Count [*/
        /*$threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        $totalMessageCount = $threadObj->getMessageCount($userId);*/
        /*]*/

        /*
        * Return Request Listing
        */
        $srchReturnReq = $this->orderReturnRequestObj();
        $srchReturnReq->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $srchReturnReq->getResultSet();
        $returnRequests = FatApp::getDb()->fetchAll($rs);

        /*
        * Cancellation Request Listing
        */
        $canSrch = $this->orderCancellationRequestObj();
        $canSrch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $canSrch->getResultSet();
        $cancellationRequests = FatApp::getDb()->fetchAll($rs);

        /*
        * Offers Listing
        */
        $offers = DiscountCoupons::getUserCoupons(UserAuthentication::getLoggedUserId(), $this->siteLangId);

        $txnObj = new Transactions();
        $txnsSummary = $txnObj->getTransactionSummary($userId, date('Y-m-d'));

        $this->set('offers', $offers);
        $this->set('data', $user->getProfileData());
        $this->set('orders', $orders);
        $this->set('returnRequests', $returnRequests);
        $this->set('cancellationRequests', $cancellationRequests);
        $this->set('OrderReturnRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->set('OrderCancelRequestStatusArr', OrderCancelRequest::getRequestStatusArr($this->siteLangId));
        $this->set('ordersCount', $srch->recordCount());
        $this->set('pendingOrderCount', FatUtility::int($ordersStats['pendingOrderCount']));
        $this->set('userBalance', User::getUserBalance($userId));
        $this->set('totalRewardPoints', UserRewardBreakup::rewardPointBalance($userId));
        $this->set('txnsSummary', $txnsSummary);
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render(true, true);
    }

    public function viewOrder($orderId, $opId = 0, $print = false)
    {
        if (!$orderId) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $opId = FatUtility::int($opId);
        if (0 < $opId) {
            $opOrderId = OrderProduct::getAttributesById($opId, 'op_order_id');
            if ($orderId != $opOrderId) {
                $message = Labels::getLabel('MSG_Invalid_Order', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                CommonHelper::redirectUserReferer();
            }
        }
        $primaryOrderDisplay = false;

        $orderObj = new Orders();
        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();
        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
        $userId = UserAuthentication::getLoggedUserId();

        $orderDetail = $orderObj->getOrderById($orderId, $this->siteLangId);
        if (!$orderDetail || ($orderDetail && $orderDetail['order_user_id'] != $userId)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $orderDetail['charges'] = $orderObj->getOrderProductChargesByOrderId($orderDetail['order_id']);

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        //$srch->joinShippingUsers();
        $srch->addOrderProductCharges();
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('order_id', '=', $orderId);


        if (0 < $opId) {
            if (true ===  MOBILE_APP_API_CALL) {
                $srch->joinTable(SelProdReview::DB_TBL, 'LEFT OUTER JOIN', 'o.order_id = spr.spreview_order_id and op.op_selprod_id = spr.spreview_selprod_id', 'spr');
                $srch->joinTable(SelProdRating::DB_TBL, 'LEFT OUTER JOIN', 'sprating.sprating_spreview_id = spr.spreview_id', 'sprating');
                $srch->addFld(array('*','IFNULL(ROUND(AVG(sprating_rating),2),0) as prod_rating'));
            }
            $srch->addCondition('op_id', '=', $opId);
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
            $primaryOrderDisplay = true;
        }

        if (true ===  MOBILE_APP_API_CALL) {
            $srch->joinTable(
                OrderReturnRequest::DB_TBL,
                'LEFT OUTER JOIN',
                'orr.orrequest_op_id = op.op_id',
                'orr'
            );
            $srch->joinTable(
                OrderCancelRequest::DB_TBL,
                'LEFT OUTER JOIN',
                'ocr.ocrequest_op_id = op.op_id',
                'ocr'
            );
            $srch->addFld(array('*','IFNULL(orrequest_id, 0) as return_request', 'IFNULL(ocrequest_id, 0) as cancel_request'));
        }

        $rs = $srch->getResultSet();

        $childOrderDetail = FatApp::getDb()->fetchAll($rs, 'op_id');
        foreach ($childOrderDetail as $opID => $val) {
            $childOrderDetail[$opID]['charges'] = $orderDetail['charges'][$opID];
        }

        if ($opId > 0) {
            $childOrderDetail = array_shift($childOrderDetail);
        }

        if (empty($childOrderDetail) || 1 > count($childOrderDetail)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $address = $orderObj->getOrderAddresses($orderDetail['order_id']);
        $orderDetail['billingAddress'] = $address[Orders::BILLING_ADDRESS_TYPE];
        $orderDetail['shippingAddress'] = (!empty($address[Orders::SHIPPING_ADDRESS_TYPE]))?$address[Orders::SHIPPING_ADDRESS_TYPE]:array();
        if ($opId > 0) {
            $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("op_id"=>$childOrderDetail['op_id']));
        } else {
            $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("order_id"=>$orderDetail['order_id']));
            $payments = $orderObj->getOrderPayments(array("order_id"=>$orderDetail['order_id']));
            if (true ===  MOBILE_APP_API_CALL) {
                $payments = array_values($payments);
            }
            $orderDetail['payments'] = $payments;
        }

        $digitalDownloads = array();
        if ($opId > 0 && $childOrderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $digitalDownloads = Orders::getOrderProductDigitalDownloads($childOrderDetail['op_id']);
        }

        $digitalDownloadLinks = array();
        if ($opId > 0 && $childOrderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $digitalDownloadLinks = Orders::getOrderProductDigitalDownloadLinks($childOrderDetail['op_id']);
        }
        $productType = !empty($childOrderDetail['selprod_product_id']) ? Product::getAttributesById($childOrderDetail['selprod_product_id'], 'product_type') : 0;
        // CommonHelper::printArray($orderDetail, true);
        $this->set('orderDetail', $orderDetail);
        $this->set('childOrderDetail', $childOrderDetail);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('primaryOrder', $primaryOrderDisplay);
        $this->set('digitalDownloads', $digitalDownloads);
        $this->set('digitalDownloadLinks', $digitalDownloadLinks);
        $this->set('productType', $productType);
        $this->set('languages', Language::getAllNames());
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));


        $urlParts = array($orderId,$opId);
        $this->set('urlParts', $urlParts);
        if ($print !== false) {
            $print = true;
        }
        $this->set('print', $print);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('opId', $opId);
        }

        $this->_template->render();
    }

    public function downloadDigitalFile($aFileId, $recordId = 0)
    {
        $aFileId = FatUtility::int($aFileId);
        $recordId = FatUtility::int($recordId);
        $userId = UserAuthentication::getLoggedUserId();

        if (1 > $aFileId || 1 > $recordId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'MyDownloads'));
        }

        $digitalDownloads = Orders::getOrderProductDigitalDownloads($recordId, $aFileId);

        if ($digitalDownloads == false || empty($digitalDownloads) || $digitalDownloads[0]['order_user_id']!= $userId) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'MyDownloads'));
        }

        $res = array_shift($digitalDownloads);

        if ($res == false || !$res['downloadable']) {
            Message::addErrorMessage(Labels::getLabel("MSG_Not_available_to_download", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'MyDownloads'));
        }

        if (!file_exists(CONF_UPLOADS_PATH.$res['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'MyDownloads'));
        }

        $fileName = isset($res['afile_physical_path']) ? $res['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $res['afile_name']);
        AttachedFile::updateDownloadCount($res['afile_id']);
    }

    public function downloadDigitalProductFromLink($linkId, $opId)
    {
        $linkId = FatUtility::int($linkId);
        $opId = FatUtility::int($opId);
        $userId = UserAuthentication::getLoggedUserId();

        if (1 > $linkId || 1 > $opId) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        $digitalDownloadLinks = Orders::getOrderProductDigitalDownloadLinks($opId, $linkId);
        if ($digitalDownloadLinks == false || empty($digitalDownloadLinks) || $digitalDownloadLinks[0]['order_user_id']!= $userId) {
            $message = Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId);
            LibHelper::dieJsonError($message);
        }
        $res = array_shift($digitalDownloadLinks);
        if ($res == false || !$res['downloadable']) {
            $message = Labels::getLabel("MSG_Link_is_not_available_to_download", $this->siteLangId);
            LibHelper::dieJsonError($message);
        }
        OrderProductDigitalLinks::updateDownloadCount($linkId);
        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('data', ['link' => trim($res['opddl_downloadable_link'])]);
            $this->_template->render();
        }
        $message = Labels::getLabel("MSG_Successfully_redirected", $this->siteLangId);
        FatUtility::dieJsonSuccess($message);
    }

    /* public function myAddresses(){
    $this->_template->render(true,false);
    } */

    /* public function searchAddresses(){
    $addresses = UserAddress::getUserAddresses( UserAuthentication::getLoggedUserId(), $this->siteLangId );
    if($addresses){
    $this->set('addresses',$addresses);
    } else {
    $this->set('noRecordsHtml',$this->_template->render( false, false, '_partial/no-record-found.php', true));
    }
    $this->_template->render(false,false);
    } */

    /* public function addAddressForm($ua_id){
    $ua_id =  FatUtility::int($ua_id);
    $addressFrm = $this->getUserAddressForm($this->siteLangId);

    $stateId = 0;

    if($ua_id > 0){
    $data =  UserAddress::getUserAddresses( UserAuthentication::getLoggedUserId(), $this->siteLangId, 0, $ua_id );
    if ($data === false) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request',$this->siteLangId));
                FatUtility::dieJsonError( Message::getHtml() );
    }
    $stateId =  $data['ua_state_id'];
    $addressFrm->fill($data);
    }


    $this->set('stateId',$stateId);
    $this->set('addressFrm',$addressFrm);
    $this->_template->render(false,false);
    } */

    public function orders()
    {
        $frmOrderSrch = $this->getOrderSearchForm($this->siteLangId);
        $this->set('frmOrderSrch', $frmOrderSrch);
        $this->_template->render(true, true);
    }

    public function orderSearchListing()
    {
        $frm = $this->getOrderSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $user_id = UserAuthentication::getLoggedUserId();

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->joinTable(
            OrderReturnRequest::DB_TBL,
            'LEFT OUTER JOIN',
            'orr.orrequest_op_id = op.op_id',
            'orr'
        );
        $srch->joinTable(
            OrderCancelRequest::DB_TBL,
            'LEFT OUTER JOIN',
            'ocr.ocrequest_op_id = op.op_id',
            'ocr'
        );

        if (true ===  MOBILE_APP_API_CALL) {
            $srch->joinSellerProducts();
            $srch->addfld('selprod_product_id');
        }

        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->joinPaymentMethod();
        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(
            array('order_id', 'order_user_id', 'order_date_added', 'order_net_amount', 'op_invoice_number',
            'totCombinedOrders as totOrders', 'op_selprod_id', 'op_selprod_title', 'op_product_name', 'op_id','op_other_charges','op_unit_price',
            'op_qty', 'op_selprod_options', 'op_brand_name', 'op_shop_name', 'op_status_id', 'op_product_type', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name','order_pmethod_id','order_status','pmethod_name', 'IFNULL(orrequest_id, 0) as return_request', 'IFNULL(ocrequest_id, 0) as cancel_request', 'orderstatus_color_code')
        );

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');
        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")))) {
            $srch->addStatusCondition($op_status_id);
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
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
            $charges = $oObj->getOrderProductChargesArr($order['op_id'], MOBILE_APP_API_CALL);
            $order['charges'] = $charges;
        }

        $this->set('orders', $orders);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function MyDownloads()
    {
        $this->_template->render(true, true);
    }

    public function downloadSearch()
    {
        $frm = $this->getOrderProductDownloadSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinOrderUser();
        $srch->joinDigitalDownloads();
        $srch->addDigitalDownloadCondition();
        $srch->addMultipleFields(array('op_id', 'op_selprod_id', 'op_invoice_number', 'order_user_id', 'op_product_type', 'order_date_added', 'op_qty', 'op_status_id', 'op_selprod_max_download_times', 'op_selprod_download_validity_in_days', 'opa.*'));
        if (true ===  MOBILE_APP_API_CALL) {
            $srch->joinSellerProducts($this->siteLangId);
            $srch->addFld(array('selprod_product_id'));
        }
        $srch->setPageNumber($page);
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addOrder('order_date_added', 'desc');
        $srch->addOrder('afile_id', 'asc');
        $srch->setPageSize($pagesize);

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->addKeywordSearch($keyword);
            $frm->fill(array('keyword' => $keyword ));
        }

        $rs = $srch->getResultSet();
        $downloads = FatApp::getDb()->fetchAll($rs);

        $digitalDownloads = Orders::digitalDownloadFormat($downloads);

        $this->set('frmSrch', $frm);
        $this->set('digitalDownloads', $digitalDownloads);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('languages', Language::getAllNames());

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false);
    }

    public function downloadLinksSearch()
    {
        $frm = $this->getOrderProductDownloadSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinOrderUser();
        $srch->joinDigitalDownloadLinks();
        $srch->addDigitalDownloadCondition();
        $srch->joinSellerProducts();
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');
        $srch->addMultipleFields(array('op_id','op_invoice_number','order_user_id','op_product_type','order_date_added','op_qty','op_status_id','op_selprod_max_download_times', 'op_selprod_id','op_selprod_id', 'product_image_updated_on', 'selprod_product_id','op_selprod_download_validity_in_days','opd.*'));
        $srch->setPageNumber($page);
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addOrder('order_date_added', 'desc');
        $srch->addOrder('opddl_link_id', 'asc');
        $srch->setPageSize($pagesize);
        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->addKeywordSearch($keyword);
            $frm->fill(array('keyword' => $keyword ));
        }

        $rs = $srch->getResultSet();
        $downloads = FatApp::getDb()->fetchAll($rs);

        $digitalDownloadLinks = Orders::digitalDownloadLinksFormat($downloads);

        $this->set('frmSrch', $frm);
        $this->set('digitalDownloadLinks', $digitalDownloadLinks);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('languages', Language::getAllNames());

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false);
    }

    public function orderCancellationRequest($op_id)
    {
        $op_id = FatUtility::int($op_id);

        $user_id = UserAuthentication::getLoggedUserId();
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(array('op_status_id', 'op_id','op_product_type'));
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
            // CommonHelper::redirectUserReferer();
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderCancellationRequests'));
        }

        $oReturnRequestSrch = new OrderReturnRequestSearch();
        $oReturnRequestSrch->doNotCalculateRecords();
        $oReturnRequestSrch->doNotLimitRecords();
        $oReturnRequestSrch->addCondition('orrequest_op_id', '=', $opDetail['op_id']);
        $oReturnRequestSrch->addCondition('orrequest_status', '!=', OrderReturnRequest::RETURN_REQUEST_STATUS_CANCELLED);
        $oReturnRequestRs = $oReturnRequestSrch->getResultSet();

        if (FatApp::getDb()->fetch($oReturnRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Already_submitted_return_request', $this->siteLangId));
            // CommonHelper::redirectUserReferer();
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderCancellationRequests'));
        }

        if ($opDetail["op_product_type"] == Product::PRODUCT_TYPE_DIGITAL) {
            if (!in_array($opDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses(true))) {
                Message::addErrorMessage(Labels::getLabel('MSG_Order_Cancellation_cannot_placed', $this->siteLangId));
                // CommonHelper::redirectUserReferer();
                FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderCancellationRequests'));
            }
        } else {
            if (!in_array($opDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses())) {
                Message::addErrorMessage(Labels::getLabel('MSG_Order_Cancellation_cannot_placed', $this->siteLangId));
                // CommonHelper::redirectUserReferer();
                FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderCancellationRequests'));
            }
        }

        if (false !== OrderCancelRequest::getCancelRequestById($opDetail['op_id'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_You_have_already_sent_the_cancellation_request_for_this_order', $this->siteLangId));
            // CommonHelper::redirectUserReferer();
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderCancellationRequests'));
        }

        $frm = $this->getOrderCancelRequestForm($this->siteLangId);
        $frm->fill(array('op_id' => $opDetail['op_id'] ));
        $this->set('frmOrderCancel', $frm);
        $this->_template->render(true, true);
    }

    public function orderCancellationReasons()
    {
        $orderCancelReasonsArr = OrderCancelReason::getOrderCancelReasonArr($this->siteLangId);
        $count = 0;
        foreach ($orderCancelReasonsArr as $key => $val) {
            $cancelReasonsArr[$count]['key']= $key;
            $cancelReasonsArr[$count]['value']= $val;
            $count++;
        }
        $this->set('data', array('reasons' =>$cancelReasonsArr));
        $this->_template->render();
    }

    public function orderReturnRequestsReasons($op_id)
    {
        if (1 > FatUtility::int($op_id)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $user_id = UserAuthentication::getLoggedUserId();
        $orderReturnReasonsArr = OrderReturnReason::getOrderReturnReasonArr($this->siteLangId);
        $count = 0;
        foreach ($orderReturnReasonsArr as $key => $val) {
            $returnReasonsArr[$count]['key']= $key;
            $returnReasonsArr[$count]['value']= $val;
            $count++;
        }
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(array('op_status_id', 'op_id', 'op_qty','op_product_type'));
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $this->set('data', array('reasons' => $returnReasonsArr));
        $this->_template->render();
    }

    public function setupOrderCancelRequest()
    {
        $frm = $this->getOrderCancelRequestForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(current($frm->getValidationErrors()));
            }
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $op_id = FatUtility::int($post['op_id']);

        $user_id = UserAuthentication::getLoggedUserId();
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addOrder("op_id", "DESC");
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            $message = Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($opDetail["op_product_type"] == Product::PRODUCT_TYPE_DIGITAL) {
            if (!in_array($opDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses(true))) {
                $message = Labels::getLabel('MSG_Order_Cancellation_cannot_placed', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieWithError(Message::getHtml());
            }
        } else {
            if (!in_array($opDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses())) {
                $message = Labels::getLabel('MSG_Order_Cancellation_cannot_placed', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        if (!in_array($opDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses())) {
            $message = Labels::getLabel('MSG_Order_Cancellation_cannot_placed', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $ocRequestSrch = new OrderCancelRequestSearch();
        $ocRequestSrch->doNotCalculateRecords();
        $ocRequestSrch->doNotLimitRecords();
        $ocRequestSrch->addCondition('ocrequest_op_id', '=', $opDetail['op_id']);
        $ocRequestRs = $ocRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($ocRequestRs)) {
            $message = Labels::getLabel('MSG_You_have_already_sent_the_cancellation_request_for_this_order', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $dataToSave = array(
        'ocrequest_user_id'    =>    $user_id,
        'ocrequest_op_id'    =>    $opDetail['op_id'],
        'ocrequest_ocreason_id'    =>    FatUtility::int($post['ocrequest_ocreason_id']),
        'ocrequest_message'        =>    $post['ocrequest_message'],
        'ocrequest_date'        =>    date('Y-m-d H:i:s'),
        'ocrequest_status'        =>    OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING
        );

        $oCRequestObj = new OrderCancelRequest();
        $oCRequestObj->assignValues($dataToSave);


        if (!$oCRequestObj->save()) {
            Message::addErrorMessage($oCRequestObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $ocrequest_id = $oCRequestObj->getMainTableRecordId();
        if (!$ocrequest_id) {
            $message = Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $emailObj = new EmailHandler();
        if (!$emailObj->sendOrderCancellationNotification($ocrequest_id, $this->siteLangId)) {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($emailObj->getError());
            }
            Message::addErrorMessage($emailObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* send notification to admin */
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_ORDER_CANCELATION,
        'notification_record_id' => $oCRequestObj->getMainTableRecordId(),
        'notification_user_id' => $user_id,
        'notification_label_key' => Notification::ORDER_CANCELLATION_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel('MSG_NOTIFICATION_COULD_NOT_BE_SENT', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($emailObj->getError());
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $msg = Labels::getLabel('MSG_Your_cancellation_request_submitted', $this->siteLangId);
        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('msg', $msg);
            $this->_template->render();
        }

        Message::addMessage($msg);
        FatUtility::dieJsonSuccess(Message::getHtml());
        //$this->_template->render( false, false, 'json-success.php' );
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
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = $this->orderCancellationRequestObj();
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        if (true ===  MOBILE_APP_API_CALL) {
            $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'selprod_id = op_selprod_id');
            $srch->joinTable(SellerProduct::DB_LANG_TBL, 'INNER JOIN', 'selprod_id = selprodlang_selprod_id AND selprodlang_lang_id = '.$this->siteLangId);
            $srch->addFld(array('selprod_product_id', 'selprod_title'));
        }

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

        /* $ocrequest_status = $post['ocrequest_status'];
        if( !empty( $ocrequest_status ) ){ */
        $ocrequest_status = FatApp::getPostedData('ocrequest_status', null, '-1');
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

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false);
    }

    private function orderCancellationRequestObj()
    {
        $srch = new OrderCancelRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrderCancelReasons();
        $srch->joinOrders();
        $srch->addCondition('ocrequest_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addMultipleFields(array( 'ocrequest_id', 'ocrequest_date', 'ocrequest_status', 'order_id', 'op_invoice_number', 'IFNULL(ocreason_title, ocreason_identifier) as ocreason_title', 'ocrequest_message', 'op_id', 'op_is_batch', 'op_selprod_id', 'order_id', 'op_selprod_title'));
        $srch->addOrder('ocrequest_date', 'DESC');
        return $srch;
    }

    public function orderReturnRequests()
    {
        $frm = $this->getOrderReturnRequestsSearchForm($this->siteLangId);
        $this->set('frmOrderReturnRequestsSrch', $frm);
        $this->_template->render(true, true);
    }

    public function orderReturnRequestSearch()
    {
        $frm = $this->getOrderReturnRequestsSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $srch = $this->orderReturnRequestObj();
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addMultipleFields(
            array( 'orrequest_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type', 'orrequest_reference', 'orrequest_date', 'orrequest_status',
            'op_invoice_number', 'op_selprod_title', 'op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model')
        );

        if (true ===  MOBILE_APP_API_CALL) {
            $srch->joinTable(OrderReturnReason::DB_TBL, 'LEFT JOIN', 'orrequest_returnreason_id = orreason_id');
            $srch->joinTable(OrderReturnReason::DB_TBL_LANG, 'LEFT JOIN', 'orreasonlang_orreason_id = orreason_id AND orreasonlang_lang_id  = '.$this->siteLangId);
            $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'selprod_id = op_selprod_id');
            $srch->joinTable(SellerProduct::DB_LANG_TBL, 'INNER JOIN', 'selprod_id = selprodlang_selprod_id AND selprodlang_lang_id = '.$this->siteLangId);
            $srch->addFld(array('selprod_product_id', 'selprod_title', 'IFNULL(orreason_title, orreason_identifier) as requestReason'));
        }

        $srch->addOrder('orrequest_date', 'DESC');

        $keyword = $post['keyword'];
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('op_invoice_number', '=', $keyword);
            $cnd->attachCondition('op_selprod_title', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_product_name', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_brand_name', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_selprod_options', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_selprod_sku', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('op_product_model', 'LIKE', '%'.$keyword.'%', 'OR');
            $cnd->attachCondition('orrequest_reference', 'LIKE', '%'.$keyword.'%', 'OR');
        }

        $orrequest_status = FatApp::getPostedData('orrequest_status', null, '-1');
        if ($orrequest_status > -1) {
            $orrequest_status = FatUtility::int($orrequest_status);
            $srch->addCondition('orrequest_status', '=', $orrequest_status);
        }

        $orrequest_date_from = $post['orrequest_date_from'];
        if (!empty($orrequest_date_from)) {
            $srch->addCondition('orrequest_date', '>=', $orrequest_date_from. ' 00:00:00');
        }

        $orrequest_date_to = $post['orrequest_date_to'];
        if (!empty($orrequest_date_to)) {
            $srch->addCondition('orrequest_date', '<=', $orrequest_date_to. ' 23:59:59');
        }

        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        $this->set('sellerPage', false);
        $this->set('buyerPage', true);

        $this->set('requests', $requests);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('returnRequestTypeArr', OrderReturnRequest::getRequestTypeArr($this->siteLangId));
        $this->set('OrderReturnRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function orderReturnRequestObj()
    {
        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->addCondition('orrequest_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addMultipleFields(
            array( 'orrequest_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type', 'orrequest_reference', 'orrequest_date', 'orrequest_status',
            'op_invoice_number', 'op_selprod_title', 'op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_id', 'op_is_batch', 'op_selprod_id', 'order_id')
        );
        $srch->addOrder('orrequest_date', 'DESC');
        return $srch;
    }

    public function viewOrderReturnRequest($orrequest_id, $print = false)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('orrequest_user_id', '=', $user_id);
        $srch->joinOrderProducts();
        $srch->joinOrderProductSettings();
        $srch->joinOrders();
        //$srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();
        $srch->addOrderProductCharges();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array( 'orrequest_id','orrequest_op_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type',
            'orrequest_date', 'orrequest_status', 'orrequest_reference', 'op_invoice_number', 'op_selprod_title', 'op_product_name',
            'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model','op_qty',
            'op_unit_price', 'op_selprod_user_id', 'IFNULL(orreason_title, orreason_identifier) as orreason_title',
            'op_shop_id', 'op_shop_name', 'op_shop_owner_name', 'order_tax_charged','op_other_charges','op_refund_amount','op_commission_percentage','op_affiliate_commission_percentage','op_commission_include_tax','op_commission_include_shipping','op_free_ship_upto','op_actual_shipping_charges')
        );
        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);
        if (!$request) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderReturnRequests'));
        }

        $oObj = new Orders();
        $charges = $oObj->getOrderProductChargesArr($request['orrequest_op_id']);
        $request['charges'] = $charges;

        $sellerUserObj = new User($request['op_selprod_user_id']);
        $vendorReturnAddress = $sellerUserObj->getUserReturnAddress($this->siteLangId);

        $returnRequestMsgsSrchForm = $this->getOrderReturnRequestMessageSearchForm($this->siteLangId);
        $returnRequestMsgsSrchForm->fill(array( 'orrequest_id' => $request['orrequest_id'] ));

        $frm = $this->getOrderReturnRequestMessageForm($this->siteLangId);
        $frm->fill(array( 'orrmsg_orrequest_id' => $request['orrequest_id'] ));
        $this->set('frmMsg', $frm);

        $canEscalateRequest = false;
        $canWithdrawRequest = false;
        /* if( $request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING ){
        $canEscalateRequest = true;
        } */

        if (($request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING) || $request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED) {
            $canWithdrawRequest = true;
        }
        if ($attachedFile = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $orrequest_id)) {
            $this->set('attachedFile', $attachedFile);
        }
        $this->set('canEscalateRequest', $canEscalateRequest);
        $this->set('canWithdrawRequest', $canWithdrawRequest);
        $this->set('returnRequestMsgsSrchForm', $returnRequestMsgsSrchForm);
        $this->set('request', $request);
        $this->set('vendorReturnAddress', $vendorReturnAddress);
        $this->set('returnRequestTypeArr', OrderReturnRequest::getRequestTypeArr($this->siteLangId));
        $this->set('requestRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->set('logged_user_name', UserAuthentication::getLoggedUserAttribute('user_name'));
        $this->set('logged_user_id', UserAuthentication::getLoggedUserId());

        if ($print) {
            $print = true;
        }
        $this->set('print', $print);
        $urlParts = array_filter(FatApp::getParameters());
        $this->set('urlParts', $urlParts);

        $this->_template->render();
    }

    public function downloadAttachedFileForReturn($recordId, $recordSubid =0)
    {
        $recordId = FatUtility::int($recordId);

        if (1 > $recordId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $recordId, $recordSubid);

        if (false == $file_row) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

    public function WithdrawOrderReturnRequest($orrequest_id)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();

        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('orrequest_user_id', '=', $user_id);
        $cnd = $srch->addCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING);
        $cnd->attachCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'op_id', 'order_language_id'));
        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);
        if (!$request) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'viewOrderReturnRequest', array($orrequest_id)));
        }

        $orrObj = new OrderReturnRequest();
        if (!$orrObj->withdrawRequest($request['orrequest_id'], $user_id, $this->siteLangId, $request['op_id'], $request['order_language_id'])) {
            $message = Labels::getLabel($orrObj->getError(), $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'viewOrderReturnRequest', array($orrequest_id)));
        }

        /* email notification handling[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification($request['orrequest_id'], $this->siteLangId)) {
            $message = Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }
        /* ] */

        //send notification to admin
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_ORDER_RETURN_REQUEST,
        'notification_record_id' => $request['orrequest_id'],
        'notification_user_id' => UserAuthentication::getLoggedUserId(),
        'notification_label_key' => Notification::RETURN_REQUEST_STATUS_CHANGE_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel('MSG_NOTIFICATION_COULD_NOT_BE_SENT', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        Message::addMessage(Labels::getLabel('MSG_Request_Withdrawn', $this->siteLangId));
        FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'viewOrderReturnRequest', array($orrequest_id)));
    }

    /* public function orderReturnRequestMessageSearch(){
    $frm = $this->getOrderReturnRequestMessageSearchForm( $this->siteLangId );
    $post = $frm->getFormDataFromArray( FatApp::getPostedData() );
    $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
    $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
    $user_id = UserAuthentication::getLoggedUserId();

    $orrequest_id = isset($post['orrequest_id']) ? FatUtility::int($post['orrequest_id']) : 0;

    $srch = new OrderReturnRequestMessageSearch( $this->siteLangId );
    $srch->joinOrderReturnRequests();
    $srch->joinMessageUser();
    $srch->addCondition( 'orrmsg_orrequest_id', '=', $orrequest_id );
    //$srch->addCondition( 'orrequest_user_id', '=', $user_id );
    $srch->setPageNumber($page);
    $srch->setPageSize($pageSize);
    $srch->addOrder('orrmsg_id','DESC');
    $srch->addMultipleFields( array( 'orrmsg_from_user_id', 'orrmsg_msg',
    'orrmsg_date', 'msg_user.user_name as msg_user_name', 'orrequest_status' ) );

    $rs = $srch->getResultSet();
    $messagesList = FatApp::getDb()->fetchAll($rs);

    $this->set( 'messagesList', $messagesList );
    $this->set('page', $page);
    $this->set('pageCount', $srch->pages());
    $this->set('postedData', $post);

    $startRecord = ($page-1)*$pageSize + 1 ;
    $endRecord = $page * $pageSize;
    $totalRecords = $srch->recordCount();
    if ($totalRecords < $endRecord) { $endRecord = $totalRecords; }
    $json['totalRecords'] = $totalRecords;
    $json['startRecord'] = $startRecord;
    $json['endRecord'] = $endRecord;
    $json['html'] = $this->_template->render( false, false, 'buyer/order-return-request-messages-list.php', true);
    $json['loadMoreBtnHtml'] = $this->_template->render( false, false, 'buyer/order-return-request-messages-list-load-more-btn.php', true);
    FatUtility::dieJsonSuccess($json);
    } */

    public function setUpReturnOrderRequestMessage()
    {
        $orrmsg_orrequest_id = FatApp::getPostedData('orrmsg_orrequest_id', null, '0');

        $frm = $this->getOrderReturnRequestMessageForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            $message = current($frm->getValidationErrors());
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $orrmsg_orrequest_id = FatUtility::int($orrmsg_orrequest_id);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->addCondition('orrequest_id', '=', $orrmsg_orrequest_id);
        $srch->addCondition('orrequest_user_id', '=', $user_id);
        $srch->joinOrderProducts();
        $srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'orrequest_status', ));
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED || $requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_WITHDRAWN) {
            $message = Labels::getLabel('MSG_Message_cannot_be_posted_now,_as_order_is_refunded_or_withdrawn.', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
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
            $message = $oReturnRequestMsgObj->getError();
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $orrmsg_id = $oReturnRequestMsgObj->getMainTableRecordId();
        if (!$orrmsg_id) {
            $message = Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendReturnRequestMessageNotification($orrmsg_id, $this->siteLangId)) {
            $message = $emailNotificationObj->getError();
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        //send notification to admin
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_ORDER_RETURN_REQUEST,
        'notification_record_id' => $requestRow['orrequest_id'],
        'notification_user_id' => UserAuthentication::getLoggedUserId(),
        'notification_label_key' => Notification::ORDER_RETURNED_REQUEST_MESSAGE_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel('MSG_NOTIFICATION_COULD_NOT_BE_SENT', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('orrmsg_orrequest_id', $orrmsg_orrequest_id);
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->siteLangId));
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function orderFeedback($opId = 0)
    {
        $opId = FatUtility::int($opId);
        if (1 > $opId) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $userId = UserAuthentication::getLoggedUserId();

        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $opId);
        $srch->addOrder("op_id", "DESC");
        /* $srch->addMultipleFields( array('op_status_id', 'op_selprod_user_id', 'op_selprod_code','op_order_id','op_selprod_id','op_is_batch') ); */
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail) || !(FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0))) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        if (!in_array($opDetail["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
            $statuses = SelProdReview::getBuyerAllowedOrderReviewStatuses();
            $statusNames = array();

            foreach ($statuses as $status) {
                $statusNames[] = $orderStatuses[$status];
            }

            Message::addErrorMessage(sprintf(Labels::getLabel('MSG_Feedback_can_be_placed_', $this->siteLangId), implode(',', $statusNames)));
            CommonHelper::redirectUserReferer();
        }

        if ($opDetail['op_is_batch']) {
            $selProdIdArr = explode('|', $opDetail['op_batch_selprod_id']);
            $selProdId = array_shift($selProdIdArr);
        } else {
            $selProdId = $opDetail['op_selprod_id'];
        }

        if (1 > FatUtility::int($selProdId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $oFeedbackSrch = new SelProdReviewSearch();
        $oFeedbackSrch->doNotCalculateRecords();
        $oFeedbackSrch->doNotLimitRecords();
        $oFeedbackSrch->addCondition('spreview_postedby_user_id', '=', $userId);
        $oFeedbackSrch->addCondition('spreview_order_id', '=', $opDetail['op_order_id']);
        $oFeedbackSrch->addCondition('spreview_selprod_id', '=', $selProdId);
        $oFeedbackRs = $oFeedbackSrch->getResultSet();
        if (FatApp::getDb()->fetch($oFeedbackRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Already_submitted_order_feedback', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $canSubmitFeedback = Orders::canSubmitFeedback($userId, $opDetail['op_order_id'], $selProdId);

        if (!$canSubmitFeedback) {
            Message::addErrorMessage(Labels::getLabel('MSG_Already_submitted_order_feedback', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }


        $frm = $this->getOrderFeedbackForm($opId, $this->siteLangId);
        $this->set('frm', $frm);
        $this->set('opDetail', $opDetail);
        $this->_template->addCss(array('css/star-rating.css'));
        $this->_template->addJs(array('js/jquery.barrating.min.js'));
        $this->_template->render(true, true);
    }

    public function setupOrderFeedback()
    {
        $opId = FatApp::getPostedData('op_id', FatUtility::VAR_INT, 0);
        if (1 > $opId) {
            $message = Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $userId = UserAuthentication::getLoggedUserId();

        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $opId);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(array('op_status_id', 'op_selprod_user_id', 'op_selprod_code','op_order_id','op_selprod_id','op_is_batch','op_batch_selprod_id'));
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);

        if (!$opDetail || CommonHelper::isMultidimArray($opDetail) || !(FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0))) {
            $message = Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        if ($opDetail['op_is_batch']) {
            $selProdIdArr = explode('|', $opDetail['op_batch_selprod_id']);
            $selProdId = array_shift($selProdIdArr);
        } else {
            $selProdId = $opDetail['op_selprod_id'];
        }

        if (1 > FatUtility::int($selProdId)) {
            $message = Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        if (!in_array($opDetail["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
            $statuses = SelProdReview::getBuyerAllowedOrderReviewStatuses();
            $statusNames = array();

            foreach ($statuses as $status) {
                $statusNames[] = $orderStatuses[$status];
            }
            $message = sprintf(Labels::getLabel('MSG_Feedback_can_be_placed_', $this->siteLangId), implode(',', $statusNames));
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }


        /* checking Abusive Words[ */
        $enteredAbusiveWordsArr = array();
        if (!Abusive::validateContent(FatApp::getPostedData('spreview_description', FatUtility::VAR_STRING, ''), $enteredAbusiveWordsArr)) {
            if (!empty($enteredAbusiveWordsArr)) {
                $errStr =  Labels::getLabel("LBL_Word_{abusiveword}_is/are_not_allowed_to_post", $this->siteLangId);
                $errStr = str_replace("{abusiveword}", '"'.implode(", ", $enteredAbusiveWordsArr).'"', $errStr);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($errStr);
                }
                Message::addErrorMessage($errStr);
                CommonHelper::redirectUserReferer();
                //FatUtility::dieWithError( Message::getHtml() );
            }
        }
        /* ] */

        $sellerId = $opDetail['op_selprod_user_id'];

        /* $selProdDetail = SellerProduct::getAttributesById($selProdId);
        $productId = FatUtility::int($selProdDetail['selprod_product_id']); */

        $op_selprod_code = explode('|', $opDetail['op_selprod_code']);
        $selProdCode = array_shift($op_selprod_code);
        $selProdCodeArr = explode('_', $selProdCode);
        $productId = array_shift($selProdCodeArr);


        $canSubmitFeedback = Orders::canSubmitFeedback($userId, $opDetail['op_order_id'], $selProdId);

        if (!$canSubmitFeedback) {
            $message = Labels::getLabel('MSG_Already_submitted_order_feedback', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $frm = $this->getOrderFeedbackForm($opId, $this->siteLangId);
        $post = FatApp::getPostedData();

        if (false ===  MOBILE_APP_API_CALL) {
            $post = $frm->getFormDataFromArray($post);
            if (false === $post) {
                Message::addErrorMessage($frm->getValidationErrors());
                $this->orderFeedback($opId);
                return true;
            }
        }

        $post['spreview_seller_user_id'] = $sellerId;
        $post['spreview_order_id'] = $opDetail['op_order_id'];
        $post['spreview_product_id'] = $productId ;
        $post['spreview_selprod_id'] = $selProdId;
        $post['spreview_selprod_code'] = $selProdCode;
        $post['spreview_postedby_user_id'] = $userId;
        $post['spreview_posted_on'] = date('Y-m-d H:i:s');
        $post['spreview_lang_id'] = $this->siteLangId;
        $post['spreview_status'] = FatApp::getConfig('CONF_DEFAULT_REVIEW_STATUS', FatUtility::VAR_INT, 0);

        $selProdReview = new SelProdReview();

        $selProdReview->assignValues($post);

        $db = FatApp::getDb();
        $db->startTransaction();

        if (!$selProdReview->save()) {
            $db->rollbackTransaction();
            $this->orderFeedback($opId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($selProdReview->getError());
            }
            Message::addErrorMessage($selProdReview->getError());
            return true;
        }
        $spreviewId = $selProdReview->getMainTableRecordId();
        $ratingsPosted = FatApp::getPostedData('review_rating');
        $ratingAspects = SelProdRating::getRatingAspectsArr($this->siteLangId);
        foreach ($ratingsPosted as $ratingAspect => $ratingValue) {
            if (isset($ratingAspects[$ratingAspect])) {
                $selProdRating = new SelProdRating();
                $ratingRow = array('sprating_spreview_id' => $spreviewId, 'sprating_rating_type'=> $ratingAspect ,'sprating_rating' => $ratingValue);
                $selProdRating->assignValues($ratingRow);
                if (!$selProdRating->save()) {
                    Message::addErrorMessage($selProdRating->getError());
                    $db->rollbackTransaction();
                    $this->orderFeedback($opId);
                    if (true ===  MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($selProdRating->getError());
                    }
                    return true;
                }
            }
        }
        $db->commitTransaction();
        $emailNotificationObj = new EmailHandler();
        if ($post['spreview_status'] == SelProdReview::STATUS_APPROVED) {
            $emailNotificationObj->sendBuyerReviewStatusUpdatedNotification($spreviewId, $this->siteLangId);
        }
        $reviewTitle = $post['spreview_title'];
        $reviewTitleArr = preg_split("/[\s,-]+/", $reviewTitle);
        $reviewDesc = $post['spreview_description'];
        $reviewDescArr = preg_split("/[\s,-]+/", $reviewDesc);

        $abusiveWords = Abusive::getAbusiveWords();
        if (!empty(array_intersect($abusiveWords, $reviewTitleArr)) || !empty(array_intersect($abusiveWords, $reviewDescArr))) {
            $emailNotificationObj->sendAdminAbusiveReviewNotification($spreviewId, $this->siteLangId);

            //send notification to admin
            $notificationData = array(
            'notification_record_type' => Notification::TYPE_PRODUCT_REVIEW,
            'notification_record_id' => $spreviewId,
            'notification_user_id' => UserAuthentication::getLoggedUserId(),
            'notification_label_key' => Notification::ABUSIVE_REVIEW_POSTED_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
            );

            if (!Notification::saveNotifications($notificationData)) {
                $message = Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId);
                Message::addErrorMessage($message);
                $this->orderFeedback($opId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                return true;
            }
        } else {
            $notificationData = array(
            'notification_record_type' => Notification::TYPE_PRODUCT_REVIEW,
            'notification_record_id' => $spreviewId,
            'notification_user_id' => UserAuthentication::getLoggedUserId(),
            'notification_label_key' => Notification::PRODUCT_REVIEW_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
            );

            if (!Notification::saveNotifications($notificationData)) {
                $message = Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId);
                Message::addErrorMessage($message);
                $this->orderFeedback($opId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                return true;
            }
        }
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        Message::addMessage(Labels::getLabel('MSG_Feedback_Submitted_Successfully', $this->siteLangId));
        FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'Orders'));
    }

    public function orderReturnRequest($op_id)
    {
        $op_id = FatUtility::int($op_id);

        $oCancelRequestSrch = new OrderCancelRequestSearch();
        $oCancelRequestSrch->doNotCalculateRecords();
        $oCancelRequestSrch->doNotLimitRecords();
        $oCancelRequestSrch->addCondition('ocrequest_op_id', '=', $op_id);
        $oCancelRequestSrch->addCondition('ocrequest_status', '!=', OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED);
        $oCancelRequestRs = $oCancelRequestSrch->getResultSet();

        if (FatApp::getDb()->fetch($oCancelRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Already_submitted_cancel_request', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $user_id = UserAuthentication::getLoggedUserId();
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(array('op_status_id', 'op_id', 'op_qty','op_product_type'));
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);

        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
            // CommonHelper::redirectUserReferer();
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderReturnRequests'));
        }
        /* $ocRequestSrch = new OrderCancelRequestSearch();
        $ocRequestSrch->doNotCalculateRecords();
        $ocRequestSrch->doNotLimitRecords();
        $ocRequestSrch->addCondition( 'ocrequest_op_id', '=', $opDetail['op_id'] );
        $ocRequestSrch->addCondition( 'ocrequest_status', '!=', OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED );
        $ocRequestRs = $ocRequestSrch->getResultSet();
        if( FatApp::getDb()->fetch($ocRequestRs) ){
        if ( !in_array($opDetail["op_status_id"],$getBuyerAllowedOrderReturnStatuses)) {
        Message::addErrorMessage( Labels::getLabel('MSG_Your_request_to_refund_this_order_is_already_is_in_process', $this->siteLangId) );
        // CommonHelper::redirectUserReferer();
        FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderReturnRequests'));
        }
        } */

        if ($opDetail["op_product_type"] == Product::PRODUCT_TYPE_DIGITAL) {
            $getBuyerAllowedOrderReturnStatuses = (array)Orders::getBuyerAllowedOrderReturnStatuses(true);
        } else {
            $getBuyerAllowedOrderReturnStatuses = (array)Orders::getBuyerAllowedOrderReturnStatuses();
        }

        if (!in_array($opDetail["op_status_id"], $getBuyerAllowedOrderReturnStatuses)) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
            $statuses = $getBuyerAllowedOrderReturnStatuses;

            $status_names = array();
            foreach ($statuses as $status) {
                $status_names[] = $orderStatuses[$status];
            }
            Message::addErrorMessage(sprintf(Labels::getLabel('MSG_Return_Refund_cannot_placed', $this->siteLangId), implode(',', $status_names)));
            // CommonHelper::redirectUserReferer();
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderReturnRequests'));
        }

        $oReturnRequestSrch = new OrderReturnRequestSearch();
        $oReturnRequestSrch->doNotCalculateRecords();
        $oReturnRequestSrch->doNotLimitRecords();
        $oReturnRequestSrch->addCondition('orrequest_op_id', '=', $opDetail['op_id']);
        $oReturnRequestRs = $oReturnRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oReturnRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Already_submitted_return_request_order', $this->siteLangId));
            // CommonHelper::redirectUserReferer();
            FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderReturnRequests'));
        }

        $frm = $this->getOrderReturnRequestForm($this->siteLangId, $opDetail);
        $fld = $frm->getField('orrequest_qty');

        $frm->fill(array('op_id' => $opDetail['op_id'] ));
        $this->set('frmOrderReturnRequest', $frm);
        $this->_template->render(true, true);
    }

    public function setupOrderReturnRequest()
    {
        $op_id = FatApp::getPostedData('op_id', null, '0');
        $user_id = UserAuthentication::getLoggedUserId();
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT, 'cvd');
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(array('order_language_id', 'op_status_id', 'op_id', 'op_qty', 'op_product_type','op_unit_price','opcharge_amount'));
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);

        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            $message = Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        $frm = $this->getOrderReturnRequestForm($this->siteLangId, $opDetail);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(current($frm->getValidationErrors()));
            }
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (abs($opDetail['opcharge_amount']) > 0) {
            $orrequestQty = FatUtility::int($post['orrequest_qty']);

            $volumeDiscountPerItem = abs($opDetail['opcharge_amount'])/$opDetail['op_qty'];
            $amtChargeBackToBuyer = ($opDetail['op_qty'] - $orrequestQty)*$volumeDiscountPerItem;

            $pricePerItemCharged = $opDetail['op_unit_price'] - $volumeDiscountPerItem;

            if ($amtChargeBackToBuyer > ($opDetail['op_unit_price'] - $volumeDiscountPerItem)*abs($orrequestQty)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Order_not_eligible_for_partial_qty_refund', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        if ($opDetail["op_product_type"] == Product::PRODUCT_TYPE_DIGITAL) {
            $getBuyerAllowedOrderReturnStatuses = (array)Orders::getBuyerAllowedOrderReturnStatuses(true);
        } else {
            $getBuyerAllowedOrderReturnStatuses = (array)Orders::getBuyerAllowedOrderReturnStatuses();
        }

        if (!in_array($opDetail["op_status_id"], $getBuyerAllowedOrderReturnStatuses)) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
            $statuses = $getBuyerAllowedOrderReturnStatuses;

            $status_names = array();
            foreach ($statuses as $status) {
                $status_names[] = $orderStatuses[$status];
            }
            $message = sprintf(Labels::getLabel('MSG_Return_Refund_cannot_placed', $this->siteLangId), implode(',', $status_names));
            LibHelper::dieJsonError($message);
        }

        $oReturnRequestSrch = new OrderReturnRequestSearch();
        $oReturnRequestSrch->doNotCalculateRecords();
        $oReturnRequestSrch->doNotLimitRecords();
        $oReturnRequestSrch->addCondition('orrequest_op_id', '=', $opDetail['op_id']);
        $oReturnRequestRs = $oReturnRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oReturnRequestRs)) {
            $message = Labels::getLabel('MSG_Already_submitted_return_request_order', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }


        $reference_number = $user_id.'-'.time();
        $returnRequestDataToSave = array(
        'orrequest_user_id'            =>    $user_id,
        'orrequest_reference'        =>    $reference_number,
        'orrequest_op_id'            =>    $opDetail['op_id'],
        'orrequest_qty'                =>    FatUtility::int($post['orrequest_qty']),
        'orrequest_returnreason_id'    =>    FatUtility::int($post['orrequest_returnreason_id']),
        'orrequest_type'            =>    FatUtility::int($post['orrequest_type']),
        'orrequest_date'            =>    date('Y-m-d H:i:s'),
        'orrequest_status'            =>    OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING
        );
        $oReturnRequestObj = new OrderReturnRequest();
        $oReturnRequestObj->assignValues($returnRequestDataToSave);
        if (!$oReturnRequestObj->save()) {
            Message::addErrorMessage($oReturnRequestObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $orrequest_id = $oReturnRequestObj->getMainTableRecordId();
        if (!$orrequest_id) {
            $message = Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        /* attach file with request [ */

        if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
            $uploadedFile = $_FILES['file']['tmp_name'];
            $uploadedFileExt = pathinfo($uploadedFile, PATHINFO_EXTENSION);

            if (filesize($uploadedFile) > 10240000) {
                $message = Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieJsonError(Message::getHtml());
            }

            if (getimagesize($uploadedFile) === false && in_array($uploadedFileExt, array('.zip'))) {
                $message = Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->siteLangId);
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieJsonError(Message::getHtml());
            }

            $fileHandlerObj = new AttachedFile();
            if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $orrequest_id, 0, $_FILES['file']['name'], -1, true)) {
                if (true ===  MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($fileHandlerObj->getError());
                }
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        /* ] */

        /* save return request message[ */
        $returnRequestMsgDataToSave = array(
        'orrmsg_orrequest_id'    =>    $orrequest_id,
        'orrmsg_from_user_id'    =>    $user_id,
        'orrmsg_msg'            =>    $post['orrmsg_msg'],
        'orrmsg_date'            =>    date('Y-m-d H:i:s'),
        );

        $oReturnRequestMsgObj = new OrderReturnRequestMessage();
        $oReturnRequestMsgObj->assignValues($returnRequestMsgDataToSave);
        if (!$oReturnRequestMsgObj->save()) {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($oReturnRequestMsgObj->getError());
            }
            Message::addErrorMessage($oReturnRequestMsgObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $orrmsg_id = $oReturnRequestMsgObj->getMainTableRecordId();
        if (!$orrmsg_id) {
            $message = Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }
        /* ] */

        /* adding child order history[ */
        $orderObj = new Orders();
        $orderObj->addChildProductOrderHistory($opDetail['op_id'], $opDetail['order_language_id'], FatApp::getConfig("CONF_RETURN_REQUEST_ORDER_STATUS"), Labels::getLabel('LBL_Buyer_Raised_Return_Request', $opDetail['order_language_id']), 1);
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestNotification($orrmsg_id, $opDetail['order_language_id'])) {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($oReturnRequestMsgObj->getError());
            }
            Message::addErrorMessage($emailNotificationObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        /* $this->set( 'msg', Labels::getLabel('MSG_Your_return_request_submitted', $this->siteLangId) );
        $this->_template->render( false, false, 'json-success.php' ); */

        //send notification to admin
        $notificationData = array(
        'notification_record_type' => Notification::TYPE_ORDER_RETURN_REQUEST,
        'notification_record_id' => $orrequest_id,
        'notification_user_id' => UserAuthentication::getLoggedUserId(),
        'notification_label_key' => Notification::ORDER_RETURNED_REQUEST_NOTIFICATION,
        'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel('MSG_NOTIFICATION_COULD_NOT_BE_SENT', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        $msg = Labels::getLabel('MSG_Your_return_request_submitted', $this->siteLangId);
        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('msg', $msg);
            $this->_template->render();
        }
        Message::addMessage($msg);
        FatUtility::dieJsonSuccess(Message::getHtml());
        // $this->_template->render(false, false, 'json-success.php');
    }

    public function rewardPoints($convertReward = '')
    {
        $frm = $this->getRewardPointSearchForm($this->siteLangId);
        $frm->fill(array('convertReward'=>$convertReward));
        $this->set('frmSrch', $frm);

        $userId = UserAuthentication::getLoggedUserId();

        /* $srch = new UserRewardSearch;
        $srch->joinUser();
        $srch->addCondition('urp.urp_user_id','=',$userId);
        $cnd = $srch->addCondition('urp.urp_date_expiry','=','0000-00-00');
        $cnd->attachCondition('urp.urp_date_expiry','>=',date('Y-m-d'),'OR');
        $srch->addMultipleFields(array('IFNULL(sum(urp.urp_points),0) as totalRewardPoints'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);
        $this->set('totalRewardPoints',$records['totalRewardPoints']); */

        $this->set('totalRewardPoints', UserRewardBreakup::rewardPointBalance($userId));
        $this->set('convertReward', $convertReward);
        $this->_template->render(true, true);
    }

    public function rewardPointsSearch()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $frm = $this->getRewardPointSearchForm($this->siteLangId);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $convertReward = $post['convertReward'];

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $srch = new UserRewardSearch;
        $srch->joinUser();
        $srch->addCondition('urp.urp_user_id', '=', $userId);
        $srch->addOrder('urp.urp_date_added', 'DESC');
        $srch->addOrder('urp.urp_id', 'DESC');
        $srch->addMultipleFields(array('urp.*','uc.credential_username'));

        if ($convertReward == 'coupon') {
            $srch->addCondition('urp.urp_used', '=', 0);
            $cond = $srch->addCondition('urp.urp_date_expiry', '=', '0000-00-00');
            $cond->attachCondition('urp.urp_date_expiry', '>=', date('Y-m-d'), 'OR');
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pagesize);
        }
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('convertReward', $convertReward);
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function generateCoupon()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();

        if (empty($post['rewardOptions'])) {
            Message::addErrorMessage(Labels::getLabel('ERR_Please_select_options', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $rewardOptions = str_replace('|', ',', rtrim($post['rewardOptions'], '|'));

        $srch = new UserRewardSearch;
        $srch->joinUser();
        $srch->addCondition('urp.urp_user_id', '=', $userId);
        $srch->addCondition('urp_id', 'in', array($rewardOptions));
        $srch->addCondition('urp.urp_used', '=', 0);
        $cond = $srch->addCondition('urp.urp_date_expiry', '=', '0000-00-00');
        $cond->attachCondition('urp.urp_date_expiry', '>=', date('Y-m-d'), 'OR');
        $srch->addOrder('urp.urp_date_added', 'DESC');
        $srch->addOrder('urp.urp_id', 'DESC');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('sum(urp_points) as totalRewardPoints','min(urp.urp_date_expiry) as expiredOn'));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);

        if (empty($records)) {
            Message::addErrorMessage(Labels::getLabel('ERR_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($records['totalRewardPoints'] < FatApp::getConfig('CONF_MIN_REWARD_POINT') || $records['totalRewardPoints'] > FatApp::getConfig('CONF_MAX_REWARD_POINT')) {
            Message::addErrorMessage(Labels::getLabel('ERR_PLEASE_VERIFY_REWARD_CONVERSION_LIMIT', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        $couponData = array(
        'coupon_type'=>DiscountCoupons::TYPE_DISCOUNT,
        'coupon_identifier'=> Labels::getLabel('LBL_Generated_From_Reward_Point', $this->siteLangId),
        'coupon_code'=>uniqid(),
        'coupon_min_order_value'=>1,
        'coupon_discount_in_percent'=>applicationConstants::PERCENTAGE,
        'coupon_discount_value'=>CommonHelper::convertRewardPointToCurrency($records['totalRewardPoints']),
        'coupon_max_discount_value'=>CommonHelper::convertRewardPointToCurrency($records['totalRewardPoints']),
        'coupon_start_date'=>date('Y-m-d'),
        'coupon_end_date'=>$records['expiredOn'],
        'coupon_uses_count'=>1,
        'coupon_uses_coustomer'=>1,
        'coupon_active'=>applicationConstants::ACTIVE,
        );
        $couponObj = new DiscountCoupons();
        $couponObj->assignValues($couponData);
        if (!$couponObj->save()) {
            $db->rollbackTransaction();
            Message::addErrorMessage($couponObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $couponId = $couponObj->getMainTableRecordId();
        if (1 > $couponId) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('ERR_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new DiscountCoupons();
        if (!$obj->addUpdateCouponUser($couponId, $userId)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $rewardOptionsArr = explode(',', $rewardOptions);
        foreach ($rewardOptionsArr as $urp_id) {
            $rewardsRecord = new UserRewards($urp_id);
            $rewardsRecord->assignValues(
                array(
                'urp_used'        =>    1,
                )
            );
            if (!$rewardsRecord->save()) {
                $db->rollbackTransaction();
                Message::addErrorMessage(Labels::getLabel($rewardsRecord->getError(), $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $db->commitTransaction();

        $this->set('msg', Labels::getLabel('LBL_Successfully_generated_coupon_from_Rewar_points', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function offers()
    {
        $this->_template->render(true, true, 'buyer/offers.php');
    }

    public function searchOffers()
    {
        $offers = DiscountCoupons::getUserCoupons(UserAuthentication::getLoggedUserId(), $this->siteLangId);

        if ($offers) {
            $this->set('offers', $offers);
        } else {
            if (true ===  MOBILE_APP_API_CALL) {
                $this->set('offers', array());
            } else {
                $this->set('noRecordsHtml', $this->_template->render(false, false, '_partial/no-record-found.php', true));
            }
        }
        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'buyer/search-offers.php');
    }

    public function twitterCallback()
    {
        include_once CONF_INSTALLATION_PATH . 'library/APIs/twitteroauth-master/autoload.php';
        $get = FatApp::getQueryStringData();

        if (!empty($get['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"), $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
            try {
                $access_token = $twitteroauth->oauth("oauth/access_token", ["oauth_verifier" => $get['oauth_verifier']]);
            } catch (exception $e) {
                $this->set('errors', $e->getMessage());
                $this->_template->render(false, false, 'buyer/twitter-response.php');
                return;
            }

            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"), $access_token['oauth_token'], $access_token['oauth_token_secret']);

            $info = $twitteroauth->get('account/verify_credentials', array("include_entities" => false));
            $anchor_tag = CommonHelper::referralTrackingUrl(UserAuthentication::getLoggedUserAttribute('user_referral_code'));
            $urlapi = "http://tinyurl.com/api-create.php?url=".$anchor_tag;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlapi);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $shorturl = curl_exec($ch);
            curl_close($ch);
            $anchor_length=strlen($shorturl);

            //$message = substr($shorturl." Twitter Message will go here ",0,(140-$anchor_length-6));
            $message = substr($shorturl." ".sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_TWITTER_POST_TITLE".$this->siteLangId), FatApp::getConfig("CONF_WEBSITE_NAME_".$this->siteLangId)), 0, 134-$anchor_length);

            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE, 0, 0, $this->siteLangId);
            $error = false;
            $postMedia = false;
            if (!empty($file_row)) {
                $image_path = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
                $image_path = CONF_UPLOADS_PATH.$image_path;
                if (filesize($image_path) <= (5*1000000)) { /*Max 5mb size image can be uploaded by Twitter*/
                    $handle = fopen($image_path, 'rb');
                    $image = fread($handle, filesize($image_path));
                    fclose($handle);
                    $twitteroauth->setTimeouts(60, 30);
                    try {
                        $result = $twitteroauth->upload('media/upload', array('media' => $image_path));
                        if ($twitteroauth->getLastHttpCode() == 200) {
                            $parameters = array('Name' => FatApp::getConfig("CONF_WEBSITE_NAME_".$this->siteLangId), 'status' => $message, 'media_ids' => $result->media_id_string);
                            try {
                                $post = $twitteroauth->post('statuses/update', $parameters);
                                $postMedia = true;
                            } catch (exception $e) {
                                $error = $e->getMessage();
                            }
                        }
                    } catch (exception $e) {
                        ;
                        $error = $e->getMessage();
                    }
                }
            }

            if (!$postMedia) {
                $parameters = array('Name' => FatApp::getConfig("CONF_WEBSITE_NAME_".$this->siteLangId), 'status' => $message);
                try {
                    $post = $twitteroauth->post('statuses/update', $parameters, false);
                } catch (exception $e) {
                    $error = $e->getMessage();
                }
            }

            $this->set('errors', isset($post->errors) ? $post->errors : $error);
            $this->_template->render(false, false, 'buyer/twitter-response.php');
        }
    }

    public function twitterCallback_old()
    {
        include_once CONF_INSTALLATION_PATH . 'library/APIs/twitter/twitteroauth.php';
        $get = FatApp::getQueryStringData();

        if (!empty($get['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
            // We've got everything we need
            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"), $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
            // Let's request the access token
            $access_token = $twitteroauth->getAccessToken($get['oauth_verifier']);
            // Save it in a session var
            $_SESSION['access_token'] = $access_token;
            // Let's get the user's info
            $twitter_info = $twitteroauth->get('account/verify_credentials');
            //$twitter_info->id
            $anchor_tag=CommonHelper::referralTrackingUrl(UserAuthentication::getLoggedUserAttribute('user_referral_code'));
            $urlapi = "http://tinyurl.com/api-create.php?url=".$anchor_tag;
            /***
 * activate cURL for URL shortening
***/

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlapi);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $shorturl = curl_exec($ch);
            curl_close($ch);
            $anchor_length=strlen($shorturl);
            //$message = substr($shorturl." Twitter Message will go here ",0,(140-$anchor_length-6));
            $message = substr($shorturl." ".sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_TWITTER_POST_TITLE".$this->siteLangId), FatApp::getConfig("CONF_WEBSITE_NAME_".$this->siteLangId)), 0, 134-$anchor_length);
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE, 0, 0, $this->siteLangId);
            $post='';
            if (!empty($file_row)) {
                $image_path = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
                $image_path = CONF_UPLOADS_PATH.$image_path;
                $handle = fopen($image_path, 'rb');
                $image = fread($handle, filesize($image_path));
                fclose($handle);
                /* $parameters = array('media[]' => "{$image};type=image/jpeg;filename={$image_path}",'status' => $message);
                $post = $twitteroauth->post('statuses/update_with_media', $parameters, true); */
                $parameters = array('media_type'=>'image/jpeg','media'=>$image);
                $post = $twitteroauth->post('media/upload', $parameters, true);
            } else {
                $parameters = array('Name' => FatApp::getConfig("CONF_WEBSITE_NAME_".$this->siteLangId), 'status' => $message);
                $post = $twitteroauth->post('statuses/update', $parameters, false);
            }
            $this->set('errors', isset($post->errors) ? $post->errors : '');
            $this->_template->render(false, false, 'buyer/twitter-response.php');
        }
    }

    public function shareEarn()
    {
        if (!FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE", FatUtility::VAR_INT, 1)) {
            Message::addErrorMessage(Labels::getLabel('Msg_INVALID_REQUEST', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
        if (empty(UserAuthentication::getLoggedUserAttribute('user_referral_code'))) {
            Message::addErrorMessage(Labels::getLabel('Msg_Referral_Code_is_empty', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $get_twitter_url = $_SESSION["TWITTER_URL"]=CommonHelper::generateFullUrl('Buyer', 'twitterCallback');

        try {
            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"));

            $request_token = $twitteroauth->oauth('oauth/request_token', array('oauth_callback' => $get_twitter_url));

            $_SESSION['oauth_token'] = $request_token['oauth_token'];
            $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
            $twitterUrl = $twitteroauth->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
            $this->set('twitterUrl', $twitterUrl);
        } catch (\Exception $e) {
            $this->set('twitterUrl', false);
        }

        $this->set('referralTrackingUrl', CommonHelper::referralTrackingUrl(UserAuthentication::getLoggedUserAttribute('user_referral_code')));
        $this->set('sharingFrm', $this->getFriendsSharingForm($this->siteLangId));
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render(true, true);
    }

    public function sendMailShareEarn()
    {
        $post = FatApp::getPostedData();
        $err = '';
        if (!FatUtility::validateMultipleEmails($post["email"], $err)) {
            Message::addErrorMessage($err);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $email = CommonHelper::multipleExplode(array(",",";","\t","\n"), trim($post["email"], ","));
        $email = array_unique($email);
        if (count($email) && !empty($email)) {
            $email = array_unique($email);
            $personalMessage = empty($post['message'])?"":"<b>".Labels::getLabel('Lbl_Personal_Message_From_Sender', $this->siteLangId).":</b> ".nl2br($post['message']);
            $emailNotificationObj = new EmailHandler();
            foreach ($email as $email_id) {
                $email_id = trim($email_id);
                if (!CommonHelper::isValidEmail($email_id)) {
                    continue;
                }
                /* email notification handling[ */
                if (!$emailNotificationObj->sendMailShareEarn(UserAuthentication::getLoggedUserId(), $email_id, $personalMessage, $this->siteLangId)) {
                    Message::addErrorMessage(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
                    CommonHelper::redirectUserReferer();
                }
                /* ] */
                /* EmailHandler::sendMailTpl($email_id, 'invitation_email', array(
                '{Sender_Name}' => htmlentities($this->user_details['user_name']),
                '{Tracking_URL}' => $this->referral_tracking_url($this->user_details['user_referral_code']),
                '{Invitation_Message}' => $personalMessage,
                )); */
            }
        }
        $this->set('msg', Labels::getLabel('MSG_invitation_emails_sent_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getFriendsSharingForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmShareEarn');
        $fld=$frm->addTextArea(Labels::getLabel('L_Friends_Email', $langId), 'email');
        $fld->htmlAfterField=' <small>('.Labels::getLabel('L_Use_commas_separate_emails', $langId).')</small>';
        $fld->requirements()->setRequired();
        $frm->addTextArea(Labels::getLabel('L_Personal_Message', $langId), 'message');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('L_Invite_Your_Friends', $langId));
        return $frm;
    }

    private function getRewardPointSearchForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmRewardPointSearch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'convertReward');
        /* $frm->addTextBox('','keyword');
        $fldSubmit = $frm->addSubmitButton( '', 'btn_submit', Labels::getLabel('LBL_Search',$langId) );
        $fldCancel = $frm->addButton( "", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearSearch();') ); */
        return $frm;
    }

    private function getOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code','currency_symbol_left','currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];

        $frm = new Form('frmOrderSrch');
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $langId) ));
        $frm->addSelectBox('', 'status', Orders::getOrderProductStatusArr($langId, unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS"))), '', array(), Labels::getLabel('LBL_Status', $langId));
        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $langId),'readonly'=>'readonly' ));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $langId),'readonly'=>'readonly' ));
        $frm->addTextBox('', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Price_Min', $langId).' ['.$currencySymbol.']' ));
        $frm->addTextBox('', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Price_Max', $langId).' ['.$currencySymbol.']' ));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearSearch();'));
        $frm->addHiddenField('', 'page');
        //$fldSubmit->attachField($fldCancel);
        return $frm;
    }

    private function getOrderProductDownloadSearchForm($langId)
    {
        $frm = new Form('frmSrch');
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $langId) ));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function getOrderCancelRequestForm($langId)
    {
        $frm = new Form('frmOrderCancel');
        $orderCancelReasonsArr = OrderCancelReason::getOrderCancelReasonArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Reason_for_cancellation', $langId), 'ocrequest_ocreason_id', $orderCancelReasonsArr, '', array(), Labels::getLabel('LBL_Select_Reason', $langId))->requirements()->setRequired();
        $frm->addTextArea(Labels::getLabel('LBL_Comments', $langId), 'ocrequest_message')->requirements()->setRequired();
        $frm->addHiddenField('', 'op_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send_Request', $langId));
        return $frm;
    }

    private function getOrderReturnRequestForm($langId, $opDetail = array())
    {
        $returnQtyArr = array();
        if (!empty($opDetail)) {
            $op_qty = isset($opDetail["op_qty"]) ? $opDetail["op_qty"] : 1;
            for ($k = 1; $k <= $op_qty; $k++) {
                $returnQtyArr[$k] = $k;
            }
        }
        $frm = new Form('frmOrderReturnRequest', array('enctype' => "multipart/form-data"));
        $frm->addSelectBox(Labels::getLabel('LBL_Return_Qty', $langId), 'orrequest_qty', $returnQtyArr, '', array(), '')->requirements()->setRequired();
        $orderReturnReasonsArr = OrderReturnReason::getOrderReturnReasonArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Reason_for_return', $langId), 'orrequest_returnreason_id', $orderReturnReasonsArr, '', array(), Labels::getLabel('LBL_Select_Reason', $langId))->requirements()->setRequired();

        /* if( $opDetail['op_status_id'] != FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS") ){
        $requestTypeArr = OrderReturnRequest::getRequestTypeArr($langId);
        unset($requestTypeArr[OrderReturnRequest::RETURN_REQUEST_TYPE_REPLACE]);
        $frm->addRadioButtons( Labels::getLabel('LBL_Return_Request_Type', $langId), 'orrequest_type', $requestTypeArr, OrderReturnRequest::RETURN_REQUEST_TYPE_REFUND )->requirements()->setRequired();
        } else {
        $frm->addRadioButtons( Labels::getLabel('LBL_Return_Request_Type', $langId), 'orrequest_type', OrderReturnRequest::getRequestTypeArr($langId), OrderReturnRequest::RETURN_REQUEST_TYPE_REFUND )->requirements()->setRequired();
        } */

        // For now untill $requestTypeArr having single value
        $frm->addHiddenField('', 'orrequest_type', OrderReturnRequest::RETURN_REQUEST_TYPE_REFUND);

        $fileFld = $frm->addFileUpload(Labels::getLabel('LBL_Upload_Images', $langId), 'file', array('accept'=>'image/*,.zip'));
        $fileFld->htmlBeforeField='<div class="filefield"><span class="filename"></span>';
        $fileFld->htmlAfterField = '<label class="filelabel">'.Labels::getLabel('LBL_Browse_File', $this->siteLangId).'</label></div><span class="note">' .Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->siteLangId) .'</span>' ;
        $frm->addTextArea(Labels::getLabel('LBL_Comments', $langId), 'orrmsg_msg')->requirements()->setRequired();
        $frm->addHiddenField('', 'op_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send_Request', $langId));
        return $frm;
    }

    private function getOrderFeedbackForm($op_id, $langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmOrderFeedback');

        $ratingAspects = SelProdRating::getRatingAspectsArr($langId);
        foreach ($ratingAspects as $aspectVal => $aspectLabel) {
            $fld=$frm->addSelectBox($aspectLabel, "review_rating[$aspectVal]", array("1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5"), "", array('class'=>"star-rating"), Labels::getLabel('L_Rate', $langId));
            $fld->requirements()->setRequired(true);
            $fld->setWrapperAttribute('class', 'rating-f');
        }

        $frm->addRequiredField(Labels::getLabel('LBL_Title', $langId), 'spreview_title');
        $frm->addTextArea(Labels::getLabel('LBL_Description', $langId), 'spreview_description')->requirements()->setRequired();
        $frm->addHiddenField('', 'op_id', $op_id);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send_Review', $langId));
        return $frm;
    }

    public function getFbToken()
    {
        $userId = UserAuthentication::getLoggedUserId();
        if (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['redirect_user'])) {
            $redirectUrl = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['redirect_user'];
            unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['redirect_user']);
        } else {
            $redirectUrl = CommonHelper::generateUrl('Buyer', 'ShareEarn');
        }


        include_once CONF_INSTALLATION_PATH.'library/Fbapi.php';

        $config = array(
        'app_id' => FatApp::getConfig('CONF_FACEBOOK_APP_ID', FatUtility::VAR_STRING, ''),
        'app_secret' => FatApp::getConfig('CONF_FACEBOOK_APP_SECRET', FatUtility::VAR_STRING, ''),
        );
        $fb = new Fbapi($config);
        $fbObj = $fb->getInstance();

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            Message::addErrorMessage($e->getMessage());
            FatApp::redirectUser($redirectUrl);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            Message::addErrorMessage($e->getMessage());
            FatApp::redirectUser($redirectUrl);
        }

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                Message::addErrorMessage($helper->getErrorDescription());
            //Message::addErrorMessage($helper->getErrorReason());
            } else {
                Message::addErrorMessage(Labels::getLabel('Msg_Bad_Request', $this->siteLangId));
            }
        } else {
            // The OAuth 2.0 client handler helps us manage access tokens
            $oAuth2Client = $fbObj->getOAuth2Client();

            if (! $accessToken->isLongLived()) {
                try {
                    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                } catch (Facebook\Exceptions\FacebookSDKException $e) {
                    Message::addErrorMessage($helper->getMessage());
                    FatApp::redirectUser($redirectUrl);
                }
            }

            $fbAccessToken = $accessToken->getValue();
            unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_code']);
            unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_access_token']);
            unset($_SESSION['fb_'.FatApp::getConfig("CONF_FACEBOOK_APP_ID").'_user_id']);

            $userObj = new User($userId);
            $userData = array('user_fb_access_token'=>$fbAccessToken);
            $userObj->assignValues($userData);
            if (!$userObj->save()) {
                Message::addErrorMessage(Labels::getLabel("MSG_Token_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
            }
        }
        FatApp::redirectUser($redirectUrl);
    }

    public function addItemsToCart($orderId)
    {
        if (!$orderId) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            return;
        }

        $userId = UserAuthentication::getLoggedUserId();

        $orderObj = new Orders();
        $orderDetail = $orderObj->getOrderById($orderId, $this->siteLangId);
        if (!$orderDetail || ($orderDetail && $orderDetail['order_user_id'] != $userId)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            return;
        }

        $cartObj = new Cart();
        $cartInfo = unserialize($orderDetail['order_cart_data']);
        unset($cartInfo['shopping_cart']);
        $outOfStock = false;
        foreach ($cartInfo as $key => $quantity) {
            $keyDecoded = unserialize(base64_decode($key));

            $selprod_id = 0;

            if (strpos($keyDecoded, Cart::CART_KEY_PREFIX_PRODUCT) !== false) {
                $selprod_id = FatUtility::int(str_replace(Cart::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
            }
            $selProdStock = SellerProduct::getAttributesById($selprod_id, 'selprod_stock', false);
            if (!$selProdStock && $selProdStock <= 0) {
                $outOfStock = true;
                continue;
            }
            $cartObj->add($selprod_id, $quantity);
        }

        if ($outOfStock) {
            $message = Labels::getLabel('MSG_Product_not_available_or_out_of_stock_so_removed_from_cart_listing', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                $error['status'] = 0;
                $error['msg'] = strip_tags($message);
                $error['cartItemsCount'] = $this->cartItemsCount;
                FatUtility::dieJsonError($error);
            }
            Message::addErrorMessage($message);
            return false;
        }

        $cartObj->removeUsedRewardPoints();
        $cartObj->removeCartDiscountCoupon();
        $cartObj->removeProductShippingMethod();

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        /* Update existing cart [ */

        /* $db = FatApp::getDb();
        if(!$db->updateFromArray( 'tbl_user_cart', array( 'usercart_details' => $orderDetail['order_cart_data'],"usercart_added_date" => date ( 'Y-m-d H:i:s' ) ), array('smt' => 'usercart_user_id = ?', 'vals' => array($userId) ) )){
        Message::addErrorMessage(Labels::getLabel("MSG_Can_not_be_Re-Order",$this->siteLangId));
        FatUtility::dieJsonError( Message::getHtml() );
        } */

        /* ] */
        return;
    }


    /* repay payment pending order [ */
    /* public function repayOrder($opId){
    $opId = FatUtility::convertToType($opId,FatUtility::VAR_INT);
    if(empty($opId)){
    Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request',$this->siteLangId));
    CommonHelper::redirectUserReferer();
    }
    $userId = UserAuthentication::getLoggedUserId();
    $srch = new OrderProductSearch( $this->siteLangId, true);
    $srch->joinPaymentMethod();
    $srch->addStatusCondition( unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")) );
    $srch->addCondition( 'order_user_id', '=', $userId );
    $srch->addCondition( 'op_id', '=', $opId );
    $srch->addCondition( 'op_status_id', '=', FatApp::getConfig('CONF_DEFAULT_ORDER_STATUS') );
    $srch->addOrder("op_id","DESC");
    $srch->addMultipleFields( array('op_status_id', 'op_id', 'order_id', 'order_cart_data', 'pmethod_code') );

    $rs = $srch->getResultSet();
    $opDetail = FatApp::getDb()->fetch( $rs );

    if( !$opDetail || CommonHelper::isMultidimArray($opDetail) ){
    Message::addErrorMessage(Labels::getLabel( 'MSG_ERROR_INVALID_ACCESS', $this->siteLangId ));
    CommonHelper::redirectUserReferer();
    }

    // Repayment is not allowed for CashOnDelivery Orders for Now, to enable make it uncomment[
    if( strtolower($opDetail['pmethod_code']) == "cashondelivery" ){
    Message::addErrorMessage( Labels::getLabel( "MSG_Repayment_is_not_allowed_for_".$opDetail['pmethod_code'], $this->siteLangId ) );
    CommonHelper::redirectUserReferer();
    }
    //]

    $_SESSION['shopping_cart']["order_id"] = $opDetail['order_id'];

    $cartInfo = unserialize( $opDetail['order_cart_data'] );
    unset($cartInfo['shopping_cart']);
    FatApp::getDb()->deleteRecords('tbl_user_cart', array('smt'=>'`usercart_user_id`=?', 'vals'=>array(UserAuthentication::getLoggedUserId())));
    $cartObj = new Cart();
    foreach($cartInfo as $key => $quantity){

    $keyDecoded = unserialize( base64_decode($key) );

    $selprod_id = 0;
    $prodgroup_id = 0;

    if( strpos($keyDecoded, Cart::CART_KEY_PREFIX_PRODUCT ) !== FALSE ){
                $selprod_id = FatUtility::int(str_replace( Cart::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded ));
    }

    if( strpos($keyDecoded, Cart::CART_KEY_PREFIX_BATCH ) !== FALSE ){
                $prodgroup_id = FatUtility::int(str_replace( Cart::CART_KEY_PREFIX_BATCH, '', $keyDecoded ));
    }

    $cartObj->add($selprod_id, $quantity,$prodgroup_id);


    }
    $cartObj->updateUserCart();
    FatApp::redirectUser(CommonHelper::generateUrl('Cart'));
    } */
    /* ] */

    public function shareEarnUrl()
    {
        $userId = UserAuthentication::getLoggedUserId();
        if (!FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE")) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_This_module_is_not_enabled', $this->siteLangId));
        }
        $userObj = new User($userId);
        $userInfo = $userObj->getUserInfo(array('user_referral_code'), true, true);
        if (empty($userInfo['user_referral_code'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_User', $this->siteLangId));
        }

        $referralTrackingUrl = CommonHelper::referralTrackingUrl($userInfo['user_referral_code']);

        $this->set('data', array('trackingUrl'=>$referralTrackingUrl));
        $this->_template->render();
    }
    public function orderReceipt($orderId)
    {
        if (empty($orderId)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        $emailObj = new EmailHandler();
        if (!$emailObj->newOrderBuyerAdmin($orderId, $this->siteLangId, false, false)) {
            $message = Labels::getLabel('MSG_Unable_to_notify_customer', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }
        $this->set('msg', Labels::getLabel('MSG_Email_Sent', $this->siteLangId));
        $this->_template->render();
    }
}
