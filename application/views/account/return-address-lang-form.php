<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'returnAddressLangFrm');
$frm->setFormTagAttribute('class','form layout--'.$formLayout);
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'setReturnAddressLang(this); return(false);');

?>
<div class="row">	
	<div class="col-md-8">
		 
			<div class="tabs tabs-sm clearfix">
				<ul class="setactive-js">
					<li ><a href="javascript:void(0)" onClick="returnAddressForm()"><?php echo Labels::getLabel('LBL_General',$siteLangId); ?></a></li>
					<?php foreach($languages as $langId => $langName){?>
					<li <?php echo ($formLangId == $langId)?'class="is-active"':'';?>><a href="javascript:void(0);" onclick="returnAddressLangForm(<?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php } ?>								
				</ul>
			</div>
		 
		<?php echo $frm->getFormHtml();?>
	</div>
</div>