<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
	'listserial'=>Labels::getLabel('LBL_Sr.', $adminLangId),
	'product_identifier' => Labels::getLabel('LBL_Product', $adminLangId),
	'attrgrp_name' => Labels::getLabel('LBL_Attribute_Group', $adminLangId),
	'product_model' => Labels::getLabel('LBL_Model', $adminLangId),
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
				$td->appendElement('plaintext', array(), ''.$sr_no,true);
			break;
			case 'product_identifier':
				$td->appendElement('plaintext', array(), ''.$row['product_name'] . '<br>', true);
				$td->appendElement('plaintext', array(), '('.$row[$key].')', true);
			break;
			case 'attrgrp_name':
				$td->appendElement('plaintext', array(), ''.CommonHelper::displayNotApplicable($adminLangId, $row[$key]),true);
			break;
			case 'action':
				/* $ul = $td->appendElement("ul",array('class'=>'actions'),'<span class="caption--td">'.$val.'</span>',true);
				$li = $ul->appendElement("li"); */
				$td->appendElement('a', array('href'=>CommonHelper::generateUrl('sellerProducts','index',array($row['product_id'])), 'class'=>'btn btn--primary btn--sm','title'=>Labels::getLabel('LBL_Pick_to_Sell',$adminLangId)),
				Labels::getLabel('LBL_Pick_to_Sell',$adminLangId), true);
			break;
			default:
				$td->appendElement('plaintext', array(), ''.$row[$key],true);
			break;
		}
	}
}
if (count($arr_listing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_products_found', $adminLangId));
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmCatalogProductSearchPaging') );

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'callBackJsFunc' => 'goToCatalogProductSearchPage','adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
