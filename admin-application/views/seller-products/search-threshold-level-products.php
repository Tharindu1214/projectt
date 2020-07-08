<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'listserial'=>Labels::getLabel('LBL_Sr_no.',$adminLangId),
		'product_name'=>Labels::getLabel('LBL_Product_Name',$adminLangId),	
		'selprod_stock'=>Labels::getLabel('LBL_Stock_left',$adminLangId),
		'selprod_threshold_stock_level'=>Labels::getLabel('LBL_Threshold_Stock',$adminLangId),
		'emailarchive_sent_on'=>Labels::getLabel('LBL_Last_Email_Sent',$adminLangId),
		'action' => Labels::getLabel('LBL_Action',$adminLangId),
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
	$tr->setAttribute ("id",$row['selprod_id']);

	if($row['selprod_threshold_stock_level'] < $row['selprod_stock']) {
		$tr->setAttribute ("class","fat-inactive");
	}
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'action':
				$ul = $td->appendElement("ul",array("class"=>"actions"));
				if($canEdit){
					$li = $ul->appendElement("li");
					$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 
					'title'=>Labels::getLabel('LBL_Email_Seller',$adminLangId),"onclick"=>"sendMailForm(".$row['selprod_user_id'].",".$row['selprod_id'].")"),'<i class="ion-email icon"></i>', true);
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
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
	'name' => 'frmProductSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);