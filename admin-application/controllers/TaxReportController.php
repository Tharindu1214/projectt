<?php
class TaxReportController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    
    public function __construct( $action )
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewTaxReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditTaxReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }
    
    public function index( ) 
    {
        $this->objPrivilege->canViewTaxReport();    
        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);    
        $this->_template->render();
    }
    
    public function search( $type = false )
    {
        $this->objPrivilege->canViewTaxReport();
        $db = FatApp::getDb();
        
        $srchFrm = $this->getSearchForm();
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        
        $srch = new OrderProductSearch($this->adminLangId, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerUser();
        $srch->joinTable(OrderProduct::DB_TBL_CHARGES, 'LEFT OUTER JOIN', 'op.op_id = opcharge.opcharge_op_id', 'opcharge');
        $cnd = $srch->addCondition('o.order_is_paid', '=', Orders::ORDER_IS_PAID);
        $cnd->attachCondition('pmethod_code', '=', 'cashondelivery');
        $srch->addStatusCondition(unserialize(FatApp::getConfig('CONF_COMPLETED_ORDER_STATUS')));
        $srch->addCondition('opcharge.opcharge_type', '=', OrderProduct::CHARGE_TYPE_TAX);
        $srch->addGroupBy('op.op_shop_id');
        $srch->addMultipleFields(array('op_shop_name', 'op.op_selprod_user_id', 'o.order_id', 'op.op_id', 'opcharge.opcharge_id', 'opcharge.opcharge_type', 'SUM(opcharge.opcharge_amount) as totTax', 'count(op.op_id) as totChildOrders', 'seller.user_name as owner_name','seller_cred.credential_email as owner_email',));
        
        
        $op_shop_id = FatApp::getPostedData('op_shop_id', null, '');
        $shop_keyword = FatApp::getPostedData('shop_name', null, '');
        if($op_shop_id ) {
            $op_shop_id = FatUtility::int($op_shop_id);
            $srch->addCondition('op.op_shop_id', '=', $op_shop_id);
        }
        
        $op_selprod_user_id = FatApp::getPostedData('op_selprod_user_id', null, '');
        $shop_owner_keyword = FatApp::getPostedData('user_name', null, '');
        if($op_selprod_user_id ) {
            $op_selprod_user_id = FatUtility::int($op_selprod_user_id);
            $srch->addCondition('op.op_selprod_user_id', '=', $op_selprod_user_id);
        }
        
        if($op_shop_id==0 AND $op_selprod_user_id==0 AND $shop_keyword!='') {        
            $cond = $srch->addCondition('op_shop_name', '=', $shop_keyword);
            $cond->attachCondition('op_shop_name', 'like', '%'.$shop_keyword.'%', 'OR');            
        }
        
        if($op_shop_id==0 AND $op_selprod_user_id==0 AND $shop_owner_keyword!='') {        
            $cond1 = $srch->addCondition('user_name', '=', $shop_owner_keyword);
            $cond1->attachCondition('user_name', 'like', '%'.$shop_owner_keyword.'%', 'OR');
            $cond1->attachCondition('credential_email', 'like', '%'. $shop_owner_keyword .'%');            
        }        
        
        if($type == 'export' ) {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Name', $this->adminLangId),Labels::getLabel('LBL_Owner', $this->adminLangId),Labels::getLabel('LBL_Orders', $this->adminLangId),Labels::getLabel('LBL_Tax', $this->adminLangId));
            array_push($sheetData, $arr);
            while( $row = $db->fetch($rs) ){
                $arr = array( $row['op_shop_name'], $row['owner_name']."\n(".$row['owner_email'].")", $row['totChildOrders'], CommonHelper::displayMoneyFormat($row['totTax'], true, true) );
                array_push($sheetData, $arr);
            }
            CommonHelper::convertToCsv($sheetData, 'Tax_Report_'.date("d-M-Y").'.csv', ','); exit;
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
        $frm = new Form('frmTaxReportSearch');
        $frm->addHiddenField('', 'page', 1);
        
        $frm->addTextBox(Labels::getLabel('LBL_Shop', $this->adminLangId), 'shop_name');
        $frm->addHiddenField('', 'op_shop_id', 0);
        
        $frm->addTextBox(Labels::getLabel('LBL_Shop_Owner', $this->adminLangId), 'user_name');
        $frm->addHiddenField('', 'op_selprod_user_id', 0);
        
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}