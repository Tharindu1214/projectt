<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form form--horizontal');
$frm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
$frm->developerTags['fld_default_col'] = 6;
$frm->setFormTagAttribute( 'onSubmit', 'exportData(this,'.$actionType.'); return false;' );
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
?>

	<div class="tabs tabs-sm tabs--scroll clearfix">
		<ul>
			<li class="is-active"><a class="is-active" href="javascript:void(0);" onclick="exportForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Content',$siteLangId); ?></a></li>
			<?php if($displayMediaTab){?>
			<li><a href="javascript:void(0);" onclick="exportMediaForm('<?php echo $actionType;?>');"><?php echo Labels::getLabel('LBL_Media',$siteLangId); ?></a></li>
			<?php }?>
		</ul>
	</div>

<div class="form__subcontent">
	<?php echo $frm->getFormHtml(); ?>
</div>
