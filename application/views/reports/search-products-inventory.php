<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
	'sr'	=>	Labels::getLabel('LBL_SrNo.', $siteLangId),
	'name'	=>	Labels::getLabel('LBL_Product', $siteLangId),
	'selprod_stock'	=>	Labels::getLabel('LBL_Stock_Quantity', $siteLangId)
);

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => ''));
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));
foreach ($arrListing as $sn => $listing){
	$sr_no++;
	$tr = $tbl->appendElement('tr',array('class' =>'' ));

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'sr':
				$td->appendElement('plaintext', array(), $sr_no,true);
			break;
			case 'name':
				$name = '<div class="item__title">'.$listing['product_name'].'</div>';
				if( $listing['selprod_title'] != '' ){
					$name .= '<div class="item__sub_title"><strong>'.Labels::getLabel('LBL_Custom_Title', $siteLangId).": </strong>".$listing['selprod_title'].'</div>';
				}
                $name .= '<div class="item__brand">'.Labels::getLabel('LBL_Product_SKU', $siteLangId).": </strong>".$listing['selprod_sku'].'</div>';
				if( $listing['brand_name'] != '' ){
					$name .= '<div class="item__brand">'.Labels::getLabel('LBL_Brand', $siteLangId).": </strong>".$listing['brand_name'].'</div>';
				}

				$td->appendElement('plaintext', array(), $name,true);
			break;

			case 'selprod_stock':
				$td->appendElement('plaintext', array(), $listing['selprod_stock'],true);
			break;

			default:
				$td->appendElement('plaintext', array(), $listing[$key],true);
			break;
		}
	}
}
echo $tbl->getHtml();
if (count($arrListing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmProductInventorySrchPaging') );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToProductsInventorySearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
