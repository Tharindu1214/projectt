<?php
class TopCategoriesReportController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewPerformanceReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditPerformanceReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewPerformanceReport();
        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search( $export = false )
    {
        $this->objPrivilege->canViewPerformanceReport();
        $db = FatApp::getDb();

        $srchFrm = $this->getSearchForm();
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pageSize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, 10);
        $orderBy = FatApp::getPostedData('order_by', FatUtility::VAR_STRING, 'DESC');


        /* Sub Query to get, how many users added current product in his/her wishlist[ */
        $uWsrch = new UserWishListProductSearch();
        $uWsrch->doNotCalculateRecords();
        $uWsrch->doNotLimitRecords();
        $uWsrch->joinWishLists();
        $uWsrch->joinSellerProducts();
        $uWsrch->joinProducts();
        $uWsrch->joinProductToCategory();
        $uWsrch->addGroupBy('ptc_prodcat_id');
        $uWsrch->addMultipleFields(array( 'uwlp_selprod_id', 'uwlist_user_id', 'ptc_prodcat_id', 'count(uwlist_user_id) as wishlist_user_counts' ));

        /* ] */

        /* $srch = new OrderProductSearch( 0, true );
        //$srch->joinTable( '(' . $uWsrch->getQuery() . ')', 'LEFT OUTER JOIN', 'tquwl.uwlp_selprod_id = op.op_selprod_id', 'tquwl' );
        $srch->joinTable( Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'SUBSTRING( op_selprod_code, 1, (LOCATE( "_", op_selprod_code ) - 1 ) ) = ptc.ptc_product_id', 'ptc' );
        $srch->joinTable( ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'ptc.ptc_prodcat_id = pc.prodcat_id', 'pc' );
        $srch->joinTable( ProductCategory::DB_LANG_TBL, 'LEFT OUTER JOIN', 'pc.prodcat_id = pc_l.prodcatlang_prodcat_id AND pc_l.prodcatlang_lang_id = '. $this->adminLangId, 'pc_l' );
        $srch->joinTable( 'tbl_seller_products', 'LEFT OUTER JOIN', 'op_selprod_id = sp.selprod_id', 'sp' );
        $srch->addCondition( 'op_is_batch', '=', '0' );
        $srch->addStatusCondition( unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")) );
        $srch->addCondition( 'order_is_paid', '=', Orders::ORDER_IS_PAID );
        $srch->addGroupBy('pc.prodcat_id');
        $srch->addMultipleFields( array( 'pc.prodcat_id', 'IFNULL(pc_l.prodcat_name, pc.prodcat_identifier) as prodcat_name', 'count(op_id) as totSoldQty' , 'GROUP_CONCAT(op_id)') );
        $srch->addOrder ( 'totSoldQty', $orderBy ); */

        //$srch->addCondition('prodcat_id', '=', '35');
        //$srch->addHaving( 'op_product_id', '=', '19' );
        //$srch->addCondition( 'op_id', '=', 325 );

        $srch = new ProductCategorySearch($this->adminLangId, false, false, false, false);
        $srch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'ptc');
        $srch->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_product_id = ptc.ptc_product_id', 'sp');
        $srch->joinTable('(' . $uWsrch->getQuery() . ')', 'LEFT OUTER JOIN', 'tquwl.ptc_prodcat_id = c.prodcat_id', 'tquwl');


        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array( 'c.prodcat_id', 'IFNULL(c.prodcat_identifier, c_l.prodcat_name) as prodcat_name', 'SUM(IFNULL(selprod_sold_count, 0)) as totSoldQty', 'GETCATCODE(prodcat_id) AS prodcat_code', 'prodcat_active', 'prodcat_deleted', 'IFNULL(tquwl.wishlist_user_counts, 0) as wishlistUserCounts'));
        $srch->addGroupBy('prodcat_id');
        $srch->addHaving('totSoldQty', '>', 0);
        $srch->addOrder('totSoldQty', $orderBy);
        $srch->addOrder('prodcat_name');
        
        if($export == 'export' ) {
            /* Cat Tree Structure Assoc Arr[ */
            $catObj = new ProductCategory();
            $catTreeAssocArr = $catObj->getProdCatTreeStructure(0, $this->adminLangId, '', 0, '', false, false, true);
            /* ] */

            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Category', $this->adminLangId),Labels::getLabel('LBL_Sold_Quantity', $this->adminLangId), Labels::getLabel('LBL_Favorites', $this->adminLangId));
            array_push($sheetData, $arr);
            while( $row = $db->fetch($rs) ){
                $arr = array( $catTreeAssocArr[$row['prodcat_id']], $row['totSoldQty'], $row['wishlistUserCounts'] );
                array_push($sheetData, $arr);
            }
            if($orderBy == "DESC" ) {
                CommonHelper::convertToCsv($sheetData, 'Top_Categories_Report_'.date("d-M-Y").'.csv', ','); exit;
            } else {
                CommonHelper::convertToCsv($sheetData, 'Bad_Categories_Report_'.date("d-M-Y").'.csv', ','); exit;
            }
        } else {
            /* Cat Tree Structure Assoc Arr[ */
            $catObj = new ProductCategory();
            $catTreeAssocArr = $catObj->getProdCatTreeStructure(0, $this->adminLangId, '', 0, '', false, false);
            /* ] */
            /* echo $srch->getQuery(); die; */
            $rs = $srch->getResultSet();
            $arr_listing = $db->fetchAll($rs);
            $this->set("arr_listing", $arr_listing);
            $this->set('pageCount', $srch->pages());
            $this->set('recordCount', $srch->recordCount());
            $this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('postedData', $post);
            $this->set('catTreeAssocArr', $catTreeAssocArr);
            $this->_template->render(false, false);
        }
    }

    public function export()
    {
        $this->search('export');
    }

    private function getSearchForm()
    {
        $frm = new Form('frmTopCategoriesReportSearch');
        $frm->addHiddenField('', 'page', 1);
        $frm->addSelectBox(Labels::getLabel('LBL_Record_Per_Page', $this->adminLangId), 'pagesize', array( 10 => '10', 20 => '20', 30 => '30', 50 => '50'), '', array(), '');
        $frm->addHiddenField('', 'order_by', 'DESC');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
?>
