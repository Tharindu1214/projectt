<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'frmAttributes');
$frm->setFormTagAttribute('class', 'web_form');
$frm->setFormTagAttribute('action', CommonHelper::generateUrl( 'Attributes','setupAttributes') );
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 4;

$numHeadingFld = $frm->getField('numeric_section_heading');
$numHeadingFld->developerTags['col'] = 12;
$numHeadingFld->value = '<h2><strong>'.Labels::getLabel('LBL_Enter_Numerics_data_below',$adminLangId).': </strong></h2>';

$textHeadingFld = $frm->getField('text_section_heading');
$textHeadingFld->developerTags['col'] = 12;
$textHeadingFld->value = '<h2><strong>'.Labels::getLabel('LBL_Enter_Textual_data_below',$adminLangId).': </strong></h2>';	
	
for( $i = 1; $i <= AttrGroupAttribute::MAX_NUMERIC_ATTRIBUTE_ROWS; $i++ ){
	$numFld = $frm->getField( 'prodnumattr_num_'.$i );
	$numFld->setWrapperAttribute( 'id' , 'prodnumattr_num_'.$i );	
}
	
for( $i = 1; $i <= AttrGroupAttribute::MAX_TEXTUAL_ATTRIBUTE_ROWS; $i++ ){
	$txtFld = $frm->getField( 'prodtxtattr_text_'.$i );
	$txtFld->setWrapperAttribute( 'id' , 'prodtxtattr_text_'.$i );	
	$txtFld->developerTags['colClassPrefix'] = 'col-md-';
	$txtFld->developerTags['col'] = 4;
	
	$identifierFld = $frm->getField( 'attr_identifier_text_'.$i );
	$identifierFld->developerTags['colClassPrefix'] = 'col-md-';
	$identifierFld->developerTags['col'] =8;
}
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Manage_Attributes',$adminLangId); ?> ---- (<?php echo $attrgrp_row['attrgrp_name']; ?>)</h1>
	<div class="tabs_nav_container responsive flat">		
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	var MAX_NUMERIC_ATTRIBUTE_ROWS = <?php echo AttrGroupAttribute::MAX_NUMERIC_ATTRIBUTE_ROWS;?>;
	var MAX_TEXTUAL_ATTRIBUTE_ROWS = <?php echo AttrGroupAttribute::MAX_TEXTUAL_ATTRIBUTE_ROWS;?>;
</script>
