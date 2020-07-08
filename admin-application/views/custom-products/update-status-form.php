<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
	<div class="sectionhead">  
		<h4><?php echo Labels::getLabel('LBL_Product_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
			<div class="tabs_nav_container responsive flat">							
				<ul class="tabs_nav">
					<li><a <?php echo ($preqId) ? "onclick='productForm( ".$preqId.", 0 );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
					<li><a <?php echo ($preqId) ? "onclick='sellerProductForm( ".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Inventory/Info',$adminLangId); ?></a></li>
					<li><a  <?php echo ($preqId) ? "onclick='customCatalogSpecifications( ".$preqId." );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specifications', $adminLangId );?></a></li>
					<?php 
						foreach($languages as $langId=>$langName){?>
							<li class="<?php echo (!$preqId) ? 'fat-inactive' : ''; ?>"><a href="javascript:void(0);" <?php echo ($preqId) ? "onclick='productLangForm( ".$preqId.",".$langId." );'" : ""; ?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
						<?php }
					?>
					<?php if(!empty($productOptions) && count($productOptions)>0) { ?>
					<li><a <?php echo ($preqId) ? "onClick='customEanUpcForm( ".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_EAN/UPC_setup',$adminLangId); ?></a></li>
					<?php } ?>
					<li><a class="active" <?php echo ($preqId) ? "onclick='updateStatusForm( ".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Change_Status',$adminLangId); ?></a></li>
				</ul>
				<div class="tabs_panel_wrap">							
					<?php 
					$frm->setFormTagAttribute('onsubmit','updateStatus(this); return(false);');
					$frm->setFormTagAttribute('class','web_form form_horizontal layout--'.$formLayout);;
					$frm->developerTags['colClassPrefix'] = 'col-md-';
					$frm->developerTags['fld_default_col'] = 12;
					echo $frm->getFormHtml(); ?>
				</div>
			
		</div>
	</div>
</section>