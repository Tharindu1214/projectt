<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
	'listserial'	=>	Labels::getLabel('LBL_Sr_no.',$adminLangId),
	'polling_question'	=>	Labels::getLabel('LBL_Question',$adminLangId),
	'count_yes'	=>	Labels::getLabel('LBL_Yes',$adminLangId),
	'count_no'	=>	Labels::getLabel('LBL_No',$adminLangId),
	'count_maybe'	=>	Labels::getLabel('LBL_May_be',$adminLangId),
	'polling_active'	=>	Labels::getLabel('LBL_Status',$adminLangId),
	'action'	=>	Labels::getLabel('LBL_Action',$adminLangId),
	);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	if($row['polling_active'] != applicationConstants::ACTIVE) {
		$tr->setAttribute ("class","fat-inactive");
	}
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'polling_question':				
				$td->appendElement('plaintext', array(), $row['polling_question'],true);				
			break;
			case 'polling_active':				
				$td->appendElement('plaintext', array(), $activeInactiveArr[$row[$key]],true);				
			break;
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"));
				if( $canEdit ){
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"pollingForm(".$row['polling_id'].")"),'<i class="ion-edit icon"></i>', true);
				}
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
	'name' => 'frmPollingSearchPaging'
) );
$pagingArr = array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>