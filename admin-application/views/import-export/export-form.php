<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute( 'onSubmit', 'exportData(this,'.$actionType.'); return false;' );

$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
$actionTypeArr = array(Importexport::TYPE_PRODUCTS,Importexport::TYPE_SELLER_PRODUCTS,Importexport::TYPE_USERS);
if(in_array($actionType,$actionTypeArr)){
	$startIdFld = $frm->getField('start_id');	
	$startIdFld->setWrapperAttribute( 'class' , 'range_fld');

	$endIdFld = $frm->getField('end_id');	
	$endIdFld->setWrapperAttribute( 'class' , 'range_fld');

	$batchCountFld = $frm->getField('batch_count');	
	$batchCountFld->setWrapperAttribute( 'class' , 'batch_fld');

	$batchNumberFld = $frm->getField('batch_number');	
	$batchNumberFld->setWrapperAttribute( 'class' , 'batch_fld');

	$rangeTypeFld = $frm->getField('export_data_range');
	$rangeTypeFld->setfieldTagAttribute( 'onchange' , "showHideExtraFld(this.value,".Importexport::BY_ID_RANGE.",".Importexport::BY_BATCHES.");");
}
?><section class="section">
<div class="sectionhead">
   
    <h4><?php echo $title; ?></h4>
</div>
<div class="sectionbody space">
<div class="row">


<div class="col-sm-12">
	
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a class="active" href="javascript:void(0);" onclick="exportForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Content',$adminLangId); ?></a></li>
			<?php if($displayMediaTab){?>
			<li><a href="javascript:void(0);" onclick="exportMediaForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Media',$adminLangId); ?></a></li>
			<?php }?>
			</ul>
		<div class="tabs_panel_wrap">			
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>			
		</div>
	</div>
</div>
</div></div></section>