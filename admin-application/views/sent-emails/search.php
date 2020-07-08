<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$arr_flds = array(
	'listserial'=>Labels::getLabel('LBL_Sr.',$adminLangId),
	'emailarchive_subject'=>Labels::getLabel('LBL_Subject',$adminLangId),
	'emailarchive_to_email'=>Labels::getLabel('LBL_Sent_To',$adminLangId),
	'emailarchive_headers'=>Labels::getLabel('LBL_Email_Headers',$adminLangId),
	'emailarchive_sent_on'=>Labels::getLabel('LBL_Sent_On',$adminLangId),
	'action' => 'Action'
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
	
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"));
				$li = $ul->appendElement("li");
				$li->appendElement('a', array('href'=>CommonHelper::generateUrl('SentEmails','view',
				array($row['emailarchive_id'])), 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_View_Details',$adminLangId)),
				'<i class="ion-eye icon"></i>',true);
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

echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmSentEmailSearchPaging') );

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
