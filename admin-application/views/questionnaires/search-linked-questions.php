<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'dragdrop'=>'',
		'listserial'=>Labels::getLabel('LBL_Sr_no.',$adminLangId),
		'qbank_name'=>Labels::getLabel('LBL_Question_Bank',$adminLangId),			
		'question_title'=>Labels::getLabel('LBL_Question',$adminLangId),			
		'action' => Labels::getLabel('LBL_Action',$adminLangId),
	);
if(!$canEdit){
	unset($arr_flds['dragdrop']);
}
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive','id'=>'linkedQuestions'));

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){ 
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	$tr->setAttribute ("id",$row['question_id']);
	
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'dragdrop':
				$td->appendElement('i',array('class'=>'ion-arrow-move icon'));
				$td->setAttribute ("class",'dragHandle');
			break;
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;	
			case 'question_identifier':
				if(!empty($row['question_title'])){
					$td->appendElement('plaintext', array(), $row['question_title'].'<br/>('.$row['question_identifier'].')',true);
				}
				else{
					$td->appendElement('plaintext', array(), $row['question_identifier'], true);
				}
			break;					
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"));
				if($canEdit){
					$li = $ul->appendElement("li");
					$li->appendElement('a', array(
						'href'=>"javascript:void(0)", 'class'=>'button small green','title'=>Labels::getLabel('LBL_Remove',$adminLangId),"onclick"=>"removeQuestion(".$row['questionnaire_id'].",".$row['question_id'].")"),'<i class="ion-close-round icon"></i>', true);
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
		'name' => 'frmQuestionsSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
<script>
$(document).ready(function(){
	var questionnaire_id = $('input[name="questionnaire_id"]').val();
	$('#linkedQuestions').tableDnD({
		onDrop: function (table, row) {
			$.mbsmessage('Updating display order....');
			var order = $.tableDnD.serialize('id');
			order += '&questionnaire_id='+questionnaire_id;
			fcom.ajax(fcom.makeUrl('Questionnaires', 'updateQuestionsOrder'), order, function (res) {
				var ans =$.parseJSON(res);
				if(ans.status==1)
				{
					$.mbsmessage(ans.msg,true,'alert--success');
				}else{
					$.mbsmessage(ans.msg,true,'alert--danger');
				}
			});
		},
		dragHandle: ".dragHandle",		
	});
});
</script>