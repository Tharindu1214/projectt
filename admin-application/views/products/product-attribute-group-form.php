<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$productAttributeGroupForm->setFormTagAttribute('class', 'web_form form_horizontal');
$productAttributeGroupForm->setFormTagAttribute('id', 'frmProductAttributeGroup');
$productAttributeGroupForm->setFormTagAttribute('onsubmit', 'p
	roductForm(0, $("#frmProductAttributeGroup select[name=\'attrgrp_id\']").val()); return(false);');

$productAttributeGroupForm->developerTags['colClassPrefix'] = 'col-md-';
$productAttributeGroupForm->developerTags['fld_default_col'] = 12;
?><section class="section">
<div class="sectionhead">

    <h4><?php echo Labels::getLabel('LBL_Product_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
 <div class="row">

<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Product_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo Labels::getLabel('LBL_Select_Attribute_Group',$adminLangId); ?>
				<?php echo $productAttributeGroupForm->getFormHtml(); ?>
			</div>
		</div>
	</div>
</div>
</div></div></section>