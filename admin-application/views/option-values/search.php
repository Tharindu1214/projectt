<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'dragdrop'=>'',
		'optionvalue_identifier'=>Labels::getLabel('LBL_OPTION_VALUE_NAME',$adminLangId),			
		'action' => Labels::getLabel('LBL_ACTION',$adminLangId),
	);
if(!$canEdit){
	unset($arr_flds['dragdrop']);
}
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hovered', 'id'=>'optionvalues'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	$tr->setAttribute ("id",$row['optionvalue_id']);	
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'dragdrop':
				$td->appendElement('i',array('class'=>'ion-arrow-move icon'));					
				$td->setAttribute ("class",'dragHandle');
			break;
			case 'optionvalue_identifier':
				if($row['optionvalue_name']!=''){
					$td->appendElement('plaintext', array(), $row['optionvalue_name'], true);
					$td->appendElement('br', array());
					$td->appendElement('plaintext', array(), '('.$row[$key].')', true);
				}else{
					$td->appendElement('plaintext', array(), $row[$key], true);
				}
				break;						
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
				if($canEdit){
					$li = $ul->appendElement("li",array('class'=>'droplink'));
					$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
              		$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
              		$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
              		$innerLiEdit=$innerUl->appendElement('li');
					$innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 
					'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit',$adminLangId),
					"onclick"=>"optionValueForm(".$row['optionvalue_option_id'].",".$row['optionvalue_id'].")"),Labels::getLabel('LBL_Edit',$adminLangId), true);

					$innerLiDelete = $innerUl->appendElement("li");
					$innerLiDelete->appendElement('a', array('href'=>"javascript:void(0)", 
					'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Delete',$adminLangId),"onclick"=>"deleteOptionValue(".$row['optionvalue_option_id'].",".$row['optionvalue_id'].")"),Labels::getLabel('LBL_Delete',$adminLangId), true);
				}
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if (count($arr_listing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), 
	Labels::getLabel('MSG_NO_RECORD_FOUND',$adminLangId));
}
echo $tbl->getHtml();
?>
<script>
$(document).ready(function(){
	
	$('#optionvalues').tableDnD({
		onDrop: function (table, row) {
			fcom.displayProcessing();
			var order = $.tableDnD.serialize('id');			
			fcom.ajax(fcom.makeUrl('OptionValues', 'setOptionsOrder'), order, function (res) {
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