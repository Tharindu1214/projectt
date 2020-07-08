<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$arr_flds = array(
		'listserial'=> Labels::getLabel('LBL_Sr._No',$adminLangId),
		'mshipapi_sduration_id'=>Labels::getLabel('LBL_Duration',$adminLangId),			
		'mshipapi_volume_upto'=>Labels::getLabel('LBL_Volume(cc)',$adminLangId),							
		'mshipapi_weight_upto'=>Labels::getLabel('LBL_Weight(gm)',$adminLangId),							
		'mshipapi_zip'=>Labels::getLabel('LBL_Postal_Code',$adminLangId),							
		'mshipapi_state_id'=>Labels::getLabel('LBL_State',$adminLangId),							
		'mshipapi_country_id'=>Labels::getLabel('LBL_Country',$adminLangId),							
		'mshipapi_cost'=>Labels::getLabel('LBL_Cost',$adminLangId),							
		'action' => Labels::getLabel('LBL_Action',$adminLangId),
	);
$tbl = new HtmlElement('table',array('width'=>'100%', 'class'=>'table table-responsive'));

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){ 
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no,true);
			break;
			case 'mshipapi_state_id':
				$td->appendElement('plaintext', array(), $row['state_name'],true);				
			break;
			case 'mshipapi_country_id':
				$td->appendElement('plaintext', array(), $row['country_name'],true);				
			break;
			case 'mshipapi_sduration_id':
				$td->appendElement('plaintext', array(), $row['sduration_name'],true);				
			break;			
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"));
				if($canEdit){					
					$li = $ul->appendElement("li");
					$li->appendElement('a', array(
						'href'=>'javascript:void(0)', 
						'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit',$adminLangId),
						"onclick"=>"manualShippingForm(".$row['mshipapi_id'].")"),
						'<i class="ion-edit icon"></i>', true);

					$li = $ul->appendElement("li");
					$li->appendElement('a', array(
						'href'=>"javascript:void(0)", 'class'=>'button small green', 
						'title'=>Labels::getLabel('LBL_Delete',$adminLangId),"onclick"=>"deleteRecord(".$row['mshipapi_id'].")"),
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
		'name' => 'frmManualShippingSrchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>