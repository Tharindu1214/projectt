<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$arrFlds1 = array(
	'listserial'=>Labels::getLabel('LBL_Sr_no.',$siteLangId),
	'order_date'=>Labels::getLabel('LBL_Date',$siteLangId),
	'totOrders'=>Labels::getLabel('LBL_No._of_Orders',$siteLangId),
);
$arrFlds2  = array(
	'listserial'=>Labels::getLabel('LBL_Sr_no.',$siteLangId),
	'op_invoice_number'=>Labels::getLabel('LBL_Invoice_Number',$siteLangId),
);
$arr = array(
	'totQtys'=>Labels::getLabel('LBL_No._of_Qty',$siteLangId),
	'totRefundedQtys'=>Labels::getLabel('LBL_Refunded_Qty',$siteLangId),
	'inventoryValue'=>Labels::getLabel('LBL_Inventory_Value',$siteLangId),
	'orderNetAmount'=>Labels::getLabel('LBL_Order_Net_Amount',$siteLangId),
	'taxTotal'=>Labels::getLabel('LBL_Tax_Charged',$siteLangId),
	'shippingTotal'=>Labels::getLabel('LBL_Shipping_Charges',$siteLangId),
	'totalRefundedAmount'=>Labels::getLabel('LBL_Refunded_Amount',$siteLangId),
	'totalSalesEarnings'=>Labels::getLabel('LBL_Sales_Earnings',$siteLangId)
);
if(empty($orderDate)){
	$arr_flds = array_merge($arrFlds1,$arr);
}else{
	$arr_flds = array_merge($arrFlds2,$arr);
}

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => ''));
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));
foreach ($arrListing as $sn => $row){
	$sr_no++;
	$tr = $tbl->appendElement('tr',array('class' =>'' ));

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;

			case 'order_date':
				$td->appendElement('plaintext', array(), '<a href="'.CommonHelper::generateUrl('Reports',
						'salesReport',array($row[$key])).'">'.FatDate::format($row[$key]).'</a>',true);
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
if( count($arrListing) == 0 ){
    echo $tbl->getHtml();
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
} else {
	echo '<div class="box__head"><div class="btn-group">';
	if(!empty($orderDate)){
		echo '<a href="'.CommonHelper::generateUrl('Reports','SalesReport').'" class="btn btn--primary btn--sm">'.Labels::getLabel('LBL_Back',$siteLangId).'</a>';
	}
	echo '</div></div>';
    echo $tbl->getHtml();
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmSalesReportSrchPaging') );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToSalesReportSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
