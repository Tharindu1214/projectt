<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
	'name'	=>	Labels::getLabel('LBL_Product', $siteLangId),
	'wishlist_user_counts'	=>	Labels::getLabel('LBL_User_Counts', $siteLangId)
);

$tbl = new HtmlElement('table', array('class'=>'table table--orders'));
$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => ''));
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
foreach ($arrListing as $sn => $listing){
	$sr_no++;
	$tr = $tbl->appendElement('tr',array('class' =>'' ));

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'name':
				$name = $listing['product_name']/* . ', '.$listing['selprod_id'] */;
				if( $listing['selprod_title'] != '' ){
					$name .= "<br/><strong>".Labels::getLabel('LBL_Custom_Title', $siteLangId).": </strong>".$listing['selprod_title'];
				}

				/* if( $listing['op_selprod_options'] != '' ){
					$name .= "<br/><strong>".Labels::getLabel('LBL_Options', $siteLangId).": </strong>".$listing['op_selprod_options'];
				} */

				if( $listing['brand_name'] != '' ){
					$name .= "<br/><strong>".Labels::getLabel('LBL_Brand', $siteLangId).": </strong>".$listing['brand_name'];
				}
				$td->appendElement('plaintext', array(), $name,true);
			break;

			case 'wishlist_user_counts':
				$td->appendElement('plaintext', array(), $listing['wishlist_user_counts'],true);
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
echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmMostWishListAddedProdSrchPaging') );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToMostWishListAddedProdSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>
