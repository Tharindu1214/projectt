<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'sr'        =>    Labels::getLabel('LBL_SrNo.', $siteLangId),
    'name'        =>    Labels::getLabel('LBL_Product', $siteLangId),
    'selprod_stock'    =>    Labels::getLabel('LBL_Stock_Available', $siteLangId),
    'stock_on_order'=>    Labels::getLabel('LBL_Stock_On_Order', $siteLangId),
    'selprod_cost'    =>    Labels::getLabel('LBL_Cost_Price', $siteLangId),
    'inventory_value'    =>    Labels::getLabel('LBL_Inventory_Value_', $siteLangId).'<br/>('.Labels::getLabel('LBL_Stock_Available', $siteLangId).' * '.Labels::getLabel('LBL_Cost_Price', $siteLangId).')',
    'selprod_price'    =>    Labels::getLabel('LBL_Unit_Price', $siteLangId),
    'total_value'    =>    Labels::getLabel('LBL_Total_Value_', $siteLangId).'<br/>('.Labels::getLabel('LBL_Stock_Available', $siteLangId).' * '.Labels::getLabel('LBL_Unit_Price', $siteLangId).')'
);

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val, true);
}

$sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));
foreach ($arrListing as $sn => $listing) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' =>'' ));

    foreach ($arr_flds as $key=>$val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'sr':
                $td->appendElement('plaintext', array(), $sr_no, true);
                break;
            case 'name':
                $name = '<div class="item__title">'.$listing['product_name'].'</div>';
                if ($listing['selprod_title'] != '') {
                    $name .= '<div class="item__sub_title"><strong>'.Labels::getLabel('LBL_Custom_Title', $siteLangId).": </strong>".$listing['selprod_title'].'</div>';
                }

                if ($listing['brand_name'] != '') {
                    $name .= '<div class="item__brand">'.Labels::getLabel('LBL_Brand', $siteLangId).": </strong>".$listing['brand_name'].'</div>';
                }
                $td->setAttribute('width', '40%');
                $td->appendElement('plaintext', array(), $name, true);
                break;

            case 'selprod_stock':
                $td->appendElement('plaintext', array(), $listing['selprod_stock'], true);
                break;

            case 'stock_on_order':
                $td->appendElement('plaintext', array(), $listing['stock_on_order'], true);
                break;

            case 'selprod_cost':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($listing['selprod_cost']), true);
                break;

            case 'inventory_value':
                $inventory_value = $listing['selprod_stock'] * $listing['selprod_cost'];
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($inventory_value), true);
                break;

            case 'selprod_price':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($listing['selprod_price']), true);
                break;

            case 'total_value':
                $total_value = $listing['selprod_stock'] * $listing['selprod_price'];
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($total_value), true);
                break;

            default:
                $td->appendElement('plaintext', array(), $listing[$key], true);
                break;
        }
    }
}
echo $tbl->getHtml();
if (count($arrListing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductInventoryStockStatusSrchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToProductsInventoryStockStatusPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
