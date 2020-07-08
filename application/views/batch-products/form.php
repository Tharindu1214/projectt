<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frm->setFormTagAttribute('class','form form--horizontal');
$frm->setFormTagAttribute( 'onSubmit', 'setUpBatch(this); return false;' );
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
 ?>
<div class="popup__body">
	<h2>Manage Batch Products</h2>
	<ul class="tabs tabs--small    -js clearfix setactive-js">
		<li class="is-active"><a href="javascript:void(0)" onclick="batchForm()"><?php echo Labels::getLabel( 'LBL_General', $siteLangId ); ?></a></li>
		<?php 
		$inactive = ($prodgroup_id == 0) ? 'fat-inactive' : '';
		foreach($language as $lang_id => $lang_name ){ ?>
		<li class="<?php echo $inactive;?>"><a href="javascript:void(0)" <?php if( $prodgroup_id >0){ ?>onclick="batchLangForm(<?php echo $prodgroup_id; ?>, <?php echo $lang_id; ?>)" <?php } ?>><?php echo $lang_name; ?></a></li>
		<?php } ?>
		
		<li class="<?php echo $inactive;?>"><a href="javascript:void(0)" <?php if( $prodgroup_id >0){ ?> onClick="batchMediaForm(<?php echo $prodgroup_id; ?>)" <?php } ?>><?php echo Labels::getLabel('LBL_Media',$siteLangId); ?></a></li>
	</ul>
	
	<div class="col-md-12">
		<?php echo $frm->getFormHtml(); ?>
	</div>
</div>