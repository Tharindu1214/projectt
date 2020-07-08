<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php	
$arr_flds = array(	
	'user_name'=>Labels::getLabel('LBL_Name',$adminLangId),	
	'credential_email'=>Labels::getLabel('LBL_Email',$adminLangId),				
	'user_regdate'=>Labels::getLabel('LBL_Reg._Date',$adminLangId),				
	'totUserBalance'=>Labels::getLabel('LBL_Balance',$adminLangId),
);

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
		switch ( $key ){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			
			case 'user_regdate':
				$td->appendElement('plaintext', array(), FatDate::format($row[$key]));
			break;
			
			case 'totUserBalance':			
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat( $row[$key], true, true ));
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
		'name' => 'frmAdvertisersReportSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>