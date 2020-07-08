<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$mediaFrm->setFormTagAttribute("class","form form--horizontal");
?>
<div class="popup__body">
	<h2><?php echo Labels::getLabel('LBL_Manage_Batch_Products_Media', $siteLangId); ?></h2>
	<ul class="tabs tabs--small    -js clearfix setactive-js">
		<li ><a href="javascript:void(0)" onclick="batchForm()"><?php echo Labels::getLabel( 'LBL_General', $siteLangId ); ?></a></li>
		<?php
		$inactive = ($prodgroup_id == 0) ? 'fat-inactive' : '';
		foreach($language as $lang_id => $lang_name ){ ?>
		<li class="<?php echo $inactive;?>"><a href="javascript:void(0)" <?php if( $prodgroup_id >0){ ?>onclick="batchLangForm(<?php echo $prodgroup_id; ?>, <?php echo $lang_id; ?>)" <?php } ?>><?php echo $lang_name; ?></a></li>
		<?php } ?>

		<li class="is-active"><a href="javascript:void(0)" <?php if( $prodgroup_id >0){ ?> onClick="batchMediaForm(<?php echo $prodgroup_id; ?>)" <?php } ?>><?php echo Labels::getLabel('LBL_Media',$siteLangId); ?></a></li>
	</ul>

	<div class="col-md-12">
		<?php echo $mediaFrm->getFormHtml(); ?>
	</div>

	<?php if($batchImgArr){
	//CommonHelper::printArray($batchImgArr);
	?>
	<div class="col-md-12">
		<ul class="image-listing">
			<?php foreach( $batchImgArr as $batchImage ){ ?>
			<li><?php echo $language[$batchImage['afile_lang_id']]?>
				<div class="uploaded--image"><img src="<?php echo CommonHelper::generateUrl('Image', 'BatchProduct', array($batchImage['afile_record_id'],$batchImage['afile_lang_id'], 'THUMB') ); ?>"></div>
				<div class="btngroup--fix">
					<a class="btn btn--primary btn--sm" href="javascript:void(0);" onclick="removeBatchImage(<?php echo $prodgroup_id; ?>, <?php echo $batchImage['afile_lang_id']; ?>)"><?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?></a>
				</div>
			</li>
			<?php } ?>
		</ul>
		<?php ?>
	</div>
	<?php } ?>
</div>
