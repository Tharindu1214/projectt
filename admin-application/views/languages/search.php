<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'listserial'=> Labels::getLabel('LBL_Sr._No',$adminLangId),
		'language_code'=> Labels::getLabel('LBL_Language_Code',$adminLangId),	
		'language_name'=> Labels::getLabel('LBL_Language_Name',$adminLangId),	
		'language_active'=> Labels::getLabel('LBL_Status',$adminLangId),	
		'action' =>  Labels::getLabel('LBL_Action',$adminLangId),
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
	$tr->setAttribute ("id",$row['language_id']);

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
				
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"));
				if($canEdit){
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 
					'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"editLanguageForm(".$row['language_id'].")"),'<i class="ion-edit icon"></i>', 
					true);				
				}
			break;
			case 'language_active':
					$active = "active";
					if( !$row['language_active'] ) {
						$active = '';
					}
					$statucAct = ( $canEdit === true ) ? 'toggleStatus(event,this)' : '';
					$str='<label id="'.$row['language_id'].'" class="statustab '.$active.'" onclick="'.$statucAct.'">
					  <span data-off="'. Labels::getLabel('LBL_Active', $adminLangId) .'" data-on="'. Labels::getLabel('LBL_Inactive', $adminLangId) .'" class="switch-labels"></span>
					  <span class="switch-handles"></span>
					</label>';
					$td->appendElement('plaintext', array(), $str,true);
					
					
					
					/* $str = '<div class="checkbox-switch"><input '.$active.' type="checkbox" id="" value="'.$row['language_id'].'" onclick="'.$statucAct.'"/><label for="switch'.$row['language_id'].'">Toggle</label></div>';
					$td->appendElement('plaintext', array(), $str,true); */
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key],true);
			break;
		}
	}
}
if (count($arr_listing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
}
echo $tbl->getHtml();
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmLanguageSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>