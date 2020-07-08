<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'listserial'=> Labels::getLabel('LBL_Sr._No',$adminLangId),
		'prodcat_name' => Labels::getLabel('LBL_Category_Name',$adminLangId),
		'meta_identifier'=>Labels::getLabel('LBL_Identifier',$adminLangId),	
		'meta_title'=>Labels::getLabel('LBL_Title',$adminLangId),	
		'action' => Labels::getLabel('LBL_Action',$adminLangId),
	);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	$metaId = FatUtility::int($row['meta_id']);
	$recordId = FatUtility::int($row['prodcat_id']);
	$tr->setAttribute ("id",$metaId);

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
              		$innerLiEdit=$innerUl->appendElement('li');
              		
					$innerLiEdit->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 
					'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"editMetaTagFormNew($metaId,'$metaType',$recordId)"),Labels::getLabel('LBL_Edit',$adminLangId), 
					true);
				}
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

if(isset($pageCount)){
	$postedData['page']=$page;
	echo FatUtility::createHiddenFormFromData ( $postedData, array (
			'name' => 'frmMetaTagSearchPaging'
	) );
	$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
	$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
}