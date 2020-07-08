<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="cards-content pl-4 pr-4 ">
	<div class="tabs tabs-sm tabs--scroll clearfix">
		<ul>
			<li class="is-active"><a href="javascript:void(0)" onClick="socialPlatformForm(<?php echo $splatform_id;?>);"><?php echo Labels::getLabel('LBL_General', $siteLangId); ?></a></li>
			<?php $inactive = ($splatform_id==0)?'fat-inactive':'';
            foreach ($language as $langId => $langName) {?>
			<li class="<?php echo $inactive ; ?>"><a href="javascript:void(0)" <?php if ($splatform_id>0) {?> onClick="addLangForm(<?php echo $splatform_id;?> , <?php echo $langId;?>);" <?php }?>>
			<?php echo $langName;?></a></li>
			<?php }?>
		</ul>
	</div>
	<div class="form__subcontent">
		<?php
        $frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
        $frm->setFormTagAttribute('class', 'form form--horizontal');
        $frm->developerTags['colClassPrefix'] = 'col-lg-8 col-md-8 col-sm-';
        $frm->developerTags['fld_default_col'] = 8;
        $urlFld = $frm->getField('splatform_url');
        $urlFld->htmlAfterField = '<span class="text--small">'.Labels::getLabel('LBL_Example_Url', $siteLangId).'</span>';
        echo $frm->getFormHtml();
        ?>
	</div>
</div>
