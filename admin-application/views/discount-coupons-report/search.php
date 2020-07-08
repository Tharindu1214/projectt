<?php 
	$arr_flds = array(
		'coupon_code'=> Labels::getLabel('LBL_Coupon_Code',$adminLangId),
		'couponhistory_order_id'=> Labels::getLabel('LBL_Order_Id',$adminLangId),
		'credential_username'=> Labels::getLabel('LBL_Customer',$adminLangId),
		'couponhistory_amount' => Labels::getLabel('LBL_Amount',$adminLangId),
		'couponhistory_added_on' => Labels::getLabel('LBL_Date',$adminLangId),						
	);
	$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive'));
	$th = $tbl->appendElement('thead')->appendElement('tr');
	foreach ($arr_flds as $key=>$val) {					
		$e = $th->appendElement('th', array(), $val,true);
	}
	$sr_no = 0;
	foreach ($arr_listing as $sn=>$row){
		$sr_no++;
		$tr = $tbl->appendElement('tr');

		foreach ($arr_flds as $key=>$val){
			$td = $tr->appendElement('td');
			switch ($key){
				case 'credential_username':
				$td->appendElement('plaintext', array(),$row[$key], true);
				break;
				case 'couponhistory_amount':
				$td->appendElement('plaintext', array(),CommonHelper::displayMoneyFormat($row[$key]));
				break;														
				case 'couponhistory_added_on':								
				$td->appendElement('plaintext', array(), FatDate::format($row[$key]),true);
				break;												
				default:
				$td->appendElement('plaintext', array(), $row[$key], true);
				break;
			}
		}
	}
	if (count($arr_listing) == 0){
		$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
	}
	echo $tbl->getHtml();
	$postedData['page'] = $page;
	echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmHistorySearchPaging'
	) );
	$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'callBackJsFunc'=>'goToSearchPage','adminLangId'=>$adminLangId);
	$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>