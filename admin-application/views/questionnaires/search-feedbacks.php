<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
	'listserial'=>Labels::getLabel('LBL_Sr_No.',$adminLangId),
	'qfeedback_user_name'=>Labels::getLabel('LBL_User_Name',$adminLangId),
	'qfeedback_user_email'=>Labels::getLabel('LBL_User_Email',$adminLangId),
	'qfeedback_added_on'=>Labels::getLabel('LBL_Posted_On',$adminLangId),
	'action' => Labels::getLabel('LBL_Action',$adminLangId),
);
$tbl = new HtmlElement('table', 
array('width'=>'100%', 'class'=>'table table-responsive'));

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){ 
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	if($row['questionnaire_active']==0) {
		$tr->setAttribute ("class","fat-inactive");
	}
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'scheduled_date':
				$td->appendElement('plaintext', array(), FatDate::format($row['questionnaire_start_date']) .' - '.FatDate::format($row['questionnaire_end_date']));
			break;
			case 'response_count':
				$td->appendElement('plaintext', array(), $row['qFeedbackCount']);
			break;
			case 'questionnaire_identifier':
				if($row['questionnaire_name']!=''){
					$td->appendElement('plaintext', array(), $row['questionnaire_name'], true);
					$td->appendElement('br', array());
					$td->appendElement('plaintext', array(), '('.$row[$key].')', true);
				}else{
					$td->appendElement('plaintext', array(), $row[$key], true);
				}
			break;						
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"));
				$li = $ul->appendElement("li");
				$li->appendElement('a', array( 'href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_View_Report',$adminLangId),"onclick"=>"viewFeedback(".$row['qfeedback_id']."); return false;" ),	'<i class="ion-eye icon"></i>', true);
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
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmFeedbackSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>