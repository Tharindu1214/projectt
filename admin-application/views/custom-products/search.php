<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
	'listserial'=>'Sr.',
	'product_identifier' => Labels::getLabel('LBL_Product', $adminLangId),	
	'credential_username' => Labels::getLabel('LBL_User', $adminLangId),	
	'preq_added_on' => Labels::getLabel('LBL_Added_on', $adminLangId),	
	'preq_status' => Labels::getLabel('LBL_Status', $adminLangId),	
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
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'product_identifier':				
				$td->appendElement('plaintext', array(), $row['product_name'] . '<br>', true);
				$td->appendElement('plaintext', array(), '('.$row[$key].')', true);
			break;
			case 'preq_status':
				$td->appendElement('label', array('class'=>'label label-'.$reqStatusClassArr[$row[$key]].''), $reqStatusArr[$row[$key]],true);
			break;		
			case 'preq_added_on':
				$td->appendElement('plaintext', array(), FatDate::Format($row[$key]),true);
			break;						
			case 'action':
				if($row['preq_status']!= ProductRequest::STATUS_APPROVED){
				$ul = $td->appendElement("ul",array("class"=>"actions actions--centered"));
				$li = $ul->appendElement("li",array('class'=>'droplink'));
				
				$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
				$innerDiv = $li->appendElement('div',array('class'=>'dropwrap'));
				$innerUl = $innerDiv->appendElement('ul',array('class'=>'linksvertical'));
				
				$innerLiLinks = $innerUl->appendElement("li");
				$innerLiLinks->appendElement('a', array('href'=>'javascript:void(0)', "onclick"=>"addProductForm(".$row['preq_id'].")", 'class'=>'','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),
				Labels::getLabel('LBL_Edit',$adminLangId), true);
				
				$innerLiLinks = $innerUl->appendElement("li");
				$innerLiLinks->appendElement('a', array('href'=>'javascript:void(0)', "onclick"=>"productImagesForm(".$row['preq_id'].")", 'class'=>'','title'=>Labels::getLabel('LBL_Images',$adminLangId)),
				Labels::getLabel('LBL_Images',$adminLangId), true);

				$innerLiLinks = $innerUl->appendElement("li");
				$innerLiLinks->appendElement("a", array('title' => Labels::getLabel('LBL_Change_Status',$adminLangId),
				'onclick' => 'updateStatusForm('.$row['preq_id'].')','href'=>'javascript:void(0)'),
				Labels::getLabel('LBL_Change_Status',$adminLangId), true);		
				}				
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key],true);
			break;
		}
	}
}
if (count($arr_listing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_products_found', $adminLangId));
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmCustomProdReqSrchPaging') );

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId,'callBackJsFunc' => 'goToCustomCatalogProductSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
