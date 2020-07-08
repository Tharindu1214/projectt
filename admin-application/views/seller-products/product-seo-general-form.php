<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>

<section class="section">
	<div class="sectionhead">		
		<h4><?php echo Labels::getLabel('LBL_SEO_CONTENT',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
			<div class="row">
				<div class="col-sm-12">
				<?php /* <div class="tabs_nav_container responsive flat">
						<?php require_once('sellerCatalogProductTop.php');?>
					</div> */?>		
					<div class="border-box">
					<div class="tabs_nav_container responsive">
						<?php require_once('sellerProductSeoTop.php');?>
					</div>
					<div class="tabs_nav_container responsive">
						
						<div class="tabs_panel_wrap">
							<div class="tabs_panel">				
								<?php 
								$productSeoForm->setFormTagAttribute('class', 'web_form form--horizontal');
								$productSeoForm->setFormTagAttribute('onsubmit', 'setupProductMetaTag(this); return(false);');
								$productSeoForm->developerTags['colClassPrefix'] = 'col-md-';
								$productSeoForm->developerTags['fld_default_col'] = 8; 
								
					//$customProductFrm->getField('option_name')->setFieldTagAttribute('class','mini');
								echo $productSeoForm->getFormHtml();
								?>
							</div>
						</div>
					</div>	
				</div>
			</div>
			</div>
		</div>
	</div>
</section>