<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array();
if($canEdit){
	$arr_flds['dragdrop'] = '';
}
$arr_flds['listserial'] =  Labels::getLabel('LBL_Sr._No',$adminLangId);
$arr_flds['attr_identifier'] =  Labels::getLabel('LBL_Attribute',$adminLangId);
if($canEdit){
	$arr_flds['action'] = Labels::getLabel('LBL_Action',$adminLangId);
}
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive','id'=>'attributes'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	$tr->setAttribute ("id",$row['attr_id']);
	
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
			case 'attr_identifier':
				if($row['attr_name']!=''){
					$td->appendElement('plaintext', array(), $row['attr_name'], true);
					$td->appendElement('br', array());
				}
				$td->appendElement('plaintext', array(), '('.$row[$key].')', true);
				break;

			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"));
				if( $canEdit ){
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"langForm(".$row['attr_id'].", ". $adminDefaultLangId .")"),'<i class="ion-edit icon"></i>', true);
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
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmAttrSearchPaging'
) );
?>
<script>
$(document).ready(function(){
	var attrgrp_id=$('#attrgrp_id').val();
	$('#attributes').tableDnD({		
		onDrop: function (table, row) {
			$.mbsmessage('Updating display order....');
			var order = $.tableDnD.serialize('id');
			order += '&attrgrp_id=' + attrgrp_id;
			fcom.ajax(fcom.makeUrl('Attributes', 'updateOrder'), order, function (res) {
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