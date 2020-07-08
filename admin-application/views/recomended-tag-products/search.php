<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
	'listserial'	=>	Labels::getLabel('LBL_Sr_no.',$adminLangId),
	'tag_name'			=>	Labels::getLabel('LBL_Tag',$adminLangId),
	'product_name'		=>	Labels::getLabel('LBL_Product',$adminLangId),
	'tpr_weightage'	=>	Labels::getLabel('LBL_System_Weightage',$adminLangId),
	'tpr_custom_weightage'	=>	Labels::getLabel('LBL_Custom_Weightage',$adminLangId),
	'tpr_custom_weightage_valid_till'	=>	Labels::getLabel('LBL_Valid_Till_<br/>(Custom_Weightage)',$adminLangId),
	/* 'spw_is_excluded'	=>	'Is Excluded', */
);
$tbl = new HtmlElement('table', array('class'=>'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val, true);
}

$sr_no = $page==1 ? 0 : $pageSize*($page-1);
foreach ($arr_listing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'product_name':
				$product_name = $row['product_name']; 
								
				$td->appendElement( 'plaintext', array(), $product_name, true );
			break;
			case 'tpr_custom_weightage':
				$customWeigtage = new Form('customWeigtageFrm');
				$customWeigtage->setFormTagAttribute( 'onSubmit', 'return false;' );
				/* $customWeigtage->addHiddenField('','tpr_tag_id',$row['tpr_tag_id']);
				$customWeigtage->addHiddenField('','tpr_product_id',$row['tpr_product_id']); */
				$customWeigtage->addTextBox('', 'tpr_custom_weightage', $row[$key])->requirements()->setFloatPositive();
				$fld = $customWeigtage->getField('tpr_custom_weightage');
				if(!$canEdit)
				{
					$fld->setFieldTagAttribute('disabled' , 'disabled');
				}
				$fld->setFieldTagAttribute( 'onchange', 'saveData(\''.$row['tpr_tag_id'].'\',\''.$row['tpr_product_id'].'\',this)' );				
				$td->appendElement( 'plaintext', array(), $customWeigtage->getFormHtml() , true );
			break;
			case 'tpr_custom_weightage_valid_till':
				$tillDateFrm = new Form('tillDateFrm');
				$tillDateFrm->setFormTagAttribute( 'onSubmit', 'return false;' );
				/* $tillDateFrm->addHiddenField('','tpr_tag_id',$row['tpr_tag_id']);
				$tillDateFrm->addHiddenField('','tpr_product_id',$row['tpr_product_id']); */
				$tillDateFrm->addDateField( '', 'tpr_custom_weightage_valid_till', $row[$key], array('onchange' => 'saveData(\''.$row['tpr_tag_id'].'\',\''.$row['tpr_product_id'].'\',this)' , 'readonly'=>'readonly') );
				if(!$canEdit)
				{
					$fld = $tillDateFrm->getField('tpr_custom_weightage_valid_till');
					$fld->setFieldTagAttribute('disabled' , 'disabled');
				}
				$td->appendElement( 'plaintext', array(), $tillDateFrm->getFormHtml() , true );
			break;
			/* case 'spw_is_excluded':
				$checkBox = new Form('spw_is_excluded');
				$checked = ($row[$key] == 1) ? true : false;
				$checkBox->addCheckBox( '', 'spw_is_excluded', 1, array('onchange' => 'saveData(\''.$row['spw_product_id'].'\', this, \'spw_is_excluded\')'), $checked );
				if(!$canEdit)
				{
					$fld = $checkBox->getField('spw_is_excluded');
					$fld->setFieldTagAttribute('disabled' , 'disabled');
				}
				$td->appendElement( 'plaintext', array(), $checkBox->getFormHtml(), true );
			break; */
			default:
				$td->appendElement( 'plaintext', array(), $row[$key] );
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