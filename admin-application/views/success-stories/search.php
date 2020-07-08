<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'dragdrop'=>'',
		'listserial'=> Labels::getLabel('LBL_Sr._No',$adminLangId),
		'sstory_identifier'=> Labels::getLabel('LBL_Title',$adminLangId),
		'sstory_featured' => Labels::getLabel('LBL_Featured',$adminLangId),
		'action' => Labels::getLabel('LBL_Action',$adminLangId),
	);
if(!$canEdit){
	unset($arr_flds['dragdrop']);
}
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive','id'=>'stories'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

//$sr_no = $page==1?0:$pageSize*($page-1);
$sr_no = 0;
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	if($row['sstory_active'] == applicationConstants::ACTIVE){
		$tr->setAttribute ("id",$row['sstory_id']);
	}

	if($row['sstory_active'] != applicationConstants::ACTIVE) {
		$tr->setAttribute ("class","fat-inactive nodrag nodrop");
	}
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'dragdrop':
				if($row['sstory_active'] == applicationConstants::ACTIVE){				
					$td->appendElement('i',array('class'=>'ion-arrow-move icon'));					
					$td->setAttribute ("class",'dragHandle');
				}
			break;
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'sstory_identifier':
				if($row['sstory_title']!=''){
					$td->appendElement('plaintext', array(), $row['sstory_title'],true);
					$td->appendElement('br', array());
					$td->appendElement('plaintext', array(), '('.$row[$key].')',true);
				}else{
					$td->appendElement('plaintext', array(), $row[$key], true);
				}				
				break;
			case 'sstory_featured':
				$td->appendElement('plaintext', array(), ($row[$key] == 1) ? Labels::getLabel('LBL_Yes', $adminLangId) : Labels::getLabel('LBL_No', $adminLangId), true);
			break;
			case 'sstory_active':
					$active = "active";
					if($row['sstory_active']) {
						$active = '';
					}
					$statucAct = ($canEdit === true) ? 'toggleStatus(this)' : '';
					$str='<label id="'.$row['sstory_id'].'" class="statustab '.$active.'" onclick="'.$statucAct.'">
					  <span data-off="'. Labels::getLabel('LBL_Active', $adminLangId) .'" data-on="'. Labels::getLabel('LBL_Inactive', $adminLangId) .'" class="switch-labels"></span>
					  <span class="switch-handles"></span>
					</label>';
					$td->appendElement('plaintext', array(), $str,true);
			break;
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"));
				
				if( $canEdit ){
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"storiesForm(".$row['sstory_id'].")"),'<i class="ion-edit icon"></i>', true);
			
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>"javascript:void(0)", 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Delete',$adminLangId),"onclick"=>"deleteRecord(".$row['sstory_id'].")"),'<i class="ion-android-delete icon"></i>', true);
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
	'name' => 'frmStoriesSearchPaging'
) );
?>
<script>
$(document).ready(function(){	
	$('#stories').tableDnD({		
		onDrop: function (table, row) {
			$.mbsmessage('Updating display order....');
			var order = $.tableDnD.serialize('id');			
			fcom.ajax(fcom.makeUrl('SuccessStories', 'updateOrder'), order, function (res) {
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