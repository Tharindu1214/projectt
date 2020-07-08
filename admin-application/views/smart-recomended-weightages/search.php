<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
		'listserial'=>Labels::getLabel('LBL_Sr._No',$adminLangId),
		'swsetting_name'=>Labels::getLabel('LBL_Event',$adminLangId),
		'swsetting_weightage'=>Labels::getLabel('LBL_Weightage',$adminLangId),				
	);
$tbl = new HtmlElement('table', array('class'=>'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}

$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	$tr->setAttribute ("id",$row['swsetting_key']);

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			/* case 'brand_logo':
				$td->appendElement('plaintext', array(), 
				'<img src="'.CommonHelper::generateUrl('image','brand',array($row['brand_id'],'THUMB'),CONF_WEBROOT_FRONT_URL).'">',true);
			break; */
			case 'swsetting_name':				
				$key = str_replace('#',' ',$row[$key]);
				$td->appendElement('plaintext', array(), $key, true);				
				break;						
			case 'swsetting_weightage':
				$td->appendElement('plaintext', array(), '<input '.((!$canEdit)?"disabled='disabled'":"").' type="text" name="weightage" value="'.$row[$key].'" onBlur="updateWeightage('.$row['swsetting_key'].',this.value)">', true);
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key]);
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
		'name' => 'frmSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>