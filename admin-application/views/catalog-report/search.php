<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arrFlds = array(
	'title'	=>	Labels::getLabel('LBL_Title',$adminLangId),
	'totOrders' => Labels::getLabel('LBL_No._of_Orders',$adminLangId),
	'sold_qty'	=>Labels::getLabel('LBL_Sold_Qty.',$adminLangId).'<br/>'.Labels::getLabel('LBL_(Sold_-_Refund_Qty)',$adminLangId),
	'total'		=>	Labels::getLabel('LBL_Total(A)',$adminLangId),
	'shipping'	=>	Labels::getLabel('LBL_Shipping(B)',$adminLangId),
	'tax'		=>	Labels::getLabel('LBL_Tax(C)',$adminLangId),
	'sub_total'		=>	Labels::getLabel('LBL_Total(A+B+C)',$adminLangId),
	'commission'	=>	Labels::getLabel('LBL_Commission',$adminLangId)
);

$tbl = new HtmlElement('table', 
array('width'=>'100%', 'class'=>'table table-responsive table--hovered'));

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arrFlds as $val) {
	$e = $th->appendElement('th', array(), $val, true);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){ 
	$sr_no++;
	$tr = $tbl->appendElement('tr');
		
	foreach ($arrFlds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			
			case 'title':
				$name = $row['product_name'];
				/* if( $row['selprod_title'] != '' ){
					$name .= '<br/><strong>'.Labels::getLabel('LBL_Custom_Title',$adminLangId).': </strong>'. $row['selprod_title'];
				}
				if( $row['grouped_option_name'] != '' ){
					$groupedOptionNameArr = explode(',', $row['grouped_option_name']);
					$groupedOptionValueArr = explode(',', $row['grouped_optionvalue_name']);
					if( !empty($groupedOptionNameArr) ){
						foreach( $groupedOptionNameArr as $key => $optionName ){
							$name .= '<br/><strong>' . $optionName.':</strong> '.$groupedOptionValueArr[$key];
						}
					}
				}
				
				
				*/
				if( $row['brand_name'] != '' ){
					$name .= "<br/><strong>".Labels::getLabel('LBL_Brand',$adminLangId).": </strong>" . $row['brand_name'];
				}
				$td->appendElement('plaintext', array(), $name, true );
			break;
			
			case 'totOrders':
				$td->appendElement('plaintext', array(), $row['totOrders']);
			break;
			
			case 'sold_qty':
				$td->appendElement('plaintext', array(), $row['totSoldQty']);
			break;
			
			case 'total':
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['total'], true, true));
			break;
			
			case 'shipping':
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['shippingTotal'], true, true) );
			break;
			
			case 'tax':
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['taxTotal'], true, true));
			break;
			
			case 'sub_total':
				$subTotal = $row['total'] + $row['shippingTotal'] + $row['taxTotal'];
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($subTotal, true, true) );
			break;
			
			case 'commission':
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['commission'], true, true) );
			break;
			
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if( count($arr_listing) == 0 ){
	$tbl->appendElement('tr')->appendElement('td', array(
	'colspan'=>count($arrFlds)), 
	Labels::getLabel('LBL_No_Records_Found',$adminLangId)
	);
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmCatalogReportSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>