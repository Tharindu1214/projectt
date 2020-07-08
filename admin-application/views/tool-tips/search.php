<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$arr_flds = array(
		'listserial'=>Labels::getLabel('LBL_Sr._No',$adminLangId),
		'tooltip_key'=> Labels::getLabel('LBL_Key',$adminLangId),
		'tooltip_default_value' => Labels::getLabel('LBL_Default_Value',$adminLangId),
		'action' => Labels::getLabel('LBL_Action',$adminLangId),
	);

$tbl = new HtmlElement('table', array('class'=>'table table-responsive','id'=>'post'));
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
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
				if($canEdit){
					$li = $ul->appendElement("li",array('class'=>'droplink'));
					$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
              		$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));
              		$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));

					$innerLiEdit = $innerUl->appendElement("li",array('class'=>'droplink'));
					$innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"addtooltipLangForm(".$row['tooltip_id'].",1)"),Labels::getLabel('LBL_Edit',$adminLangId), true);
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

$postedData['page']= $page;

echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmSearchPaging'
) );

$pagingArr = array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);

$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>




