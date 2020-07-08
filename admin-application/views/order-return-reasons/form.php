<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupReason(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

?>
<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Order_Return_Reason_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">
			<div class="col-sm-12">
				<h1><?php //echo Labels::getLabel('LBL_Order_Return_Reason_Setup',$adminLangId); ?></h1>
				<div class="tabs_nav_container responsive flat">
					<ul class="tabs_nav">
						<li><a class="active" href="javascript:void(0)" onclick="editReasonForm(<?php echo $orreason_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
						<?php 
						$inactive=($orreason_id==0)?'fat-inactive':'';	
						foreach($languages as $langId=>$langName){?>
						<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($orreason_id>0){?> onclick="editReasonLangForm(<?php echo $orreason_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
						<?php } ?>
					</ul>
					<div class="tabs_panel_wrap">
						<div class="tabs_panel">
							<?php echo $frm->getFormHtml(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>