<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'listserial'=> Labels::getLabel('LBL_Sr._No',$adminLangId),
		'eattrgroup_identifier'=> Labels::getLabel('LBL_Identifier_Name',$adminLangId),
	);
if( $canEdit ){
	$arr_flds['action'] = Labels::getLabel('LBL_Action',$adminLangId);
}
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	$tr->setAttribute ("id",$row['eattrgroup_id']);
	
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'eattrgroup_identifier':
				if($row['eattrgroup_name']!=''){
					$td->appendElement('plaintext', array(), $row['eattrgroup_name'], true);
					$td->appendElement('br', array());
					$td->appendElement('plaintext', array(), '('.$row[$key].')', true);
				}else{
					$td->appendElement('plaintext', array(), $row[$key], true);
				}
				break;
			case 'action':
				if($canEdit){
					$ul = $td->appendElement("ul",array("class"=>"actions"));
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>CommonHelper::generateUrl('ExtraAttributes','index',array($row['eattrgroup_id'])), 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Extra_Attributes',$adminLangId)),'<i class="ion-navicon-round icon"></i>', true);
					
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"extraAttributeGroupForm(".$row['eattrgroup_id'].")"),'<i class="ion-edit icon"></i>', true);

					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>"javascript:void(0)", 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Delete',$adminLangId),"onclick"=>"deleteRecord(".$row['eattrgroup_id'].")"),'<i class="ion-android-delete icon"></i>', true);
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
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmExtraAttributeGroupSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>