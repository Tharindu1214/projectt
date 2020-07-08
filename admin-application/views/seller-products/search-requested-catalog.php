<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="sectionhead">
	<h4><?php echo Labels::getLabel('LBL_Catalog_Listing',$adminLangId); ?></h4>
	<a href="javascript:void(0);" onClick="addNewCatalogRequest()" class="themebtn btn btn--primary btn--sm"><?php echo Labels::getLabel('LBL_Request_to_add_catalog',$adminLangId); ?></a>
</div>
<div class="sectionbody">																				
<?php
$arr_flds = array(
	'listserial'=>Labels::getLabel('LBL_Sr.', $adminLangId),
	'scatrequest_reference' => Labels::getLabel('LBL_Reference_number', $adminLangId),
	'scatrequest_title' => Labels::getLabel('LBL_Title', $adminLangId),
	'scatrequest_status' => Labels::getLabel('LBL_Status', $adminLangId),	
	'action' => Labels::getLabel('LBL_Action', $adminLangId)
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table'));
$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => 'hide--mobile'));
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));
foreach ($arr_listing as $sn => $row){
	$sr_no++;
	$tr = $tbl->appendElement('tr',array('class' => ''));

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), '<span class="caption--td">'.$val.'</span>'.$sr_no,true);
			break;
			case 'scatrequest_title':
				$td->appendElement('plaintext', array(), '<span class="caption--td">'.$val.'</span>'.$row[$key] . '<br>', true);				
			break;
			case 'scatrequest_status':
				$td->appendElement('plaintext', array(), '<span class="caption--td">'.$val.'</span>'.$catalogReqStatusArr[$row[$key]],true);
			break;
			case 'action':
				$ul = $td->appendElement("ul",array('class'=>'actions'),'<span class="caption--td">'.$val.'</span>',true);
				$li = $ul->appendElement("li");
				$li->appendElement('a', array('href'=>'javascript:void(0)','onClick'=>'viewRequestedCatalog('.$row['scatrequest_id'].')', 'class'=>'','title'=>Labels::getLabel('LBL_View',$adminLangId)),
				'<i class="fa fa-eye"></i>', true);
				if($row['scatrequest_status'] == User::CATALOG_REQUEST_PENDING){
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>'javascript:void(0)','onClick'=>'deleteRequestedCatalog('.$row['scatrequest_id'].')', 'class'=>'','title'=>Labels::getLabel('LBL_Delete',$adminLangId)),
				'<i class="fa fa-trash"></i>', true);
				}
				$li = $ul->appendElement("li");
				$li->appendElement('a', array('href'=>'javascript:void(0)','onClick'=>'messageForm('.$row['scatrequest_id'].')', 'class'=>'','title'=>Labels::getLabel('LBL_Messages',$adminLangId)),
				'<i class="fa fa-envelope"></i>', true);
			break;
			default:
				$td->appendElement('plaintext', array(), '<span class="caption--td">'.$val.'</span>'.$row[$key],true);
			break;
		}
	}
}
if (count($arr_listing) == 0){
	$this->includeTemplate('_partial/no-record-found.php' , array('adminLangId'=>$adminLangId),false);
} else {
	echo $tbl->getHtml();
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmCatalogReqSearchPaging') );

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'callBackJsFunc' => 'goToCatalogReqSearchPage','adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
</div>