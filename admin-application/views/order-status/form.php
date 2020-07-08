<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;	
$frm->setFormTagAttribute('onsubmit', 'setupOrderStatus(this); return(false);');
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_OrderStatus_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="border-box border-box--space">
			<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li><a class="active" href="javascript:void(0)" onclick="editOrderStatusForm(<?php echo $orderstatus_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
				<?php 
				$inactive = ($orderstatus_id == 0)?'fat-inactive':'';	
				foreach($languages as $langId=>$langName){?>
					<li class="<?php echo $inactive;?>"><a href="javascript:void(0);" <?php if($orderstatus_id>0){?> onclick="editOrderStatusLangForm(<?php echo $orderstatus_id ?>, <?php echo $langId;?>);" <?php }?>><?php echo $langName;?></a></li>
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
</section>

