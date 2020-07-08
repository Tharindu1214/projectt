<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
	'op_invoice_number'=>	Labels::getLabel('LBL_INV_No',$adminLangId),
	'vendor'=>Labels::getLabel('LBL_Seller',$adminLangId),
	'buyer_name'=>Labels::getLabel('LBL_Customer',$adminLangId),
	'order_date_added'=>Labels::getLabel('LBL_Date',$adminLangId),
	'order_net_amount'=>Labels::getLabel('LBL_Amount',$adminLangId),
	'op_status_id'=>Labels::getLabel('LBL_Status',$adminLangId),
	'action' => Labels::getLabel('LBL_Action',$adminLangId),
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}
$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($vendorOrdersList as $sn=>$row){  /* CommonHelper::printArray($row); */
	$sr_no++;
	$tr = $tbl->appendElement('tr');

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'op_invoice_number':
				$td->appendElement('a', array('target' => '_blank', 'href' => CommonHelper::generateUrl('SellerOrders','view',array($row['op_id']))), $row[$key], true);
			break;
			case 'vendor':
				$td->appendElement('plaintext', array(), '<strong>'.Labels::getLabel('LBL_Seller_Name',$adminLangId).':  </strong>', true);
				if($canViewUsers){
					$td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("'.CommonHelper::generateUrl('Users').'", '.$row['op_selprod_user_id'].')'), $row['op_shop_owner_name'], true);
				} else {
					$td->appendElement('plaintext', array(), $row['op_shop_owner_name'], true);
				}
				$txt = '<br/><strong>'.Labels::getLabel('LBL_Shop',$adminLangId).':  </strong>'.$row['op_shop_name'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_User_Name',$adminLangId).':  </strong>'.$row['op_shop_owner_username'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_Email',$adminLangId).':   </strong><a href="mailto:'.$row['op_shop_owner_email'].'">'.$row['op_shop_owner_email'].'</a>';
				/* $txt .= '<br/><strong>'.Labels::getLabel('LBL_Phone',$adminLangId).':   </strong>'.$row['op_shop_owner_phone']; */
				$td->appendElement('plaintext', array(), $txt, true);
			break;
			case 'buyer_name':
				$td->appendElement('plaintext', array(), '<strong>'.Labels::getLabel('LBL_Name',$adminLangId).':  </strong>', true);
				if($canViewUsers){
					$td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("'.CommonHelper::generateUrl('Users').'", '.$row['user_id'].')'), $row[$key], true);
				} else {
					$td->appendElement('plaintext', array(), $row[$key], true);
				}
				$txt = '<br/><strong>'.Labels::getLabel('LBL_User_Name',$adminLangId).':  </strong>'.$row['buyer_username'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_Email',$adminLangId).':  </strong><a href="mailto:'.$row['buyer_email'].'">'.$row['buyer_email'].'</a>';
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_Phone',$adminLangId).':  </strong>'.$row['buyer_phone'];
				$td->appendElement('plaintext', array(), $txt, true);
			break;
			case 'order_net_amount':
				$amt = CommonHelper::orderProductAmount($row,'netamount',false,USER::USER_TYPE_SELLER);
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($amt, true, true) );
			break;
			case 'op_status_id':
				$td->appendElement('plaintext', array(), $row['orderstatus_name'], true);
			break;
			case 'order_date_added':
				$td->appendElement('plaintext',array(),FatDate::format($row[$key],true,true,
				FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())));
			break;
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));

				$li = $ul->appendElement("li",array('class'=>'droplink'));
				$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
				$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
				$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));

				$innerLi=$innerUl->appendElement('li');
				$innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('SellerOrders','view',array($row['op_id'])),'class'=>'button small green redirect--js','title'=>Labels::getLabel('LBL_View_Order_Detail',$adminLangId)),Labels::getLabel('LBL_View_Order_Detail',$adminLangId), true);

				if($canEdit){
					$innerLi=$innerUl->appendElement('li');
					$innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('SellerOrders','CancelOrder',array($row['op_id'])),'class'=>'button small green redirect--js','title'=>Labels::getLabel('LBL_Cancel_Order',$adminLangId)),Labels::getLabel('LBL_Cancel_Order',$adminLangId), true);

					//$innerLi=$innerUl->appendElement('li');
					//$innerLi->appendElement('a', array('href'=>'javascript:void(0)','onclick' => "cancelOrder('".$row['op_id']."')",'class'=>'button small green','title'=>Labels::getLabel('LBL_Cancel_Order',$adminLangId),'target'=>'_new'),Labels::getLabel('LBL_Cancel_Order',$adminLangId), true);


				}
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if (count($vendorOrdersList) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
}
echo $tbl->getHtml();
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmVendorOrderSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
