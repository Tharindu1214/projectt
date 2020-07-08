<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'listserial'=>Labels::getLabel('LBL_Sr._No',$adminLangId),
		'prodcat_identifier'=>Labels::getLabel('LBL_Identifier_Name',$adminLangId),
		'prodcat_active'=>Labels::getLabel('LBL_Active',$adminLangId),
		'child_count' => Labels::getLabel('LBL_Subcategories',$adminLangId),
		'action' => Labels::getLabel('LBL_Action',$adminLangId),
	);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive','id'=>'prodcat'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	$tr->setAttribute ("id",$row['prodcat_id']);

	if($row['prodcat_active']==0) {
		$tr->setAttribute ("class","inactive-tr");
	}
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'prodcat_active':
					$active = "";
					if($row['prodcat_active']) {
						$active = 'checked';
					}
					$statucAct = ($canEdit === true) ? 'toggleStatus(event,this)' : '';
					$str = '<div class="checkbox-switch"><input '.$active.' type="checkbox" id="switch'.$row['prodcat_id'].'" value="'.$row['prodcat_id'].'" onclick="'.$statucAct.'"/><label for="switch'.$row['prodcat_id'].'">Toggle</label></div>';
					$td->appendElement('plaintext', array(), $str,true);
			break;
			case 'child_count':
				if($row[$key]==0){
					$td->appendElement('plaintext', array(), $row[$key], true);
				}else{
					$td->appendElement('a', array('href'=>CommonHelper::generateUrl('ProductCategories','index',array($row['prodcat_id'])),'title'=>Labels::getLabel('LBL_View_Categories',$adminLangId)),$row[$key] );
				}
			break;

			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"));
				if($canEdit){
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>CommonHelper::generateUrl('ProductCategories','form',array('general',$row['prodcat_id'])), 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"editRecord(".$row['prodcat_id'].")"),'<i class="ion-edit icon"></i>', true);

					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>"javascript:;", 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Delete',$adminLangId),"onclick"=>"deleteRecord(".$row['prodcat_id'].")"),'<i class="ion-android-delete icon"></i>', true);
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
/* echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmUserSearchPaging','id'=>'pretend_search_form'
) ); */
echo isset($pagination)?html_entity_decode($pagination):'';
?>
<script>
$(document).ready(function(){
	var pcat_id=$('#prodcat_parent').val();
	$('#prodcat').tableDnD({
		onDrop: function (table, row) {
			fcom.displayProcessing();
			var order = $.tableDnD.serialize('id');
			order += '&pcat_id=' + pcat_id;
			fcom.ajax(fcom.makeUrl('productCategories', 'update_order'), order, function (res) {
				var ans =$.parseJSON(res);
				if(ans.status==1)
				{
					fcom.displaySuccessMessage(ans.msg);
				}else{
					fcom.displayErrorMessage(ans.msg);
				}
			});
		}
	});
});
</script>
