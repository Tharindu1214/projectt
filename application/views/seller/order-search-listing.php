<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
	'order_id'	=>	Labels::getLabel('LBL_Order_Id_Date', $siteLangId),
	'product'	=>	Labels::getLabel('LBL_Subscription_Package', $siteLangId),
	'ossubs_status_id'	=>	Labels::getLabel('LBL_Status', $siteLangId),
	'total'		=>	Labels::getLabel('LBL_Total', $siteLangId),
	'ossubs_till_date'	=>	Labels::getLabel('LBL_Subscription_Valid_till', $siteLangId),
	'action'	=>	Labels::getLabel('LBL_Action', $siteLangId),
);

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => ''));
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
foreach ($orders as $sn => $order){
	$sr_no++;

	$tr = $tbl->appendElement('tr',array('class' =>'' ));
	$orderDetailUrl = CommonHelper::generateUrl('seller', 'viewSubscriptionOrder', array($order['ossubs_id']) );

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'order_id':
			$txt = '<a title="'.Labels::getLabel('LBL_View_Order_Detail', $siteLangId).'" href="'.$orderDetailUrl.'">';
			$txt .= $order['ossubs_invoice_number'];
			$txt .= '</a><br/>'. FatDate::format($order['order_date_added']);
			$txt .=$order['order_id'];
			$td->appendElement('plaintext', array(), $txt , true);
			break;
			case 'product':
				$txt = '';
				if( $order['ossubs_subscription_name'] != '' ){
					$txt .=

					OrderSubscription::getSubscriptionTitle($order,$order['order_language_id']).'<br/>';
				}

				$td->appendElement('plaintext', array(), $txt , true);
			break;
			case 'ossubs_status_id':
				$txt = '';
				if($order['ossubs_status_id']==FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS') && $order['ossubs_till_date']<date("Y-m-d") )
				{
					$txt .= Labels::getLabel('LBL_Expired',$siteLangId);
				}
				else
				{
					$txt .= $orderStatuses[$order['ossubs_status_id']];
				}

				$td->appendElement('plaintext', array(), $txt , true);
			break;
			case 'total':
				$txt = '';
				// $txt .= CommonHelper::displayMoneyFormat($order['order_net_amount']);
				 $txt .= CommonHelper::displayMoneyFormat(CommonHelper::orderSubscriptionAmount($order));
				$td->appendElement('plaintext', array(), $txt, true);
			break;
			case 'status':
				$txt = $order['orderstatus_name'];
				$td->appendElement('plaintext', array(), $txt , true);
			break;
			case 'ossubs_till_date':
				if($order['ossubs_from_date']==0 || $order['ossubs_till_date']==0) $subscritpionValidTill = ''; else $subscritpionValidTill = FatDate::format($order['ossubs_from_date'])." - " .FatDate::format($order['ossubs_till_date']);
				$txt = $subscritpionValidTill;
				$td->appendElement('plaintext', array(), $txt , true);
			break;
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"),'',true);

				$li = $ul->appendElement("li");
				$li->appendElement('a', array('href'=> $orderDetailUrl, 'class'=>'',
				'title'=>Labels::getLabel('LBL_View_Order',$siteLangId)),
				'<i class="fa fa-eye"></i>', true);

				if(!$order['user_autorenew_subscription'] && date("Y-m-d")>=$order['ossubs_till_date'] && $order['ossubs_status_id']==FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS') && $order['ossubs_type']==SellerPackages::PAID_TYPE){
					$li = $ul->appendElement("li");
				$li->appendElement('a', array('href'=> CommonHelper::generateUrl('SubscriptionCheckout', 'renewSubscriptionOrder', array($order['ossubs_id']) )	, 'class'=>'',
				'title'=>Labels::getLabel('LBL_Renew_Subscription',$siteLangId)),
				'<i class="fa fa-history"></i>', true);
				}
			break;
			default:
				$td->appendElement('plaintext', array(), ''.$order[$key],true);
			break;
		}
	}
}

echo $tbl->getHtml();
if (count($orders) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmOrderSrchPaging') );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToOrderSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
