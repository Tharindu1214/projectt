<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="box__head">
	<h4><?php echo Labels::getLabel('LBL_Catalog_Listing',$siteLangId); ?></h4>
	<div class="">
		<a href="<?php echo CommonHelper::generateUrl('seller','products');?>" class="btn btn--primary btn--sm"><?php echo Labels::getLabel('LBL_Back_To_Products',$siteLangId); ?></a>
		<a href="javascript:void(0);" onClick="addNewCatalogRequest()" class="btn btn--primary-border btn--sm"><?php echo Labels::getLabel('LBL_Request_to_add_catalog',$siteLangId); ?></a>
	</div>
</div>
<div class="box__body">
<?php
$arr_flds = array(
	'listserial'=>'Sr.',
	'scatrequest_reference' => Labels::getLabel('LBL_Reference_number', $siteLangId),
	'scatrequest_title' => Labels::getLabel('LBL_Title', $siteLangId),
	'scatrequest_status' => Labels::getLabel('LBL_Status', $siteLangId),
	'action' => Labels::getLabel('LBL_Action', $siteLangId)
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => ''));
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
				$td->appendElement('plaintext', array(), $sr_no,true);
			break;
			case 'scatrequest_title':
				$td->appendElement('plaintext', array(), $row[$key] . '<br>', true);
			break;
			case 'scatrequest_status':
				$td->appendElement('plaintext', array(), $catalogReqStatusArr[$row[$key]],true);
			break;
			case 'action':
				$ul = $td->appendElement("ul",array('class'=>'actions'),'',true);
				$li = $ul->appendElement("li");
				$li->appendElement('a', array('href'=>'javascript:void(0)','onClick'=>'viewRequestedCatalog('.$row['scatrequest_id'].')', 'class'=>'','title'=>Labels::getLabel('LBL_View',$siteLangId)),
				'<i class="fa fa-eye"></i>', true);
				if($row['scatrequest_status'] == User::CATALOG_REQUEST_PENDING){
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>'javascript:void(0)','onClick'=>'deleteRequestedCatalog('.$row['scatrequest_id'].')', 'class'=>'','title'=>Labels::getLabel('LBL_Delete',$siteLangId)),
				'<i class="fa fa-trash"></i>', true);
				}
				$li = $ul->appendElement("li");
				$li->appendElement('a', array('href'=>'javascript:void(0)','onClick'=>'messageForm('.$row['scatrequest_id'].')', 'class'=>'','title'=>Labels::getLabel('LBL_Messages',$siteLangId)),
				'<i class="fa fa-envelope"></i>', true);
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key],true);
			break;
		}
	}
}
echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmCatalogReqSearchPaging') );

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'callBackJsFunc' => 'goToCatalogReqSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
</div>
