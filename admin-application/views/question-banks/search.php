<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
	'listserial'=>Labels::getLabel('LBL_Sr._No',$adminLangId),
	'qbank_identifier'=>Labels::getLabel('LBL_Question_Bank_Name',$adminLangId),			
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
	if($row['qbank_active']==0) {
		$tr->setAttribute ("class","fat-inactive");
	}
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'qbank_identifier':
				if($row['qbank_name']!=''){
					$td->appendElement('plaintext', array(), $row['qbank_name'], true);
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
						"onclick"=>"questionBankForm(".$row['qbank_id'].")"),'<i class="ion-edit icon"></i>', true);
						
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>CommonHelper::generateUrl('Questions','index',array($row['qbank_id'])),'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Questions',$adminLangId)),
						'<i class="ion-navicon-round icon"></i>', true);	

					$li = $ul->appendElement("li");
					$li->appendElement('a', array(
						'href'=>"javascript:void(0)", 'class'=>'button small green','title'=>Labels::getLabel('LBL_Delete',$adminLangId),"onclick"=>"deleteRecord(".$row['qbank_id'].")"),
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
		'name' => 'frmQuestionBankSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>