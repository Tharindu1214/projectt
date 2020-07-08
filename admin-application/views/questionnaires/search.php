<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
	'listserial'=>Labels::getLabel('LBL_Sr_No.',$adminLangId),
	'questionnaire_identifier'=>Labels::getLabel('LBL_Questionnaire_Name',$adminLangId),
	'scheduled_date'=>Labels::getLabel('LBL_Scheduled_Date',$adminLangId),
	'qnCount'=>Labels::getLabel('LBL_No._of_Questions',$adminLangId),
	'response_count'=>Labels::getLabel('LBL_Response_Count',$adminLangId),
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
	if($row['questionnaire_active'] != applicationConstants::ACTIVE) {
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
				if($canEdit){
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit',$adminLangId),
						"onclick"=>"questionnaireForm(".$row['questionnaire_id'].")"),'<i class="ion-edit icon"></i>', true);
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>CommonHelper::generateUrl('Questionnaires','questions',array($row['questionnaire_id'])),'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Link_Questions',$adminLangId)),
						'<i class="ion-levels icon"></i>', true);
					$li = $ul->appendElement("li");
					$li->appendElement('a', array(
						'href'=>"javascript:void(0)", 'class'=>'button small green','title'=>Labels::getLabel('LBL_Generate_Link',$adminLangId),"onclick"=>"generateLink(".$row['questionnaire_id'].")"),
						'<i class="ion-link icon"></i>', true);
					$li = $ul->appendElement("li");
					$li->appendElement('a', array(
						'href'=>CommonHelper::generateUrl('Questionnaires','viewReport',array($row['questionnaire_id'])), 'class'=>'button small green','title'=>Labels::getLabel('LBL_View_Report',$adminLangId)),
						'<i class="ion-eye icon"></i>', true);
					$li = $ul->appendElement("li");
					$li->appendElement('a', array(
						'href'=>"javascript:void(0)", 'class'=>'button small green','title'=>Labels::getLabel('LBL_Delete',$adminLangId),"onclick"=>"deleteRecord(".$row['questionnaire_id'].")"),
						'<i class="ion-android-delete icon"></i>', true);
				}
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
		'name' => 'frmQuestionnaireSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>