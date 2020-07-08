<?php
class CatalogReportController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    
    public function __construct( $action )
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCatalogReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCatalogReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }
    
    public function index( ) 
    {
        $this->objPrivilege->canViewCatalogReport();    
        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);    
        $this->_template->render();
    }
    
    public function search( $type = false )
    {
        $this->objPrivilege->canViewProductsReport();
        $db = FatApp::getDb();
        
        $srchFrm = $this->getSearchForm();
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        
        
        /* get Seller Order Products[ */
        $opSrch = new OrderProductSearch($this->adminLangId, true);
        $opSrch->joinPaymentMethod();
        $opSrch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_TAX, 'optax');
        $opSrch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_SHIPPING, 'opship');        
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $cnd = $opSrch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PAID, 'OR');
        $cnd->attachCondition('pmethod_code', '=', 'CashOnDelivery');
        
        $opSrch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
        $opSrch->addMultipleFields(
            array( 'SUBSTRING( op_selprod_code, 1, (LOCATE( "_", op_selprod_code ) - 1 ) ) as op_product_id', 'COUNT(op_order_id) as totOrders', 'SUM(op_qty - op_refund_qty) as totSoldQty', 
            'SUM( (op_unit_price * op_qty) - (op_unit_price * op_refund_qty) ) as total', '(SUM(opship.opcharge_amount - op_refund_shipping)) as shippingTotal', '(SUM(optax.opcharge_amount - (optax.opcharge_amount/op_qty * op_refund_qty))) as taxTotal', 'SUM(op_commission_charged - op_refund_commission) as commission' )
        );
        $opSrch->addGroupBy('op_product_id');
        
        /* ] */
        
        $srch = new ProductSearch($this->adminLangId, '', '', false, false, false);
        $srch->joinBrands($this->adminLangId, false, true);
        $srch->joinProductToCategory();
        $srch->joinTable('(' . $opSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'p.product_id = opq.op_product_id', 'opq');
        $srch->addMultipleFields(array( 'product_id', 'IFNULL(tp_l.product_name,p.product_identifier) as product_name', 'IFNULL(tb_l.brand_name, brand_identifier) as brand_name', 'IFNULL(totOrders, 0) as totOrders', 'IFNULL(totSoldQty, 0) as totSoldQty', 'opq.total', 'opq.shippingTotal', 'opq.taxTotal', 'opq.commission', ));
        $srch->addGroupBy('product_id');
        $srch->addOrder('product_name');
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING);
        if(!empty($keyword) ) {
            $srch->addCondition('product_name', 'LIKE', '%' . $keyword . '%');
        }        
        
        if($type == 'export' ) {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array('Title', 'No. of Orders','Sold Qty','Total(A)','Shipping(B)','Tax(C)','Total(A+B+C)','Commission');
            array_push($sheetData, $arr);            
            while( $row = $db->fetch($rs) ){
                $name = $row['product_name'];
                
                
                if($row['brand_name'] != '' ) {
                    $name .= "\nBrand: " . $row['brand_name'];
                }
                
                $total = CommonHelper::displayMoneyFormat($row['total'], true, true);
                $shipping = CommonHelper::displayMoneyFormat($row['shippingTotal'], true, true);
                $tax = CommonHelper::displayMoneyFormat($row['taxTotal'], true, true);
                $subTotal = $row['total'] + $row['shippingTotal'] + $row['taxTotal'];
                $subTotal = CommonHelper::displayMoneyFormat($subTotal, true, true);
                $commission = CommonHelper::displayMoneyFormat($row['commission'], true, true);
                
                $arr = array($name, $row['totOrders'], $row['totSoldQty'], $total, $shipping, $tax, $subTotal, $commission );
                array_push($sheetData, $arr);                
            }
        
            CommonHelper::convertToCsv($sheetData, 'Catalog_Report_'.date("d-M-Y").'.csv', ','); exit;
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
            $rs = $srch->getResultSet();
            $arr_listing = $db->fetchAll($rs);
            $this->set("arr_listing", $arr_listing);
            $this->set('pageCount', $srch->pages());
            $this->set('recordCount', $srch->recordCount());
            $this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('postedData', $post);
            $this->_template->render(false, false);
        }
    }
    
    public function export()
    {
        $this->search('export');
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmCatalogReportSearch');
        $frm->addHiddenField('', 'page', 1);
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
?>