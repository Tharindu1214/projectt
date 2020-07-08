<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arrFlds = array(
	'name'	=>	Labels::getLabel('LBL_Products',$adminLangId),	
	'wishlistUserCounts'	=>	Labels::getLabel('LBL_WishList_User_Counts',$adminLangId)
);
if($topPerformed){
	$arrFlds['totSoldQty'] = Labels::getLabel('LBL_Sold_Quantity',$adminLangId);
}else{
	$arrFlds['totRefundQty'] = Labels::getLabel('LBL_Refund_Quantity',$adminLangId);
}

$tbl = new HtmlElement('table', 
array('width'=>'100%', 'class'=>'table table-responsive table--hovered'));

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arrFlds as $val) {
	$e = $th->appendElement('th', array(), $val, true);
}

$sr_no = $page == 1 ? 0 : $pageSize * ( $page - 1 );
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
		
	foreach ($arrFlds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			
			case 'name':
				$name = $row['op_product_name'];
				if( $row['op_selprod_title'] != '' ){
					$name .= "<br/><strong>".Labels::getLabel('LBL_Custom_Title',$adminLangId).": </strong>".$row['op_selprod_title'];
				}
				
				if( $row['op_selprod_options'] != '' ){
					$name .= "<br/><strong>".Labels::getLabel('LBL_Options',$adminLangId).": </strong>".$row['op_selprod_options'];
				}
				
				if( $row['op_brand_name'] != '' ){
					$name .= "<br/><strong>".Labels::getLabel('LBL_Brand',$adminLangId).": </strong>".$row['op_brand_name'];
				}
				
				if( $row['op_shop_name'] != '' ){
					$name .= "<br/><strong>".Labels::getLabel('LBL_Shop',$adminLangId).": </strong>". $row['op_shop_name'];
				}
				$td->appendElement('plaintext', array(), $name, true);
			break;
			
			case 'wishlistUserCounts':
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
			
			case 'totSoldQty':
				$td->appendElement('plaintext', array(), $row['totSoldQty'], true);
			break;
			
			case 'totRefundQty':
				$td->appendElement('plaintext', array(), $row['totRefundQty'],true);
			break;
			
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if (count($arr_listing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array(
	'colspan'=>count($arrFlds)), 
	Labels::getLabel('LBL_No_Records_Found',$adminLangId)
	);
}
echo $tbl->getHtml();