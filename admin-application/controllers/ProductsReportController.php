<?php
class ProductsReportController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewProductsReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditProductsReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewProductsReport();
        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search($type = false)
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
        $cnd = $opSrch->addCondition('o.order_is_paid', '=', Orders::ORDER_IS_PAID);
        $cnd->attachCondition('pmethod_code', '=', 'cashondelivery');
        $opSrch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
        $opSrch->addMultipleFields(
            array('op_selprod_id', 'COUNT(op_order_id) as totOrders', 'SUM(op_qty - op_refund_qty) as totSoldQty',
            'SUM( (op_unit_price) * op_qty - op_refund_amount ) as total', '(SUM(opship.opcharge_amount)) as shippingTotal', '(SUM(optax.opcharge_amount)) as taxTotal', 'SUM(op_commission_charged - op_refund_commission) as commission' )
        );
        $opSrch->addGroupBy('op_selprod_id');
        /* ] */


        /* get Seller product Options[ */
        $spOptionSrch = new SearchBase(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'spo');
        $spOptionSrch->joinTable(OptionValue::DB_TBL, 'INNER JOIN', 'spo.selprodoption_optionvalue_id = ov.optionvalue_id', 'ov');
        $spOptionSrch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ov_lang.optionvaluelang_optionvalue_id = ov.optionvalue_id AND ov_lang.optionvaluelang_lang_id = '.$this->adminLangId, 'ov_lang');
        $spOptionSrch->joinTable(Option::DB_TBL, 'INNER JOIN', '`option`.option_id = ov.optionvalue_option_id', '`option`');
        $spOptionSrch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', '`option`.option_id = option_lang.optionlang_option_id AND option_lang.optionlang_lang_id = '.$this->adminLangId, 'option_lang');
        $spOptionSrch->doNotCalculateRecords();
        $spOptionSrch->doNotLimitRecords();
        $spOptionSrch->addGroupBy('spo.selprodoption_selprod_id');
        $spOptionSrch->addMultipleFields(array('spo.selprodoption_selprod_id', 'IFNULL(option_name, option_identifier) as option_name', 'IFNULL(optionvalue_name, optionvalue_identifier) as optionvalue_name', 'GROUP_CONCAT(option_name) as grouped_option_name', 'GROUP_CONCAT(optionvalue_name) as grouped_optionvalue_name'));
        /* ] */

        /* Sub Query to get, how many users added current product in his/her wishlist[ */
        $uWsrch = new UserWishListProductSearch($this->adminLangId);
        $uWsrch->doNotCalculateRecords();
        $uWsrch->doNotLimitRecords();
        $uWsrch->joinWishLists();
        $uWsrch->addMultipleFields(array( 'uwlp_selprod_id', 'uwlist_user_id' ));
        /* ] */

        $srch = new ProductSearch($this->adminLangId, '', '', false, false, false);
        $srch->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'p.product_id = selprod.selprod_product_id', 'selprod');
        $srch->joinTable(SellerProduct::DB_LANG_TBL, 'LEFT OUTER JOIN', 'selprod.selprod_id = sprod_l.selprodlang_selprod_id AND sprod_l.selprodlang_lang_id = '.$this->adminLangId, 'sprod_l');
        $srch->joinSellers();
        $srch->joinBrands($this->adminLangId, false, true);
        //$srch->addCondition('brand_id', '!=', 'NULL');
        $srch->joinShops($this->adminLangId, false, false);
        $srch->joinTable('(' . $spOptionSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'selprod_id = spoq.selprodoption_selprod_id', 'spoq');
        $srch->joinTable('(' . $opSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'selprod.selprod_id = opq.op_selprod_id', 'opq');
        $srch->joinTable('(' . $uWsrch->getQuery() . ')', 'LEFT OUTER JOIN', 'tquwl.uwlp_selprod_id = selprod.selprod_id', 'tquwl');
        $srch->joinProductToCategory();
        $srch->addCondition('selprod.selprod_id', '!=', 'NULL');
        $srch->addOrder('totSoldQty', 'desc');
        $srch->addOrder('tp_l.product_name');
        $srch->addOrder('selprod_title');
        $srch->addOrder('selprod_id');
        $srch->addMultipleFields(array('product_id', 'product_name', 'selprod_id', 'selprod_code', 'selprod_user_id', 'selprod_title', 'selprod_price', 'IFNULL(totOrders, 0) as totOrders', 'IFNULL(totSoldQty, 0) as totSoldQty', 'grouped_option_name', 'grouped_optionvalue_name', 'IFNULL(s_l.shop_name, shop_identifier) as shop_name', 'opq.total', 'opq.shippingTotal', 'opq.taxTotal', 'opq.commission', 'IFNULL(tb_l.brand_name, brand_identifier) as brand_name', 'count(distinct tquwl.uwlist_user_id) as followers'));

        /* groupby added, because if same product is linked with multiple categories, then showing in repeat for each category[ */
        $srch->addGroupBy('selprod_id');
        /* ] */

        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING);
        if (!empty($keyword)) {
            $srch->addKeywordSearch($keyword);
        }

        $shop_id = FatApp::getPostedData('shop_id', null, '');
        if ($shop_id) {
            $shop_id = FatUtility::int($shop_id);
            $srch->addShopIdCondition($shop_id);
        }

        $brand_id = FatApp::getPostedData('brand_id', null, '');
        if ($brand_id) {
            $brand_id = FatUtility::int($brand_id);
            $srch->addBrandCondition($brand_id);
        }

        $category_id = FatApp::getPostedData('category_id', null, '');
        if ($category_id) {
            $category_id = FatUtility::int($category_id);
            $srch->addCategoryCondition($category_id);
        }

        $price_from = FatApp::getPostedData('price_from', null, '');
        if (!empty($price_from)) {
            $min_price_range_default_currency =  CommonHelper::getDefaultCurrencyValue($price_from, false, false);
            $srch->addCondition('selprod_price', '>=', $min_price_range_default_currency);
        }

        $price_to = FatApp::getPostedData('price_to', null, '');
        if (!empty($price_to)) {
            $max_price_range_default_currency =  CommonHelper::getDefaultCurrencyValue($price_to, false, false);
            $srch->addCondition('selprod_price', '<=', $max_price_range_default_currency);
        }

        if ($type == 'export') {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Title', $this->adminLangId), Labels::getLabel('LBL_Options_(If_Any)', $this->adminLangId), Labels::getLabel('LBL_Brand', $this->adminLangId), Labels::getLabel('LBL_Shop_Name', $this->adminLangId), Labels::getLabel('LBL_Unit_Price', $this->adminLangId),Labels::getLabel('LBL_No._Of_Orders', $this->adminLangId),Labels::getLabel('LBL_Sold_QTY', $this->adminLangId),Labels::getLabel('LBL_Total(A)', $this->adminLangId),Labels::getLabel('LBL_Shipping(B)', $this->adminLangId),Labels::getLabel('LBL_Tax(C)', $this->adminLangId),Labels::getLabel('LBL_Total(A+B+C)', $this->adminLangId),Labels::getLabel('LBL_Commission', $this->adminLangId));
            array_push($sheetData, $arr);
            while ($row = $db->fetch($rs)) {
                $name = $row['product_name'];
                if ($row['selprod_title'] != '') {
                    $name .= "\n". Labels::getLabel('LBL_Custom_Title:', $this->adminLangId).' '. $row['selprod_title'];
                }
                $optionsData = '';
                if ($row['grouped_option_name'] != '') {
                    $groupedOptionNameArr = explode(',', $row['grouped_option_name']);
                    $groupedOptionValueArr = explode(',', $row['grouped_optionvalue_name']);
                    if (!empty($groupedOptionNameArr)) {
                        foreach ($groupedOptionNameArr as $key => $optionName) {
                            $optionsData .= $optionName.': '.$groupedOptionValueArr[$key]. "\n" ;
                        }
                    }
                }

                $brandName = '';
                if ($row['brand_name'] != '') {
                    $brandName = $row['brand_name'];
                }

                $shopName = '';
                if ($row['shop_name'] != '') {
                    $shopName = $row['shop_name'];
                }
                $price = CommonHelper::displayMoneyFormat($row['selprod_price'], true, true);
                $total = CommonHelper::displayMoneyFormat($row['total'], true, true);
                $shipping = CommonHelper::displayMoneyFormat($row['shippingTotal'], true, true);
                $tax = CommonHelper::displayMoneyFormat($row['taxTotal'], true, true);
                $subTotal = $row['total'] + $row['shippingTotal'] + $row['taxTotal'];
                $subTotal = CommonHelper::displayMoneyFormat($subTotal, true, true);
                $commission = CommonHelper::displayMoneyFormat($row['commission'], true, true);
                $arr = array($name, $optionsData, $brandName, $shopName, $price, $row['totOrders'], $row['totSoldQty'], $total, $shipping, $tax, $subTotal, $commission );
                array_push($sheetData, $arr);
            }

            CommonHelper::convertToCsv($sheetData, 'Products_Report_'.date("d-M-Y").'.csv', ',');
            exit;
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
        $frm = new Form('frmProductsReportSearch');
        $frm->addHiddenField('', 'page', 1);
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $frm->addTextBox(Labels::getLabel('LBL_Shop', $this->adminLangId), 'shop_name');
        $frm->addTextBox(Labels::getLabel('LBL_Brand', $this->adminLangId), 'brand_name');
        $frm->addHiddenField('', 'shop_id', 0);
        $frm->addHiddenField('', 'brand_id', 0);
        $prodCatObj = new ProductCategory();
        $categoriesAssocArr = $prodCatObj->getProdCatTreeStructure(0, $this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Category', $this->adminLangId), 'category_id', $categoriesAssocArr);

        $frm->addTextBox(Labels::getLabel('LBL_Price_From', $this->adminLangId), 'price_from');
        $frm->addTextBox(Labels::getLabel('LBL_Price_To', $this->adminLangId), 'price_to');

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
