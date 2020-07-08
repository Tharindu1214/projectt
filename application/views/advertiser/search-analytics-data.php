<?php
$arr_flds = array(
		'listserial'=>Labels::getLabel('LBL_Sr._No', $siteLangId),
		'plog_date'=>Labels::getLabel('LBL_Date', $siteLangId),
		'clicks'=>Labels::getLabel('LBL_Clicks', $siteLangId),
		'impressions'=>Labels::getLabel('LBL_Impressions', $siteLangId),
		/* 'orders'=>Labels::getLabel('LBL_Orders', $siteLangId),	 */

	);
$tbl = new HtmlElement('table',
array('width'=>'100%', 'class'=>'table table--orders table-responsive','id'=>'promotions'));

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

			case 'plog_date':
				$td->appendElement('plaintext', array(),FatDate::format($row[$key]));
			break;

			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmPromotionSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'promotion_id'=>$promotion_id);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
