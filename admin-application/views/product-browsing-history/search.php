<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
	'listserial'	=>	Labels::getLabel('LBL_Sr._No',$adminLangId),
	'product_name'			=>	Labels::getLabel('LBL_Name',$adminLangId),
	//'shop_name'		=>	'Shop',
	'spw_weightage'	=>	Labels::getLabel('LBL_System_Weightage',$adminLangId),
	'spw_custom_weightage'	=>	Labels::getLabel('LBL_Custom_Weightage',$adminLangId),
	'spw_custom_weightage_valid_till'	=>	Labels::getLabel('LBL_Valid_Till_<br/>(Custom_Weightage)',$adminLangId),
	'spw_is_excluded'	=>	Labels::getLabel('LBL_Is_Excluded',$adminLangId),
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive'));
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
				
				if( isset( $row['options'] ) && count( $row['options'] ) ){
					$product_name .= '<br/>';
					foreach($row['options'] as $option){
						$product_name .= '<b>' . $option['option_name'] . '</b>'.': '.$option['optionvalue_name'].'<br/>';
					}
				}
				
				if( isset($row['brand_name']) && $row['brand_name'] != '' ){
					$product_name .= "<br/><b>Brand</b> : ".$row['brand_name'];
				}
				
				$td->appendElement( 'plaintext', array(), $product_name, true );
			break;
			case 'spw_custom_weightage':
				$customWeigtage = new Form('customWeigtageFrm');
				$customWeigtage->setFormTagAttribute( 'onSubmit', 'return false;' );
				$customWeigtage->addTextBox('', 'spw_custom_weightage', $row[$key])->requirements()->setFloatPositive();
				$fld = $customWeigtage->getField('spw_custom_weightage');
				$fld->setFieldTagAttribute( 'onchange', 'saveData(\''.$row['spw_selprod_code'].'\', this, \'spw_custom_weightage\')' );
				/* $customWeigtage->setFormTagAttribute('onsubmit', 'saveData(this); return false;');
				$customWeigtage->addHiddenField( '', 'spw_selprod_code', $row['spw_selprod_code'] );
				$customWeigtage->addTextBox('', 'spw_custom_weightage', $row[$key], array('onchange' => '$(this).submit(); return false;'))->requirements()->setFloatPositive(); */
				$td->appendElement( 'plaintext', array(), $customWeigtage->getFormHtml() , true );
			break;
			case 'spw_custom_weightage_valid_till':
				$tillDateFrm = new Form('tillDateFrm');
				$tillDateFrm->setFormTagAttribute( 'onSubmit', 'return false;' );
				$tillDateFrm->addDateField( '', 'spw_custom_weightage_valid_till', $row[$key], array('onchange' => 'saveData(\''.$row['spw_selprod_code'].'\', this, \'spw_custom_weightage_valid_till\')' , 'readonly'=>'readonly') );
				$td->appendElement( 'plaintext', array(), $tillDateFrm->getFormHtml() , true );
			break;
			case 'spw_is_excluded':
				$checkBox = new Form('spw_is_excluded');
				$checked = ($row[$key] == 1) ? true : false;
				$checkBox->addCheckBox( '', 'spw_is_excluded', 1, array('onchange' => 'saveData(\''.$row['spw_selprod_code'].'\', this, \'spw_is_excluded\')'), $checked );
				$td->appendElement( 'plaintext', array(), $checkBox->getFormHtml(), true );
			break;
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