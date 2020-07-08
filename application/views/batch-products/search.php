<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
	'listserial'=>'Sr.',
	'prodgroup_name' => Labels::getLabel('LBL_Batch_Name', $siteLangId),
	'prodgroup_active' => Labels::getLabel('LBL_Status', $siteLangId),
	'action' => Labels::getLabel('LBL_Action', $siteLangId)
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => ''));
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));
foreach ($arrListing as $sn => $row){
	$sr_no++;
	$tr = $tbl->appendElement('tr',array('class' => ''));

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no,true);
			break;
			case 'prodgroup_name':
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
			case 'prodgroup_active':
				$td->appendElement('plaintext', array(), applicationConstants::getActiveInactiveArr($siteLangId)[$row[$key]], true);
			break;
			case 'action':
				$ul = new HtmlElement( 'ul', array('class'=>'actions'), ' ', true );

				$li = $ul->appendElement( 'li', array(), '');
				$li->appendElement( 'a', array('href'=>'javascript:void(0)', 'title' => Labels::getLabel('LBL_Edit', $siteLangId), 'onClick' => 'batchForm(' .$row['prodgroup_id']. ')' ), '<i class="fa fa-edit"></i>', true );

				$li = $ul->appendElement('li', array(), '');
				$li->appendElement( 'a', array('href' => 'javascript:void(0)', 'title' => Labels::getLabel('LBL_Products', $siteLangId), 'onClick' => 'batchProductsForm(' . $row['prodgroup_id'] . ')' ), '<i class="fa fa-external-link"></i>', true );
				$td->appendElement('plaintext', array(), $ul->getHtml(), true);

			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key],true);
			break;
		}
	}
}
if ( count($arrListing) == 0 ){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Batch_found', $siteLangId));
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmCatalogProductSearchPaging') );

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'callBackJsFunc' => 'goToCatalogProductSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
