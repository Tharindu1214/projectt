<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arrFlds = array(
	'op_shop_name'	=>	Labels::getLabel('LBL_Shop_Name',$adminLangId),
	'owner_name'	=>	Labels::getLabel('LBL_Owner',$adminLangId),
	'total_sales'	=> Labels::getLabel('LBL_Sales',$adminLangId),
	'total_commission' => Labels::getLabel('LBL_Commission',$adminLangId),
);

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
			
			case 'op_shop_name':
				$td->appendElement('plaintext', array(), $row['op_shop_name'], true);
			break;
			
			case 'owner_name':
				$td->appendElement('plaintext', array(), $row['owner_name'].'<br/>('.$row['owner_email'].')', true);
			break;
			
			case 'total_sales':
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['total_sales'], true, true ), true);
			break;
			
			case 'total_commission':
				$td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['total_commission'], true, true ), true);
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
	Labels::getLabel('LBL_No_Record_Found',$adminLangId)
	);
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmCommissionReportSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);