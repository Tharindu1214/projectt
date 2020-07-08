<?php
class SalesReportController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewSalesReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditSalesReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index($orderDate = '')
    {
        $this->objPrivilege->canViewSalesReport();

        $frmSearch = $this->getSearchForm($orderDate);
        //$frmSearch->fill(array('orderDate'=>$orderDate));

        $this->set('frmSearch', $frmSearch);
        $this->set('orderDate', $orderDate);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewSalesReport();
        $db = FatApp::getDb();
        $orderDate = FatApp::getPostedData('orderDate');

        $srchFrm = $this->getSearchForm($orderDate);

        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        /* $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch( 0, true );
        $srch->joinPaymentMethod();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_TAX,'optax');
        $srch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_SHIPPING,'opship');

        $cnd = $srch->addCondition('o.order_is_paid', '=',Orders::ORDER_IS_PAID);
        $cnd->attachCondition('pmethod_code', '=','cashondelivery');
        $srch->addStatusCondition(unserialize(FatApp::getConfig('CONF_COMPLETED_ORDER_STATUS'))); */

        $srch = Report::salesReportObject();
        if (empty($orderDate)) {
            $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
            if (!empty($date_from)) {
                $srch->addCondition('o.order_date_added', '>=', $date_from. ' 00:00:00');
            }

            $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
            if (!empty($date_to)) {
                $srch->addCondition('o.order_date_added', '<=', $date_to. ' 23:59:59');
            }
            $srch->addGroupBy('DATE(o.order_date_added)');
        } else {
            $this->set('orderDate', $orderDate);
            $srch->addGroupBy('op_invoice_number');
            $srch->addCondition('o.order_date_added', '>=', $orderDate. ' 00:00:00');
            $srch->addCondition('o.order_date_added', '<=', $orderDate. ' 23:59:59');
            $srch->addFld(array('op_invoice_number'));
        }

        /* $srch->addMultipleFields(array('DATE(order_date_added) as order_date','count(op_id) as totOrders','SUM(op_qty) as totQtys','SUM(op_refund_qty) as totRefundedQtys','SUM(op_qty - op_refund_qty) as netSoldQty','sum((op_commission_charged - op_refund_commission)) as totalSalesEarnings','sum(op_refund_amount) as totalRefundedAmount','op.op_qty','op.op_unit_price','op_other_charges','sum(( op_unit_price * op_qty ) + op_other_charges - op_refund_amount) as orderNetAmount','(SUM(optax.opcharge_amount)) as taxTotal','(SUM(opship.opcharge_amount)) as shippingTotal')); */

        $srch->addOrder('order_date', 'desc');

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs);
       
        // echo '<pre>';
        // print_r($arr_listing);
        // exit();
       
        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function export()
    {
        $this->objPrivilege->canViewSalesReport();
        $db = FatApp::getDb();
        $orderDate = FatApp::getPostedData('orderDate', FatUtility::VAR_DATE, '');

        $srchFrm = $this->getSearchForm($orderDate);

        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        /* $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10); */

        $srch = Report::salesReportObject();
        if (empty($orderDate)) {
            $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
            if (!empty($date_from)) {
                $srch->addCondition('o.order_date_added', '>=', $date_from. ' 00:00:00');
            }

            $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
            if (!empty($date_to)) {
                $srch->addCondition('o.order_date_added', '<=', $date_to. ' 23:59:59');
            }
            $srch->addGroupBy('DATE(o.order_date_added)');
        } else {
            $this->set('orderDate', $orderDate);
            $srch->addGroupBy('op_invoice_number');
            $srch->addCondition('o.order_date_added', '>=', $orderDate. ' 00:00:00');
            $srch->addCondition('o.order_date_added', '<=', $orderDate. ' 23:59:59');
            $srch->addFld(array('op_invoice_number'));
        }

        $srch->addOrder('order_date', 'desc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        /* $srch->setPageNumber($page);
        $srch->setPageSize($pagesize); */
        //echo $srch->getQuery();
        $rs = $srch->getResultSet();
        //$arr_listing = $db->fetchAll($rs);

        $sheetData = array();
        $arr1 = array(Labels::getLabel('LBL_Sr_No', $this->adminLangId),Labels::getLabel('LBL_Date', $this->adminLangId),Labels::getLabel('LBL_No._Of_Orders', $this->adminLangId));
        $arr2 = array(Labels::getLabel('LBL_Sr_No', $this->adminLangId),Labels::getLabel('LBL_Invoice_Number', $this->adminLangId));
        $arr = array(Labels::getLabel('LBL_No._Of_Qty', $this->adminLangId),Labels::getLabel('LBL_Refund_Qty', $this->adminLangId), Labels::getLabel('LBL_Inventory_Value', $this->adminLangId),Labels::getLabel('LBL_Order_Net_Amount', $this->adminLangId),Labels::getLabel('LBL_Tax_Charged', $this->adminLangId),Labels::getLabel('LBL_Shipping_Charges', $this->adminLangId),Labels::getLabel('LBL_Refunded_Amount', $this->adminLangId),Labels::getLabel('LBL_Sales_Earnings', $this->adminLangId));
        if (empty($orderDate)) {
            $arr = array_merge($arr1, $arr);
        } else {
            $arr = array_merge($arr2, $arr);
        }
        array_push($sheetData, $arr);

        $count = 1;
        while ($row = $db->fetch($rs)) {
            if (empty($orderDate)) {
                $arr1 = array($count,FatDate::format($row['order_date']),$row['totOrders']);
            } else {
                $arr1 = array($count,$row['op_invoice_number']);
            }
            $arr = array($row['totQtys'],$row['totRefundedQtys'],$row['inventoryValue'],$row['orderNetAmount'],$row['taxTotal'],$row['shippingTotal'],$row['totalRefundedAmount'],$row['totalSalesEarnings']);
            $arr = array_merge($arr1, $arr);
            array_push($sheetData, $arr);
            $count++;
        }

        CommonHelper::convertToCsv($sheetData, 'Sales_Report_'.date("d-M-Y").'.csv', ',');
        exit;
    }

    private function getSearchForm($orderDate = '')
    {
        $frm = new Form('frmSalesReportSearch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'orderDate', $orderDate);
        if (empty($orderDate)) {
            $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender' ));
            $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));
            $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
            $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
            $fld_submit->attachField($fld_cancel);
        }
        return $frm;
    }
}
