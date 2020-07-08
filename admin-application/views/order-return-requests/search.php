<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
	'orrequest_reference'=> Labels::getLabel('LBL_Refernce_Number',$adminLangId),
	'buyer_detail'=>Labels::getLabel('LBL_Buyer_Details',$adminLangId),
	'vendor_detail'=>Labels::getLabel('LBL_Seller_Details',$adminLangId),
	'product'	=>	Labels::getLabel('LBL_Product',$adminLangId),
	'orrequest_qty'	=>	Labels::getLabel('LBL_Qty',$adminLangId),
	/* 'orrequest_type'	=>	Labels::getLabel('LBL_Request_Type',$adminLangId), */
	/* 'amount'=>Labels::getLabel('LBL_Amount',$adminLangId),	 */	
	'orrequest_date'=>Labels::getLabel('LBL_Date',$adminLangId),
	'orrequest_status'=>Labels::getLabel('LBL_Status',$adminLangId),
	'action' => Labels::getLabel('LBL_Action',$adminLangId),
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}
$sr_no = $page==1?0:$pageSize*($page-1);
//CommonHelper::printArray($arrListing);
foreach ($arrListing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'orrequest_id':
				$td->appendElement('plaintext', array(), $row['orrequest_reference'] /* CommonHelper::formatOrderReturnRequestNumber($row["orrequest_id"]) */ );
			break;
			case 'buyer_detail':
				$txt = '<strong>'.Labels::getLabel('LBL_N',$adminLangId).':  </strong>'.$row['buyer_name'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_U',$adminLangId).':  </strong>'.$row['buyer_username'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_E',$adminLangId).':  </strong>'.$row['buyer_email'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_P',$adminLangId).':  </strong>'.$row['buyer_phone'];
				$td->appendElement('plaintext', array(), $txt, true);
			break;
			case 'vendor_detail':
				$txt = '<strong>'.Labels::getLabel('LBL_N',$adminLangId).':  </strong>'.$row['seller_name'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_U',$adminLangId).':  </strong>'.$row['seller_username'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_E',$adminLangId).':  </strong>'.$row['seller_email'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_P',$adminLangId).':  </strong>'.$row['seller_phone'];
				$td->appendElement('plaintext', array(), $txt, true);
			break;
			case 'product':
				$txt = '';
				if( $row['op_selprod_title'] != '' ){
					$txt .= $row['op_selprod_title'].'<br/>'.'<small>'.$row['op_product_name'].'</small>';
				} else {
					$txt .= $row['op_product_name'];
				}
				if( $row['op_selprod_options'] != '' ){
					$txt .= '<br/>'.$row['op_selprod_options'];
				}
				if( $row['op_brand_name'] != '' ){
					$txt .= '<br/><strong>'.Labels::getLabel('LBL_Brand',$adminLangId).':  </strong> '.$row['op_brand_name'];
				}
				
				if( $row['op_shop_name'] != '' ){
					$txt .= '<br/><strong>'.Labels::getLabel('LBL_Shop',$adminLangId).':  </strong> '.$row['op_shop_name'];
				}
				
				$td->appendElement('plaintext', array(), $txt, true);
			break;
			case 'orrequest_type':
				$td->appendElement('plaintext', array(), isset($requestTypeArr[$row[$key]])?$requestTypeArr[$row[$key]]:'' , true);
			break;
			case 'orrequest_date':
				$td->appendElement('plaintext', array(), FatDate::format( $row[$key], true ), true);
			break;
			case 'amount':
				$amt = '';
				$priceTotalPerItem = CommonHelper::orderProductAmount($row,'netamount',true);
				$price = 0;								
				if($row['orrequest_status'] != OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED){
					if(FatApp::getConfig('CONF_RETURN_SHIPPING_CHARGES_TO_CUSTOMER',FatUtility::VAR_INT,0)){
						$shipCharges = isset($row['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'])?$row['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount']:0;
						$unitShipCharges = round(($shipCharges / $row['op_qty']),2);
						$priceTotalPerItem = $priceTotalPerItem + $unitShipCharges;		
						$price = $priceTotalPerItem * $row['orrequest_qty'];
					}	
				}
				
				if(!$price){
					$price = $priceTotalPerItem * $row['orrequest_qty'];
					$price = $price + $row['op_refund_shipping'];
				}
				
				$amt = CommonHelper::displayMoneyFormat($price, true, true);				
				$td->appendElement('plaintext', array(), $amt, true);
			break;
			case 'orrequest_status':
				$td->appendElement('label', array('class'=>'label label--'.$requestTypeClassArr[$row[$key]].''), $requestStatusArr[$row[$key]]);
			break;
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
				
				$li = $ul->appendElement("li",array('class'=>'droplink'));						
				$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_View',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
				$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));	
				$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
				
				$innerLi=$innerUl->appendElement('li');
				$innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('OrderReturnRequests','view',array($row['orrequest_id'])),'class'=>'button small green redirect--js','title'=>Labels::getLabel('LBL_View',$adminLangId)),Labels::getLabel('LBL_View',$adminLangId), true);					
				
				/* if(  $canEdit && ($row['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING || $row['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED ) ){ */
					/* $li = $ul->appendElement("li");
					$li->appendElement('a', array( 'onclick'=>'return confirm("Do you really want to approve the request?");' , 'href'=>CommonHelper::generateUrl('OrderReturnRequests','Approve',array($row['orrequest_id'])), 'class'=>'button small green','title'=>'Approve'),'<i class="ion-checkmark-circled icon"></i>', true);
					
					$li = $ul->appendElement("li");
					$li->appendElement('a', array( 'onclick'=>'return confirm("Do you really want to decline the request?");' ,'href'=>CommonHelper::generateUrl('OrderReturnRequests','Cancel',array($row['orrequest_id'])), 'class'=>'button small green','title'=>'Decline'),'<i class="ion-close-circled icon"></i>', true); */
				
					/* $li = $ul->appendElement("li");
					$li->appendElement('a', array('onClick'=>'updateStatusForm('.$row['orrequest_id'].')', 'class'=>'button small green','title'=>'Edit'),'<i class="ion-edit icon"></i>', true); */
				/* } */
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if (count($arrListing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
} 
echo $tbl->getHtml();
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmOrderReturnRequestSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>