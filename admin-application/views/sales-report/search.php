<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arrFlds1 = array(
	'listserial'=>Labels::getLabel('LBL_Sr_no.',$adminLangId),
	'order_date'=>Labels::getLabel('LBL_Date',$adminLangId),
	'totOrders'=>Labels::getLabel('LBL_No._of_Orders',$adminLangId),
	'orderNetAmount'=>Labels::getLabel('LBL_Order_Net_Amount',$adminLangId),
);
$arrFlds2  = array(
	'listserial'=>Labels::getLabel('LBL_Sr_no.',$adminLangId),
	'op_invoice_number'=>Labels::getLabel('LBL_Invoice_Number',$adminLangId),
	'order_net_amount'=>Labels::getLabel('LBL_Order_Net_Amount',$adminLangId),
);
$arr = array(
	'inventoryValue'=>Labels::getLabel('LBL_Inventory_Value',$adminLangId),
	'totQtys'=>Labels::getLabel('LBL_No._of_Qty',$adminLangId),
	'totRefundedQtys'=>Labels::getLabel('LBL_Refunded_Qty',$adminLangId),
	'taxTotal'=>Labels::getLabel('LBL_Tax_Charged',$adminLangId),
	'shippingTotal'=>Labels::getLabel('LBL_Shipping_Charges',$adminLangId),
	'totalRefundedAmount'=>Labels::getLabel('LBL_Refunded_Amount',$adminLangId),
	'totalSalesEarnings'=>Labels::getLabel('LBL_Sales_Earnings',$adminLangId)
);
if(empty($orderDate)){
	$arr_flds = array_merge($arrFlds1,$arr);
}else{
	$arr_flds = array_merge($arrFlds2,$arr);
}


$tbl = new HtmlElement('table',
array('width'=>'100%', 'class'=>'table table-responsive table--hovered'));

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'order_date':
				$td->appendElement('plaintext', array(), '<a href="'.CommonHelper::generateUrl('SalesReport','index',array($row[$key])).'">'.FatDate::format($row[$key]).'</a>',true);
			break;
			case 'order_net_amount':
				$amt = CommonHelper::orderProductAmount($row);
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($amt, true, true) );
			break;
			case 'totalSalesEarnings':
			case 'totalRefundedAmount':
			case 'inventoryValue':
			case 'orderNetAmount':
			case 'taxTotal':
			case 'shippingTotal':
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key],true,true));
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if (count($arr_listing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array(
	'colspan'=>count($arr_flds)),
	Labels::getLabel('LBL_No_Records_Found',$adminLangId)
	);
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmSalesReportSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
