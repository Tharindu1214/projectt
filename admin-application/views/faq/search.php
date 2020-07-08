<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'dragdrop'=>'',
		'listserial'=> Labels::getLabel('LBL_Sr._No',$adminLangId),
		'faq_identifier'=> Labels::getLabel('LBL_Faq_Title',$adminLangId),				
		'action' => Labels::getLabel('LBL_Action',$adminLangId),
	);
if(!$canEdit){
	unset($arr_flds['dragdrop']);
}
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hovered','id'=>'faqs'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

//$sr_no = $page==1?0:$pageSize*($page-1);
$sr_no = 0;
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	if($row['faq_active'] == applicationConstants::ACTIVE){
		$tr->setAttribute ("id",$row['faq_id']);
	}

	if($row['faq_active'] != applicationConstants::ACTIVE) {
		$tr->setAttribute ("class","fat-inactive nodrag nodrop");
	}
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'dragdrop':
				if($row['faq_active']==1){				
					$td->appendElement('i',array('class'=>'ion-arrow-move icon'));					
					$td->setAttribute ("class",'dragHandle');
				}
			break;
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'faq_identifier':
				if($row['faq_title']!=''){
					$td->appendElement('plaintext', array(), $row['faq_title'],true);
					$td->appendElement('br', array());
					$td->appendElement('plaintext', array(), '('.$row[$key].')',true);
				}else{
					$td->appendElement('plaintext', array(), $row[$key], true);
				}				
				break;
			case 'faq_active':
					$active = "";
					if($row['faq_active']) {
						$active = 'checked';
					}
					$statucAct = ($canEdit === true) ? 'toggleStatus(this)' : '';
					$str = '<div class="checkbox-switch"><input '.$active.' type="checkbox" id="switch'.$row['faqcat_id'].'" value="'.$row['faqcat_id'].'" onclick="'.$statucAct.'"/><label for="switch'.$row['faqcat_id'].'">Toggle</label></div>';
					$td->appendElement('plaintext', array(), $str, true);
			break;
			case 'action':
				//$ul = $td->appendElement("ul",array("class"=>"actions"));
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
				$li = $ul->appendElement("li",array('class'=>'droplink'));

				$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
              		$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
              		$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
              		//$innerLiEdit=$innerUl->appendElement('li');


				if($canEdit){	
              		$innerLiEdit=$innerUl->appendElement('li');

					//$li = $ul->appendElement("li");
					$innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"addFaqForm(".$row['faq_faqcat_id'].",".$row['faq_id'].")"),Labels::getLabel('LBL_Edit',$adminLangId), true);
              		

              		$innerLiDelete=$innerUl->appendElement('li');
					//$li = $ul->appendElement("li");
					$innerLiEdit->appendElement('a', array('href'=>"javascript:void(0)", 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Delete',$adminLangId),"onclick"=>"deleteRecord(".$row['faq_id'].")"),Labels::getLabel('LBL_Delete',$adminLangId), true);
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
	'name' => 'frmFaqsSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
<script>
$(document).ready(function(){	
	$('#faqs').tableDnD({		
		onDrop: function (table, row) {
			fcom.displayProcessing();
			var order = $.tableDnD.serialize('id');			
			fcom.ajax(fcom.makeUrl('Faq', 'updateOrder'), order, function (res) {
				var ans =$.parseJSON(res);
				if(ans.status==1)
				{	
					fcom.displaySuccessMessage(ans.msg);

				}else{
					fcom.displayErrorMessage(ans.msg);
				}
			});
		},
		dragHandle: ".dragHandle",		
	});
});
</script>