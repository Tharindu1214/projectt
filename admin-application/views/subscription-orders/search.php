<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
	'listserial'	=>	Labels::getLabel('LBL_',$adminLangId),
	'order_id'=>Labels::getLabel('LBL_Order_ID',$adminLangId),
	'buyer_user_name'=>Labels::getLabel('LBL_Customer_Name',$adminLangId),
	'order_date_added'=>Labels::getLabel('LBL_Order_Date',$adminLangId),
	'order_net_amount'=>Labels::getLabel('LBL_Total',$adminLangId),
	'order_is_paid'=>Labels::getLabel('LBL_Payment_Status',$adminLangId),
	'action' => Labels::getLabel('LBL_Action',$adminLangId),
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}
$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($ordersList as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'order_id':
				$td->appendElement('a', array('target' => '_blank', 'href' => CommonHelper::generateUrl('SubscriptionOrders','view',array($row['order_id']))), $row[$key], true);
			break;
			case 'buyer_user_name':
				if($canViewUsers){
						$td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("'.CommonHelper::generateUrl('Users').'", '.$row['user_id'].')'), $row[$key], true);
				} else {
					$td->appendElement('plaintext', array(), $row[$key], true);
				}
				$td->appendElement('plaintext', array(), '<br/>'.$row['buyer_email'], true);
			break;
			case 'order_net_amount':
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['order_net_amount'], true, true) );
			break;
			case 'order_date_added':
				$td->appendElement('plaintext',array(),FatDate::format($row[$key],true,true,
				FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())));
			break;
			case 'order_is_paid':
				$cls = 'label-info';
				switch ($row[$key]){
					case Orders::ORDER_IS_PENDING :
						$cls = 'label-info';
					break;
					case Orders::ORDER_IS_PAID :
						$cls = 'label-success';
					break;
					case Orders::ORDER_IS_CANCELLED :
						$cls = 'label-danger';
					break;
				}

				$td->appendElement('span', array('class'=>'label '.$cls), Orders::getOrderPaymentStatusArr($adminLangId)[$row[$key]] );
			break;
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));

				$li = $ul->appendElement("li",array('class'=>'droplink'));
				$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_View_Order_Detail',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
				$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
				$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));

				$innerLi=$innerUl->appendElement('li');
				$innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('SubscriptionOrders','view',array($row['order_id'])),'class'=>'button small green redirect--js','title'=>Labels::getLabel('LBL_View_Order_Detail',$adminLangId)),Labels::getLabel('LBL_View_Order_Detail',$adminLangId), true);
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if (count($ordersList) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
}
echo $tbl->getHtml();
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmSubscriptionOrderSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
